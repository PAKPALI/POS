<?php

namespace App\Http\Controllers\Component;

use App\Http\Controllers\Controller;
use App\Jobs\SendInventoryWhatsappJob;
use App\Models\Action;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\CompanyContext;
use App\Services\StreamingTabularExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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
        $this->authorize('viewAny', Inventory::class);
        $this->ensureFiltersBelongToActiveCompany($request);

        // composer require yajra/laravel-datatables-oracle
        $Object = Inventory::with([
            'product:id,name',
            'supplier:id,name',
            'user:id,name',
        ]);

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
                ->filterColumn('product_id', function ($query, $keyword) {
                    $query->whereHas('product', fn ($product) => $product->where('name', 'like', "%{$keyword}%"));
                })
                ->filterColumn('supplier_id', function ($query, $keyword) {
                    $query->whereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$keyword}%"));
                })
                ->filterColumn('created_by', function ($query, $keyword) {
                    $query->whereHas('user', fn ($user) => $user->where('name', 'like', "%{$keyword}%"));
                })
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
        return view('component.inventory.index');
    }

    public function searchProducts(Request $request)
    {
        $this->authorize('viewAny', Inventory::class);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'in_stock' => ['nullable', 'boolean'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $products = Product::query()
            ->select(['id', 'name', 'qte'])
            ->where('status', 1)
            ->when($validated['in_stock'] ?? false, fn ($query) => $query->where('qte', '>', 0))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'results' => $products->getCollection()->map(fn (Product $product) => [
                'id' => $product->id,
                'text' => $product->name.' ('.$product->qte.')',
            ])->values(),
            'pagination' => ['more' => $products->hasMorePages()],
        ]);
    }

    public function searchSuppliers(Request $request)
    {
        $this->authorize('viewAny', Inventory::class);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $suppliers = Supplier::query()
            ->select(['id', 'name'])
            ->where('status', 1)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'results' => $suppliers->getCollection()
                ->map(fn (Supplier $supplier) => ['id' => $supplier->id, 'text' => $supplier->name])
                ->values(),
            'pagination' => ['more' => $suppliers->hasMorePages()],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Inventory::class);
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Inventory::class);
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
        $this->authorize('create', Inventory::class);
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
        $this->authorize('view', $Inventory);
        return view('component.inventory.show', compact('Inventory'));
    }

    public function exportPdf(Request $request)
    {
        $this->authorize('export', Inventory::class);
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

        $maxRows = (int) config('performance.pdf_exports.inventories_max_rows', 500);
        if ((clone $query)->limit($maxRows + 1)->pluck('id')->count() > $maxRows) {
            return back()->with('error', "L’export PDF est limité à {$maxRows} mouvements. Réduisez la période ou appliquez davantage de filtres.");
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

    public function exportTabular(Request $request, string $format, StreamingTabularExport $export)
    {
        $this->authorize('export', Inventory::class);
        $this->ensureFiltersBelongToActiveCompany($request);
        $query = Inventory::query()
            ->leftJoin('products', 'products.id', '=', 'inventories.product_id')
            ->leftJoin('suppliers', 'suppliers.id', '=', 'inventories.supplier_id')
            ->leftJoin('users', 'users.id', '=', 'inventories.created_by')
            ->select([
                'inventories.type', 'inventories.qte_before', 'inventories.qte_added',
                'inventories.qte_after', 'inventories.created_at', 'products.name as product_name',
                'suppliers.name as supplier_name', 'users.name as creator_name',
            ]);
        if ($request->type !== null) $query->where('inventories.type', $request->type);
        if ($request->product_id) $query->where('inventories.product_id', $request->product_id);
        if ($request->supplier_id) $query->where('inventories.supplier_id', $request->supplier_id);
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('inventories.created_at', [
                $request->start_date.' 00:00:00', $request->end_date.' 23:59:59',
            ]);
        }

        $rows = $query->orderBy('inventories.id')->cursor()->map(fn ($inventory) => [
            $inventory->product_name ?? 'Produit supprimé',
            (int) $inventory->type === 1 ? 'Entrée' : 'Sortie',
            (int) $inventory->qte_before,
            (int) $inventory->qte_added,
            (int) $inventory->qte_after,
            $inventory->supplier_name ?? '-',
            $inventory->creator_name ?? '-',
            Carbon::parse($inventory->created_at)->format('d-m-Y H:i:s'),
        ]);
        Action::create([
            'user_id' => auth()->id(),
            'function' => 'EXPORTER INVENTAIRE '.strtoupper($format),
            'text' => auth()->user()->name.' a exporté l’inventaire en '.strtoupper($format),
        ]);

        return $export->download($format, 'inventaire-'.now()->format('Y-m-d-His'), [
            'Produit', 'Type', 'Qté avant', 'Qté saisie', 'Qté après', 'Fournisseur', 'Créé par', 'Date',
        ], $rows);
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
