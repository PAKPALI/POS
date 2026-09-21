<?php

namespace App\Http\Controllers;

use App\Services\CompanyContext;
use App\Services\EntitlementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class UserGuidePdfController extends Controller
{
    public function company(CompanyContext $context, EntitlementService $entitlements): Response
    {
        $company = $context->getCompany();

        return $this->download([
            'audience' => 'company',
            'companyName' => $company?->name ?? config('app.name'),
            'hasEcommerce' => $entitlements->feature($company, 'ecommerce'),
            'hasPromoCodes' => $entitlements->feature($company, 'promo_codes'),
            'hasSuppliers' => $entitlements->feature($company, 'suppliers'),
        ], 'guide-utilisation-'.str($company?->name ?? config('app.name'))->slug().'.pdf');
    }

    public function partner(): Response
    {
        return $this->download([
            'audience' => 'partner',
            'companyName' => config('app.name'),
            'hasEcommerce' => true,
            'hasPromoCodes' => true,
            'hasSuppliers' => true,
        ], 'guide-utilisation-'.str(config('app.name'))->slug().'.pdf');
    }

    private function download(array $data, string $filename): Response
    {
        $pdf = Pdf::loadView('pdf.user-guide', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'isHtml5ParserEnabled' => true,
            ]);

        $pdf->render();
        $pdf->getDomPDF()->getCanvas()->page_text(
            475,
            810,
            'Page {PAGE_NUM} / {PAGE_COUNT}',
            'DejaVu Sans',
            8,
            [0.38, 0.45, 0.55]
        );

        return $pdf->download($filename);
    }
}
