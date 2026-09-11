<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyOnboardingService
{
    public function __construct(private CompanyProvisioner $provisioner) {}

    /** @return array{user: User, company: Company} */
    public function registerOwner(array $data): array
    {
        $result = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'country_code' => $data['country_code'] ?? 'TG',
                'status' => 1,
                'password' => Hash::make($data['password']),
                'appearance_mode' => $data['appearance_mode'] ?? 'dark',
                'accent_color' => isset($data['accent_color']) ? strtoupper($data['accent_color']) : '#3B82F6',
            ]);

            $market = app(AfricanMarketProfile::class)->forCountry($data['country_code'] ?? 'TG');
            $company = Company::create([
                'name' => $data['company_name'],
                'email' => $data['company_email'] ?? $data['email'],
                'number1' => $data['company_phone'] ?? $data['phone'] ?? 'À compléter',
                'created_by' => $user->id,
                'country_code' => $data['country_code'] ?? 'TG',
                'currency' => $market['currency'],
                'timezone' => $market['timezone'],
                'locale' => $market['locale'],
            ]);

            $this->provisioner->provision($company, $user, isset($data['default_tax']) && $data['default_tax'] !== '' ? (float) $data['default_tax'] : null);

            return compact('user', 'company');
        });

        app(PlatformAdminNotificationService::class)->newUserRegistered(
            $result['user'],
            $result['company'],
            'Propriétaire',
        );

        return $result;
    }
}
