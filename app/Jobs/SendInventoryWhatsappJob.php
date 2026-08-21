<?php

namespace App\Jobs;

use App\Models\Inventory;
use App\Models\Company;
use App\Models\User;
use App\Services\SmsService;
use App\Services\CompanyContext;
use App\Services\NotificationRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInventoryWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $inventoryId;
    public $companyId;

    public function __construct($inventoryId, $companyId)
    {
        $this->inventoryId = $inventoryId;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        $company = Company::find($this->companyId);
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
        if ($company->inventory_whatsapp_enabled) {
            foreach ($recipientService->users($this->companyId, 'inventory', 'whatsapp') as $user) {
            try {
                $response = $smsService->sendWhatsappSms($user->phone, "Notification Inventaire", $message);
                if (is_array($response) && isset($response['status']) && $response['status'] === false) {
                    Log::warning("Inventory WhatsApp not sent to $user->phone: " . ($response['message'] ?? 'Quota ou erreur'));
                } else {
                    Log::info("Inventory WhatsApp message sent with success to $user->phone");
                }
            } catch (\Exception $e) {
                Log::error("Inventory WhatsApp error: " . $e->getMessage());
            }
            }
        }
        if ($company->inventory_sms_enabled) {
            foreach ($recipientService->users($this->companyId, 'inventory', 'sms') as $user) {
                $response = $smsService->sendSms($user->phone, $message);
                if (($response['status'] ?? false) !== true) {
                    Log::warning('Inventory SMS message not sent', [
                        'company_id' => $company->id, 'user_id' => $user->id,
                        'phone' => $user->phone, 'response' => $response,
                    ]);
                }
            }
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
