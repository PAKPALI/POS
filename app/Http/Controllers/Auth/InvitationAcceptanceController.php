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
use Illuminate\Validation\Rules\Password as PasswordRule;

class InvitationAcceptanceController extends Controller
{
    public function show(string $token, CompanyInvitationService $service)
    {
        $invitation = $service->findByToken($token);
        $existingUser = User::whereRaw('LOWER(email) = ?', [mb_strtolower($invitation->email)])->first();
        $canAcceptExistingUser = $existingUser && (int) Auth::id() === (int) $existingUser->id;
        return view('auth.invitation', compact('invitation', 'existingUser', 'token', 'canAcceptExistingUser'));
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
        $createdNewUser = false;

        if ($existingUser) {
            abort_unless((int) $existingUser->status === 1, 403, 'Le compte associé à cette invitation est désactivé.');
            if ((int) Auth::id() !== (int) $existingUser->id) {
                $message = 'Connectez-vous avec le compte invité avant d’accepter cette invitation.';

                if (!Auth::check()) {
                    return redirect()->route('invitations.show', $token)->withErrors(['email' => $message]);
                }

                return back()->withErrors(['email' => $message]);
            }
            $user = $existingUser;
        } else {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'password' => ['required', 'string', 'confirmed', PasswordRule::min(12)->mixedCase()->numbers()->symbols()],
            ]);
            $user = DB::transaction(fn () => User::create([
                'name' => $validated['name'], 'email' => $invitation->email,
                'phone' => $validated['phone'] ?? null, 'password' => $validated['password'],
                'status' => 1,
            ]));
            $createdNewUser = true;
        }

        $membership = $service->accept($invitation, $user);

        if ($createdNewUser) {
            app(\App\Services\PlatformAdminNotificationService::class)->newUserRegistered(
                $user,
                $invitation->company,
                $invitation->role?->name ?? 'Utilisateur',
            );
        }

        // Only a newly created account may be logged in from the invitation
        // flow. Existing accounts must already own the current session.
        if ($createdNewUser) {
            Auth::login($user);
        }
        $request->session()->regenerate();

        $request->session()->forget(['active_company_id', 'active_company_name']);
        // The invitation already identifies the intended company. Select it
        // explicitly instead of sending multi-company users to the chooser.
        $request->session()->put('active_company_id', $invitation->company_id);
        $request->session()->put('active_company_name', $invitation->company->name);
        $membership = $user->activeMemberships()
            ->where('company_id', $invitation->company_id)
            ->with('role.permissions')
            ->firstOrFail();

        return redirect($landingPage->forMembership($membership))
            ->with('success', 'Invitation acceptée. Bienvenue dans '.$invitation->company->name.'.');
    }

    public function decline(string $token, CompanyInvitationService $service)
    {
        $invitation = $service->findByToken($token);
        abort_unless($invitation->isPending(), 410, 'Cette invitation n’est plus valide.');
        $invitation->update(['declined_at' => now()]);
        return view('auth.invitation-result', ['title' => 'Invitation refusée', 'message' => 'Vous ne rejoindrez pas '.$invitation->company->name.'.']);
    }
}
