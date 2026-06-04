<?php

namespace App\Jobs;

use App\Models\Sale;
use App\Models\User;
use App\Services\SmsService;
use App\Services\Report\DailySalesReportService;
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

        $smsService = app(SmsService::class);

        //generate and upload sale report pdf to whatsapp media
        // $mediaId = $this->generateAndUploadSaleReport($smsService);

        $message = $this->formatMessage($sale);

        foreach ($users as $user) {
            if (!$user->phone) continue;
            // send whatsapp sms
            $smsService->sendWhatsappSms($user->phone, "Notification de vente",$message);

            // send whatsapp document
            // if($mediaId){
            //     $smsService->sendWhatsappDocument($user->phone, $mediaId, "Rapport des ventes et détails # du " . $sale->created_at->format('d-m-Y'));
            // }else{
            //     Log::error("Impossible d'envoyer le rapport de vente pour la vente #" . $sale->id);
            //     return;
            // }
            Log::info("Sale WhatsApp message sent with success to $user->phone");
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
        $msg .= "Details:" . $productsText . " | ";
        $msg .= "Total:" . number_format($sale->total_amount, 2) . "F CFA | ";
        $msg .= "Veuillez vérifier votre email pour voir liseblement les détails";

        return trim($msg);
    }

    private function generateAndUploadSaleReport($smsService)
    {
        $reportService = app(DailySalesReportService::class);
        $pdfPath = $reportService->generateDailySalesPdf();
        $upload = $smsService->uploadWhatsappDocument($pdfPath);

        if (!$upload || !isset($upload['data']['media_id'])) {
            Log::error('Upload PDF WhatsApp échoué');
            return null;
        }

        return $upload['data']['media_id'];
    }
}