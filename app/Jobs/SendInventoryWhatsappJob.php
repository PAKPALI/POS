<?php

namespace App\Jobs;

use App\Jobs\Concerns\HasReliableNotificationQueue;
use App\Models\Inventory;
use App\Models\Company;
use App\Models\User;
use App\Services\SmsService;
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

class SendInventoryWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasReliableNotificationQueue;

    public $inventoryId;
    public $companyId;

    public function __construct($inventoryId, $companyId)
    {
        $this->inventoryId = $inventoryId;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        $company = Company::active()->find($this->companyId);
        if (!$company) return;
        app(CompanyContext::class)->setPublicCompany($company);

        $inventory = Inventory::with(['product', 'user'])->find($this->inventoryId);

        if (!$inventory) return;

        if (!$company->inventory_whatsapp_enabled && !$company->inventory_sms_enabled) {
            Log::info('Notifications d’inventaire WhatsApp/SMS désactivées', ['company_id' => $company->id]);
            return;
        }

        $message = $this->formatMessage($inventory);
        $smsService = app(SmsService::class);
        $recipientService = app(NotificationRecipientService::class);
        $deliveryService = app(NotificationDeliveryService::class);
        $hasFailures = false;
        if ($company->inventory_whatsapp_enabled) {
            foreach ($recipientService->users($this->companyId, 'inventory', 'whatsapp') as $user) {
                try {
                    $sent = $deliveryService->deliver(
                        $company->id, 'inventory', $inventory->id, 'inventory', 'whatsapp', $user->id,
                        function () use ($smsService, $user, $message): void {
                            $response = $smsService->sendWhatsappSms($user->phone, 'Notification Inventaire', $message, $user->country_code, 'inventory');
                            if (($response['status'] ?? false) !== true) {
                                throw new RuntimeException('Envoi WhatsApp refusé par le fournisseur.');
                            }
                        }
                    );
                    if ($sent) {
                        Log::info('Inventory WhatsApp message sent successfully', [
                            'company_id' => $company->id, 'inventory_id' => $inventory->id, 'user_id' => $user->id,
                        ]);
                    }
                } catch (Throwable $exception) {
                    $hasFailures = true;
                    Log::warning('Inventory WhatsApp not sent', [
                        'company_id' => $company->id, 'inventory_id' => $inventory->id,
                        'user_id' => $user->id, 'error' => class_basename($exception),
                    ]);
                }
            }
        }
        if ($company->inventory_sms_enabled) {
            foreach ($recipientService->users($this->companyId, 'inventory', 'sms') as $user) {
                try {
                    $deliveryService->deliver(
                        $company->id, 'inventory', $inventory->id, 'inventory', 'sms', $user->id,
                        function () use ($smsService, $user, $message): void {
                            $response = $smsService->sendSms($user->phone, $message, $user->country_code, 'inventory');
                            if (($response['status'] ?? false) !== true) {
                                throw new RuntimeException('Envoi SMS refusé par le fournisseur.');
                            }
                        }
                    );
                } catch (Throwable $exception) {
                    $hasFailures = true;
                    Log::warning('Inventory SMS message not sent', [
                        'company_id' => $company->id, 'user_id' => $user->id,
                        'inventory_id' => $inventory->id, 'error' => class_basename($exception),
                    ]);
                }
            }
        }

        if ($hasFailures) {
            throw new RuntimeException('Une ou plusieurs notifications d’inventaire ont échoué.');
        }
    }

    private function formatMessage($inventory)
    {
        $type = $inventory->type == 1 ? "ENTRÉE" : "SORTIE";
        $product = $inventory->product->name ?? 'Produit';
        $user = $inventory->user->name ?? 'Inconnu';
        $note = $inventory->note ?? '-';
        $qtyLabel = $inventory->type == 1 ? "Qté ajoutée" : "Qté retirée";

        return
            "📦 INVENTAIRE | " .
            "Type: {$type} | " .
            "Effectué par: {$user} | " .
            "Produit: {$product} | " .
            "Qté avant: {$inventory->qte_before} | " .
            "{$qtyLabel}: {$inventory->qte_added} | " .
            "Qté après: {$inventory->qte_after} | " .
            "Note: {$note} | _________".
            "Pour plus de détails, connectez-vous à PRO-SELLER pour voir les détails de l'inventaire et générer le rapport selon le filtre choisi.";
    }
}
