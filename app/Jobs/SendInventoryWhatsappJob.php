<?php

namespace App\Jobs;

use App\Models\Inventory;
use App\Models\User;
use App\Services\SmsService;
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

    public function __construct($inventoryId)
    {
        $this->inventoryId = $inventoryId;
    }

    public function handle(): void
    {
        $inventory = Inventory::with(['product', 'user'])->find($this->inventoryId);

        if (!$inventory) return;

        $users = User::where('status', 1)->where('user_type', 2)->whereNotNull('phone')->get();
        $message = $this->formatMessage($inventory);
        $smsService = app(SmsService::class);
        foreach ($users as $user) {
            if (!$user->phone) continue;
            try {
                $response = $smsService->sendWhatsappSms($user->phone, "Notification Inventaire", $message);
                if (is_array($response) && isset($response['status']) && $response['status'] === false) {
                    Log::warning("Inventory WhatsApp not sent to $user->phone: " . ($response['message'] ?? 'Quota ou erreur'));
                    continue;
                }
                Log::info("Inventory WhatsApp message sent with success to $user->phone");
            } catch (\Exception $e) {
                Log::error("Inventory WhatsApp error: " . $e->getMessage());
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