<?php

namespace App\Http\Controllers\Sale;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // get all categories with their associated products
        $Category = Category::with('products')->get();
        $Product = Product::all();
        return view('pos.sale.index',compact('Category','Product'));
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
        $request->validate([
            'products' => 'required|array',
            'total_amount' => 'required|numeric'
        ]);

        // Enregistrement de la vente
        $sale = Sale::create([
            'total_amount' => $request->total_amount
        ]);

        // Enregistrement des détails de chaque produit
        foreach ($request->products as $product) {
            $sale->saleDetails()->create([
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'unit_price' => $product['unit_price'],
                'total_price' => $product['total_price']
            ]);
        }

        return response()->json(['message' => 'Vente enregistrée avec succès']);
    }

    // class Sale extends Model
    // {
    //     protected $fillable = ['total_amount'];

    //     public function saleDetails()
    //     {
    //         return $this->hasMany(SaleDetail::class);
    //     }
    // }

    // class SaleDetail extends Model
    // {
    //     protected $fillable = ['sale_id', 'product_id', 'quantity', 'unit_price', 'total_price'];

    //     public function sale()
    //     {
    //         return $this->belongsTo(Sale::class);
    //     }
    // }




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
