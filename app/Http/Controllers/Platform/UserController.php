<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $users = User::query()
            ->with(['memberships' => fn ($query) => $query
                ->with('company:id,name')
                ->orderByDesc('status')])
            ->withCount(['memberships', 'activeMemberships'])
            ->withMax('memberships', 'last_accessed_at')
            ->when($validated['q'] ?? null, function ($query, $search) {
                $term = '%'.str_replace(['%', '_'], ['\\%', '\\_'], trim($search)).'%';
                $query->where(fn ($nested) => $nested
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term));
            })
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status === 'active' ? 1 : 0))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('platform.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['memberships' => fn ($query) => $query
            ->with(['company:id,name,slug,status,email,currency,sms_count,whatsapp_count', 'role:id,name,key'])
            ->latest('last_accessed_at')]);

        $invitations = CompanyInvitation::query()
            ->where('email', mb_strtolower($user->email))
            ->with('company:id,name,slug')
            ->latest()
            ->limit(20)
            ->get();

        return view('platform.users.show', compact('user', 'invitations'));
    }
}
