<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Sale;
use App\Services\CompanyContext;
use App\Services\SaleInvoiceDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCustomerInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public int $tries = 3;

    public function __construct(public int $saleId, public int $companyId, public string $channel) {}

    public function handle(SaleInvoiceDeliveryService $delivery): void
    {
        $company = Company::active()->find($this->companyId);
        if (!$company) return;
        app(CompanyContext::class)->setPublicCompany($company);
        $sale = Sale::with(['client', 'saleDetails.product'])->find($this->saleId);
        if (!$sale?->client?->phone) return;
        $whatsapp = $this->channel === 'whatsapp' && (bool) $company->invoice_whatsapp_enabled && $company->whatsapp_count > 0;
        $sms = $this->channel === 'sms' && (bool) $company->invoice_sms_enabled && $company->sms_count > 0;
        if ($whatsapp || $sms) $delivery->deliver($sale, $sale->client->phone, $sale->client->country_code ?: $company->country_code, $whatsapp, $sms);
    }
}
