<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$root = dirname(__DIR__);
$source = $root.'/docs/STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.html';
$output = $root.'/docs/STRATEGIE_TARIFAIRE_ABONNEMENTS_POS_AFRIQUE.pdf';

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$html = file_get_contents($source);
if ($html === false) {
    throw new RuntimeException("Impossible de lire {$source}");
}

$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

if (file_put_contents($output, $dompdf->output()) === false) {
    throw new RuntimeException("Impossible d'écrire {$output}");
}

echo $output.PHP_EOL;
