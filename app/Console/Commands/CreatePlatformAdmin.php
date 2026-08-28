<?php

namespace App\Console\Commands;

use App\Models\PlatformAdmin;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CreatePlatformAdmin extends Command
{
    protected $signature = 'platform-admin:create
        {--name= : Nom complet}
        {--email= : Adresse e-mail}
        {--from-user= : Copier l’identité et le mot de passe chiffré d’un utilisateur POS existant}
        {--password= : Mot de passe initial (éviter cette option dans l’historique du terminal)}';

    protected $description = 'Créer ou mettre à jour le super-administrateur de la plateforme SaaS';

    public function handle(): int
    {
        if ($sourceEmail = $this->option('from-user')) {
            $user = User::where('email', mb_strtolower(trim((string) $sourceEmail)))->first();
            if (!$user) {
                $this->error('Utilisateur POS introuvable.');
                return self::FAILURE;
            }

            PlatformAdmin::updateOrCreate(
                ['email' => mb_strtolower($user->email)],
                [
                    'name' => $user->name,
                    'password' => $user->password,
                    'role' => 'super_admin',
                    'is_active' => true,
                    'must_change_password' => true,
                    'remember_token' => Str::random(60),
                ]
            );

            $this->info('Compte POS promu comme super-administrateur : '.$user->email);
            $this->warn('Son mot de passe doit être changé à la première connexion plateforme.');
            return self::SUCCESS;
        }

        $name = trim((string) ($this->option('name') ?: env('PLATFORM_ADMIN_NAME') ?: $this->ask('Nom complet')));
        $email = mb_strtolower(trim((string) ($this->option('email') ?: env('PLATFORM_ADMIN_EMAIL') ?: $this->ask('Adresse e-mail'))));
        $password = (string) ($this->option('password') ?: env('PLATFORM_ADMIN_PASSWORD') ?: $this->secret('Mot de passe initial'));

        $validator = validator(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()->symbols()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            return self::FAILURE;
        }

        $existing = PlatformAdmin::where('email', $email)->first();
        if ($existing && !$this->confirm('Ce compte existe déjà. Voulez-vous réinitialiser son accès ?', false)) {
            $this->warn('Aucune modification effectuée.');
            return self::SUCCESS;
        }

        $admin = PlatformAdmin::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
                'must_change_password' => true,
                'remember_token' => Str::random(60),
            ]
        );

        $this->info('Super-administrateur plateforme prêt : '.$admin->email);
        $this->warn('Le changement du mot de passe initial reste obligatoire avant la mise en production.');

        return self::SUCCESS;
    }
}
