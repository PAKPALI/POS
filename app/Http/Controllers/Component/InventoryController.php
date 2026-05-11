<?php

namespace App\Http\Controllers\Component;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request )
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = Inventory::with('product', 'user');

        if($request->product_id){
            $Object->where('product_id', $request->product_id);
        }

        if($request->start_date && $request->end_date){
            $Object->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $Object = $Object->latest()->get();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>';
                    return $btn;
                })
                ->editColumn('product_id', function ($Object) {
                    return $Object->product->name;
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $Product = Product::where('status',1)->orderBy('name', 'asc')->get();
        return view('component.inventory.index',compact('Product'));
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
            "product_id.required" => "Sélectionnez un produit!",
            "qte_added.required" => "Remplir le champ Quantité!",
            "qte_added.numeric" => "Le champ Quantité doit être un nombre!",
            "qte_added.min" => "La quantité ne doit pas être nulle ou négative!",
        ];
        
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'qte_added' => ['required','numeric','min:1'],
        ], $error_messages);

        if($validator->fails()){
            return response()->json([
                "status" => false,
                "title" => "ECHEC D'ENREGISTREMENT",
                "msg" => $validator->errors()->first()
            ]);
        }

        DB::beginTransaction();

        try {

            $Product = Product::findOrFail($request->product_id);

            // quantité avant
            $before = $Product->qte;

            // quantité ajoutée
            $added = $request->qte_added;

            // nouvelle quantité
            $after = $before + $added;

            // update produit
            $Product->update([
                'qte' => $after
            ]);

            // save historique
            Inventory::create([
                'product_id' => $Product->id,
                'qte_before' => $before,
                'qte_added' => $added,
                'qte_after' => $after,
                'note' => $request->note,
                'created_by' => auth()->user()->id,
            ]);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ENTREE STOCK',
                'text' => auth()->user()->name. " a ajouté ".$added." unité(s) au produit '".$Product->name."'",
            ]);

            DB::commit();

            return response()->json([
                "status" => true,
                "title" => "SUCCES",
                "msg" => "Entrée enregistrée avec succès"
            ]);

        } catch (\Exception $e){

            DB::rollback();

            return response()->json([
                "status" => false,
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
        $Inventory = Inventory::findOrFail($id);
        return view('component.inventory.show', compact('Inventory'));
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
