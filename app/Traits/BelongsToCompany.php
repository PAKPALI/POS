<?php

namespace App\Traits;

use App\Models\Company;
use App\Services\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\App;

/**
 * Trait à ajouter sur chaque modèle métier qui porte company_id.
 *
 * - Remplit company_id automatiquement à la création
 * - Applique un scope tenant par défaut sur toutes les requêtes
 * - Fournit un scope global() pour les tâches super-admin
 *
 * Usage:
 *   class Product extends Model {
 *       use BelongsToCompany;
 *   }
 *
 *   // Toutes les requêtes sont filtrées par company_id
 *   Product::all(); // SELECT * FROM products WHERE company_id = X
 *
 *   // Accès super-admin (pas de filtre)
 *   Product::withoutCompanyScope()->all();
 */
trait BelongsToCompany
{
    /**
     * Boot the BelongsToCompany trait.
     */
    public static function bootBelongsToCompany(): void
    {
        // Auto-fill company_id à la création
        static::creating(function ($model) {
            if (is_null($model->company_id) && App::bound(CompanyContext::class)) {
                $context = App::make(CompanyContext::class);
                if ($context->isResolved()) {
                    $model->company_id = $context->getCompanyId();
                }
            }
        });

        // Scope global par défaut
        static::addGlobalScope('company', function (Builder $builder) {
            if (App::bound(CompanyContext::class)) {
                $context = App::make(CompanyContext::class);
                if ($context->isResolved()) {
                    $builder->where($builder->getModel()->getTable() . '.company_id', $context->getCompanyId());
                }
            }
        });
    }

    /**
     * Remove the company scope for super-admin queries.
     */
    public function scopeWithoutCompanyScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }

    /**
     * Scope to filter by a specific company (for cross-tenant admin tasks).
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->withoutGlobalScope('company')
            ->where($this->getTable() . '.company_id', $companyId);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
