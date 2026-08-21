<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\User;

/**
 * Service singleton request-scoped qui expose la compagnie active.
 * Les services métier ne doivent JAMAIS lire la session directement.
 */
class CompanyContext
{
    private ?Company $company = null;
    private ?CompanyUser $membership = null;
    private bool $resolved = false;

    /**
     * Set the active company for this request.
     */
    public function set(Company $company, CompanyUser $membership): void
    {
        $this->company = $company;
        $this->membership = $membership;
        $this->resolved = true;
    }

    /** Resolve a public storefront without an authenticated membership. */
    public function setPublicCompany(Company $company): void
    {
        $this->company = $company;
        $this->membership = null;
        $this->resolved = true;
    }

    /**
     * Clear the current context (e.g. on switch or logout).
     */
    public function clear(): void
    {
        $this->company = null;
        $this->membership = null;
        $this->resolved = false;
    }

    /**
     * Get the active company.
     * Throws if no company is resolved.
     */
    public function getCompany(): Company
    {
        if (!$this->company) {
            throw new \RuntimeException('No company context resolved. Make sure ResolveCompany middleware is applied.');
        }

        return $this->company;
    }

    /**
     * Get the active company ID.
     */
    public function getCompanyId(): int
    {
        return $this->getCompany()->id;
    }

    /**
     * Get the current user's membership in the active company.
     */
    public function getMembership(): CompanyUser
    {
        if (!$this->membership) {
            throw new \RuntimeException('No membership context resolved.');
        }

        return $this->membership;
    }

    /** Get the active membership, or null for a public company context. */
    public function getMembershipOrNull(): ?CompanyUser
    {
        return $this->membership;
    }

    /**
     * Get the current user's role in the active company.
     */
    public function getRole()
    {
        return $this->getMembership()->role;
    }

    /**
     * Check if the current user has a specific permission.
     */
    public function hasPermission(string $permissionKey): bool
    {
        return $this->getMembership()->hasPermission($permissionKey);
    }

    /**
     * Check if a context is currently resolved.
     */
    public function isResolved(): bool
    {
        return $this->resolved && $this->company !== null;
    }

    /**
     * Get the active company or null.
     */
    public function getCompanyOrNull(): ?Company
    {
        return $this->company;
    }
}
