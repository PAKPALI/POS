<?php

namespace App\Http\Controllers\Component;

use App\Models\Action;
use App\Models\Product;
use App\Models\Category;
use App\Models\MenuProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
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
            'type' => ['required', 'numeric'],
            'category' => ['required', Rule::exists('categories', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)
            )],
            'name' => ['required'],
            'qte' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'margin' => ['numeric'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

            'products' => ['required'], // Valider que c'est un tableau
            'products.*.product_id' => ['required', Rule::exists('products', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)->where('type', 1)
            )],
            'products.*.quantity' => ['required', 'numeric', 'min:1'], // Vérifie la quantité
        ], $error_messages);
        
        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $data = [
                'category_id' => $request-> category,
                'name' => $request-> name,
                'qte' => $request-> qte,
                'price' => $request-> price,
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

            $Menu = Product::create($data);

            if ($request->has('products')) {
                $products = $request->products;
                foreach ($products as $product) {
                    MenuProduct::create([
                        'menu_id' => $Menu->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity']
                    ]);
                }
                Action::create([
                    'user_id' => auth()->user()->id,
                    'function' => 'AJOUT MENU',
                    'text' => auth()->user()->name." a créer un nouveau menu '".$request->name."'",
                ]);
    
                return response()->json([
                    "status" => true,
                    "reload" => true,
                    // "redirect_to" => route('user'),
                    "title" => "AJOUT REUSSI",
                    "msg" => "Le menu au nom de ".$request-> name." a bien été ajouté"
                ]);
            }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $MenuProduct = Product::findOrFail($id);
        return view('component.menu.show', compact('MenuProduct'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Product = Product::findOrFail($id);
        $MenuProduct = $Product->MenuProducts()->with('product:id,name')->get();
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
            'type' => ['required', 'numeric'],
            'category' => ['required', Rule::exists('categories', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)
            )],
            'name' => ['required'],
            'qte' => ['required', 'numeric'],
            'price' => ['required', 'numeric'],
            'margin' => ['numeric'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],

            'products' => ['required'], // Valider que c'est un tableau
            'products.*.product_id' => ['required', Rule::exists('products', 'id')->where(
                fn ($query) => $query->where('company_id', app(CompanyContext::class)->getCompanyId())->where('status', 1)->where('type', 1)
            )],
            'products.*.quantity' => ['required', 'numeric', 'min:1'], // Vérifie la quantité
        ], $error_messages);
        
        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $MenuProduct = Product::findOrFail($id);
            $data = [
                'category_id' => $request-> category,
                'name' => $request-> name,
                'qte' => $request-> qte,
                'price' => $request-> price,
                'purchase_price' => $request-> purchase_price,
                'margin' => $request-> margin,
                'profit' => $request-> profit,
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
            
            $MenuProduct->update($data);

            foreach ($MenuProduct->MenuProducts as $mp) {
                $mp->delete($data);
            }

            if ($request->has('products')) {
                $products = $request->products;
                foreach ($products as $product) {
                    MenuProduct::create([
                        'menu_id' => $MenuProduct->id,
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity']
                    ]);
                }
                Action::create([
                    'user_id' => auth()->user()->id,
                    'function' => 'MISE A JOUR DU MENU',
                    'text' => auth()->user()->name." a mis a jour le menu '".$MenuProduct->name."'",
                ]);
    
                return response()->json([
                    "status" => true,
                    "reload" => true,
                    // "redirect_to" => route('user'),
                    "title" => "MISE A JOUR REUSSIE",
                    "msg" => "Le menu au nom de ".$MenuProduct->name." a bien été mis a jour"
                ]);
            }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $Object = Product::findOrFail($id);
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
                "msg" => "Le menu ".$Object->name." a bien été désactivé"
            ]);
        }else{
            $Object->update([
                'status' => 1,
            ]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'RESTAURER UN MENU',
                'text' => auth()->user()->name." a restaurer le menu : ".$Object->name,
            ]); 
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "RESTAURATION REUSSIE",
                "msg" => "Le menu ".$Object->name." a bien été restauré"
            ]);
        }
    }
}
