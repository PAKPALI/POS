<?php

namespace App\Services\Report;

use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\CompanySetting;

class DailySalesReportService
{
    public function generateDailySalesPdf(): string
    {
        $company = CompanySetting::first();

        $sales = Sale::with('saleDetails.product')
            ->whereDate('created_at', now()->toDateString())
            ->orderBy('created_at', 'desc')
            ->get();

        $pdf = Pdf::loadView('pdf.daily-sale-report', [
            'sales' => $sales,
            'company' => $company
        ]);

        $fileName = 'daily_sales_report_' . now()->format('Y_m_d') . '_' . time() . '.pdf';

        $directory = storage_path('app/reports');

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $filePath = $directory . '/' . $fileName;

        $pdf->save($filePath);

        return $filePath;
    }
}