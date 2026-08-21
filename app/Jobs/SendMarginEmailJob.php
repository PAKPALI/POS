<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\User;
use App\Services\CompanyContext;
use App\Services\NotificationRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendMarginEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $productName;
    public $margin;
    public $newQte;
    public $companyId;

    public function __construct($productName,$margin,$newQte,$companyId) {
        $this->productName = $productName;
        $this->margin = $margin;
        $this->newQte = $newQte;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        try {
            $company = Company::find($this->companyId);
            if (!$company) return;
            app(CompanyContext::class)->setPublicCompany($company);
            $users = app(NotificationRecipientService::class)->users($this->companyId, 'inventory', 'email');

            $text = "Le produit '" . strtoupper($this->productName) ."' a atteint sa marge de sécurité (" .$this->margin . ")";
            $text2 = "La nouvelle quantité du produit : " .$this->newQte;

            foreach ($users as $user) {
                Mail::send(
                    'emails.user.marginMail',
                    [
                        'user_name' => $user->name,
                        'email' => $user->email,
                        'text' => $text,
                        'text2' => $text2,
                        'product_name' => $this->productName,
                        'company' => $company,
                    ],
                    function ($message) use ($user, $company) {
                        $message->to($user->email);
                        $message->subject($company->name.' — Alerte de stock');
                    }
                );
                Log::info("Margin email sent with success to $user->email");
            }
        } catch (\Throwable $e) {
            Log::error('SendMarginEmailJob Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);
            throw $e;
        }
    }
}
