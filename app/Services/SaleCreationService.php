<?php

namespace App\Services;

use App\Jobs\SendMarginEmailJob;
use App\Jobs\SendSaleEmailJob;
use App\Jobs\SendSaleWhatsappJob;
use App\Jobs\SendCustomerInvoiceJob;
use App\Models\Action;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\AMS\Transaction;
use App\Models\CodePromo;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SaleCreationService
{
    public function __construct(private CompanyContext $context) {}

    public function create(array $data, User $cashier): Sale
    {
        return DB::transaction(function () use ($data, $cashier) {
            $percent = 0;
            if (! empty($data['code_promo']) && strlen((string) $data['code_promo']) === 6) {
                $percent = (float) (CodePromo::where('code', $data['code_promo'])
                    ->where('status', 1)->value('percents') ?? 0);
            }

            $discount = (float) ($data['discount'] ?? 0);
            $totalAmount = (float) $data['total_amount'];
            $receivedAmount = (float) ($data['received_amount'] ?? $totalAmount);
            $sale = Sale::create([
                'code' => $this->code(),
                'received_amount' => $receivedAmount,
                'total_amount' => $totalAmount,
                'remaining_amount' => $receivedAmount - $totalAmount,
                'code_promo' => $percent,
                'discount' => $discount,
                'amount_init' => $discount + $totalAmount,
                'client_id' => $data['client_id'] ?? null,
                'cashier' => $cashier->name,
            ]);

            $totalProfit = $this->processProducts($sale, $data['products'], $cashier->id);
            $sale->update(['total_profit' => $totalProfit - $discount]);
            $this->handleAccounting($sale, $cashier);

            SendSaleEmailJob::dispatch($sale->id, $this->context->getCompanyId())->afterCommit();
            SendSaleWhatsappJob::dispatch($sale->id, $this->context->getCompanyId())->afterCommit();
            if ($sale->client_id) {
                SendCustomerInvoiceJob::dispatch($sale->id, $this->context->getCompanyId(), 'whatsapp')->afterCommit();
                SendCustomerInvoiceJob::dispatch($sale->id, $this->context->getCompanyId(), 'sms')->afterCommit();
            }

            Action::create([
                'user_id' => $cashier->id,
                'function' => 'VENTE',
                'text' => $cashier->name.' a effectué une vente',
            ]);

            return $sale->fresh('saleDetails.product');
        }, 3);
    }

    private function processProducts(Sale $sale, array $products, int $createdBy): float
    {
        $totalProfit = 0;
        foreach ($products as $item) {
            $product = Product::whereKey($item['product_id'])->lockForUpdate()->firstOrFail();
            $quantity = (int) $item['quantity'];
            $profit = (float) $product->profit * $quantity;

            $sale->saleDetails()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'profit' => $profit,
            ]);

            $this->decrementProduct($product, $quantity, $sale, $createdBy);
            $totalProfit += $profit;
        }

        return $totalProfit;
    }

    private function decrementProduct(Product $product, int $quantity, Sale $sale, int $createdBy): void
    {
        $previousQuantity = (int) $product->qte;
        if ($previousQuantity < $quantity) {
            throw new RuntimeException('Stock insuffisant pour le produit '.$product->name.'.');
        }

        $newQuantity = $previousQuantity - $quantity;
        $product->update(['qte' => $newQuantity]);

        // Keep the stock movement and the sale in the same transaction so an
        // incomplete sale can never leave an orphan inventory movement.
        Inventory::create([
            'type' => 2,
            'product_id' => $product->id,
            'qte_before' => $previousQuantity,
            'qte_added' => $quantity,
            'qte_after' => $newQuantity,
            'note' => 'Vente #'.$sale->code,
            'created_by' => $createdBy,
        ]);

        if ((int) $product->type === 2) {
            foreach ($product->MenuProducts as $component) {
                $componentProduct = Product::whereKey($component->product_id)->lockForUpdate()->firstOrFail();
                $this->decrementProduct($componentProduct, (int) $component->quantity * $quantity, $sale, $createdBy);
            }
        }

        $safetyMargin = (int) $product->margin;
        if ($previousQuantity > $safetyMargin && $newQuantity <= $safetyMargin) {
            SendMarginEmailJob::dispatch(
                $product->name,
                $product->margin,
                $newQuantity,
                $this->context->getCompanyId(),
                $product->id,
                $sale->id
            )->afterCommit();
        }
    }

    private function handleAccounting(Sale $sale, User $cashier): void
    {
        $setting = Setting::first();
        if (! $setting) {
            throw new RuntimeException('Aucune configuration comptable n’est disponible pour cette entreprise.');
        }

        $mainCash = CashAccount::find($setting->default_cash_id);
        if (! $mainCash) {
            throw new RuntimeException('Aucune caisse principale n’est disponible pour cette entreprise.');
        }

        $taxPercent = (float) ($setting->default_tax ?? 0);
        $netAmount = $taxPercent > 0
            ? (float) $sale->total_amount / (1 + ($taxPercent / 100))
            : (float) $sale->total_amount;
        $taxAmount = (float) $sale->total_amount - $netAmount;
        $sale->update(['tax_amount' => $taxAmount]);

        $mainCash->increment('balance', $netAmount);
        Transaction::create([
            'type' => 'IN',
            'to_cash_id' => $mainCash->id,
            'amount' => $netAmount,
            'description' => 'Vente #'.$sale->code,
            'created_by' => $cashier->id,
        ]);

        if ($taxAmount > 0) {
            $taxCash = CashAccount::find($setting->tax_cash_id);
            if (! $taxCash) {
                throw new RuntimeException('Aucune caisse de taxe n’est disponible pour cette entreprise.');
            }
            $taxCash->increment('balance', $taxAmount);
            Transaction::create([
                'type' => 'IN',
                'to_cash_id' => $taxCash->id,
                'amount' => $taxAmount,
                'description' => 'Taxe vente #'.$sale->code,
                'created_by' => $cashier->id,
            ]);
        }
    }

    private function code(): string
    {
        do {
            $code = (string) random_int(10000000, 99999999);
        } while (Sale::withoutCompanyScope()->where('code', $code)->exists());

        return $code;
    }
}
