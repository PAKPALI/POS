<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyRoleIds = DB::table('roles')->whereNull('company_id')->pluck('id');
        if (DB::table('company_user')->whereIn('role_id', $legacyRoleIds)->exists()
            || DB::table('company_invitations')->whereIn('role_id', $legacyRoleIds)->exists()) {
            throw new RuntimeException(
                'Archivage annulé : un rôle historique sans compagnie est encore utilisé par une adhésion ou une invitation.'
            );
        }

        $this->assertMembershipRelationsAreConsistent();

        Schema::create('legacy_tenant_records', function (Blueprint $table) {
            $table->id();
            $table->string('source_table', 64);
            $table->unsignedBigInteger('source_id');
            $table->json('payload');
            $table->timestamp('archived_at');
            $table->unique(['source_table', 'source_id']);
        });

        foreach (DB::table('actions')->whereNull('company_id')->orderBy('id')->get() as $action) {
            $this->archive('actions', (int) $action->id, ['record' => (array) $action]);
        }

        foreach (DB::table('roles')->whereNull('company_id')->orderBy('id')->get() as $role) {
            $permissionIds = DB::table('permission_role')->where('role_id', $role->id)->pluck('permission_id')->all();
            $this->archive('roles', (int) $role->id, [
                'record' => (array) $role,
                'permission_ids' => $permissionIds,
            ]);
        }

        DB::table('permission_role')->whereIn('role_id', $legacyRoleIds)->delete();
        DB::table('roles')->whereNull('company_id')->delete();
        DB::table('actions')->whereNull('company_id')->delete();

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE `roles` DROP FOREIGN KEY `roles_company_id_foreign`');
        DB::statement('ALTER TABLE `roles` MODIFY `company_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `roles` ADD CONSTRAINT `roles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `company_settings` (`id`) ON DELETE CASCADE');
        DB::statement('ALTER TABLE `actions` MODIFY `company_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `roles` ADD UNIQUE INDEX `roles_id_company_unique` (`id`, `company_id`)');

        DB::statement('ALTER TABLE `company_user` DROP FOREIGN KEY `company_user_role_id_foreign`');
        DB::statement('ALTER TABLE `company_user` ADD CONSTRAINT `company_user_role_company_fk` FOREIGN KEY (`role_id`, `company_id`) REFERENCES `roles` (`id`, `company_id`) ON DELETE RESTRICT');

        DB::statement('ALTER TABLE `company_invitations` DROP FOREIGN KEY `company_invitations_role_id_foreign`');
        DB::statement('ALTER TABLE `company_invitations` ADD CONSTRAINT `invitation_role_company_fk` FOREIGN KEY (`role_id`, `company_id`) REFERENCES `roles` (`id`, `company_id`) ON DELETE RESTRICT');

        DB::statement('ALTER TABLE `notification_recipients` ADD CONSTRAINT `notification_membership_company_fk` FOREIGN KEY (`company_id`, `user_id`) REFERENCES `company_user` (`company_id`, `user_id`) ON DELETE CASCADE');
        DB::statement('ALTER TABLE `ecommerce_managers` ADD CONSTRAINT `ecommerce_manager_membership_fk` FOREIGN KEY (`company_id`, `user_id`) REFERENCES `company_user` (`company_id`, `user_id`) ON DELETE CASCADE');
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `ecommerce_managers` DROP FOREIGN KEY `ecommerce_manager_membership_fk`');
            DB::statement('ALTER TABLE `notification_recipients` DROP FOREIGN KEY `notification_membership_company_fk`');

            DB::statement('ALTER TABLE `company_invitations` DROP FOREIGN KEY `invitation_role_company_fk`');
            DB::statement('ALTER TABLE `company_invitations` ADD CONSTRAINT `company_invitations_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL');

            DB::statement('ALTER TABLE `company_user` DROP FOREIGN KEY `company_user_role_company_fk`');
            DB::statement('ALTER TABLE `company_user` ADD CONSTRAINT `company_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL');

            DB::statement('ALTER TABLE `roles` DROP INDEX `roles_id_company_unique`');
            DB::statement('ALTER TABLE `roles` DROP FOREIGN KEY `roles_company_id_foreign`');
            DB::statement('ALTER TABLE `roles` MODIFY `company_id` BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE `roles` ADD CONSTRAINT `roles_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `company_settings` (`id`) ON DELETE SET NULL');
            DB::statement('ALTER TABLE `actions` MODIFY `company_id` BIGINT UNSIGNED NULL');
        }

        if (Schema::hasTable('legacy_tenant_records')) {
            foreach (DB::table('legacy_tenant_records')->where('source_table', 'roles')->orderBy('source_id')->get() as $archive) {
                $payload = json_decode($archive->payload, true, flags: JSON_THROW_ON_ERROR);
                DB::table('roles')->insertOrIgnore($payload['record']);
                foreach ($payload['permission_ids'] ?? [] as $permissionId) {
                    DB::table('permission_role')->insertOrIgnore([
                        'role_id' => $archive->source_id,
                        'permission_id' => $permissionId,
                    ]);
                }
            }

            foreach (DB::table('legacy_tenant_records')->where('source_table', 'actions')->orderBy('source_id')->get() as $archive) {
                $payload = json_decode($archive->payload, true, flags: JSON_THROW_ON_ERROR);
                DB::table('actions')->insertOrIgnore($payload['record']);
            }

            Schema::drop('legacy_tenant_records');
        }
    }

    private function archive(string $table, int $id, array $payload): void
    {
        DB::table('legacy_tenant_records')->insert([
            'source_table' => $table,
            'source_id' => $id,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            'archived_at' => now(),
        ]);
    }

    private function assertMembershipRelationsAreConsistent(): void
    {
        $invalid = [];

        if (DB::table('company_user as membership')
            ->join('roles', 'roles.id', '=', 'membership.role_id')
            ->whereColumn('membership.company_id', '!=', 'roles.company_id')->exists()) {
            $invalid[] = 'company_user.role_id';
        }
        if (DB::table('company_invitations as invitation')
            ->join('roles', 'roles.id', '=', 'invitation.role_id')
            ->whereColumn('invitation.company_id', '!=', 'roles.company_id')->exists()) {
            $invalid[] = 'company_invitations.role_id';
        }
        if (DB::table('notification_recipients as recipient')
            ->leftJoin('company_user as membership', function ($join) {
                $join->on('membership.company_id', '=', 'recipient.company_id')
                    ->on('membership.user_id', '=', 'recipient.user_id');
            })->whereNull('membership.id')->exists()) {
            $invalid[] = 'notification_recipients.membership';
        }
        if (DB::table('ecommerce_managers as manager')
            ->leftJoin('company_user as membership', function ($join) {
                $join->on('membership.company_id', '=', 'manager.company_id')
                    ->on('membership.user_id', '=', 'manager.user_id');
            })->whereNull('membership.id')->exists()) {
            $invalid[] = 'ecommerce_managers.membership';
        }

        if ($invalid !== []) {
            throw new RuntimeException(
                'Durcissement des adhésions annulé avant modification : incohérences détectées sur '.implode(', ', $invalid).'.'
            );
        }
    }
};
