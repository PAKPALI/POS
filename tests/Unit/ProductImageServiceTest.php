<?php

namespace Tests\Unit;

use App\Services\ProductImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductImageServiceTest extends TestCase
{
    public function test_it_creates_optimized_full_and_thumbnail_variants(): void
    {
        $source = storage_path('framework/testing/product-image-service-source.jpg');
        File::ensureDirectoryExists(dirname($source));
        $image = imagecreatetruecolor(2200, 1400);
        imagefill($image, 0, 0, imagecolorallocate($image, 25, 139, 91));
        imagejpeg($image, $source, 95);
        imagedestroy($image);

        $service = app(ProductImageService::class);
        $stored = $service->store(new UploadedFile($source, 'catalogue.jpg', 'image/jpeg', null, true));

        try {
            $this->assertStringStartsWith('products/', $stored);
            $this->assertFileExists(public_path('images/'.$stored));
            $this->assertFileExists(public_path('images/products/thumbs/'.pathinfo($stored, PATHINFO_FILENAME).'.webp'));
            $this->assertSame(1600, getimagesize(public_path('images/'.$stored))[0]);
            $this->assertSame(480, getimagesize(public_path('images/products/thumbs/'.pathinfo($stored, PATHINFO_FILENAME).'.webp'))[0]);
        } finally {
            $service->delete($stored);
            File::delete($source);
        }
    }

    public function test_it_generates_variants_for_a_legacy_product_image_without_changing_the_original(): void
    {
        $legacy = 'product-image-service-legacy.jpg';
        $source = public_path('images/'.$legacy);
        File::ensureDirectoryExists(dirname($source));
        $image = imagecreatetruecolor(900, 600);
        imagefill($image, 0, 0, imagecolorallocate($image, 59, 130, 246));
        imagejpeg($image, $source, 95);
        imagedestroy($image);

        $service = app(ProductImageService::class);

        try {
            $this->assertTrue($service->optimizeLegacy($legacy));
            $this->assertFileExists($source);
            $this->assertFileExists(public_path('images/optimized/'.sha1($legacy).'.webp'));
            $this->assertFileExists(public_path('images/thumbs/'.sha1($legacy).'.webp'));
        } finally {
            $service->delete($legacy);
        }
    }
}
