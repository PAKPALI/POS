<?php

namespace App\Http\Controllers\Sale;

use Carbon\Carbon;
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
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // today date
        $today = Carbon::today();

        // get all categories with their associated products
        $Category = Category::with('products')->get();
        $Product = Product::all();

        // composer require yajra/laravel-datatables-oracle
        $Object = Sale::with('saleDetails.product')->latest()->whereDate('created_at', $today)->get();
        if(request()->ajax()){
            // $Student = Student::all();
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $btn = ' <a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>';
                    return $btn;
                })
                // ->editColumn('cashier', function ($Object) {
                //     return $Object->user->name;
                // })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        // calculate total profit on sale
        $sale_total_profit = 0;
        $product_count = 0 ;
        foreach ($Object as $sale) {
            $sale_total_profit += $sale->total_profit;
            $product_count += $sale->saleDetails->count();
        }

        // calculate top-selling product 
        $mostSoldProducts = DB::table('sale_details')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereDate('created_at', $today) // Filtrer pour les ventes d'aujourd'hui
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(10) // Limit to 10 top-selling product 
            ->get();

        // Add information about each product
        $mostSoldProducts = $mostSoldProducts->map(function ($saleDetail) {
            $saleDetail->product = Product::find($saleDetail->product_id);
            return $saleDetail;
        });

        // Send data to view
        return view('pos.sale.index',compact('Category','Product','mostSoldProducts','Object','sale_total_profit','product_count'));
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

            $totalProfit = 0; // Initialize the variable before the loop

            // store sale detail
            foreach ($request->products as $product) {
                // search product
                $Product = Product::findOrFail($product['product_id']);
                if (!$Product) {
                    DB::rollBack();
                    return response()->json([
                        "status" => false,
                        "reload" => false,
                        "title" => "VENTE ECHOUEE",
                        "msg" => "Le produit avec l'ID ". $product['product_id'] . " est introuvable."
                    ]);
                }

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

                // update total profit to sale table column
                $totalProfit += $profit;// add totalProfit
                $sale->update([
                    'total_profit' =>$totalProfit,
                ]);
            }

            // Generate  PDF invoice
            // composer require barryvdh/laravel-dompdf
            $pdf = Pdf::loadView('pos.invoice', ['sale' => $sale,'saleDetails' => $sale->saleDetails])
                // ->setPaper('A6', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'dpi' => 150 // Augmente le DPI pour une meilleure résolution
                ]);

            $pdf->setPaper([0, 0, 300, 400],'portrait'); // Dimensions personnalisées en points pour un reçu plus petit

            // $pdf = Pdf::loadView('pos.invoice', ['sale' => $sale, 'saleDetails' => $saleDetails])
            //     ->setPaper([0, 0, 226.77, 650], 'portrait'); // Largeur 80mm, hauteur ajustable

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
            return response()->json(["status" => false, "msg" => $saleDetails."Erreur survenue lors de la vente , contacter le développeur". $th->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Sale = Sale::findOrFail($id);
        return view('pos.sale.show_detail', compact('Sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
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
