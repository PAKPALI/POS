<?php

namespace App\Jobs;

use App\Jobs\Concerns\HasReliableNotificationQueue;
use App\Models\Company;
use App\Models\Sale;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendSaleEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasReliableNotificationQueue;

    public $saleId;
    public $companyId;

    public function __construct($saleId, $companyId)
    {
        $this->saleId = $saleId;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        $company = Company::active()->find($this->companyId);
        if (!$company) return;
        app(CompanyContext::class)->setPublicCompany($company);

        if (!$company->sale_email_enabled) {
            Log::info('Notifications de vente par e-mail désactivées', ['company_id' => $company->id]);
            return;
        }

        $sale = Sale::with('saleDetails.product')->find($this->saleId);

        if (!$sale) return;

        $users = app(NotificationRecipientService::class)->users($this->companyId, 'sale', 'email');
        $deliveryService = app(NotificationDeliveryService::class);
        $hasFailures = false;

        foreach ($users as $user) {
            try {
                $sent = $deliveryService->deliver(
                    $company->id, 'sale', $sale->id, 'sale', 'email', $user->id,
                    function () use ($user, $sale, $company): void {
                        Mail::send('emails.sale.saleNotification', [
                            'sale' => $sale,
                            'company' => $company,
                        ], function ($message) use ($user, $sale, $company) {
                            $message->from(config('mail.from.address'), $company->name);
                            $message->to($user->email);
                            $message->subject($company->name.' — Nouvelle vente #'.$sale->code);
                        });
                    }
                );
                if ($sent) {
                    Log::info('Sale email sent successfully', [
                        'company_id' => $company->id,
                        'sale_id' => $sale->id,
                        'user_id' => $user->id,
                    ]);
                }
            } catch (Throwable $exception) {
                $hasFailures = true;
                Log::warning('Sale email delivery failed', [
                    'company_id' => $company->id,
                    'sale_id' => $sale->id,
                    'user_id' => $user->id,
                    'error' => class_basename($exception),
                ]);
            }
        }

        if ($hasFailures) {
            throw new RuntimeException('Une ou plusieurs notifications de vente par e-mail ont échoué.');
        }
    }
}
