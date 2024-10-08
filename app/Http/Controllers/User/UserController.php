<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = User::where('user_type',3)->latest()->get();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = ' <a data-id="'.$row->id.'" data-name="" data-original-title="Edit" class="btn btn-dark btn-sm absent"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                    <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('user.index');
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

    public function code(){
        $code = Str::random(4);
        return $code;
    }
    
    public function store(Request $request)
    {
        $error_messages = [
            "name.required" => "Remplir le champ nom!",
            "name.max" => "Le nombre de caractere du nom depasse les 255!",
            "email.required" => "Remplir le champ email!",
            "email.email" => "La structure d'un email n'est pas respecte!",
            "email.unique" => "Ce mail existe deja",
            // "password.required" => "Remplir le champ mot de passe!",
            // "password.min" => "Le mot de passe doit comporter au moins 8 caracteres!",
            // "password.confirmed" => "Les mots de passe ne correspondent pas",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
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
            $email = $request['email'];
            $name = $request['name'];
            $code = $this->code();
            User::create([
                'name' => $name,
                'email' => $email,
                'user_type' => 3,
                'password' => Hash::make($code),
            ]);
            $this->sendEmail($email,$name,$code);
            return response()->json([
                "status" => true,
                "reload" => true,
                "redirect_to" => '',
                "title" => "INSCRIPTION DE L'UTILISATEUR REUSSIE!",
                "msg" => "L'utilisateur '".$request-> name."' , est validé"
            ]);
            
        }
    }

    public function sendEmail($email, $name, $code)
    {
        $text = "Voici votre mot de passe ".$code."";
        // Envoyez l'e-mail avec le code généré
        Mail::send('emails.user.connectPass', ['text' => $text,'name' => $name], function($message) use ($email){
            $message->to($email);
            $message->subject('LUX-GRILL');
        });
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
