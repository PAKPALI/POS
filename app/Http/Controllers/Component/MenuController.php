<?php

namespace App\Http\Controllers\Component;

use App\Models\Action;
use App\Models\Product;
use App\Models\Category;
use App\Models\MenuProduct;
use App\Models\Inventory;
use App\Models\AMS\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = Product::with('category:id,name')->where('type',2)->latest();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
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
        $Category = Category::where('status','1')->orderBy('name')->get(['id', 'name']);
        return view('component.menu.index',compact('Category'));
    }

    public function searchProducts(Request $request)
    {
        $this->authorize('viewAny', Product::class);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $search = trim((string) ($validated['q'] ?? ''));
        $products = Product::query()
            ->select(['id', 'name', 'qte'])
            ->where('status', 1)
            ->where('type', 1)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'results' => $products->getCollection()->map(fn (Product $product) => [
                'id' => $product->id,
                'qte' => (int) $product->qte,
                'text' => $product->name.' ('.$product->qte.')',
            ])->values(),
            'pagination' => ['more' => $products->hasMorePages()],
        ]);
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
            "type.required" => "Sélectionnez un type!",
            "type.in" => "Le type de pack est invalide!",
            "category.required" => "Sélectionnez une Catégorie!",
            "name.required" => "Remplir le champ Nom!",
            "qte.required" => "Remplir le champ Quantité disponible!",
            "qte.integer" => "La quantité disponible doit être un nombre entier!",
            "price.required" => "Remplir le champ Prix unitaire!",
            "price.numeric" => "Le champ Prix unitaire doit être un nombre!",
            // "margin.required" => "Remplir le champ Marge de sécurité!",
            "image.image" => "Le fichier doit être une image!",
            "image.mimes" => "Le fichier doit être de type: jpeg, png, jpg, gif, svg!",
            "image.max" => "L'image ne doit pas dépasser 2 Mo!",

            "products.required" => "Ajoutez au moins un produit!",
            "products.array" => "Les produits doivent être sous forme de tableau!",
            "products.*.product_id.required" => "Un produit doit être sélectionné!",
            "products.*.product_id.exists" => "Le produit sélectionné est invalide!",
            "products.*.quantity.required" => "La quantité pour chaque produit est obligatoire!",
            "products.*.quantity.numeric" => "La quantité doit être un nombre!",
            "products.*.quantity.min" => "La quantité doit être au moins 1!",
        ];
        
        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in([2, '2'])],
            'category' => ['required', Rule::exists('categories', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)
            )],
            'name' => ['required', 'string', 'max:255'],
            'qte' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'margin' => ['nullable', 'integer', 'min:0', 'lt:qte'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)->where('type', 1)
            )],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ], $error_messages);

        $validator->after(function ($validator) use ($request): void {
            foreach ((array) $request->input('products', []) as $index => $component) {
                $stock = Product::whereKey($component['product_id'] ?? null)->where('type', 1)->value('qte');
                if ($stock !== null && (int) ($component['quantity'] ?? 0) > (int) $stock) {
                    $validator->errors()->add("products.{$index}.quantity", "La quantité du produit sélectionné ne peut pas dépasser son stock actuel ({$stock}).");
                }
            }
        });
        
        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $purchasePrice = $request->filled('purchase_price') ? (float) $request->purchase_price : null;
            $tax = (float) (Setting::first()?->default_tax ?? 0);
            $data = [
                'category_id' => $request-> category,
                'name' => $request-> name,
                'qte' => $request-> qte,
                'price' => $request-> price,
                'price_ttc' => (float) $request->price * (1 + $tax / 100),
                'purchase_price' => $purchasePrice,
                'type' => 2,
                'margin' => $request->filled('margin') ? (int) $request->margin : null,
                'profit' => $purchasePrice === null ? null : (float) $request->price - $purchasePrice,
                'created_by' => Auth::user()->id,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);

                $data['image'] = $imageName;
            }

            DB::transaction(function () use ($data, $request) {
                $pack = Product::create($data);
                foreach ($request->products as $product) {
                    MenuProduct::create([
                        'menu_id' => $pack->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity']
                    ]);
                }
                if ((int) $pack->qte > 0) {
                    Inventory::create([
                        'type' => 1, 'product_id' => $pack->id, 'qte_before' => 0,
                        'qte_added' => $pack->qte, 'qte_after' => $pack->qte,
                        'note' => 'Stock initial à la création du pack', 'created_by' => auth()->id(),
                    ]);
                }
                Action::create([
                    'user_id' => auth()->user()->id,
                    'function' => 'AJOUT PACK',
                    'text' => auth()->user()->name." a créé le pack '".$request->name."'",
                ]);
            });

                return response()->json([
                    "status" => true,
                    "reload" => true,
                    // "redirect_to" => route('user'),
                    "title" => "AJOUT REUSSI",
                    "msg" => "Le pack ".$request->name." a bien été ajouté"
                ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $MenuProduct = Product::where('type', 2)->findOrFail($id);
        return view('component.menu.show', compact('MenuProduct'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Product = Product::where('type', 2)->findOrFail($id);
        $MenuProduct = $Product->MenuProducts()->with('product:id,name,qte')->get();
        $Category = Category::where('status','1')->orderBy('name')->get(['id', 'name']);
        return view('component.menu.edit', compact('Product','Category','MenuProduct'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $error_messages = [
            "type.required" => "Sélectionnez un type!",
            "type.numeric" => "Sélectionnez un type qui doit être un nombre!",
            "category.required" => "Sélectionnez une Catégorie!",
            "name.required" => "Remplir le champ Nom!",
            "qte.required" => "Remplir le champ Quantité!",
            "qte.numeric" => "Le champ Quantité doit être un nombre!",
            "price.required" => "Remplir le champ Prix unitaire!",
            "price.numeric" => "Le champ Prix unitaire doit être un nombre!",
            // "margin.required" => "Remplir le champ Marge de sécurité!",
            "image.image" => "Le fichier doit être une image!",
            "image.mimes" => "Le fichier doit être de type: jpeg, png, jpg, gif, svg!",
            "image.max" => "L'image ne doit pas dépasser 2 Mo!",

            "products.required" => "Ajoutez au moins un produit!",
            "products.array" => "Les produits doivent être sous forme de tableau!",
            "products.*.product_id.required" => "Un produit doit être sélectionné!",
            "products.*.product_id.exists" => "Le produit sélectionné est invalide!",
            "products.*.quantity.required" => "La quantité pour chaque produit est obligatoire!",
            "products.*.quantity.numeric" => "La quantité doit être un nombre!",
            "products.*.quantity.min" => "La quantité doit être au moins 1!",
        ];
        
        $validator = Validator::make($request->all(), [
            'type' => ['required', Rule::in([2, '2'])],
            'category' => ['required', Rule::exists('categories', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)
            )],
            'name' => ['required', 'string', 'max:255'],
            'qte' => ['required', 'integer', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'margin' => ['nullable', 'integer', 'min:0', 'lt:qte'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'distinct', Rule::exists('products', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)->where('type', 1)
            )],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ], $error_messages);

        $validator->after(function ($validator) use ($request): void {
            foreach ((array) $request->input('products', []) as $index => $component) {
                $stock = Product::whereKey($component['product_id'] ?? null)->where('type', 1)->value('qte');
                if ($stock !== null && (int) ($component['quantity'] ?? 0) > (int) $stock) {
                    $validator->errors()->add("products.{$index}.quantity", "La quantité du produit sélectionné ne peut pas dépasser son stock actuel ({$stock}).");
                }
            }
        });
        
        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $MenuProduct = Product::where('type', 2)->findOrFail($id);
            $purchasePrice = $request->filled('purchase_price') ? (float) $request->purchase_price : null;
            $tax = (float) (Setting::first()?->default_tax ?? 0);
            $data = [
                'category_id' => $request-> category,
                'name' => $request-> name,
                'qte' => $request-> qte,
                'price' => $request-> price,
                'price_ttc' => (float) $request->price * (1 + $tax / 100),
                'purchase_price' => $purchasePrice,
                'margin' => $request->filled('margin') ? (int) $request->margin : null,
                'profit' => $purchasePrice === null ? null : (float) $request->price - $purchasePrice,
                'created_by' => Auth::user()->id,
            ];

            if ($request->hasFile('image')) {
                // delete image if exist
                $oldImagePath = public_path('images/' . $MenuProduct->image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }

                // save new image
                $image = $request->file('image');
                $imageName = time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);

                $data['image'] = $imageName;
            }
            
            DB::transaction(function () use ($MenuProduct, $data, $request) {
                $previousQuantity = (int) $MenuProduct->qte;
                $MenuProduct->update($data);
                if ($previousQuantity !== (int) $MenuProduct->qte) {
                    Inventory::create([
                        'type' => $MenuProduct->qte > $previousQuantity ? 1 : 2,
                        'product_id' => $MenuProduct->id,
                        'qte_before' => $previousQuantity,
                        'qte_added' => abs((int) $MenuProduct->qte - $previousQuantity),
                        'qte_after' => $MenuProduct->qte,
                        'note' => 'Ajustement du stock du pack',
                        'created_by' => auth()->id(),
                    ]);
                }
                $MenuProduct->MenuProducts()->delete();
                foreach ($request->products as $product) {
                    MenuProduct::create([
                        'menu_id' => $MenuProduct->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity']
                    ]);
                }
                Action::create([
                    'user_id' => auth()->user()->id,
                    'function' => 'MISE A JOUR DU MENU',
                    'text' => auth()->user()->name." a mis à jour le pack '".$MenuProduct->name."'",
                ]);
    
            });

                return response()->json([
                    "status" => true,
                    "reload" => true,
                    // "redirect_to" => route('user'),
                    "title" => "MISE A JOUR REUSSIE",
                    "msg" => "Le pack ".$MenuProduct->name." a bien été mis à jour"
                ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Object = Product::where('type', 2)->findOrFail($id);
        if($Object->status ==1){
            $Object->update([
                'status' => 0,
            ]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'ARCHIVAGE D\'UN MENU',
                'text' => auth()->user()->name." a désactivé le produit : ".$Object->name,
            ]);
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "ARCHIVAGE REUSSIE",
                "msg" => "Le pack ".$Object->name." a bien été désactivé"
            ]);
        }else{
            $Object->update([
                'status' => 1,
            ]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'RESTAURER UN MENU',
                'text' => auth()->user()->name." a restauré le pack : ".$Object->name,
            ]); 
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "RESTAURATION REUSSIE",
                "msg" => "Le pack ".$Object->name." a bien été restauré"
            ]);
        }
    }
}
