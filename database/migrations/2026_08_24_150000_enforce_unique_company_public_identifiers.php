<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $usedSlugs = [];
        $usedPublicIds = [];

        DB::table('company_settings')->orderBy('id')->get()->each(function ($company) use (&$usedSlugs, &$usedPublicIds) {
            $base = Str::limit(Str::slug($company->slug ?: $company->name), 220, '');
            $base = $base !== '' ? $base : 'entreprise-'.$company->id;
            $slug = $base;
            $suffix = 2;

            while (isset($usedSlugs[mb_strtolower($slug)])) {
                $slug = $base.'-'.$suffix;
                $suffix++;
            }
            $usedSlugs[mb_strtolower($slug)] = true;

            $publicId = $company->public_id ? mb_strtolower((string) $company->public_id) : '';
            if ($publicId === '' || isset($usedPublicIds[$publicId])) {
                do {
                    $publicId = (string) Str::uuid();
                } while (isset($usedPublicIds[$publicId]));
            }
            $usedPublicIds[$publicId] = true;

            DB::table('company_settings')->where('id', $company->id)->update([
                'slug' => $slug,
                'public_id' => $publicId,
            ]);
        });

        Schema::table('company_settings', function (Blueprint $table) {
            $table->unique('slug', 'company_settings_slug_unique');
            $table->unique('public_id', 'company_settings_public_id_unique');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE company_settings MODIFY slug VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE company_settings MODIFY public_id CHAR(36) NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE company_settings MODIFY slug VARCHAR(255) NULL');
            DB::statement('ALTER TABLE company_settings MODIFY public_id CHAR(36) NULL');
        }

        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropUnique('company_settings_slug_unique');
            $table->dropUnique('company_settings_public_id_unique');
        });
    }
};
