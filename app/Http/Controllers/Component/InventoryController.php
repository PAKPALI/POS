<?php

namespace App\Http\Controllers\Component;

use App\Http\Controllers\Controller;
use App\Jobs\SendInventoryWhatsappJob;
use App\Models\Action;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\CompanyContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index( Request $request )
    {
        $this->ensureFiltersBelongToActiveCompany($request);

        // composer require yajra/laravel-datatables-oracle
        $Object = Inventory::with('product', 'user');

        if($request->type !== null){
            $Object->where('type', $request->type);
        }

        if($request->product_id){
            $Object->where('product_id', $request->product_id);
        }

        if($request->supplier_id){
            $Object->where('supplier_id', $request->supplier_id);
        }

        if($request->start_date && $request->end_date){
            $Object->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $Object = $Object->latest();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>';
                    return $btn;
                })
                ->editColumn('type', function ($Object) {
                    $badge = '<span class="btn btn-'.($Object->type === 1 ? 'primary' : 'danger').'">'.($Object->type === 1 ? 'Entrée' : 'Sortie').'</span>';
                    // return '<span class="btn btn-success btn-sm">Entrée</span>';
                    return $badge;
                })
                ->editColumn('product_id', function ($Object) {
                    return $Object->product->name;
                })
                ->editColumn('supplier_id', function ($Object) {
                    return $Object->supplier ? $Object->supplier->name : '-';
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action', 'type'])
                ->make(true);
        }
        $Product = Product::where('status',1)->orderBy('name', 'asc')->get();
        $Supplier = Supplier::where('status',1)->orderBy('name', 'asc')->get();
        return view('component.inventory.index',compact('Product','Supplier'));
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
            "product_id.exists" => "Le produit sélectionné n'est pas disponible dans la compagnie active!",
            "supplier_id.exists" => "Le fournisseur sélectionné n'est pas disponible dans la compagnie active!",
            "qte_added.required" => "Remplir le champ Quantité!",
            "qte_added.numeric" => "Le champ Quantité doit être un nombre!",
            "qte_added.min" => "La quantité ne doit pas être nulle ou négative!",
        ];
        
        $validator = Validator::make($request->all(), [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query
                        ->where('company_id', app(CompanyContext::class)->getCompanyId())
                        ->where('status', 1)
                ),
            ],
            'supplier_id' => [
                'nullable',
                'integer',
                Rule::exists('suppliers', 'id')->where(
                    fn ($query) => $query
                        ->where('company_id', app(CompanyContext::class)->getCompanyId())
                        ->where('status', 1)
                ),
            ],
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
            $inventory = Inventory::create([
                'type' => 1,
                'supplier_id' => $request->supplier_id ?: null,
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

            // dispatch notification WhatsApp
            SendInventoryWhatsappJob::dispatch($inventory->id, app(CompanyContext::class)->getCompanyId())->afterCommit();

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

    public function remove(Request $request)
    { 
        $error_messages = [
            "product_id.required" => "Sélectionnez un produit!",
            "qte_removed.required" => "Remplir le champ Quantité!",
            "qte_removed.numeric" => "Le champ Quantité doit être un nombre!",
            "qte_removed.min" => "La quantité ne doit pas être nulle ou négative!",
        ];
        
        $validator = Validator::make($request->all(), [
            'product_id' => ['required'],
            'qte_removed' => ['required','numeric','min:1'],
        ], $error_messages);

        if($validator->fails()){
            return response()->json([
                "status" => false,
                "title" => "ECHEC D'ENREGISTREMENT",
                "msg" => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();
            $Product = Product::whereKey($request->product_id)->lockForUpdate()->firstOrFail();

            // quantité avant
            $before = $Product->qte;

            // quantité retirée
            $removed = $request->qte_removed;

            if ($removed > $before) {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'title' => 'STOCK INSUFFISANT',
                    'msg' => "La quantité demandée dépasse le stock disponible ({$before}).",
                ], 422);
            }

            // nouvelle quantité
            $after = $before - $removed;

            // update produit
            $Product->update([
                'qte' => $after
            ]);

            // save historique
            $inventory = Inventory::create([
                'type' => 2,
                'product_id' => $Product->id,
                'qte_before' => $before,
                'qte_added' => $removed,
                'qte_after' => $after,
                'note' => $request->note,
                'created_by' => auth()->user()->id,
            ]);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'SORTIE STOCK',
                'text' => auth()->user()->name. " a retiré ".$removed." unité(s) du produit '".$Product->name."'",
            ]);

            // dispatch notification WhatsApp
            SendInventoryWhatsappJob::dispatch($inventory->id, app(CompanyContext::class)->getCompanyId())->afterCommit();

            DB::commit();

            return response()->json([
                "status" => true,
                "title" => "SUCCES",
                "msg" => "Sortie enregistrée avec succès"
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

    public function exportPdf(Request $request)
    {
        $this->ensureFiltersBelongToActiveCompany($request);

        $query = Inventory::with('product', 'user');
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        if ($request->type !== null) {
            $query->where('type', $request->type);
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($start_date && $end_date) {
            $query->whereBetween('created_at', [
                $start_date . ' 00:00:00',
                $end_date . ' 23:59:59'
            ]);
        }

        $inventories = $query->latest()->get();

        $company = app(CompanyContext::class)->getCompany();
        $pdf = Pdf::loadView('component.inventory.pdf', compact('inventories', 'company','start_date','end_date'));
        Action::create([
            'user_id' => auth()->user()->id,
            'function' => 'EXPORTER PDF INVENTAIRE',
            'text' => auth()->user()->name." a exporté l'inventaire en PDF",
        ]);
        return $pdf->download('inventaires.pdf-'. strtoupper($company->name ?? config('app.name')) . '.pdf');
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

    private function ensureFiltersBelongToActiveCompany(Request $request): void
    {
        if ($request->filled('product_id')) {
            abort_unless(Product::whereKey($request->input('product_id'))->exists(), 404);
        }

        if ($request->filled('supplier_id')) {
            abort_unless(Supplier::whereKey($request->input('supplier_id'))->exists(), 404);
        }
    }
}
