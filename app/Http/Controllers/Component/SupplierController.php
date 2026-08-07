<?php

namespace App\Http\Controllers\Component;

use App\Models\Action;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $Object = Supplier::where('status', 1)->latest();
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
                ->editColumn('phone', function ($Object) {
                    return $Object->phone ?? '-';
                })
                ->editColumn('whatsapp', function ($Object) {
                    return $Object->whatsapp ?? '-';
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = ' <a class="btn btn-success btn-sm">Actif</a>';
                    }else{
                        $btn = ' <a class="btn btn-danger btn-sm">Inactif</a>';
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
        return view('component.supplier.index');
    }

    public function disabledListing()
    {
        $Object = Supplier::where('status', 0)->latest();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->status==1){
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = '<a data-id="'.$row->id.'" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->editColumn('phone', function ($Object) {
                    return $Object->phone ?? '-';
                })
                ->editColumn('whatsapp', function ($Object) {
                    return $Object->whatsapp ?? '-';
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = ' <a class="btn btn-success btn-sm">Actif</a>';
                    }else{
                        $btn = ' <a class="btn btn-danger btn-sm">Inactif</a>';
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
        return view('component.supplier.index');
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
            "name.required" => "Remplir le champ Nom!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required'],
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
                'function' => 'AJOUT FOURNISSEUR',
                'text' => auth()->user()->name." a créer un nouveau fournisseur '".$request->name."'",
            ]);
            Supplier::create([
                'name' => $request->name,
                'contact' => $request->contact,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
                'created_by' => Auth::user()->id,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "AJOUT REUSSI",
                "msg" => "Le fournisseur au nom de ".$request->name." a bien été ajouté"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Supplier = Supplier::with('products')->findOrFail($id);
        return view('component.supplier.show', compact('Supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $Supplier = Supplier::findOrFail($id);
        return view('component.supplier.edit', compact('Supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $error_messages = [
            "name.required" => "Remplir le champ Nom!",
        ];

        $validator = Validator::make($request->all(),[
            'name' => ['required'],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "MISE A JOUR ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $Supplier = Supplier::findOrFail($id);
            $Supplier->update([
                'name' => $request->name,
                'contact' => $request->contact,
                'phone' => $request->phone,
                'whatsapp' => $request->whatsapp,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "MISE A JOUR REUSSIE",
                "msg" => "Le fournisseur au nom de '".$request->name."' a bien été mis à jour"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Object = Supplier::findOrFail($id);

        // Supplier actif => archivage
        if ($Object->status == 1) {
            $Object->update([
                'status' => 0,
            ]);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ARCHIVAGE D\'UN FOURNISSEUR',
                'text' => auth()->user()->name." a désactivé le fournisseur : ".$Object->name,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "ARCHIVAGE REUSSI",
                "msg" => "Le fournisseur ".$Object->name." a été archivé."
            ]);
        }

        // Restauration
        $Object->update([
            'status' => 1,
        ]);

        Action::create([
            'user_id' => auth()->user()->id,
            'function' => 'RESTAURER UN FOURNISSEUR',
            'text' => auth()->user()->name." a restauré le fournisseur : ".$Object->name,
        ]);

        return response()->json([
            "status" => true,
            "reload" => true,
            "title" => "RESTAURATION REUSSIE",
            "msg" => "Le fournisseur ".$Object->name." a bien été restauré"
        ]);
    }
}