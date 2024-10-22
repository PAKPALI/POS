<?php

namespace App\Http\Controllers\Component;

use App\Models\Action;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
        $Product = Product::latest()->get();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Product)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = ' <a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                    <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->editColumn('category_id', function ($Product) {
                    return $Product->category->name;
                })
                ->editColumn('created_by', function ($Product) {
                    return $Product->user->name;
                })
                ->editColumn('created_at', function ($Product) {
                    return $Product->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action'])
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
            "category.required" => "Sélectionnez une Catégorie!",
            "name.required" => "Remplir le champ Nom!",
            "qte.required" => "Remplir le champ Quantité!",
            "qte.numeric" => "Le champ Quantité doit être un nombre!",
            "margin.required" => "Remplir le champ Marge de sécurité!",
            "image.image" => "Le fichier doit être une image!",
            "image.mimes" => "Le fichier doit être de type: jpeg, png, jpg, gif, svg!",
            "image.max" => "L'image ne doit pas dépasser 2 Mo!",
        ];
        
        $validator = Validator::make($request->all(), [
            'category' => ['required'],
            'name' => ['required'],
            'qte' => ['required', 'numeric'],
            'margin' => ['required', 'numeric'],
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
                'margin' => $request-> margin,
                'created_by' => Auth::user()->id,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);

                $data['image'] = $imageName;
            }

            Product::create($data);

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
            "margin.required" => "Remplir le champ Marge de sécurité!",
            "image.image" => "Le fichier doit être une image!",
            "image.mimes" => "Le fichier doit être de type: jpeg, png, jpg, gif, svg!",
            "image.max" => "L'image ne doit pas dépasser 2 Mo!",
        ];
        
        $validator = Validator::make($request->all(), [
            'category' => ['required'],
            'name' => ['required'],
            'qte' => ['required', 'numeric'],
            'margin' => ['required', 'numeric'],
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
                'margin' => $request-> margin,
                'created_by' => Auth::user()->id,
            ];

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'.'.$image->getClientOriginalExtension();
                $image->move(public_path('images'), $imageName);

                $data['image'] = $imageName;
            }
            $Product->update([$data]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "MISE A JOUR REUSSIE",
                "msg" => "La catégorie au nom de '".$request-> name."' a bien été mis à jour"
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}