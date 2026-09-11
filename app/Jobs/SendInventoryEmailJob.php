<?php

namespace App\Jobs;

use App\Jobs\Concerns\HasReliableNotificationQueue;
use App\Models\Company;
use App\Models\Inventory;
use App\Services\CompanyContext;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationRecipientService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

class SendInventoryEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use HasReliableNotificationQueue;

    public $inventoryId;
    public $companyId;

    public function __construct(int $inventoryId, int $companyId)
    {
        $this->inventoryId = $inventoryId;
        $this->companyId = $companyId;
    }

    public function handle(): void
    {
        $company = Company::active()->find($this->companyId);
        if (!$company) return;
        app(CompanyContext::class)->setPublicCompany($company);
        if (!$company->inventory_email_enabled) {
            Log::info('Notifications d’inventaire par e-mail désactivées', ['company_id' => $company->id]);
            return;
        }
        $inventory = Inventory::with(['product', 'supplier', 'user'])->find($this->inventoryId);
        if (!$inventory) return;
        if ((int) $inventory->type === 2 && str_starts_with(trim((string) $inventory->note), 'Vente #')) {
            Log::info('Notification d’inventaire ignorée : mouvement généré par une vente', [
                'company_id' => $company->id, 'inventory_id' => $inventory->id,
            ]);
            return;
        }
        $recipients = app(NotificationRecipientService::class)->users($this->companyId, 'inventory', 'email');
        $deliveryService = app(NotificationDeliveryService::class);
        $hasFailures = false;
        foreach ($recipients as $user) {
            try {
                $sent = $deliveryService->deliver($company->id, 'inventory', $inventory->id, 'inventory', 'email', $user->id,
                    function () use ($user, $inventory, $company): void {
                        Mail::send('emails.inventory.notification', ['inventory' => $inventory, 'company' => $company], function ($message) use ($user, $inventory, $company): void {
                            $type = $inventory->type === 1 ? 'Entrée' : 'Sortie';
                            $message->from(config('mail.from.address'), $company->name)->to($user->email)->subject($company->name.' — '.$type.' d’inventaire');
                        });
                    });
                if ($sent) Log::info('Inventory email sent successfully', ['company_id' => $company->id, 'inventory_id' => $inventory->id, 'user_id' => $user->id]);
            } catch (Throwable $exception) {
                $hasFailures = true;
                Log::warning('Inventory email delivery failed', ['company_id' => $company->id, 'inventory_id' => $inventory->id, 'user_id' => $user->id, 'error' => class_basename($exception)]);
            }
        }
        if ($hasFailures) throw new RuntimeException('Une ou plusieurs notifications d’inventaire par e-mail ont échoué.');
    }
}
