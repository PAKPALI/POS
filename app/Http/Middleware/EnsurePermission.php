<?php

namespace App\Http\Middleware;

use App\Services\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    private const PERMISSION_LABELS = [
        'dashboard.view' => 'au tableau de bord',
        'catalog.manage' => 'au catalogue',
        'inventory.manage' => 'à la gestion de l’inventaire',
        'sales.manage' => 'au point de vente',
        'clients.manage' => 'à la gestion des clients',
        'cash.manage' => 'à la comptabilité',
        'ecommerce.manage' => 'à la gestion de la boutique en ligne',
        'members.manage' => 'à la gestion des utilisateurs et des rôles',
        'company.manage' => 'aux paramètres de la compagnie',
        'notifications.manage' => 'aux paramètres des notifications',
        'reports.view_margin' => 'aux marges et bénéfices',
    ];

    public function __construct(private CompanyContext $context) {}

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$this->context->isResolved()) {
            abort(403, 'Veuillez sélectionner une entreprise avant de continuer.');
        }

        if (!$this->context->hasPermission($permission)) {
            $feature = self::PERMISSION_LABELS[$permission] ?? 'à cette fonctionnalité';
            abort(403, "Votre rôle ne vous donne pas accès {$feature} dans cette entreprise.");
        }

        return $next($request);
    }
}
