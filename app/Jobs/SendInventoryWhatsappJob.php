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
        $smsMessage = $this->formatSmsMessage($inventory);
        $smsService = app(SmsService::class);
        $recipientService = app(NotificationRecipientService::class);
        $deliveryService = app(NotificationDeliveryService::class);
        $hasFailures = false;
        if ($company->inventory_whatsapp_enabled) {
            $whatsappRecipients = $recipientService->users($this->companyId, 'inventory', 'whatsapp');
            if ($whatsappRecipients->isEmpty()) {
                Log::warning('WhatsApp inventaire activé sans destinataire sélectionné', ['company_id' => $company->id]);
            }
            foreach ($whatsappRecipients as $user) {
                try {
                    $sent = $deliveryService->deliver(
                        $company->id, 'inventory', $inventory->id, 'inventory', 'whatsapp', $user->id,
                        function () use ($smsService, $user, $message): void {
                            $response = $smsService->sendWhatsappSms($user->phone, 'Notification Inventaire', $message, $user->country_code, 'inventory');
                            if (($response['status'] ?? false) !== true) {
                                throw new RuntimeException($response['message'] ?? 'Envoi WhatsApp refusé par le fournisseur.');
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
                        'user_id' => $user->id,
                        'error' => class_basename($exception),
                        'message' => $exception->getMessage(),
                    ]);
                }
            }
        }
        if ($company->inventory_sms_enabled) {
            $smsRecipients = $recipientService->users($this->companyId, 'inventory', 'sms');
            if ($smsRecipients->isEmpty()) {
                Log::warning('SMS inventaire activé sans destinataire sélectionné', ['company_id' => $company->id]);
            }
            foreach ($smsRecipients as $user) {
                try {
                    $deliveryService->deliver(
                        $company->id, 'inventory', $inventory->id, 'inventory', 'sms', $user->id,
                        function () use ($smsService, $user, $smsMessage): void {
                            $response = $smsService->sendSms($user->phone, $smsMessage, $user->country_code, 'inventory');
                            if (($response['status'] ?? false) !== true) {
                                throw new RuntimeException($response['message'] ?? 'Envoi SMS refusé par le fournisseur.');
                            }
                        }
                    );
                } catch (Throwable $exception) {
                    $hasFailures = true;
                    Log::warning('Inventory SMS message not sent', [
                        'company_id' => $company->id, 'user_id' => $user->id,
                        'inventory_id' => $inventory->id,
                        'error' => class_basename($exception),
                        'message' => $exception->getMessage(),
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

    private function formatSmsMessage($inventory): string
    {
        $type = $inventory->type == 1 ? 'ENTREE' : 'SORTIE';
        $product = mb_substr((string) ($inventory->product->name ?? 'Produit'), 0, 30);
        $user = mb_substr((string) ($inventory->user->name ?? 'Inconnu'), 0, 20);
        $movement = $inventory->type == 1 ? '+' : '-';

        return "INVENTAIRE {$type} | {$product} | {$inventory->qte_before} -> {$inventory->qte_after} "
            ."({$movement}{$inventory->qte_added}) | Par: {$user}";
    }
}
