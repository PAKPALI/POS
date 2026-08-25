<?php

namespace App\Jobs;

use App\Jobs\Concerns\HasReliableNotificationQueue;
use App\Models\Company;
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

class SendMarginEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasReliableNotificationQueue;
    public $productName;
    public $margin;
    public $newQte;
    public $companyId;

    public ?int $productId;
    public ?int $saleId;

    public function __construct($productName,$margin,$newQte,$companyId, ?int $productId = null, ?int $saleId = null) {
        $this->productName = $productName;
        $this->margin = $margin;
        $this->newQte = $newQte;
        $this->companyId = $companyId;
        $this->productId = $productId;
        $this->saleId = $saleId;
    }

    public function handle(): void
    {
        try {
            $company = Company::active()->find($this->companyId);
            if (!$company) return;
            app(CompanyContext::class)->setPublicCompany($company);
            if (!$company->inventory_email_enabled) {
                Log::info('Notifications d’inventaire par e-mail désactivées', ['company_id' => $company->id]);
                return;
            }
            $users = app(NotificationRecipientService::class)->users($this->companyId, 'inventory', 'email');
            $deliveryService = app(NotificationDeliveryService::class);
            $eventKey = $this->saleId && $this->productId
                ? $this->saleId.':'.$this->productId
                : hash('sha256', $this->productName.'|'.$this->margin.'|'.$this->newQte);
            $hasFailures = false;

            $text = "Le produit '" . strtoupper($this->productName) ."' a atteint sa marge de sécurité (" .$this->margin . ")";
            $text2 = "La nouvelle quantité du produit : " .$this->newQte;

            foreach ($users as $user) {
                try {
                    $sent = $deliveryService->deliver(
                        $company->id, 'stock_margin', $eventKey, 'inventory', 'email', $user->id,
                        function () use ($user, $company, $text, $text2): void {
                            Mail::send(
                                'emails.user.marginMail',
                                [
                                    'user_name' => $user->name,
                                    'email' => $user->email,
                                    'text' => $text,
                                    'text2' => $text2,
                                    'product_name' => $this->productName,
                                    'company' => $company,
                                ],
                                function ($message) use ($user, $company) {
                                    $message->to($user->email);
                                    $message->subject($company->name.' — Alerte de stock');
                                }
                            );
                        }
                    );
                    if ($sent) {
                        Log::info('Margin email sent successfully', [
                            'company_id' => $company->id, 'user_id' => $user->id, 'product_id' => $this->productId,
                        ]);
                    }
                } catch (Throwable $exception) {
                    $hasFailures = true;
                    Log::warning('Margin email delivery failed', [
                        'company_id' => $company->id, 'user_id' => $user->id,
                        'product_id' => $this->productId, 'error' => class_basename($exception),
                    ]);
                }
            }

            if ($hasFailures) {
                throw new RuntimeException('Une ou plusieurs alertes de stock ont échoué.');
            }
        } catch (\Throwable $e) {
            Log::error('SendMarginEmailJob Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }
}
