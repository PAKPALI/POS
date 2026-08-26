<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Action;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AuthorizedLandingPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    public function __construct(private AuthorizedLandingPage $landingPage) {}

    /** Keep every login entry point on the styled POS authentication page. */
    public function showLoginForm()
    {
        return redirect()->route('user_login');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    // public function __construct()
    // {
    //     $this->middleware('guest')->except('logout');
    //     $this->middleware('auth')->only('logout');
    // }

    public function loginUser($user, Request $request)
    {
        Auth::login($user);
        $request->session()->regenerate();

        $memberships = $user->activeMemberships()->with('company')->get();
        $redirect = route('companies.select');
        $selectedMembership = null;

        if ($memberships->count() === 1 && $memberships->first()->company?->isActive()) {
            $membership = $memberships->first();
            $request->session()->put('active_company_id', $membership->company_id);
            $request->session()->put('active_company_name', $membership->company->name);
            $membership->update(['last_accessed_at' => now()]);
            $redirect = $this->landingPage->forMembership($membership);
            $selectedMembership = $membership;
        }

        if ($selectedMembership) {
            Action::create([
                'company_id' => $selectedMembership->company_id,
                'user_id' => $user->id,
                'function' => 'CONNEXION',
                'text' => $user->name.' s’est connecté à l’entreprise '.$selectedMembership->company->name.'.',
            ]);
        }

        return response()->json([
            "status" => true,
            "reload" => true,
            "redirect_to" => $redirect,
            "title" => "CONNEXION REUSSIE",
            'check' => Auth::check(),
            "msg" => "connexion réussie"
        ]);
                 
    }

    public function login(Request $request)
    {
        $throttleKey = Str::lower((string) $request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'status' => false,
                'reload' => false,
                'title' => 'TROP DE TENTATIVES',
                'msg' => 'Patientez '.RateLimiter::availableIn($throttleKey).' secondes avant de réessayer.',
            ], 429);
        }
        $error_messages = [
            "email.required" => "Remplir le champ email!",
            "email.email" => "La structure d'un email n'est pas respecte!",
            "password.required" => "Remplir le champ mot de passe!",
        ];

        $validator = Validator::make($request->all(),[
            'email' => ['required', 'email'],
            'password' => ['required']
        ], $error_messages);

        if($validator->fails())
            return response()->json([
            "status" => false,
            "reload" => false,
            "title" => "CONNEXION ECHOUEE",
            "msg" => $validator->errors()->first()]);
        
        $user = User::where('email', $request-> email)->first();

        if($user){
            if(Hash::check($request-> password, $user-> password)){
                RateLimiter::clear($throttleKey);
                // Le statut est global. Les droits sont déterminés par le rôle
                // de l'adhésion dans la compagnie active, jamais par user_type.
                if ((int) $user->status !== 1) {
                    return response()->json([
                        "status" => false,
                        "reload" => true,
                        'check' => Auth::check(),
                        "title" => "CONNEXION ÉCHOUÉE",
                        "msg" => "Votre compte est désactivé. Contactez un administrateur."
                    ]);
                }

                return $this->loginUser($user, $request);
            }else{
                RateLimiter::hit($throttleKey, 60);
                return response()->json([
                    "status" => false,
                    "reload" => true,
                    'check' => Auth::check(),
                    "title" => "CONNECTION ECHOUEE",
                    "msg" => "Les identifiants fournis sont incorrects."
                ]);
            }
        }else{
            RateLimiter::hit($throttleKey, 60);
            return response()->json([
                "status" => false,
                "reload" => true,
                'check' => Auth::check(),
                "title" => "CONNECTION ECHOUEE",
                "msg" => "Les identifiants fournis sont incorrects."
            ]);
        }
    }
}
