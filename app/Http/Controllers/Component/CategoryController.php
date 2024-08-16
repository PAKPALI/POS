<?php

namespace App\Http\Controllers\Component;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('component.category.index');
        // composer require yajra/laravel-datatables-oracle
        // $Student = Student::where('classrooms_id', $classroom_id)->get();
        // if(request()->ajax()){
        //     // $Student = Student::all();
        //     return DataTables::of($Student)
        //         ->addIndexColumn()
        //         ->addColumn('action', function($row){
        //             $btn = '<a href="javascript:void(0)" data-toggle="modal" data-target="#modal-update"  data-id="'.$row->id.'" data-original-title="Edit" class="btn btn-warning btn-sm editStudent">M</a>'.
        //             $btn = ' <a data-id="'.$row->id.'" data-name="'.$row->fullName().'" data-original-title="Edit" class="btn btn-dark btn-sm absent">AB?</a>'.
        //             $btn = ' <a data-id="'.$row->id.'" data-original-title="Archiver" class="btn btn-danger btn-sm archive">AC?</a>';
        //             return $btn;
        //         })
        //         ->rawColumns(['action'])
        //         ->make(true);
        // }
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
