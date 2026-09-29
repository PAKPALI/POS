<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class ProductImageService
{
    private const FULL_MAX_SIDE = 1600;
    private const THUMB_MAX_SIDE = 480;
    private const MAX_SOURCE_SIDE = 6000;
    private const MAX_SOURCE_PIXELS = 36_000_000;

    /**
     * Stores only optimized WebP derivatives. The uploaded original is never kept.
     */
    public function store(UploadedFile $upload): string
    {
        $image = $this->open($upload->getPathname());
        $filename = (string) Str::uuid();
        $stored = 'products/'.$filename.'.webp';

        try {
            $this->writeVariants($image, $stored);
        } finally {
            imagedestroy($image);
        }

        return $stored;
    }

    /**
     * Creates optimized variants for a legacy file without changing its database value.
     */
    public function optimizeLegacy(string $stored): bool
    {
        if (!$this->isSafePath($stored) || $this->isManagedPath($stored)) {
            return false;
        }

        $source = $this->sourcePath($stored);
        if (!is_file($source)) {
            return false;
        }

        $image = $this->open($source);
        try {
            $this->writeVariants($image, $stored, true);
        } finally {
            imagedestroy($image);
        }

        return true;
    }

    public function url(?string $stored, bool $thumbnail = false): string
    {
        if (!$stored || $stored === 'null' || !$this->isSafePath($stored)) {
            return asset('icons/product-placeholder.svg');
        }

        $path = $thumbnail ? $this->thumbnailPath($stored) : $this->optimizedPath($stored);
        if (!is_file($this->sourcePath($path))) {
            $path = $stored;
        }

        return asset('images/'.$path);
    }

    public function delete(?string $stored): void
    {
        if (!$stored || !$this->isSafePath($stored)) {
            return;
        }

        foreach (array_unique([$stored, $this->optimizedPath($stored), $this->thumbnailPath($stored)]) as $path) {
            File::delete($this->sourcePath($path));
        }
    }

    private function writeVariants(\GdImage $source, string $stored, bool $legacy = false): void
    {
        $fullPath = $legacy ? $this->optimizedPath($stored) : $stored;
        $this->writeWebp($source, $fullPath, self::FULL_MAX_SIDE, 82);
        $this->writeWebp($source, $this->thumbnailPath($stored), self::THUMB_MAX_SIDE, 74);
    }

    private function writeWebp(\GdImage $source, string $relativePath, int $maxSide, int $quality): void
    {
        [$width, $height] = [imagesx($source), imagesy($source)];
        $ratio = min(1, $maxSide / max($width, $height));
        $targetWidth = max(1, (int) round($width * $ratio));
        $targetHeight = max(1, (int) round($height * $ratio));
        $target = imagecreatetruecolor($targetWidth, $targetHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);
        $transparent = imagecolorallocatealpha($target, 0, 0, 0, 127);
        imagefill($target, 0, 0, $transparent);
        imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $destination = $this->sourcePath($relativePath);
        File::ensureDirectoryExists(dirname($destination));
        $written = imagewebp($target, $destination, $quality);
        imagedestroy($target);

        if (!$written) {
            throw new RuntimeException('La conversion de l’image a échoué.');
        }
    }

    private function open(string $path): \GdImage
    {
        if (!extension_loaded('gd') || !function_exists('imagewebp')) {
            throw new RuntimeException('L’optimisation des images requiert l’extension PHP GD avec le support WebP.');
        }

        $details = @getimagesize($path);
        if (!$details || $details[0] > self::MAX_SOURCE_SIDE || $details[1] > self::MAX_SOURCE_SIDE || ($details[0] * $details[1]) > self::MAX_SOURCE_PIXELS) {
            throw new RuntimeException('L’image est trop grande. Utilisez une image de 6 000 × 6 000 pixels maximum.');
        }

        $image = match ($details[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            default => false,
        };

        if (!$image instanceof \GdImage) {
            throw new RuntimeException('Cette image ne peut pas être optimisée. Choisissez un fichier JPEG, PNG, GIF ou WebP valide.');
        }

        return $this->orient($image, $path, $details[2]);
    }

    private function orient(\GdImage $image, string $path, int $imageType): \GdImage
    {
        if ($imageType !== IMAGETYPE_JPEG || !function_exists('exif_read_data')) {
            return $image;
        }

        $orientation = @exif_read_data($path)['Orientation'] ?? null;
        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => null,
        };

        if ($rotated instanceof \GdImage) {
            imagedestroy($image);
            return $rotated;
        }

        return $image;
    }

    private function optimizedPath(string $stored): string
    {
        return $this->isManagedPath($stored) ? $stored : 'optimized/'.sha1($stored).'.webp';
    }

    private function thumbnailPath(string $stored): string
    {
        if ($this->isManagedPath($stored)) {
            return 'products/thumbs/'.pathinfo($stored, PATHINFO_FILENAME).'.webp';
        }

        return 'thumbs/'.sha1($stored).'.webp';
    }

    private function isManagedPath(string $stored): bool
    {
        return str_starts_with($stored, 'products/');
    }

    private function sourcePath(string $stored): string
    {
        return public_path('images/'.$stored);
    }

    private function isSafePath(string $stored): bool
    {
        return $stored !== '' && !str_contains($stored, '..') && !str_starts_with($stored, '/') && !str_contains($stored, '\\');
    }
}
