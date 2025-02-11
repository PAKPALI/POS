<?php

namespace App\Http\Controllers\CodePromo;

use App\Models\CodePromo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class CodePromoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // composer require yajra/laravel-datatables-oracle
        $Object = CodePromo::latest()->get();
        if(request()->ajax()){
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    if($row->status==1){
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }else{
                        $btn = '<a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                                <a href="javascript:void(0)" data-toggle="modal" data-target="#updateModal"  data-id="'.$row->id.'" data-original-title="Modifier" class="btn btn-warning btn-sm editModal"><i class="fas fa-lg fa-fw me-0 fa-edit"></i></a>
                                <a data-id="'.$row->id.'" data-original-title="restaurer" class="btn btn-success btn-sm restore"><i class="fas fa-lg fa-fw me-0 fa-trash-alt"></i></a>';
                    }
                    return $btn;
                })
                ->editColumn('status', function ($Object) {
                    if($Object->status==1){
                        $btn = ' <a class="btn btn-success btn-sm">Actif</a>';
                    }else{
                        $btn = ' <a class="btn btn-danger btn-sm">Inactif</a>';
                    }
                    return $btn;
                })
                ->editColumn('created_by', function ($Object) {
                    return $Object->user->name;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action','status'])
                ->make(true);
        }
        return view('code.index');
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
        //
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
