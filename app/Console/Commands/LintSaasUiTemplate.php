<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class LintSaasUiTemplate extends Command
{
    protected $signature = 'ui:lint {paths?* : Fichiers Blade à vérifier} {--changed : Vérifie uniquement les vues modifiées depuis HEAD}';
    protected $description = 'Vérifie les garde-fous du template SaaS et la disponibilité de ses composants';

    private const COMPONENTS = [
        'action-button', 'action-group', 'alert', 'badge', 'button', 'card', 'company-card', 'detail',
        'details-list', 'empty-state', 'export-panel', 'filter-panel', 'form-actions', 'glass-panel', 'input',
        'modal', 'notice', 'page-header', 'password', 'permission-denied', 'progress', 'select', 'skeleton',
        'stat-card', 'status', 'switch', 'tab', 'table-shell', 'tabs', 'textarea',
    ];

    public function handle(): int
    {
        $errors = [];
        $componentDirectory = resource_path('views/components/ui');

        foreach (self::COMPONENTS as $component) {
            if (!File::exists($componentDirectory.DIRECTORY_SEPARATOR.$component.'.blade.php')) {
                $errors[] = "Composant UI manquant : x-ui.{$component}";
            }
        }

        $head = resource_path('views/partials/design-system-head.blade.php');
        foreach (['saas-toolkit.css', 'saas-toolkit.js', 'design-system.css'] as $asset) {
            if (!str_contains((string) File::get($head), $asset)) {
                $errors[] = "Fondation SaaS absente du partial global : {$asset}";
            }
        }

        foreach (File::files(resource_path('views/layouts')) as $layout) {
            if ($layout->getFilename() === 'navigation.blade.php') {
                continue;
            }
            if (!str_contains((string) File::get($layout->getPathname()), "partials.design-system-head")) {
                $errors[] = 'Layout sans fondation SaaS : '.$layout->getFilename();
            }
        }

        foreach ($this->paths() as $path) {
            $source = (string) File::get($path);
            $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);

            preg_match_all('/<x-ui\.([a-z0-9-]+)/i', $source, $matches);
            foreach (array_unique($matches[1] ?? []) as $component) {
                if (!in_array($component, self::COMPONENTS, true)) {
                    $errors[] = "{$relative} référence un composant UI non répertorié : x-ui.{$component}";
                }
            }

            if (str_contains($relative, 'resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'ui'.DIRECTORY_SEPARATOR)) {
                if (preg_match('/<style\b/i', $source)) {
                    $errors[] = "{$relative} contient un style local : déplacer le style dans sa feuille partagée.";
                }
                if (preg_match('/\sstyle=(?!"--saas-progress:)/i', $source)) {
                    $errors[] = "{$relative} contient un style inline non autorisé.";
                }
            }

            if (preg_match('/^\s*<!doctype/i', $source)
                && !$this->isDocumentOrPublicTemplate($relative)
                && !str_contains($source, "partials.design-system-head")) {
                $errors[] = "{$relative} est une interface autonome sans fondation SaaS.";
            }
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }
            $this->newLine();
            $this->line('Utilisez les composants x-ui et le partial design-system-head ; les variations visuelles vont dans saas-toolkit.css.');
            return self::FAILURE;
        }

        $this->info('Garde-fous UI SaaS : OK (composants, fondations et vues contrôlés).');
        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function paths(): array
    {
        $requested = array_filter((array) $this->argument('paths'));
        if ($this->option('changed')) {
            $process = new Process(['git', 'diff', '--name-only', '--diff-filter=ACMR', 'HEAD', '--', 'resources/views']);
            $process->run();
            $changed = $process->isSuccessful()
                ? preg_split('/\R/', trim($process->getOutput()))
                : [];
            $requested = array_merge($requested, $changed ?: []);
        }

        if ($requested === []) {
            return collect(File::allFiles(resource_path('views')))
                ->map(fn ($file) => $file->getPathname())
                ->filter(fn ($path) => str_ends_with($path, '.blade.php'))
                ->values()
                ->all();
        }

        return collect($requested)
            ->map(fn ($path) => str_starts_with($path, base_path()) ? $path : base_path($path))
            ->filter(fn ($path) => File::exists($path) && str_ends_with($path, '.blade.php'))
            ->values()
            ->all();
    }

    private function isDocumentOrPublicTemplate(string $relative): bool
    {
        $normalised = str_replace('\\', '/', $relative);
        return str_contains($normalised, '/views/emails/')
            || str_contains($normalised, '/views/pdf/')
            || str_contains($normalised, '/views/code/pdf.blade.php')
            || str_contains($normalised, '/views/component/inventory/pdf.blade.php')
            || str_contains($normalised, '/views/component/product/pdf.blade.php')
            || str_contains($normalised, '/views/pos/invoice.blade.php')
            || str_contains($normalised, '/views/pos/sale/history_pdf.blade.php')
            || str_contains($normalised, '/views/ecommerce/public/layout.blade.php')
            || str_contains($normalised, '/views/welcome.blade.php');
    }
}
