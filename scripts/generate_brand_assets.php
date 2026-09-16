<?php

declare(strict_types=1);

$public = dirname(__DIR__).DIRECTORY_SEPARATOR.'public';
$icons = $public.DIRECTORY_SEPARATOR.'icons';
if (! is_dir($icons)) {
    mkdir($icons, 0775, true);
}

function lineRound($image, int $x1, int $y1, int $x2, int $y2, int $width, int $color): void
{
    imagesetthickness($image, $width);
    imageline($image, $x1, $y1, $x2, $y2, $color);
    $radius = intdiv($width, 2);
    imagefilledellipse($image, $x1, $y1, $radius * 2, $radius * 2, $color);
    imagefilledellipse($image, $x2, $y2, $radius * 2, $radius * 2, $color);
}

function brandIcon(int $size, bool $maskable = false)
{
    $scale = 4;
    $canvasSize = $size * $scale;
    $image = imagecreatetruecolor($canvasSize, $canvasSize);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
    imagefill($image, 0, 0, $transparent);
    imagealphablending($image, true);

    $navy = imagecolorallocate($image, 8, 43, 89);
    $blue = imagecolorallocate($image, 22, 119, 255);
    $white = imagecolorallocate($image, 255, 255, 255);
    $unit = $canvasSize / 128;

    if ($maskable) {
        imagefilledrectangle($image, 0, 0, $canvasSize, $canvasSize, $navy);
        $ring = $white;
        $m = $white;
        $padding = 13;
        $backgroundColor = $navy;
    } else {
        $ring = $navy;
        $m = $navy;
        $padding = 0;
        $backgroundColor = $transparent;
    }

    $cx = (int) (64 * $unit);
    $cy = (int) (64 * $unit);
    $diameter = (int) ((96 - $padding) * $unit);
    imagefilledellipse($image, $cx, $cy, $diameter, $diameter, $ring);
    if (! $maskable) {
        imagealphablending($image, false);
    }
    imagefilledellipse($image, $cx, $cy, $diameter - (int) (22 * $unit), $diameter - (int) (22 * $unit), $backgroundColor);
    lineRound($image, (int) (87 * $unit), (int) (40 * $unit), (int) (111 * $unit), (int) (16 * $unit), (int) (19 * $unit), $backgroundColor);
    imagealphablending($image, true);
    lineRound($image, (int) (35 * $unit), (int) (83 * $unit), (int) (35 * $unit), (int) (51 * $unit), (int) (12 * $unit), $m);
    lineRound($image, (int) (35 * $unit), (int) (51 * $unit), (int) (62 * $unit), (int) (75 * $unit), (int) (12 * $unit), $m);
    lineRound($image, (int) (62 * $unit), (int) (75 * $unit), (int) (99 * $unit), (int) (35 * $unit), (int) (13 * $unit), $blue);

    $output = imagecreatetruecolor($size, $size);
    imagealphablending($output, false);
    imagesavealpha($output, true);
    imagecopyresampled($output, $image, 0, 0, 0, 0, $size, $size, $canvasSize, $canvasSize);
    imagedestroy($image);

    return $output;
}

foreach ([16, 32, 180, 192, 512] as $size) {
    $image = brandIcon($size);
    $target = match ($size) {
        180 => $icons.DIRECTORY_SEPARATOR.'apple-touch-icon-180.png',
        192 => $icons.DIRECTORY_SEPARATOR.'icon-192.png',
        512 => $icons.DIRECTORY_SEPARATOR.'icon-512.png',
        default => $icons.DIRECTORY_SEPARATOR."favicon-{$size}.png",
    };
    imagepng($image, $target, 9);
    imagedestroy($image);
}

$maskable = brandIcon(512, true);
imagepng($maskable, $icons.DIRECTORY_SEPARATOR.'icon-maskable-512.png', 9);
imagedestroy($maskable);

$social = imagecreatetruecolor(1200, 630);
$background = imagecolorallocate($social, 247, 250, 255);
$navy = imagecolorallocate($social, 8, 43, 89);
imagefill($social, 0, 0, $background);
$icon = brandIcon(390);
imagecopy($social, $icon, 405, 78, 0, 0, 390, 390);
imagedestroy($icon);
$font = 'C:\\Windows\\Fonts\\arialbd.ttf';
if (is_file($font)) {
    imagettftext($social, 54, 0, 430, 520, $navy, $font, 'MAXANOU');
    imagettftext($social, 20, 0, 431, 563, $navy, $font, 'VENTE  STOCK  GESTION');
} else {
    imagestring($social, 5, 538, 500, 'MAXANOU', $navy);
    imagestring($social, 4, 438, 535, 'VENTE  STOCK  GESTION', $navy);
}
imagepng($social, $icons.DIRECTORY_SEPARATOR.'maxanou-social.png', 9);
imagedestroy($social);

$png = file_get_contents($icons.DIRECTORY_SEPARATOR.'favicon-32.png');
$ico = pack('vvv', 0, 1, 1)
    .pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($png), 22)
    .$png;
file_put_contents($public.DIRECTORY_SEPARATOR.'favicon.ico', $ico);

echo "Maxanou brand assets generated.\n";
