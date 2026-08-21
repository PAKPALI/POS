<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvitation;
use App\Models\Role;
use App\Services\CompanyContext;
use App\Services\CompanyInvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompanyInvitationController extends Controller
{
    public function store(Request $request, CompanyContext $context, CompanyInvitationService $service)
    {
        $company = $context->getCompany();
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role_id' => ['required', Rule::exists('roles', 'id')->where(fn ($query) => $query->where('company_id', $company->id))],
        ]);
        $role = Role::where('company_id', $company->id)->findOrFail($validated['role_id']);
        $invitation = $service->create($company, $role, $validated['email'], Auth::user());

        return response()->json([
            'status' => true,
            'title' => 'INVITATION ENVOYÉE',
            'msg' => 'Une invitation valable 48 heures a été envoyée à '.$invitation->email.'.',
        ]);
    }

    public function resend(CompanyInvitation $invitation, CompanyContext $context, CompanyInvitationService $service)
    {
        abort_unless($invitation->company_id === $context->getCompanyId(), 404);
        $service->resend($invitation);
        return response()->json(['status' => true, 'title' => 'INVITATION RENVOYÉE', 'msg' => 'Un nouveau lien valable 48 heures a été envoyé.']);
    }

    public function destroy(CompanyInvitation $invitation, CompanyContext $context)
    {
        abort_unless($invitation->company_id === $context->getCompanyId(), 404);
        abort_if($invitation->accepted_at || $invitation->declined_at || $invitation->revoked_at, 422, 'Cette invitation est déjà clôturée.');
        $invitation->update(['revoked_at' => now()]);
        return response()->json(['status' => true, 'title' => 'INVITATION RÉVOQUÉE', 'msg' => 'Le lien ne peut plus être utilisé.']);
    }
}
