<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use League\CommonMark\CommonMarkConverter;

$source = __DIR__.'/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.md';
$target = __DIR__.'/CAHIER_DES_CHARGES_DESIGN_SYSTEM_UI_UX.pdf';

if (!is_file($source)) {
    fwrite(STDERR, "Source Markdown introuvable : {$source}\n");
    exit(1);
}

$converter = new CommonMarkConverter([
    'html_input' => 'strip',
    'allow_unsafe_links' => false,
]);

$content = (string) $converter->convert((string) file_get_contents($source));
$date = date('d/m/Y');

$html = <<<HTML
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Cahier des charges — Design System UI/UX</title>
    <style>
        @page { margin: 22mm 16mm 20mm; }
        body { font-family: "DejaVu Sans", sans-serif; color: #243044; font-size: 10pt; line-height: 1.48; }
        .cover { text-align: center; padding-top: 105px; page-break-after: always; }
        .cover .eyebrow { color: #d97706; font-size: 11pt; font-weight: bold; letter-spacing: 1.3px; text-transform: uppercase; }
        .cover h1 { color: #111827; font-size: 29pt; line-height: 1.16; margin: 24px 0 14px; }
        .cover .subtitle { color: #526176; font-size: 13.5pt; margin: 0 auto 42px; width: 84%; }
        .cover .badge { display: inline-block; color: #fff; background: #d97706; border-radius: 18px; padding: 8px 20px; font-weight: bold; }
        .cover .date { color: #718096; margin-top: 75px; font-size: 10pt; }
        h1 { color: #111827; font-size: 21pt; margin: 0 0 18px; }
        h2 { color: #172554; font-size: 16pt; border-bottom: 2px solid #f2a64f; padding-bottom: 5px; margin: 24px 0 10px; page-break-after: avoid; }
        h3 { color: #263e63; font-size: 12.5pt; margin: 18px 0 7px; page-break-after: avoid; }
        h4 { color: #9a4d0b; font-size: 11pt; margin: 15px 0 6px; page-break-after: avoid; }
        p { margin: 6px 0 9px; }
        ul, ol { margin: 5px 0 11px 20px; padding: 0; }
        li { margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 8.7pt; }
        th { color: #fff; background: #172554; text-align: left; padding: 7px; }
        td { border: 1px solid #cbd5e1; padding: 7px; vertical-align: top; }
        tr:nth-child(even) td { background: #f8fafc; }
        code { color: #9a3412; background: #fff7ed; font-family: "DejaVu Sans Mono", monospace; font-size: 8.5pt; }
        pre { background: #f1f5f9; border-left: 4px solid #e68a20; padding: 10px; white-space: pre-wrap; }
        blockquote { border-left: 4px solid #e68a20; color: #526176; margin-left: 0; padding-left: 12px; }
        strong { color: #111827; }
        img { display: block; width: 100%; max-width: 100%; height: auto; margin: 12px auto 8px; border: 1px solid #cbd5e1; border-radius: 8px; page-break-inside: avoid; }
    </style>
</head>
<body>
    <section class="cover">
        <div class="eyebrow">Plateforme POS SaaS multi-entreprises</div>
        <h1>Design System<br>et harmonisation UI/UX</h1>
        <div class="subtitle">Une expérience moderne, fluide et professionnelle fondée sur un glassmorphisme maîtrisé, des animations discrètes et une couleur dominante personnalisable.</div>
        <div class="badge">Cahier des charges — Version 1.5 validée</div>
        <div class="date">Généré le {$date}</div>
    </section>
    {$content}
</body>
</html>
HTML;

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$options->set('isRemoteEnabled', false);
$options->set('isHtml5ParserEnabled', true);
$options->set('chroot', __DIR__);

$dompdf = new Dompdf($options);
$dompdf->setBasePath(__DIR__);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$canvas = $dompdf->getCanvas();
$font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
$canvas->page_text(410, 812, 'Design System UI/UX  •  Page {PAGE_NUM} / {PAGE_COUNT}', $font, 7.5, [0.39, 0.45, 0.55]);

file_put_contents($target, $dompdf->output());
fwrite(STDOUT, $target.PHP_EOL);
