<?php

namespace App\Http\Controllers\Component;

use App\Models\Action;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = Product::latest()->get();
        if(request()->ajax()){
            // $Student = Student::all();
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
        $Category = Category::where('status','1')->latest()->get();
        return view('component.product.index',compact('Category'));
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
            'type' => ['required', 'numeric'],
            'category' => ['required'],
            'name' => ['required'],
            'qte' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'margin' => ['numeric', 'min:0'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
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
                'function' => 'AJOUT PRODUIT',
                'text' => auth()->user()->name." a créer un nouveau produit '".$request->name."'",
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
        return view('component.product.show', compact('Product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $Product = Product::findOrFail($id);
        $Category = Category::where('status','1')->latest()->get();
        return view('component.product.edit', compact('Product','Category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $error_messages = [
            "category.required" => "Sélectionnez une Catégorie!",
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
            'category' => ['required'],
            'name' => ['required'],
            'qte' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'purchase_price' => ['required', 'numeric', 'min:0'],
            'margin' => ['numeric', 'min:0'],
            'image' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ], $error_messages);

        if($validator->fails())
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $Product = Product::findOrFail($id);
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
                'function' => 'ARCHIVAGE D\'UN PRODUIT',
                'text' => auth()->user()->name." a désactivé le produit : ".$Object->name,
            ]);
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "ARCHIVAGE REUSSIE",
                "msg" => "Le produit ".$Object->name." a bien été désactivé"
            ]);
        }else{
            $Object->update([
                'status' => 1,
            ]);
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'RESTAURER UN PRODUIT',
                'text' => auth()->user()->name." a restaurer le produit : ".$Object->name,
            ]); 
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "RESTAURATION REUSSIE",
                "msg" => "Le produit ".$Object->name." a bien été restauré"
            ]);
        }
    }
}