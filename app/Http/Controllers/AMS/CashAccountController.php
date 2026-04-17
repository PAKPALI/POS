<?php

namespace App\Http\Controllers\AMS;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\AMS\CashAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class CashAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = CashAccount::latest()->get();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->editColumn('is_default', function ($Object) {
                    if($Object->is_default==1){
                        $btn = ' <a class="btn btn-success btn-sm">Oui</a>';
                    }else{
                        $btn = ' <a class="btn btn-danger btn-sm">Non</a>';
                    }
                    return $btn;
                })
                ->addColumn('action', function($row){
                    if($row->status==1){
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = ' <a class="btn btn-success btn-sm">Actif</a>';
                    }else{
                        $btn = ' <a class="btn btn-danger btn-sm">Inactif</a>';
                    }
                    return $btn;
                })
                // ->editColumn('created_by', function ($Object) {
                //     return $Object->user->name;
                // })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['is_default','action','status'])
                ->make(true);
        }

        $totalCash = CashAccount::selectRaw('COUNT(*) as count, COALESCE(SUM(balance),0) as total')->first();
        $activeCash = CashAccount::where('status', 1)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance),0) as total')
            ->first();
        $inactiveCash = CashAccount::where('status', 0)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance),0) as total')
            ->first();
        $defaultCash = CashAccount::where('is_default', 1)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(balance),0) as total')
            ->first();
        $defaultCashName = CashAccount::where('is_default', 1)->first()->name;
        $taxCash = CashAccount::where('is_tax', 1)->first();
        return view('ams.cash.index', compact(
            'totalCash',
            'activeCash',
            'inactiveCash',
            'defaultCash',
            'defaultCashName',
            'taxCash'
        ));
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
            "name.required" => "Remplir le champ Nom de la caisse!",
        ];

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ], $error_messages);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);
        }

        DB::beginTransaction();

        try {
            $nextId = CashAccount::max('id') + 1;
            $code = 'CASH-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $data = [
                'name' => $request->name,
                'code' => $code,
                'is_default' => $request->is_default ? 1 : 0,
                'is_tax' => $request->is_tax ? 1 : 0,
                'status' => $request->status ? 1 : 0,
                'description' => $request->description,
                'created_by' => auth()->user()->id,
            ];

            $newCash = CashAccount::create($data);

            if ($request->is_default) {
                CashAccount::setDefaultCash($newCash->id);
            }

            if ($request->is_tax) {
                CashAccount::setTaxCash($newCash->id);
            }

            // LOG ACTION (comme ton POS)
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'AJOUT CAISSE',
                'text' => auth()->user()->name . " a ajouté la caisse '" . $request->name . "'",
            ]);

            DB::commit();

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "AJOUT REUSSI",
                "msg" => "La caisse '" . $request->name . "' a été ajoutée avec succès"
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "ERREUR",
                "msg" => $e->getMessage()
            ]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cashAccount = CashAccount::findOrFail($id);
        return view('ams.cash.show', compact('cashAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cashAccount = CashAccount::findOrFail($id);
        return view('ams.cash.edit', compact('cashAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cashAccount = CashAccount::findOrFail($id);

        $error_messages = [
            "name.required" => "Remplir le champ Nom de la caisse!",
        ];

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
        ], $error_messages);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "MODIFICATION ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);
        }

        DB::beginTransaction();

        try {
            $cashAccount->update([
                'name' => $request->name,
                'is_default' => $request->is_default ? 1 : 0,
                'is_tax' => $request->is_tax ? 1 : 0,
                'status' => $request->status ? 1 : 0,
                'description' => $request->description,
            ]);

            //cash manage by default, only one cash can be default
            if ($request->is_default) {
                CashAccount::setDefaultCash($cashAccount->id);
            }
            if ($request->is_tax) {
                CashAccount::setTaxCash($cashAccount->id); // 👈 NEW
            }

            // LOG ACTION
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'MODIFICATION CAISSE',
                'text' => auth()->user()->name . " a modifié la caisse '" . $cashAccount->name . "'",
            ]);

            DB::commit();

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "MODIFICATION REUSSIE",
                "msg" => "La caisse a été mise à jour avec succès"
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "ERREUR",
                "msg" => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Object = CashAccount::findOrFail($id);
        if($Object->status ==1){
            $Object->update([
                'status' => 0,
            ]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ARCHIVAGE D\'UNE CAISSE',
                'text' => auth()->user()->name." a désactivé la caisse : ".$Object->name,
            ]);
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "ARCHIVAGE REUSSIE",
                "msg" => "La caisse ".$Object->name." a bien été désactivée"
            ]);
        }else{
            $Object->update([
                'status' => 1,
            ]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'RESTAURER UNE CAISSE',
                'text' => auth()->user()->name." a restaurer la caisse : ".$Object->name,
            ]); 
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "RESTAURATION REUSSIE",
                "msg" => "La caisse ".$Object->name." a bien été restaurée"
            ]);
        }
    }
}
