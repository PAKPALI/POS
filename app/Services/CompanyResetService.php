<?php

namespace App\Services;

use App\Models\Company;
use App\Models\CompanyUser;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyResetService
{
    public function isOwner(Company $company, User $user): bool
    {
        $ownerId = $company->created_by ?: $company->subscriptionAccount?->owner_id;
        return (int) $ownerId === (int) $user->id && CompanyUser::query()
            ->where('company_id', $company->id)->where('user_id', $user->id)->where('status', 'active')
            ->whereHas('role', fn ($q) => $q->where('company_id', $company->id)->where('key', 'owner'))->exists();
    }

    public function definitions(): array
    {
        return [
            'categories' => ['label' => 'Catégories', 'impact' => 'Efface les catégories. Tous les produits et packs sont également supprimés, avec les historiques associés.', 'depends' => ['products', 'packs']],
            'products' => ['label' => 'Produits', 'impact' => 'Efface tous les articles, y compris archivés. Supprime aussi les packs, ventes, inventaires et commandes pour éviter des références incomplètes.', 'depends' => ['packs', 'sales', 'inventories', 'orders']],
            'packs' => ['label' => 'Packs', 'impact' => 'Efface les offres groupées et leur composition. Réinitialise également les ventes et commandes : leurs lignes peuvent contenir ces packs.', 'depends' => ['sales', 'orders']],
            'sales' => ['label' => 'Ventes et factures', 'impact' => 'Efface les ventes, lignes et factures consultables. Les statistiques de chiffre d’affaires, bénéfices et taxes repartent à zéro. Supprime les commandes et opérations de caisse. Le stock actuel n’est pas restauré automatiquement.', 'depends' => ['orders', 'transactions']],
            'inventories' => ['label' => 'Inventaires et stocks', 'impact' => 'Efface les entrées/sorties et remet à zéro les quantités des produits conservés. Efface aussi les ventes et commandes pour repartir sur un stock cohérent.', 'depends' => ['sales', 'orders']],
            'clients' => ['label' => 'Clients', 'impact' => 'Efface le carnet de clients. Les ventes et commandes sont également effacées ; les clients devront être enregistrés à nouveau.', 'depends' => ['sales', 'orders']],
            'suppliers' => ['label' => 'Fournisseurs', 'impact' => 'Efface les fournisseurs. Les produits et mouvements conservés perdent seulement leur référence fournisseur.', 'depends' => []],
            'cash_accounts' => ['label' => 'Caisses', 'impact' => 'Efface les caisses et leurs soldes, ventes et opérations. Deux caisses vides (principale et taxe) sont recréées pour permettre les prochaines ventes. Le taux de taxe est conservé.', 'depends' => ['sales', 'transactions']],
            'transactions' => ['label' => 'Opérations et soldes des caisses', 'impact' => 'Efface les entrées, sorties et transferts. Remet tous les soldes à zéro et efface aussi les ventes et commandes pour garder une comptabilité cohérente. Les caisses sont conservées sauf si vous les sélectionnez.', 'depends' => ['sales']],
            'orders' => ['label' => 'Commandes e-commerce', 'impact' => 'Efface les commandes, coordonnées de livraison et lignes. Les ventes déjà issues de commandes restent présentes sauf si « Ventes et factures » est sélectionné. La boutique et son adresse restent configurées.', 'depends' => []],
            'members' => ['label' => 'Utilisateurs de l’entreprise', 'impact' => 'Retire les autres membres et leurs accès à cette entreprise, invitations et préférences de notifications. Leur compte personnel et leurs accès aux autres entreprises restent présents. Le propriétaire conserve son accès.', 'depends' => ['invitations']],
            'roles' => ['label' => 'Rôles personnalisés', 'impact' => 'Efface les rôles créés dans cette entreprise. Retire les autres membres et invitations pour ne laisser aucun accès sans rôle. Les rôles système et le propriétaire sont conservés.', 'depends' => ['members', 'invitations']],
            'invitations' => ['label' => 'Invitations', 'impact' => 'Supprime l’historique des invitations et invalide les liens d’invitation non utilisés. Les membres déjà inscrits restent présents sauf si vous les sélectionnez.', 'depends' => []],
            'promo_codes' => ['label' => 'Codes promo clients', 'impact' => 'Efface les codes promotionnels de cette entreprise. Les clients ne pourront plus les utiliser ; les remises historiques des ventes conservées restent présentes.', 'depends' => []],
            'quota_history' => ['label' => 'Journal des achats de quotas', 'impact' => 'Retire les achats finalisés du journal visible. Les quotas restants, paiements en attente et preuves de paiement conservées par la plateforme ne changent pas. Aucun remboursement ni crédit supplémentaire.', 'depends' => []],
            'messages' => ['label' => 'Journal des SMS et WhatsApp', 'impact' => 'Efface les détails consultables des messages envoyés. Les messages déjà reçus restent sur les téléphones et les quotas consommés ne sont pas recrédités.', 'depends' => []],
            'actions' => ['label' => 'Journal d’activité', 'impact' => 'Efface les anciennes actions de cette entreprise. Une preuve séparée de cette réinitialisation reste conservée pour la sécurité du compte.', 'depends' => []],
        ];
    }

    private function rows(string $table, int $companyId): Builder
    {
        return DB::table($table)->where('company_id', $companyId);
    }

    public function catalog(Company $company, User $owner): array
    {
        $id = $company->id;
        $counts = [];
        foreach (['categories', 'sales', 'clients', 'suppliers', 'cash_accounts', 'transactions', 'orders', 'actions'] as $table) {
            $counts[$table] = $this->rows($table, $id)->count();
        }
        $counts['products'] = $this->rows('products', $id)->where('type', 1)->count();
        $counts['packs'] = $this->rows('products', $id)->where('type', 2)->count();
        $counts['inventories'] = $this->rows('inventories', $id)->count();
        if (!$counts['inventories']) $counts['inventories'] = $this->rows('products', $id)->where('qte', '<>', 0)->count();
        $counts['members'] = $this->rows('company_user', $id)->where('user_id', '<>', $owner->id)->count();
        $counts['roles'] = $this->rows('roles', $id)->where('is_system', false)->where('key', '<>', 'owner')->count();
        $counts['invitations'] = $this->rows('company_invitations', $id)->count();
        $counts['promo_codes'] = $this->rows('code_promos', $id)->count();
        $counts['quota_history'] = $this->quotaHistory($id)->count();
        $counts['messages'] = $this->rows('communication_logs', $id)->count();
        $catalog = [];
        foreach ($this->definitions() as $key => $definition) {
            if ($counts[$key] > 0) $catalog[$key] = [...$definition, 'count' => $counts[$key]];
        }
        return $catalog;
    }

    private function quotaHistory(int $companyId): Builder
    {
        return $this->rows('quota_payments', $companyId)->whereNull('reset_hidden_at')->whereIn('status', ['paid', 'failed', 'expired', 'cancelled']);
    }

    public function plan(Company $company, User $owner, array $selection): array
    {
        $catalog = $this->catalog($company, $owner);
        if (!$selection || array_diff($selection, array_keys($catalog))) {
            throw ValidationException::withMessages(['selection' => 'Choisissez des éléments présents dans cette entreprise, puis actualisez la liste si nécessaire.']);
        }
        $selected = array_fill_keys($selection, true);
        do {
            $before = count($selected);
            foreach (array_keys($selected) as $key) {
                foreach ($this->definitions()[$key]['depends'] as $dependency) {
                    if (isset($catalog[$dependency])) $selected[$dependency] = true;
                }
            }
        } while (count($selected) !== $before);
        $keys = array_keys($selected);
        sort($keys);
        return ['selection' => $keys, 'summary' => array_intersect_key($catalog, $selected), 'fingerprint' => $this->fingerprint($company->id)];
    }

    private function fingerprint(int $id): string
    {
        // Rejette une confirmation ancienne si des ventes, accès ou stocks ont changé pendant la 2FA.
        $hash = hash_init('sha256');
        foreach (['categories', 'products', 'menu_products', 'suppliers', 'clients', 'sales', 'sale_details', 'inventories', 'cash_accounts', 'transactions', 'orders', 'order_items', 'company_user', 'roles', 'company_invitations', 'code_promos', 'quota_payments', 'communication_logs', 'actions', 'settings'] as $table) {
            hash_update($hash, $table);
            $this->rows($table, $id)->orderBy('id')->chunkById(500, function ($rows) use ($hash) {
                foreach ($rows as $row) hash_update($hash, json_encode($row, JSON_THROW_ON_ERROR));
            });
        }
        return hash_final($hash);
    }

    public function execute(Company $company, User $owner, array $selection): void
    {
        // Appelé uniquement dans la transaction de consommation du challenge.
        $id = $company->id;
        $has = fn (string $key) => in_array($key, $selection, true);
        if ($has('orders')) {
            $this->rows('order_items', $id)->delete();
            $this->rows('orders', $id)->delete();
        }
        if ($has('sales')) {
            $this->rows('communication_logs', $id)->update(['sale_id' => null]);
            $this->rows('sale_details', $id)->delete();
            $this->rows('sales', $id)->delete();
        }
        if ($has('transactions')) {
            $this->rows('transactions', $id)->delete();
            $this->rows('cash_accounts', $id)->update(['balance' => 0, 'updated_at' => now()]);
        }
        if ($has('inventories')) {
            $this->rows('inventories', $id)->delete();
            $this->rows('products', $id)->update(['qte' => 0, 'updated_at' => now()]);
        }
        if ($has('products') || $has('packs')) {
            $products = $this->rows('products', $id)->when(!$has('products'), fn ($q) => $q->where('type', 2));
            $productIds = (clone $products)->select('id');
            $images = (clone $products)->whereNotNull('image')->pluck('image')->unique()->all();
            $this->rows('inventories', $id)->whereIn('product_id', clone $productIds)->delete();
            $this->rows('menu_products', $id)->where(function ($q) use ($productIds) {
                $q->whereIn('menu_id', clone $productIds)->orWhereIn('product_id', clone $productIds);
            })->delete();
            $products->delete();
            DB::afterCommit(function () use ($images) {
                foreach ($images as $image) {
                    if (!Product::withoutCompanyScope()->where('image', $image)->exists()) {
                        try { app(ProductImageService::class)->delete($image); } catch (\Throwable $e) { report($e); }
                    }
                }
            });
        }
        foreach (['categories', 'clients', 'actions'] as $table) if ($has($table)) $this->rows($table, $id)->delete();
        if ($has('suppliers')) {
            foreach (['products', 'inventories'] as $table) $this->rows($table, $id)->update(['supplier_id' => null]);
            $this->rows('suppliers', $id)->delete();
        }
        if ($has('cash_accounts')) {
            $setting = $this->rows('settings', $id)->first();
            $this->rows('settings', $id)->update(['default_cash_id' => null, 'tax_cash_id' => null]);
            $this->rows('cash_accounts', $id)->delete();
            $cashIds = [];
            foreach (['MAIN' => 'Caisse principale', 'TAX' => 'Caisse de taxe'] as $code => $name) {
                $cashIds[$code] = DB::table('cash_accounts')->insertGetId([
                    'company_id' => $id, 'code' => $code.'-'.$id, 'name' => $name, 'balance' => 0,
                    'currency' => $company->currency ?: 'XOF', 'is_default' => $code === 'MAIN', 'is_tax' => $code === 'TAX',
                    'status' => 1, 'created_by' => $owner->id, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            DB::table('settings')->updateOrInsert(['company_id' => $id], [
                'default_cash_id' => $cashIds['MAIN'], 'tax_cash_id' => $cashIds['TAX'],
                'default_tax' => $setting?->default_tax ?? 0, 'updated_at' => now(),
            ]);
        }
        if ($has('members')) {
            foreach (['notification_recipients', 'sale_invoice_preferences', 'ecommerce_managers'] as $table) {
                $this->rows($table, $id)->where('user_id', '<>', $owner->id)->delete();
            }
            $this->rows('company_user', $id)->where('user_id', '<>', $owner->id)->delete();
        }
        if ($has('invitations')) $this->rows('company_invitations', $id)->delete();
        if ($has('roles')) {
            $roles = $this->rows('roles', $id)->where('is_system', false)->where('key', '<>', 'owner');
            DB::table('permission_role')->whereIn('role_id', (clone $roles)->select('id'))->delete();
            $roles->delete();
        }
        if ($has('promo_codes')) $this->rows('code_promos', $id)->delete();
        if ($has('messages')) $this->rows('communication_logs', $id)->delete();
        if ($has('quota_history')) $this->quotaHistory($id)->update(['reset_hidden_at' => now()]);
    }
}
