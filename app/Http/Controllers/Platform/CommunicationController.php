<?php

namespace App\Http\Controllers\Platform;

use App\Exports\PlatformCommunicationsExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendEcommerceOrderEmailJob;
use App\Jobs\SendInventoryWhatsappJob;
use App\Jobs\SendSaleEmailJob;
use App\Jobs\SendSaleWhatsappJob;
use App\Models\Company;
use App\Models\NotificationDelivery;
use App\Models\PlatformAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CommunicationController extends Controller
{
    public function index(Request $request)
    {
        [$query, $filters] = $this->filtered($request);
        $stats = (clone $query)->selectRaw('channel, status, COUNT(*) total')->groupBy('channel', 'status')->get();
        $companies = (clone $query)->selectRaw('company_id, channel, COUNT(*) total')
            ->with('company:id,name')->groupBy('company_id', 'channel')->orderByDesc('total')->limit(20)->get();
        $deliveries = (clone $query)
            ->when($filters['delivery_search'] ?? null, function ($q, $value) {
                $q->where(function ($search) use ($value) {
                    $term = '%'.$value.'%';
                    $search->where('event_key', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('channel', 'like', $term)
                        ->orWhere('status', 'like', $term)
                        ->orWhereHas('company', fn ($company) => $company->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($user) => $user->where(function ($identity) use ($term) {
                            $identity->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term)
                                ->orWhere('phone', 'like', $term);
                        }));
                });
            })
            ->with(['company:id,name', 'user:id,name,email,phone'])
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25))
            ->withQueryString();
        return view('platform.communications.index', compact('deliveries', 'stats', 'companies', 'filters'));
    }

    public function export(Request $request, string $format)
    {
        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);
        [$query] = $this->filtered($request);
        $query->with(['company:id,name', 'user:id,email,phone'])->latest();
        return Excel::download(new PlatformCommunicationsExport($query), 'communications-'.now()->format('Ymd-His').'.'.$format,
            $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX);
    }

    public function retry(Request $request, NotificationDelivery $delivery)
    {
        abort_unless(Auth::guard('platform')->user()->hasPlatformPermission('platform.communications.retry'), 403);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        abort_unless($delivery->status === 'failed' && $this->retryable($delivery), 422, 'Cette communication ne peut pas être relancée automatiquement en toute sécurité.');

        DB::transaction(function () use ($request, $delivery, $data) {
            $updated = NotificationDelivery::whereKey($delivery->id)->where('status', 'failed')->update(['status' => 'pending', 'last_error' => null]);
            abort_unless($updated === 1, 409, 'Cette communication est déjà en cours de relance.');
            match (true) {
                $delivery->event_type === 'sale' && $delivery->channel === 'email' => SendSaleEmailJob::dispatch((int) $delivery->event_key, $delivery->company_id),
                $delivery->event_type === 'sale' && in_array($delivery->channel, ['sms','whatsapp'], true) => SendSaleWhatsappJob::dispatch((int) $delivery->event_key, $delivery->company_id),
                $delivery->event_type === 'inventory' && in_array($delivery->channel, ['sms','whatsapp'], true) => SendInventoryWhatsappJob::dispatch((int) $delivery->event_key, $delivery->company_id),
                $delivery->event_type === 'ecommerce_order' && $delivery->channel === 'email' => SendEcommerceOrderEmailJob::dispatch((int) $delivery->event_key, $delivery->company_id),
            };
            PlatformAuditLog::create(['platform_admin_id' => Auth::guard('platform')->id(), 'action' => 'platform.communication.retried',
                'target_type' => NotificationDelivery::class, 'target_id' => (string) $delivery->id, 'reason' => $data['reason'],
                'old_values' => ['status' => 'failed'], 'new_values' => ['status' => 'pending'], 'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 1000, '')]);
        });
        return response()->json(['message' => 'La communication a été remise dans la file d’attente.']);
    }

    private function filtered(Request $request): array
    {
        $filters = $request->validate(['company_id'=>['nullable','integer','exists:company_settings,id'],
            'channel'=>['nullable',Rule::in(['email','sms','whatsapp'])], 'status'=>['nullable',Rule::in(['pending','processing','sent','failed'])],
            'category'=>['nullable','string','max:30'], 'from'=>['nullable','date'], 'to'=>['nullable','date','after_or_equal:from'],
            'search'=>['nullable','string','max:100'], 'delivery_search'=>['nullable','string','max:100'],
            'per_page'=>['nullable','integer',Rule::in([10,25,50,100])]]);
        $query = NotificationDelivery::query()
            ->when($filters['company_id']??null,fn($q,$v)=>$q->where('company_id',$v))->when($filters['channel']??null,fn($q,$v)=>$q->where('channel',$v))
            ->when($filters['status']??null,fn($q,$v)=>$q->where('status',$v))->when($filters['category']??null,fn($q,$v)=>$q->where('category',$v))
            ->when($filters['from']??null,fn($q,$v)=>$q->whereDate('created_at','>=',$v))->when($filters['to']??null,fn($q,$v)=>$q->whereDate('created_at','<=',$v))
            ->when($filters['search']??null,fn($q,$v)=>$q->where(fn($s)=>$s->where('event_key','like',"%$v%")->orWhereHas('company',fn($c)=>$c->where('name','like',"%$v%"))));
        return [$query, $filters];
    }

    private function retryable(NotificationDelivery $d): bool
    {
        return ($d->event_type==='sale' && in_array($d->channel,['email','sms','whatsapp'],true))
            || ($d->event_type==='inventory' && in_array($d->channel,['sms','whatsapp'],true))
            || ($d->event_type==='ecommerce_order' && $d->channel==='email');
    }
}
