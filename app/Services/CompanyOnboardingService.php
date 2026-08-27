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
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'country_code' => $data['country_code'] ?? 'TG',
                'status' => 1,
                'password' => Hash::make($data['password']),
            ]);

            $company = Company::create([
                'name' => $data['company_name'],
                'email' => $data['company_email'] ?? $data['email'],
                'number1' => $data['company_phone'] ?? $data['phone'] ?? 'À compléter',
                'created_by' => $user->id,
                'country_code' => $data['country_code'] ?? 'TG',
            ]);

            $this->provisioner->provision($company, $user, isset($data['default_tax']) && $data['default_tax'] !== '' ? (float) $data['default_tax'] : null);

            return compact('user', 'company');
        });
    }
}
