<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvitation;
use App\Models\User;
use App\Services\CompanyInvitationService;
use App\Services\AuthorizedLandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvitationAcceptanceController extends Controller
{
    public function show(string $token, CompanyInvitationService $service)
    {
        $invitation = $service->findByToken($token);
        $existingUser = User::whereRaw('LOWER(email) = ?', [mb_strtolower($invitation->email)])->first();
        return view('auth.invitation', compact('invitation', 'existingUser', 'token'));
    }

    public function accept(
        Request $request,
        string $token,
        CompanyInvitationService $service,
        AuthorizedLandingPage $landingPage
    )
    {
        $invitation = $service->findByToken($token);
        abort_unless($invitation->isPending(), 410, 'Cette invitation n’est plus valide.');
        $existingUser = User::whereRaw('LOWER(email) = ?', [mb_strtolower($invitation->email)])->first();

        if ($existingUser) {
            abort_unless((int) $existingUser->status === 1, 403, 'Le compte associé à cette invitation est désactivé.');
            if (! Auth::check()) {
                return redirect()->route('user_login')->with(
                    'error',
                    'Connectez-vous avec le compte '.$invitation->email.' puis ouvrez de nouveau le lien d’invitation.'
                );
            }
            abort_unless(Auth::id() === $existingUser->id, 403, 'Cette invitation appartient à un autre compte.');
            $user = $existingUser;
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);
            $user = DB::transaction(fn () => User::create([
                'name' => $validated['name'], 'email' => $invitation->email,
                'phone' => $validated['phone'] ?? null, 'password' => $validated['password'],
                'status' => 1,
            ]));
        }

        $service->accept($invitation, $user);

        if (! Auth::check()) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        $request->session()->forget(['active_company_id', 'active_company_name']);
        if ($user->activeMemberships()->count() === 1) {
            $request->session()->put('active_company_id', $invitation->company_id);
            $request->session()->put('active_company_name', $invitation->company->name);
            $membership = $user->activeMemberships()
                ->where('company_id', $invitation->company_id)
                ->with('role.permissions')
                ->firstOrFail();
            return redirect($landingPage->forMembership($membership))
                ->with('success', 'Invitation acceptée. Bienvenue dans '.$invitation->company->name.'.');
        }
        return redirect()->route('companies.select')->with('success', 'Invitation acceptée. Vous pouvez maintenant sélectionner '.$invitation->company->name.'.');
    }

    public function decline(string $token, CompanyInvitationService $service)
    {
        $invitation = $service->findByToken($token);
        abort_unless($invitation->isPending(), 410, 'Cette invitation n’est plus valide.');
        $invitation->update(['declined_at' => now()]);
        return view('auth.invitation-result', ['title' => 'Invitation refusée', 'message' => 'Vous ne rejoindrez pas '.$invitation->company->name.'.']);
    }
}
