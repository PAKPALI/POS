<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$source = __DIR__ . '/documentation-saas-pos.html';
$target = __DIR__ . '/Documentation-migration-SaaS-POS.pdf';

if (!is_file($source)) {
    fwrite(STDERR, "Source HTML introuvable: {$source}\n");
    exit(1);
}

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml((string) file_get_contents($source), 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
$canvas->page_text(500, 810, 'Page {PAGE_NUM} / {PAGE_COUNT}', $font, 8, [0.35, 0.39, 0.45]);

file_put_contents($target, $dompdf->output());
fwrite(STDOUT, $target . PHP_EOL);

