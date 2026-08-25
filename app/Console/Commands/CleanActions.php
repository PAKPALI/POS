<?php

namespace App\Console\Commands;

use App\Models\Action;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanActions extends Command
{
    protected $signature = 'actions:clean
        {--days=365 : Nombre de jours à conserver}
        {--company= : Limiter le nettoyage à une compagnie}
        {--pretend : Afficher ce qui serait supprimé sans modifier la base}';

    protected $description = 'Applique une rétention sûre au journal, compagnie par compagnie';

    public function handle(): int
    {
        $days = filter_var($this->option('days'), FILTER_VALIDATE_INT);
        if ($days === false || $days < 30) {
            $this->error('La durée de conservation doit être un entier d’au moins 30 jours.');

            return Command::INVALID;
        }

        $companyOption = $this->option('company');
        $companies = Company::query()
            ->when($companyOption, fn ($query) => $query->whereKey((int) $companyOption))
            ->orderBy('id')
            ->get(['id', 'name']);

        if ($companyOption && $companies->isEmpty()) {
            $this->error('La compagnie demandée est introuvable.');

            return Command::FAILURE;
        }

        $cutoff = now()->subDays($days);
        $pretend = (bool) $this->option('pretend');
        $total = 0;

        foreach ($companies as $company) {
            $query = Action::withoutCompanyScope()
                ->where('company_id', $company->id)
                ->where('created_at', '<', $cutoff);
            $count = $query->count();

            if (! $pretend && $count > 0) {
                $query->delete();
            }

            $total += $count;
            $suffix = $pretend ? ' action(s) éligible(s)' : ' action(s) supprimée(s)';
            $this->line($company->name.' : '.$count.$suffix);
        }

        Log::info('Rétention du journal d’activité exécutée', [
            'retention_days' => $days,
            'company_id' => $companyOption ? (int) $companyOption : null,
            'pretend' => $pretend,
            'affected_actions' => $total,
        ]);
        $this->info('Total : '.$total.' action(s). Les lignes historiques sans compagnie ont été conservées.');

        return Command::SUCCESS;
    }
}
