<?php

namespace App\Jobs;

use App\Models\CompanySetting;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendSaleEmailJob implements ShouldQueue
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

        $company = CompanySetting::first();
        $users = User::where('status', 1)->where('user_type','!=', 1)->get();

        foreach ($users as $user) {
            Mail::send('emails.sale.saleNotification', [
                'sale' => $sale,
                'company' => $company,
            ], function ($message) use ($user, $sale, $company) {
                $message->to($user->email);
                $message->subject("Nouvelle vente #" . $sale->code . " - " . $company->name ?? config('app.name'));
            });
            Log::info("Sale email sent with success to $user->email");
        }
    }
}