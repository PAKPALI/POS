<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Action;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
    protected $redirectTo = '/home';

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

    public function loginUser($user)
    {
        Auth::login($user);
        // $request->session()->regenerate();
        Action::create([
            'user_id' => auth()->user()->id,
            'function' => 'CONNEXION',
            'text' => " s'est connecté",
        ]);           
    }

    public function login(Request $request)
    {
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
                // verify admin login
                if($user->user_type == 2){
                    $this->loginUser($user);           
                    return response()->json([
                        "status" => true,
                        "reload" => true,
                        "redirect_to" => route('dashboard'),
                        "title" => "CONNEXION REUSSIE",
                        'check' => Auth::check(),
                        "msg" => "connexion réussie"
                    ]);
                }elseif($user->user_type == 3){
                    // verify employe login
                    if($user->status == 1){
                        $this->loginUser($user);
                        return response()->json([
                            "status" => true,
                            "reload" => true,
                            "redirect_to" => route('dashboard'),
                            "title" => "CONNEXION REUSSIE",
                            'check' => Auth::check(),
                            "msg" => "connexion réussie"
                        ]);
                    }else{
                        // verify super admin login
                        return response()->json([
                            "status" => false,
                            "reload" => true,
                            'check' => Auth::check(),
                            "title" => "CONNECTION ECHOUEE",
                            "msg" => "Vous n'etes pas autorisé à vous connecté"
                        ]);  
                    }
                }else{
                    return response()->json([
                        "status" => true,
                        "reload" => true,
                        "redirect_to" => route('dashboard'),
                        "title" => "CONNEXION REUSSIE",
                        'check' => Auth::check(),
                        "msg" => "connexion réussie"
                    ]);
                }
            }else{
                return response()->json([
                    "status" => false,
                    "reload" => true,
                    'check' => Auth::check(),
                    "title" => "CONNECTION ECHOUEE",
                    "msg" => "Le mot de passe est incorrecte"
                ]);
            }
        }else{
            return response()->json([
                "status" => false,
                "reload" => true,
                'check' => Auth::check(),
                "title" => "CONNECTION ECHOUEE",
                "msg" => "L'email est incorrecte"
            ]);
        }
    }
}
