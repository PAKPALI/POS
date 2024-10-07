<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    
    public function store(Request $request)
    {
        $error_messages = [
            "name.required" => "Remplir le champ nom!",
            "name.max" => "Le nombre de caractere du nom depasse les 255!",
            "email.required" => "Remplir le champ email!",
            "email.email" => "La structure d'un email n'est pas respecte!",
            "email.unique" => "Ce mail existe deja",
            "password.required" => "Remplir le champ mot de passe!",
            "password.min" => "Le mot de passe doit comporter au moins 8 caracteres!",
            "password.confirmed" => "Les mots de passe ne correspondent pas",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $error_messages);

        if($validator->fails())
        {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "INSCRIPTION ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);
        }else{
            if($request['user_type'] ==2){
                User::create([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'user_type' => 2,
                    'password' => Hash::make($request['password']),
                ]);
                // $this->sendEmail($request['email'],$request['name']);
                return response()->json([
                    "status" => true,
                    "reload" => true,
                    "redirect_to" => url('user_login'),
                    "title" => "INSCRIPTION DE L'ADMINISTRATION REUSSIE!",
                    "msg" => "L'administrateur '".$request-> name."' , est validé"
                ]);
            }else{
                return response()->json([
                    "status" => false,
                    "reload" => false,
                    "title" => "INSCRIPTION ECHOUEE",
                    "msg" => 'Pas de  usertype 2'
                ]);
            }
            
        }
    }

    public function register(Request $request)
    {
        $error_messages = [
            "name.required" => "Remplir le champ nom!",
            "name.max" => "Le nombre de caractere du nom depasse les 255!",
            "email.required" => "Remplir le champ email!",
            "email.email" => "La structure d'un email n'est pas respecte!",
            "email.unique" => "Ce mail existe deja",
            "password.required" => "Remplir le champ mot de passe!",
            "password.min" => "Le mot de passe doit comporter au moins 8 caracteres!",
            "password.confirmed" => "Les mots de passe ne correspondent pas",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], $error_messages);

        if($validator->fails())
        {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "INSCRIPTION ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);
        }else{
            if($request['user_type'] ==2){
                User::create([
                    'name' => $request['name'],
                    'email' => $request['email'],
                    'user_type' => 2,
                    'password' => Hash::make($request['password']),
                ]);
                // $this->sendEmail($request['email'],$request['name']);
                return response()->json([
                    "status" => true,
                    "reload" => true,
                    "redirect_to" => url('user_login'),
                    "title" => "INSCRIPTION DE L'ADMINISTRATION REUSSIE!",
                    "msg" => "L'administrateur '".$request-> name."' , est validé"
                ]);
            }else{
                return response()->json([
                    "status" => false,
                    "reload" => false,
                    "title" => "INSCRIPTION ECHOUEE",
                    "msg" => 'Pas de  usertype 2'
                ]);
            }
            
        }
    }

    // public function sendEmail($email, $fullName)
    // {
    //     $text = "Voici votre code ".$fullName." qui vous permetrra de vous connecter."";
    //     // Envoyez l'e-mail avec le code généré
    //     Mail::send('emails.absenceEmail', ['text' => $text], function($message) use ($email,){
    //         if ($email1) {
    //             $message->to($email);
    //         }
    //         $message->subject('POS');
    //     });
    // }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
