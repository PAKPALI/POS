<?php

namespace App\Jobs;

use App\Jobs\Concerns\HasReliableNotificationQueue;
use App\Mail\EcommerceOrderNotification;
use App\Models\Company;
use App\Models\EcommerceManager;
use App\Models\Order;
use App\Services\CompanyContext;
use App\Services\NotificationDeliveryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;
use RuntimeException;

class SendEcommerceOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasReliableNotificationQueue;

    public function __construct(
        public int $orderId,
        public int $companyId,
    ) {}

    public function handle(): void
    {
        $company = Company::active()->find($this->companyId);
        if (! $company) {
            return;
        }

        app(CompanyContext::class)->setPublicCompany($company);
        $order = Order::withoutCompanyScope()
            ->where('company_id', $company->id)
            ->with('items')
            ->find($this->orderId);
        if (! $order) {
            return;
        }

        $managers = EcommerceManager::with('user')
            ->where('company_id', $company->id)
            ->whereHas('user', fn ($query) => $query->where('status', 1))
            ->whereHas('user.memberships', fn ($query) => $query
                ->where('company_id', $company->id)
                ->where('status', 'active'))
            ->get()
            ->pluck('user')
            ->filter(fn ($user) => filled($user?->email))
            ->unique('id');
        $deliveryService = app(NotificationDeliveryService::class);
        $hasFailures = false;

        foreach ($managers as $user) {
            try {
                $sent = $deliveryService->deliver(
                    $company->id, 'ecommerce_order', $order->id, 'ecommerce', 'email', $user->id,
                    fn () => Mail::to($user->email)->send(new EcommerceOrderNotification($order, $company))
                );
                if ($sent) {
                    Log::info('E-commerce order email sent', [
                        'company_id' => $company->id, 'order_id' => $order->id, 'user_id' => $user->id,
                    ]);
                }
            } catch (Throwable $exception) {
                $hasFailures = true;
                Log::error('E-commerce order email sending failed', [
                    'company_id' => $company->id,
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'error' => class_basename($exception),
                ]);
            }
        }

        if ($hasFailures) {
            throw new RuntimeException('Un ou plusieurs e-mails de commande E-commerce ont échoué.');
        }
    }
}
