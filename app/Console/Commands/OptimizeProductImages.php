<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Console\Command;
use Throwable;

class OptimizeProductImages extends Command
{
    protected $signature = 'products:optimize-images';
    protected $description = 'Génère des versions WebP optimisées des images produits existantes.';

    public function handle(ProductImageService $images): int
    {
        $optimized = 0;
        $skipped = 0;
        $failed = 0;

        Product::query()->whereNotNull('image')->where('image', '!=', 'null')->orderBy('id')->cursor()->each(function (Product $product) use ($images, &$optimized, &$skipped, &$failed): void {
            try {
                if ($images->optimizeLegacy((string) $product->image)) {
                    $optimized++;
                } else {
                    $skipped++;
                }
            } catch (Throwable $exception) {
                $failed++;
                $this->warn("Produit #{$product->id} : {$exception->getMessage()}");
            }
        });

        $this->info("Images optimisées : {$optimized} · ignorées : {$skipped} · erreurs : {$failed}.");
        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
