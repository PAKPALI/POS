<?php

namespace App\Http\Controllers\Company;

use Illuminate\Http\Request;
use App\Models\CompanySetting;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = CompanySetting::latest()->get();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                     $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>';
                        return $btn;
                })
                ->editColumn('number2', function ($Object) {
                    return $Object->number2 ?? '--';
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('company.index', compact('Object'));
    }

    public function store(Request $request)
    {
        $error_messages = [
            "name.required" => "Remplir le champ Nom!",
            "email.required" => "Remplir le champ Email!",
            "adress.required" => "Remplir le champ Adresse!",
            "number1.required" => "Remplir le champ Numéro1!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required'],
            'email' => ['required'],
            'adress' => ['required'],
            'number1' => ['required'],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);
            // Action::create([
            //     'user_id' => auth()->user()->id,
            //     'function' => 'AJOUT CATEGORIE',
            //     'text' => auth()->user()->name." a créer une nouvelle catégorie '".$request->name."'",
            // ]);
            CompanySetting::create([
                'name' => $request-> name,
                'email' => $request-> email,
                'adress' => $request-> adress,
                'number1' => $request-> number1,
                'number2' => $request-> number2,
                'message' => $request-> message,
                // 'created_by' => Auth::user()->id,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "AJOUT REUSSI",
                "msg" => "La compagnie au nom de ".$request-> name." a bien été ajoutée"
            ]);
    }

    public function show(string $id)
    {
        $Company = CompanySetting::findOrFail($id);
        return view('company.show', compact('Company'));
    }

    public function edit($id)
    {
        $Company = CompanySetting::findOrFail($id);
        return view('company.edit', compact('Company'));
    }

    public function update(Request $request, string $id)
    {
        $error_messages = [
            "name.required" => "Remplir le champ Nom!",
            "email.required" => "Remplir le champ Email!",
            "number1.required" => "Remplir le champ Numéro1!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required'],
            'email' => ['required'],
            'number1' => ['required'],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "MISE A JOUR ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);

            $Company = CompanySetting::findOrFail($id);
            $Company->update([
                'name' => $request-> name,
                'email' => $request-> email,
                'adress' => $request-> adress,
                'number1' => $request-> number1,
                'number2' => $request-> number2,
                'message' => $request-> message,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "MISE A JOUR REUSSIE",
                "msg" => "La compagnie au nom de '".$request-> name."' a bien été mise à jour"
            ]);
    }
}
