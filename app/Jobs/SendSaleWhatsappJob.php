<?php

namespace App\Jobs;

use App\Jobs\Concerns\HasReliableNotificationQueue;
use App\Models\Sale;
use App\Models\Company;
use App\Models\User;
use App\Services\SmsService;
use App\Services\Report\DailySalesReportService;
use App\Services\CompanyContext;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SendSaleWhatsappJob implements ShouldQueue
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

        $sale = Sale::with('saleDetails.product')->find($this->saleId);
        if (!$sale) return;
        $smsService = app(SmsService::class);
        $recipientService = app(NotificationRecipientService::class);
        $deliveryService = app(NotificationDeliveryService::class);
        $hasFailures = false;

        if (!$company->sale_whatsapp_enabled && !$company->sale_sms_enabled) {
            Log::info('Notifications de vente WhatsApp/SMS désactivées', ['company_id' => $company->id]);
            return;
        }

        //generate and upload sale report pdf to whatsapp media
        // $mediaId = $this->generateAndUploadSaleReport($smsService);

        $message = $this->formatMessage($sale);

        if ($company->sale_whatsapp_enabled) {
            foreach ($recipientService->users($this->companyId, 'sale', 'whatsapp') as $user) {
                try {
                    $sent = $deliveryService->deliver(
                        $company->id, 'sale', $sale->id, 'sale', 'whatsapp', $user->id,
                        function () use ($smsService, $user, $message): void {
                            $response = $smsService->sendWhatsappSms($user->phone, 'Notification de vente', $message);
                            if (($response['status'] ?? false) !== true) {
                                throw new RuntimeException('Envoi WhatsApp refusé par le fournisseur.');
                            }
                        }
                    );
                    if ($sent) {
                        Log::info('Sale WhatsApp message sent successfully', [
                            'company_id' => $company->id, 'sale_id' => $sale->id, 'user_id' => $user->id,
                        ]);
                    }
                } catch (Throwable $exception) {
                    $hasFailures = true;
                    Log::warning('Sale WhatsApp message not sent', [
                        'company_id' => $company->id, 'sale_id' => $sale->id,
                        'user_id' => $user->id, 'error' => class_basename($exception),
                    ]);
                }
            }
        }

        if ($company->sale_sms_enabled) {
            foreach ($recipientService->users($this->companyId, 'sale', 'sms') as $user) {
                try {
                    $deliveryService->deliver(
                        $company->id, 'sale', $sale->id, 'sale', 'sms', $user->id,
                        function () use ($smsService, $user, $message): void {
                            $response = $smsService->sendSms($user->phone, $message);
                            if (($response['status'] ?? false) !== true) {
                                throw new RuntimeException('Envoi SMS refusé par le fournisseur.');
                            }
                        }
                    );
                } catch (Throwable $exception) {
                    $hasFailures = true;
                    Log::warning('Sale SMS message not sent', [
                        'company_id' => $company->id, 'user_id' => $user->id,
                        'sale_id' => $sale->id, 'error' => class_basename($exception),
                    ]);
                }
            }
        }

        if ($hasFailures) {
            throw new RuntimeException('Une ou plusieurs notifications de vente mobiles ont échoué.');
        }
    }

    private function formatMessage($sale)
    {
        $totalProducts = $sale->saleDetails->count();
        $details = [];

        foreach ($sale->saleDetails as $detail) {
            $product = $detail->product->name ?? 'Produit';
            $qty = $detail->quantity;

            // format compact produit
            $details[] = $product . "->" . $qty;
        }

        $productsText = implode(", ", $details);

        $msg = "VENTE | ";
        $msg .= "Vendeur:" . ($sale->cashier ?? 'N/A') . " | ";
        $msg .= "Code:" . ($sale->code ?? 'N/A') . " | ";
        $msg .= "Nombre de produits:" . $totalProducts . " | ";
        // $msg .= "Details:" . $productsText . " | ";
        $msg .= "Total:" . number_format($sale->total_amount, 2) . "F CFA | ";
        $msg .= "Veuillez Vérifier votre email pour plus de détails";

        return trim($msg);
    }

    private function generateAndUploadSaleReport($smsService)
    {
        $reportService = app(DailySalesReportService::class);
        $pdfPath = $reportService->generateDailySalesPdf($this->companyId);
        $upload = $smsService->uploadWhatsappDocument($pdfPath);

        if (!$upload || !isset($upload['data']['media_id'])) {
            Log::error('Upload PDF WhatsApp échoué');
            return null;
        }

        return $upload['data']['media_id'];
    }
}
