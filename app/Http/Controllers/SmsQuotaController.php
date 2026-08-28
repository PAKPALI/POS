<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use App\Models\QuotaPayment;
use App\Services\CompanyContext;
use App\Services\KprimePayService;
use App\Services\PlatformPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class SmsQuotaController extends Controller
{
    public function index(PlatformPricingService $pricing)
    {
        $company = CompanySetting::findOrFail(app(CompanyContext::class)->getCompanyId());
        $payments = QuotaPayment::latest()->paginate(10);
        $smsUnitPrice = $pricing->smsUnitPrice();
        $whatsappUnitPrice = $pricing->whatsappUnitPrice();

        return view('sms_quota.index', compact('company', 'payments', 'smsUnitPrice', 'whatsappUnitPrice'));
    }

    public function checkout(Request $request, KprimePayService $kprimePay, PlatformPricingService $pricing)
    {
        if (blank(config('services.kprimepay.token'))) {
            return response()->json([
                'status' => false,
                'title' => 'KPrimePay non configuré',
                'msg' => 'Ajoutez une clé KPrimePay valide dans le fichier .env, puis videz le cache de configuration.',
            ], 503);
        }

        $validator = Validator::make($request->all(), [
            'sms_quantity' => ['required', 'integer', 'min:0', 'max:100000'],
            'whatsapp_quantity' => ['required', 'integer', 'min:0', 'max:100000'],
        ], [
            'sms_quantity.integer' => 'Le nombre de SMS doit être un entier.',
            'whatsapp_quantity.integer' => 'Le nombre de WhatsApp doit être un entier.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'title' => 'Validation échouée',
                'msg' => $validator->errors()->first(),
            ]);
        }

        $smsQuantity = (int) $request->input('sms_quantity');
        $whatsappQuantity = (int) $request->input('whatsapp_quantity');
        if ($smsQuantity + $whatsappQuantity < 1) {
            return response()->json([
                'status' => false,
                'title' => 'Quantité requise',
                'msg' => 'Choisissez au moins un SMS ou un message WhatsApp.',
            ], 422);
        }

        $smsUnitPrice = $pricing->smsUnitPrice();
        $whatsappUnitPrice = $pricing->whatsappUnitPrice();
        $smsUnitCost = $pricing->smsUnitCost();
        $whatsappUnitCost = $pricing->whatsappUnitCost();
        $amount = $smsQuantity * $smsUnitPrice + $whatsappQuantity * $whatsappUnitPrice;
        if ($amount > 2000000) {
            return response()->json(['status' => false, 'title' => 'Montant trop élevé', 'msg' => 'Le montant maximum est de 2 000 000 FCFA.'], 422);
        }

        $companyId = app(CompanyContext::class)->getCompanyId();
        $reference = 'QUOTA-'.$companyId.'-'.strtoupper(Str::random(16));
        $payment = QuotaPayment::create([
            'company_id' => $companyId,
            'user_id' => $request->user()->id,
            'transaction_id' => $reference,
            'idempotency_key' => 'checkout-'.strtolower($reference),
            'sms_quantity' => $smsQuantity,
            'sms_unit_price' => $smsUnitPrice,
            'sms_unit_cost' => $smsUnitCost,
            'whatsapp_quantity' => $whatsappQuantity,
            'whatsapp_unit_price' => $whatsappUnitPrice,
            'whatsapp_unit_cost' => $whatsappUnitCost,
            'amount' => $amount,
            'currency' => 'XOF',
            'status' => 'created',
        ]);

        try {
            $data = $kprimePay->createCheckout($payment, route('sms-quota.return', ['transaction_id' => $reference]));
            $payment->update([
                'status' => 'pending',
                'kpp_reference' => $data['kpp_tx_reference'],
                'checkout_url' => $data['checkout_url'],
                'expires_at' => $data['expires_at'] ?? now()->addHours(app(\App\Services\PlatformConfigurationService::class)->integer('security.payment_expiry_hours', 24)),
            ]);
        } catch (Throwable $exception) {
            $payment->update(['status' => 'failed', 'failure_reason' => class_basename($exception), 'failed_at' => now()]);
            report($exception);

            return response()->json([
                'status' => false,
                'title' => 'Paiement indisponible',
                'msg' => $exception->getMessage(),
            ], 502);
        }

        return response()->json([
            'status' => true,
            'checkout_url' => $payment->checkout_url,
            'transaction_id' => $payment->transaction_id,
            'msg' => 'Redirection vers le paiement sécurisé…',
        ]);
    }

    public function status(string $transactionId)
    {
        $payment = QuotaPayment::where('transaction_id', $transactionId)->firstOrFail();

        return response()->json([
            'status' => true,
            'payment_status' => $payment->status,
            'transaction_id' => $payment->transaction_id,
        ]);
    }

    public function returned(Request $request)
    {
        return redirect()->route('sms-quota.index')
            ->with('info', 'Paiement reçu. Les quotas seront ajoutés automatiquement dès confirmation de KPrimePay.');
    }
}
