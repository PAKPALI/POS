<?php

namespace App\Http\Controllers\Component;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\AMS\Setting;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\CompanyContext;
use App\Services\StreamingTabularExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function __construct(
        private CompanyContext $companyContext,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);
        $this->ensureCategoryFilterBelongsToActiveCompany($request);

        // composer require yajra/laravel-datatables-oracle
        $query = Product::with(['category:id,name', 'supplier:id,name'])
            ->where('type',1)->where('status', 1);

        if($request->category_id){
            $query->where('category_id', $request->category_id);
        }

        if($request->status !== null && $request->status !== ''){
            $query->where('status', $request->status);
        }

        if($request->qte == 'with'){
            $query->where('qte', '>', 0);
        }

        if($request->qte == 'without'){
            $query->where('qte', '<=', 0);
        }

        $Object = $query->latest();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->filterColumn('category_id', function ($query, $keyword) {
                    $query->whereHas('category', fn ($category) => $category->where('name', 'like', "%{$keyword}%"));
                })
                ->filterColumn('supplier_id', function ($query, $keyword) {
                    $query->whereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$keyword}%"));
                })
                ->editColumn('margin', function ($Object) {
                    if($Object->qte>$Object->margin){
                        $btn = '<a class="btn btn-primary btn-sm state1"></a>';
                    }else{
                        $btn = '<a class="btn btn-danger btn-sm state"></a>';
                    }
                    // $btn = ' <a data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"></a>';
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
                ->editColumn('category_id', function ($Object) {
                    return $Object->category->name;
                })
                ->editColumn('supplier_id', function ($Object) {
                    return $Object->supplier ? $Object->supplier->name : '-';
                })
                ->editColumn('price', function ($Object) {
                    return $Object->price? number_format($Object->price, 0, ',', ' ') . ' FCFA' : '-';
                })
                ->editColumn('price_ttc', function ($Object) {
                    return $Object->price_ttc? number_format($Object->price_ttc, 0, ',', ' ') . ' FCFA' : '-';
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = '<span class="saas-status-badge is-active">Actif</span>';
                    }else{
                        $btn = '<span class="saas-status-badge is-inactive">Inactif</span>';
                    }
                    return $btn;
                })
                // ->editColumn('created_by', function ($Object) {
                //     return $Object->user->name;
                // })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['margin','action','status'])
                ->make(true);
        }
        $Category = Category::where('status','1')->orderBy('name', 'asc')->get();
        $Supplier = Supplier::where('status','1')->orderBy('name', 'asc')->get();
        return view('component.product.index',compact('Category','Supplier'));
    }

    public function disabledListing(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with(['category:id,name', 'supplier:id,name'])
            ->where('type',1)->where('status', 0);

        $Object = $query->latest();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
                ->filterColumn('category_id', function ($query, $keyword) {
                    $query->whereHas('category', fn ($category) => $category->where('name', 'like', "%{$keyword}%"));
                })
                ->filterColumn('supplier_id', function ($query, $keyword) {
                    $query->whereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$keyword}%"));
                })
                ->addColumn('action', function($row){
                    $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                            <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                            <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->editColumn('category_id', function ($Object) {
                    return $Object->category->name;
                })
                ->editColumn('supplier_id', function ($Object) {
                    return $Object->supplier ? $Object->supplier->name : '-';
                })
                ->editColumn('price', function ($Object) {
                    return $Object->price? number_format($Object->price, 0, ',', ' ') . ' FCFA' : '-';
                })
                ->editColumn('price_ttc', function ($Object) {
                    return $Object->price_ttc? number_format($Object->price_ttc, 0, ',', ' ') . ' FCFA' : '-';
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = '<span class="saas-status-badge is-active">Actif</span>';
                    }else{
                        $btn = '<span class="saas-status-badge is-inactive">Inactif</span>';
                    }
                    return $btn;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }

        $Category = Category::where('status','1')->orderBy('name', 'asc')->get();
        $Supplier = Supplier::where('status','1')->orderBy('name', 'asc')->get();
        return view('component.product.index',compact('Category','Supplier'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Product::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);

        $error_messages = [
            "type.required" => "Sélectionnez un type!",
            "type.numeric" => "Sélectionnez un type qui doit être un nombre!",
            "type.in" => "Le type de produit sélectionné est invalide!",
            "category.required" => "Sélectionnez une Catégorie!",
            "category.exists" => "La catégorie sélectionnée n'appartient pas à la compagnie active!",
            "supplier_id.exists" => "Le fournisseur sélectionné n'appartient pas à la compagnie active!",
            "name.required" => "Remplir le champ Nom!",
            // "qte.required" => "Remplir le champ Quantité!",
            // "qte.numeric" => "Le champ Quantité doit être un nombre!",
            // "qte.min" => "La quantité ne doit pas être négative!",
            "price.required" => "Remplir le champ Prix unitaire!",
            "price.numeric" => "Le champ Prix unitaire doit être un nombre!",
            "price.min" => "Le prix unitaire ne doit pas être négatif!",
            "purchase_price.required" => "Remplir le champ Prix d'achat!",
            "purchase_price.numeric" => "Le champ Prix d'achat doit être un nombre!",
            "purchase_price.min" => "Le Prix d'achat ne doit pas être négatif!",
            "margin.numeric" => "Le champ Marge doit être un nombre!",
            "margin.min" => "La marge ne doit pas être négative!",
            "image.image" => "Le fichier doit être une image!",
            "image.mimes" => "Le fichier doit être de type: jpeg, png, jpg, gif, svg!",
            "image.max" => "L'image ne doit pas dépasser 2 Mo!",
        ];
        
        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in([1, '1'])],
            'category' => ['required', 'integer', $this->companyExistsRule('categories')],
            'supplier_id' => ['nullable', 'integer', $this->companyExistsRule('suppliers')],
            'name' => ['required'],
            // 'qte' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'margin' => ['numeric', 'min:0'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], $error_messages);
        
        
        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $setting = Setting::first();
            $tax = $setting->default_tax ?? 0;

            // calcul TTC
            $price_ttc = $request->price + ($request->price * $tax / 100);

            $data = [
                'category_id' => $request-> category,
                'supplier_id' => $request->supplier_id ?: null,
                'name' => $request-> name,
                'qte' => $request-> qte??0,
                'price' => $request-> price,
                'price_ttc' => $price_ttc,
                'purchase_price' => $request-> purchase_price,
                'type' => $request-> type,
                'margin' => $request-> margin,
                'profit' => $request-> profit,
                'created_by' => Auth::user()->id,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);

                $data['image'] = $imageName;
            }

            Product::create($data);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'AJOUT PRODUIT',
                'text' => auth()->user()->name." a modifié le produit '".$request->name."'",
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "AJOUT REUSSI",
                "msg" => "Le produit au nom de ".$request-> name." a bien été ajouté"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Product = Product::findOrFail($id);
        $this->authorize('view', $Product);

        return view('component.product.show', compact('Product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Product = Product::findOrFail($id);
        $this->authorize('update', $Product);

        $Category = Category::where('status','1')->latest()->get();
        $Supplier = Supplier::where('status','1')->latest()->get();
        return view('component.product.edit', compact('Product','Category','Supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $Product = Product::findOrFail($id);
        $this->authorize('update', $Product);

        $error_messages = [
            "category.required" => "Sélectionnez une Catégorie!",
            "category.exists" => "La catégorie sélectionnée n'appartient pas à la compagnie active!",
            "supplier_id.exists" => "Le fournisseur sélectionné n'appartient pas à la compagnie active!",
            "name.required" => "Remplir le champ Nom!",
            "qte.required" => "Remplir le champ Quantité!",
            "qte.numeric" => "Le champ Quantité doit être un nombre!",
            "qte.min" => "La quantité ne doit pas être négative!",
            "price.required" => "Remplir le champ Prix unitaire!",
            "price.numeric" => "Le champ Prix unitaire doit être un nombre!",
            "price.min" => "Le prix unitaire ne doit pas être négatif!",
            "purchase_price.required" => "Remplir le champ Prix d'achat!",
            "purchase_price.numeric" => "Le champ Prix d'achat doit être un nombre!",
            "purchase_price.min" => "Le Prix d'achat ne doit pas être négatif!",
            "margin.numeric" => "Le champ Marge doit être un nombre!",
            "margin.min" => "La marge ne doit pas être négative!",
            "image.image" => "Le fichier doit être une image!",
            "image.mimes" => "Le fichier doit être de type: jpeg, png, jpg, gif, svg!",
            "image.max" => "L'image ne doit pas dépasser 2 Mo!",
        ];
        
        $validator = Validator::make($request->all(), [
            'category' => ['required', 'integer', $this->companyExistsRule('categories')],
            'supplier_id' => ['nullable', 'integer', $this->companyExistsRule('suppliers')],
            'name' => ['required'],
            // 'qte' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'margin' => ['numeric', 'min:0'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $setting = Setting::first();
            $tax = $setting->default_tax ?? 0;

            $price_ttc = $request->price + ($request->price * $tax / 100);

            $data = [
                'category_id' => $request-> category,
                'supplier_id' => $request->supplier_id ?: null,
                'name' => $request-> name,
                // 'qte' => $request-> qte??0,
                'price' => $request-> price,
                'price_ttc' => $price_ttc,
                'purchase_price' => $request-> purchase_price,
                'margin' => $request-> margin,
                'profit' => $request-> profit,
                'created_by' => Auth::user()->id,
            ];

            if ($request->hasFile('image')) {
                // delete image if exist
                $oldImagePath = public_path('images/' . $Product->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }

                // save new image
                $image = $request->file('image');
                $imageName = time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);

                $data['image'] = $imageName;
            }

            // verify if new qte of product > product margin
            if ($Product->email ==1) {
                if ($request->qte > $request->margin) {
                    // Log::info('ok');
                    $data['email'] = 0;
                }
            }
            
            $Product->update($data);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'MODIFIER PRODUIT',
                'text' => auth()->user()->name." a modifié le produit '".$request->name."'",
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "MISE A JOUR REUSSIE",
                "msg" => "La catégorie au nom de '".$request-> name."' a bien été mis à jour".$request-> profit
            ]);
    }

    // public function exportPdf()
    // {
    //     $products = Product::where('type', 1)->where('status', 1)->latest()->get();
    //     $company = CompanySetting::first();
    //     $pdf = Pdf::loadView('component.product.pdf', compact('products', 'company'));
    //     return $pdf->download('liste-produits-' . strtoupper($company->name ?? config('app.name')) . '.pdf');
    // }

    public function exportPdf(Request $request)
    {
        $this->authorize('export', Product::class);
        $this->ensureCategoryFilterBelongsToActiveCompany($request);

        $query = Product::where('type', 1);

        if($request->category_id){
            $query->where('category_id', $request->category_id);
        }

        if($request->status !== null && $request->status !== ''){
            $query->where('status', $request->status);
        }

        if($request->qte == 'with'){
            $query->where('qte', '>', 0);
        }

        if($request->qte == 'without'){
            $query->where('qte', '<=', 0);
        }

        $maxRows = (int) config('performance.pdf_exports.products_max_rows', 300);
        if ((clone $query)->limit($maxRows + 1)->pluck('id')->count() > $maxRows) {
            return back()->with('error', "L’export PDF est limité à {$maxRows} produits. Appliquez un filtre plus précis avant de réessayer.");
        }

        $products = $query->latest()->get();

        $company = $this->companyContext->getCompany();

        $pdf = Pdf::loadView('component.product.pdf',compact('products', 'company'));
        Action::create([
            'user_id' => auth()->user()->id,
            'function' => 'EXPORTER PDF PRODUITS',
            'text' => auth()->user()->name." a exporté la liste des produits en PDF",
        ]);

        return $pdf->download('liste-produits-' . strtoupper($company->name ?? config('app.name')) . '.pdf');
    }

    public function exportTabular(Request $request, string $format, StreamingTabularExport $export)
    {
        $this->authorize('export', Product::class);
        $this->ensureCategoryFilterBelongsToActiveCompany($request);
        $query = Product::query()->where('type', 1);
        if ($request->category_id) $query->where('category_id', $request->category_id);
        if ($request->status !== null && $request->status !== '') $query->where('status', $request->status);
        if ($request->qte === 'with') $query->where('qte', '>', 0);
        if ($request->qte === 'without') $query->where('qte', '<=', 0);

        $rows = $query->orderBy('id')->cursor()->map(fn (Product $product) => [
            $product->name,
            (int) $product->qte,
            (float) $product->purchase_price,
            (float) $product->price,
            (float) $product->price_ttc,
            (float) $product->price - (float) $product->purchase_price,
            (int) $product->status === 1 ? 'Actif' : 'Archivé',
        ]);
        Action::create([
            'user_id' => auth()->id(),
            'function' => 'EXPORTER PRODUITS '.strtoupper($format),
            'text' => auth()->user()->name.' a exporté la liste des produits en '.strtoupper($format),
        ]);

        return $export->download($format, 'produits-'.now()->format('Y-m-d-His'), [
            'Nom', 'Quantité', 'Prix achat', 'Prix vente', 'Prix TTC', 'Profit unitaire', 'Statut',
        ], $rows);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Object = Product::with('saleDetails')->findOrFail($id);
        $this->authorize($Object->status == 1 ? 'delete' : 'restore', $Object);

        if($Object->status == 1){
            $productHasSales = $Object->saleDetails()->exists();
            // Produit jamais vendu
            if (!$productHasSales) {

                Action::create([
                    'user_id' => auth()->user()->id,
                    'function' => 'SUPPRESSION D\'UN PRODUIT',
                    'text' => auth()->user()->name." a supprimé le produit : ".$Object->name,
                ]);

                $productName = $Object->name;

                $Object->delete();

                return response()->json([
                    "status" => true,
                    "reload" => true,
                    "title" => "SUPPRESSION REUSSIE",
                    "msg" => "Le produit ".$productName." a été supprimé définitivement car aucune vente n'y est associée."
                ]);
            }

            // Produit déjà vendu => archivage
            $Object->update([
                'status' => 0,
            ]);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ARCHIVAGE D\'UN PRODUIT',
                'text' => auth()->user()->name." a désactivé le produit : ".$Object->name,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "ARCHIVAGE REUSSI",
                "msg" => "Le produit ".$Object->name." a été archivé car il est lié à des ventes."
            ]);

        } else {

            // 🔒 Vérifier la catégorie avant restauration
            if ($Object->category && $Object->category->status == 0) {
                return response()->json([
                    "status" => false,
                    "title" => "RESTAURATION IMPOSSIBLE",
                    "msg" => "Ce produit ne peut pas être restauré car sa catégorie est inactive."
                ]);
            }

            $Object->update([
                'status' => 1,
            ]);

            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'RESTAURER UN PRODUIT',
                'text' => auth()->user()->name." a restauré le produit : ".$Object->name,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "RESTAURATION REUSSIE",
                "msg" => "Le produit ".$Object->name." a bien été restauré"
            ]);
        }
    }

    private function companyExistsRule(string $table)
    {
        return Rule::exists($table, 'id')->where(
            fn ($query) => $query->where('company_id', $this->companyContext->getCompanyId())
        );
    }

    private function ensureCategoryFilterBelongsToActiveCompany(Request $request): void
    {
        if (!$request->filled('category_id')) {
            return;
        }

        abort_unless(Category::whereKey($request->input('category_id'))->exists(), 404);
    }
}
