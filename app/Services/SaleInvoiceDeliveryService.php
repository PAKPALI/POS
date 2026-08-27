<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use RuntimeException;

class SaleInvoiceDeliveryService
{
    public function __construct(private SmsService $sms) {}

    public function deliver(Sale $sale, string $phone, string $countryCode, bool $whatsapp, bool $sms): array
    {
        $company = CompanySetting::firstOrFail();
        $phone = trim($phone);
        if (!preg_match('/^\d{6,15}$/', $phone)) {
            throw new RuntimeException('Le numéro doit contenir entre 6 et 15 chiffres, sans indicatif.');
        }
        $countryCode = strtoupper($countryCode);
        if (!array_key_exists($countryCode, config('african_countries', []))) {
            throw new RuntimeException('Sélectionnez un pays africain valide.');
        }
        if (!$whatsapp && !$sms) throw new RuntimeException('Choisissez WhatsApp, SMS ou les deux.');

        $results = [];
        if ($whatsapp) {
            if (!$company->invoice_whatsapp_enabled) throw new RuntimeException('L’envoi de factures WhatsApp n’est pas autorisé dans les paramètres.');
            if ($company->whatsapp_count < 1) throw new RuntimeException('Le quota WhatsApp est épuisé.');
            $path = tempnam(sys_get_temp_dir(), 'invoice_').'.pdf';
            try {
                Pdf::loadView('pos.invoice', ['sale' => $sale, 'saleDetails' => $sale->saleDetails, 'company' => $company])->save($path);
                $upload = $this->sms->uploadWhatsappDocument($path);
                $mediaId = data_get($upload, 'media_id') ?: data_get($upload, 'data.media_id');
                if (!$mediaId) throw new RuntimeException('Le fournisseur n’a pas accepté la facture WhatsApp.');
                $response = $this->sms->sendWhatsappDocument($phone, $mediaId, 'Votre facture n°'.$sale->code, $countryCode);
                if (($response['status'] ?? false) !== true) throw new RuntimeException($response['message'] ?? 'Échec de l’envoi WhatsApp.');
                $results[] = 'WhatsApp';
            } finally {
                if (is_file($path)) @unlink($path);
            }
        }
        if ($sms) {
            if (!$company->invoice_sms_enabled) throw new RuntimeException('L’envoi de factures SMS n’est pas autorisé dans les paramètres.');
            if ($company->sms_count < 1) throw new RuntimeException('Le quota SMS est épuisé.');
            $message = 'Facture n°'.$sale->code.' - Total: '.number_format((float) $sale->total_amount, 0, ',', ' ').' FCFA. Merci pour votre achat.';
            $response = $this->sms->sendSms($phone, $message, $countryCode);
            if (($response['status'] ?? false) !== true) throw new RuntimeException($response['message'] ?? 'Échec de l’envoi SMS.');
            $results[] = 'SMS';
        }
        return $results;
    }
}
