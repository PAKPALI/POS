<?php

namespace App\Http\Controllers\Sale;

use App\Models\Sale;
use App\Models\Action;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

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

    public function code()
    {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= rand(0, 9);
        }
        return $code;
    }

    public function store(Request $request)
    {
        $error_messages = [
            'products.required' => "Choisir un produit",
            'products.*.quantity.min' => "La quantité doit être supérieure à 0 pour chaque produit",
            'total_amount.required' => "Remplir le total",
        ];
        
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric'
        ], $error_messages);
        
        
        if($validator->fails())
        return response()->json([
            "status" => false,
            "reload" => false,
            "title" => "VENTE ECHOUEE",
            "msg" => $validator->errors()->first()
        ]);
        try {
            DB::beginTransaction();
            // store sale
            $sale = Sale::create([
                'code' => $this->code(),
                'total_amount' => $request->total_amount,
                'cashier' => auth()->user()->name
            ]);

            // store sale detail
            foreach ($request->products as $product) {
                // search product
                $Product = Product::findOrFail($product['product_id']);
                // calculate profit and store it in sale detail
                $profit = $Product->profit*$product['quantity'];
                // store sale detail
                $saleDetails = $sale->saleDetails()->create([
                    'product_id' => $product['product_id'],
                    'quantity' => $product['quantity'],
                    'unit_price' => $product['unit_price'],
                    'total_price' => $product['total_price'],
                    'profit' => $profit
                ]);
                // update product quantity if product qte is greater than user quantity
                if($Product->qte>$product['quantity']){
                    $newQte = $Product->qte - $product['quantity'];
                    $Product->update([
                        'qte' =>$newQte,
                    ]);
                }else{
                    DB::rollBack();
                    return response()->json([
                        "status" => false,
                        "reload" => false,
                        "title" => "VENTE ECHOUEE",
                        "msg" => "Le produit ". $Product->name. " n'a plus de stock disponible pour la quantité demandée"
                    ]);
                } 
            }

            // Generate  PDF invoice
            // composer require barryvdh/laravel-dompdf
            $pdf = Pdf::loadView('pos.invoice', ['sale' => $sale,'saleDetails' => $saleDetails]);

            // save or return PDF in base64
            $pdfBase64 = base64_encode($pdf->output());

            // log action
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'VENTE',
                'text' => auth()->user()->name." a effectué une vente",
            ]);
            DB::commit();
            return response()->json([
                "status" => true,
                "reload" => true,
                // "redirect_to" => route('user'),
                "title" => "VENTE EFFECTUEE",
                "msg" => "",
                'pdfBase64' => $pdfBase64
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Une erreur est survenue lors de la vente' . $th->getMessage());
            return response()->json(["status" => false, "msg" => "Erreur survenue lors de la vente , contacter le développeur". $th->getMessage()]);
        }
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
