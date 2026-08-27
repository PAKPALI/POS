<?php
namespace App\Http\Controllers;

use App\Models\CommunicationLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunicationLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'from' => ['nullable','date'], 'to' => ['nullable','date','after_or_equal:from'],
            'channel' => ['nullable', Rule::in(['sms','whatsapp'])],
            'function' => ['nullable', Rule::in(['sale','inventory','invoice','other'])],
        ]);
        $query = CommunicationLog::query()
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('sent_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('sent_at', '<=', $date))
            ->when($filters['channel'] ?? null, fn ($q, $value) => $q->where('channel', $value))
            ->when($filters['function'] ?? null, fn ($q, $value) => $q->where('function', $value));
        $totals = (clone $query)->selectRaw('channel, SUM(units) total')->groupBy('channel')->pluck('total', 'channel');
        $logs = $query->latest('sent_at')->paginate(30)->withQueryString();
        return view('communications.index', compact('logs', 'totals', 'filters'));
    }
}
