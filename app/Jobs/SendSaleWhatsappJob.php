<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSaleWhatsappJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $saleId;

    public function __construct($saleId)
    {
        $this->saleId = $saleId;
    }

    public function handle(): void
    {
        $sale = Sale::with('saleDetails.product')->find($this->saleId);

        if (!$sale) return;

        $users = User::where('status', 1)->where('user_type', 2)->whereNotNull('phone')->get();

        foreach ($users as $user) {
            if (!$user->phone) continue;
            $message = $this->formatMessage($sale);
            $smsService = new SmsService();
            Log::info("Message content: $message");
            $smsService->sendWhatsappSms(
                $user->phone,
                "Notification de vente",
                $message
            );
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
        $msg .= "Total:" . number_format($sale->total_amount, 2) . "F CFA | ";
        $msg .= "Nombre de produits:" . $totalProducts . " | ";
        $msg .= "Details:" . $productsText;

        return trim($msg);
    }
}