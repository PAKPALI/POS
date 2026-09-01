<?php

namespace App\Http\Controllers\Component;

use App\Models\Action;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Client::class);

        // composer require yajra/laravel-datatables-oracle
        $Object = Client::with('user:id,name')->where('status', 1)->latest();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->status==1){
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = '<span class="saas-status-badge is-active">Actif</span>';
                    }else{
                        $btn = '<span class="saas-status-badge is-inactive">Inactif</span>';
                    }
                    return $btn;
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
        return view('component.client.index');
    }

    public function disabledListing()
    {
        $this->authorize('viewAny', Client::class);

        // composer require yajra/laravel-datatables-oracle
        $Object = Client::with('user:id,name')->where('status', 0)->latest();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->status==1){
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = '<span class="saas-status-badge is-active">Actif</span>';
                    }else{
                        $btn = '<span class="saas-status-badge is-inactive">Inactif</span>';
                    }
                    return $btn;
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
        return view('component.client.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Client::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $error_messages = [
            "phone.digits_between" => "Le numéro doit contenir entre 6 et 15 chiffres, sans indicatif.",
            "name.required" => "Remplir le champ Nom!",
            "name.string" => "Le nom du client doit être un texte!",
            "name.max" => "Le nom du client ne doit pas dépasser 255 caractères!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'digits_between:6,15'],
            'country_code' => ['nullable', Rule::in(array_keys(config('african_countries', [])))],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'AJOUT CLIENT',
                'text' => auth()->user()->name." a créer un nouveau client '".$request->name."'",
            ]);
            Client::create([
                'name' => $request-> name,
                'phone' => $request->phone,
                'country_code' => $request->country_code ?: (app(\App\Services\CompanyContext::class)->getCompanyOrNull()?->country_code ?? 'TG'),
                'created_by' => Auth::user()->id,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "AJOUT REUSSI",
                "msg" => "Le client au nom de ".$request-> name." a bien été ajouté"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Client = Client::findOrFail($id);
        $this->authorize('view', $Client);

        return view('component.client.show', compact('Client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $Client = Client::findOrFail($id);
        $this->authorize('update', $Client);

        return view('component.client.edit', compact('Client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $Client = Client::findOrFail($id);
        $this->authorize('update', $Client);

        $error_messages = [
            "phone.digits_between" => "Le numéro doit contenir entre 6 et 15 chiffres, sans indicatif.",
            "name.required" => "Remplir le champ Nom!",
            "name.string" => "Le nom du client doit être un texte!",
            "name.max" => "Le nom du client ne doit pas dépasser 255 caractères!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'digits_between:6,15'],
            'country_code' => ['nullable', Rule::in(array_keys(config('african_countries', [])))],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "MISE A JOUR ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $Client->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'country_code' => $request->country_code ?: ($Client->country_code ?? 'TG'),
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "MISE A JOUR REUSSIE",
                "msg" => "Le client au nom de '".$request-> name."' a bien été mis à jour"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Object = Client::findOrFail($id);
        $this->authorize($Object->status == 1 ? 'delete' : 'restore', $Object);

        // Client actif => archivage
        if ($Object->status == 1) {
            $Object->update([
                'status' => 0,
            ]);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ARCHIVAGE D\'UN CLIENT',
                'text' => auth()->user()->name." a désactivé le client : ".$Object->name,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "ARCHIVAGE REUSSI",
                "msg" => "Le client ".$Object->name." a été archivé."
            ]);
        }

        // Restauration
        $Object->update([
            'status' => 1,
        ]);

        Action::create([
            'user_id' => auth()->user()->id,
            'function' => 'RESTAURER UN CLIENT',
            'text' => auth()->user()->name." a restauré le client : ".$Object->name,
        ]);

        return response()->json([
            "status" => true,
            "reload" => true,
            "title" => "RESTAURATION REUSSIE",
            "msg" => "Le client ".$Object->name." a bien été restauré"
        ]);
    }
}
