<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Sale;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\NotificationRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSaleEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $saleId;
    public $companyId;

    public function __construct($saleId, $companyId)
    {
        $this->saleId = $saleId;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        $company = Company::find($this->companyId);
        if (!$company) return;
        app(CompanyContext::class)->setPublicCompany($company);

        $sale = Sale::with('saleDetails.product')->find($this->saleId);

        if (!$sale) return;

        $users = app(NotificationRecipientService::class)->users($this->companyId, 'sale', 'email');

        foreach ($users as $user) {
            Mail::send('emails.sale.saleNotification', [
                'sale' => $sale,
                'company' => $company,
            ], function ($message) use ($user, $sale, $company) {
                $message->to($user->email);
                $message->subject($company->name.' — Nouvelle vente #'.$sale->code);
            });
            Log::info("Sale email sent with success to $user->email");
        }
    }
}
