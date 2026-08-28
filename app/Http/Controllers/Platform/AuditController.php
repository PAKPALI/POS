<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\PlatformAdmin;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'admin_id' => ['nullable', 'integer', 'exists:platform_admins,id'],
            'result' => ['nullable', 'in:success,failed'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
        $logs = PlatformAuditLog::with('admin:id,name,email')
            ->when($validated['q'] ?? null, function ($query, $search) {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($search)).'%';
                $query->where(fn ($nested) => $nested->where('action', 'like', $term)->orWhere('target_type', 'like', $term)->orWhere('target_id', 'like', $term)->orWhere('reason', 'like', $term));
            })
            ->when($validated['admin_id'] ?? null, fn ($query, $id) => $query->where('platform_admin_id', $id))
            ->when($validated['result'] ?? null, fn ($query, $result) => $query->where('result', $result))
            ->when($validated['from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()->paginate(30)->withQueryString();
        $admins = PlatformAdmin::orderBy('name')->get(['id', 'name', 'email']);
        return view('platform.audit.index', compact('logs', 'admins'));
    }

    public function show(PlatformAuditLog $audit)
    {
        $audit->load('admin:id,name,email');
        return view('platform.audit.show', compact('audit'));
    }
}
