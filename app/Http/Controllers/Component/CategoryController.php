<?php

namespace App\Http\Controllers\Component;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
        // composer require yajra/laravel-datatables-oracle
        $Category = Category::latest()->get();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Category)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = ' <a data-id="'.$row->id.'" data-name="" data-original-title="Edit" class="btn btn-dark btn-sm absent"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                    <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                    <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    return $btn;
                })
                ->editColumn('created_at', function ($Category) {
                    return $Category->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('component.category.index');
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

            Category::create([
                'name' => $request-> name,
                // 'created_by' => Auth::user()->id,
                'created_by' => 1,
            ]);

            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "AJOUT REUSSI",
                "msg" => "La catégorie au nom de ".$request-> name." a bien été ajoutée"
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $Category = Category::findOrFail($id);
        return view('component.category.edit', compact('Category'));
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
                "title" => "AJOUT ECHOUE",
                "msg" => $validator->errors()->first()
            ]);

            $Category = Category::findOrFail($id);
            $Category->update([
                'name' => $request->name,
            ]);

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
