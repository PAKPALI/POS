<?php

namespace App\Jobs;

use App\Models\CompanySetting;
use App\Models\User;
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

    public function __construct($productName,$margin,$newQte) {
        $this->productName = $productName;
        $this->margin = $margin;
        $this->newQte = $newQte;
    }

    public function handle(): void
    {
        try {
            $company = CompanySetting::first();
            $users = User::where('status', 1)->where('user_type','!=', 1)->get();

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
                        $message->subject(
                            $company->name ?? config('app.name').''." - Alerte de stock"
                        );
                    }
                );
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
