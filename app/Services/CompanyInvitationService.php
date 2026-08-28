<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CompanyInvitationService
{
    public function create(Company $company, Role $role, string $email, User $inviter): CompanyInvitation
    {
        $email = mb_strtolower(trim($email));
        abort_unless($role->company_id === $company->id && $role->key !== 'owner', 422, 'Rôle invalide pour cette compagnie.');

        $existingUser = User::whereRaw('LOWER(email) = ?', [$email])->first();
        if ($existingUser && CompanyUser::where('company_id', $company->id)->where('user_id', $existingUser->id)->where('status', 'active')->exists()) {
            abort(422, 'Cet utilisateur appartient déjà à cette compagnie.');
        }
        if (CompanyInvitation::where('company_id', $company->id)->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')->whereNull('declined_at')->whereNull('revoked_at')->where('expires_at', '>', now())->exists()) {
            abort(422, 'Une invitation active existe déjà pour cette adresse.');
        }

        [$invitation, $token] = DB::transaction(function () use ($company, $role, $email, $inviter) {
            $token = CompanyInvitation::generateToken();
            $invitation = CompanyInvitation::create([
                'company_id' => $company->id,
                'email' => $email,
                'role_id' => $role->id,
                'invited_by' => $inviter->id,
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addHours(app(PlatformConfigurationService::class)->integer('security.invitation_expiry_hours', 48)),
            ]);
            return [$invitation, $token];
        });

        $this->send($invitation->load('company', 'role', 'inviter'), $token);
        return $invitation;
    }

    public function resend(CompanyInvitation $invitation): void
    {
        abort_if($invitation->accepted_at || $invitation->declined_at || $invitation->revoked_at, 422, 'Cette invitation est clôturée.');
        $token = CompanyInvitation::generateToken();
        $invitation->update([
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addHours(app(PlatformConfigurationService::class)->integer('security.invitation_expiry_hours', 48)),
        ]);
        $this->send($invitation->load('company', 'role', 'inviter'), $token);
    }

    public function findByToken(string $token): CompanyInvitation
    {
        return CompanyInvitation::with('company', 'role', 'inviter')
            ->where('token_hash', hash('sha256', $token))->firstOrFail();
    }

    public function accept(CompanyInvitation $invitation, User $user): CompanyUser
    {
        abort_unless($invitation->isPending(), 410, 'Cette invitation n’est plus valide.');
        abort_unless(strcasecmp($invitation->email, $user->email) === 0, 403, 'Cette invitation appartient à une autre adresse e-mail.');

        return DB::transaction(function () use ($invitation, $user) {
            $locked = CompanyInvitation::lockForUpdate()->findOrFail($invitation->id);
            abort_unless($locked->isPending(), 410, 'Cette invitation n’est plus valide.');
            $role = Role::where('company_id', $locked->company_id)->whereKey($locked->role_id)->firstOrFail();
            abort_if($role->key === 'owner', 403, 'Le rôle propriétaire ne peut pas être attribué par invitation.');

            $membership = CompanyUser::updateOrCreate(
                ['company_id' => $locked->company_id, 'user_id' => $user->id],
                ['role_id' => $role->id, 'status' => 'active', 'invited_by' => $locked->invited_by, 'joined_at' => now()]
            );
            $locked->update(['accepted_at' => now()]);
            return $membership;
        });
    }

    private function send(CompanyInvitation $invitation, string $token): void
    {
        $url = route('invitations.show', ['token' => $token]);
        try {
            $sentMessage = Mail::send('emails.user.companyInvitation', [
                'invitation' => $invitation,
                'url' => $url,
            ], function ($message) use ($invitation) {
                $message->from(config('mail.from.address'), $invitation->company->name);
                $message->to($invitation->email);
                $message->subject($invitation->company->name.' — Invitation à rejoindre l’entreprise');
            });
            $invitation->update(['last_sent_at' => now()]);
            Log::info('Invitation email accepted by SMTP', [
                'invitation_id' => $invitation->id,
                'company_id' => $invitation->company_id,
                'recipient' => $invitation->email,
                'message_id' => $sentMessage?->getMessageId(),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Invitation email sending failed', [
                'invitation_id' => $invitation->id,
                'company_id' => $invitation->company_id,
                'recipient' => $invitation->email,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }
}
