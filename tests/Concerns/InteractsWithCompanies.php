<?php

namespace Tests\Concerns;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Role;
use App\Models\User;
use App\Services\CompanyContext;

trait InteractsWithCompanies
{
    protected Company $company;

    protected function activateCompanyFor(User $user, string $suffix = 'main'): Company
    {
        $this->company = Company::create([
            'name' => 'Entreprise Test ' . $suffix,
            'email' => "company-{$suffix}-{$user->id}@test.local",
            'number1' => '000000000',
        ]);

        $role = Role::create([
            'company_id' => $this->company->id,
            'name' => 'Propriétaire',
            'key' => 'owner',
            'is_system' => true,
        ]);

        $membership = CompanyUser::create([
            'company_id' => $this->company->id,
            'user_id' => $user->id,
            'role_id' => $role->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        app(CompanyContext::class)->set($this->company, $membership->load('role.permissions'));
        $this->withSession(['active_company_id' => $this->company->id]);

        return $this->company;
    }
}
