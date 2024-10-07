<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
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
                if($user->user_type == 2){
                    Auth::login($user);
                    // $request->session()->regenerate();           
                    return response()->json([
                        "status" => true,
                        "reload" => true,
                        "redirect_to" => route('dashboard'),
                        "title" => "CONNEXION REUSSIE",
                        'check' => Auth::check(),
                        "msg" => "connexion réussie."
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
