<?php

namespace App\Http\Controllers\Sale;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Action;
use App\Models\Product;
use App\Models\Category;
use App\Models\CodePromo;
use Illuminate\Support\Str;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
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
        $total_amount = 0;
        $sale_total_profit = 0;
        $product_count = 0 ;
        foreach ($Object as $sale) {
            $total_amount += $sale->total_amount;
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
        return view('pos.sale.index',compact('Category','Product','mostSoldProducts','Object','sale_total_profit','product_count','total_amount'));
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

    // public function store(Request $request)
    // {
    //     $error_messages = [
    //         'products.required' => "Choisir un produit",
    //         'products.*.quantity.min' => "La quantité doit être supérieure à 0 pour chaque produit",
    //         'total_amount.required' => "Remplir le total",
    //     ];
        
    //     $validator = Validator::make($request->all(), [
    //         'products' => 'required|array',
    //         'products.*.quantity' => 'required|integer|min:1',
    //         'total_amount' => 'required|numeric'
    //     ], $error_messages);
        
    //     if($validator->fails())
    //     return response()->json([
    //         "status" => false,
    //         "reload" => false,
    //         "title" => "VENTE ECHOUEE",
    //         "msg" => $validator->errors()->first()
    //     ]);
    //     try {
    //         DB::beginTransaction();
    //         // store sale
    //         $sale = Sale::create([
    //             'code' => '#'.$this->code(),
    //             'total_amount' => $request->total_amount,
    //             'cashier' => auth()->user()->name
    //         ]);
    //         $totalProfit = 0; // Initialize the variable before the loop

    //         // store sale detail
    //         foreach ($request->products as $product) {
    //             // search product
    //             $Product = Product::findOrFail($product['product_id']);
    //             if (!$Product) {
    //                 DB::rollBack();
    //                 return response()->json([
    //                     "status" => false,
    //                     "reload" => false,
    //                     "title" => "VENTE ECHOUEE",
    //                     "msg" => "Le produit avec l'ID ". $product['product_id'] . " est introuvable."
    //                 ]);
    //             }

    //             // calculate profit and store it in sale detail
    //             $profit = $Product->profit*$product['quantity'];

    //             // store sale detail
    //             $saleDetails = $sale->saleDetails()->create([
    //                 'product_id' => $product['product_id'],
    //                 'quantity' => $product['quantity'],
    //                 'unit_price' => $product['unit_price'],
    //                 'total_price' => $product['total_price'],
    //                 'profit' => $profit
    //             ]);

    //             // update product quantity if product qte is greater than user quantity
    //             if($Product->qte >= $product['quantity']){
    //                 $newQte = $Product->qte - $product['quantity'];
    //                 $Product->update([
    //                     'qte' =>$newQte,
    //                 ]);
    //                 // check if security margin is affected
    //                 if($newQte <= $Product->margin) {
    //                     $User = User::where('status',1)->get();
    //                     foreach ($User as $user) {
    //                         $this->sendEmailMargin($user->name,$user->email,$Product->name,$Product->margin, $newQte);
    //                     }
    //                 }
    //             }else{
    //                 DB::rollBack();
    //                 return response()->json([
    //                     "status" => false,
    //                     "reload" => false,
    //                     "title" => "VENTE ECHOUEE",
    //                     "msg" => "Le produit ". $Product->name. " n'a plus de stock disponible pour la quantité demandée"
    //                 ]);
    //             }

    //             // update total profit to sale table column
    //             $totalProfit += $profit;// add totalProfit
    //             $sale->update([
    //                 'total_profit' =>$totalProfit,
    //             ]);
    //         }

    //         // Generate  PDF invoice
    //         // composer require barryvdh/laravel-dompdf
    //         $pdf = Pdf::loadView('pos.invoice', ['sale' => $sale,'saleDetails' => $sale->saleDetails])
    //             // ->setPaper('A6', 'portrait')
    //             ->setOptions([
    //                 'isHtml5ParserEnabled' => true,
    //                 'isRemoteEnabled' => true,
    //                 'dpi' => 150 // Augmente le DPI pour une meilleure résolution
    //             ]);

    //         $pdf->setPaper([0, 0, 300, 400],'portrait'); // Dimensions personnalisées en points pour un reçu plus petit

    //         // save or return PDF in base64
    //         $pdfBase64 = base64_encode($pdf->output());

    //         // log action
    //         Action::create([
    //             'user_id' => auth()->user()->id,
    //             'function' => 'VENTE',
    //             'text' => auth()->user()->name." a effectué une vente",
    //         ]);

    //         DB::commit();
    //         return response()->json([
    //             "status" => true,
    //             "reload" => true,
    //             // "redirect_to" => route('user'),
    //             "title" => "VENTE EFFECTUEE",
    //             "msg" => "",
    //             'pdfBase64' => $pdfBase64
    //         ]);

    //     } catch (\Throwable $th) {
    //         DB::rollBack();
    //         Log::error('Une erreur est survenue lors de la vente' . $th->getMessage());
    //         return response()->json(["status" => false, "msg" => $saleDetails."Erreur survenue lors de la vente , contacter le développeur". $th->getMessage()]);
    //     }
    // }

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
        
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "VENTE ECHOUEE",
                "msg" => $validator->errors()->first()
            ]);
        }

        try {
            DB::beginTransaction();
            $percent=0;
            if($request->code_promo){
                $code_promo = CodePromo::where('code', $request->code_promo)->where('status', 1)->first();
                $percent = $code_promo->percents;
            }

            // Store sale
            $sale = Sale::create([
                'code' => $this->code(),
                'received_amount' => $request->received_amount,
                'total_amount' => $request->total_amount,
                'remaining_amount' => $request->received_amount-$request->total_amount,
                'code_promo' => $percent,
                'discount' => $request->discount,
                'amount_init' => $request->discount+$request->total_amount,
                'cashier' => auth()->user()->name,
            ]);

            // Process products and store sale details
            $totalProfit = $this->processProducts($sale, $request->products);

            // Update the total profit in the sale table
            $sale->update(['total_profit' => $totalProfit]);

            // Generate PDF invoice
            $pdfBase64 = $this->generatePdfInvoice($sale);

            // send sms to client
            $number = '90859488';
            $message = 'Vous venez de faire un achat au total de 0FCFA au niveau de LUX-GRILL et nous vous remercions.';
            $this->sendSms($number, $message);

            // Log action
            Action::create([
                'user_id' => auth()->user()->id,
                'function' => 'VENTE',
                'text' => auth()->user()->name . " a effectué une vente",
            ]);

            DB::commit();
            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "VENTE EFFECTUEE",
                "msg" => "",
                'pdfBase64' => $pdfBase64,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "msg" => "Erreur survenue lors de la vente liée au produit ou au menu. " . $th->getMessage(),
            ]);
        }
    }

    private function processProducts($sale, $products)
    {
        $totalProfit = 0;

        foreach ($products as $product) {
            // search product
            $Product = Product::findOrFail($product['product_id']);
            if (!$Product) {
                throw new \Exception("Le produit avec l'ID " . $product['product_id'] . " est introuvable.");
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
            $sale->saleDetails()->create([
                'product_id' => $product['product_id'],
                'quantity' => $product['quantity'],
                'unit_price' => $product['unit_price'],
                'total_price' => $product['total_price'],
                'profit' => $profit
            ]);

            // update product quantity if product qte is greater than user quantity
            $this->updateProductQuantity($Product, $product['quantity']);

            // Ajouter le profit total
            $totalProfit += $profit;
        }
        return $totalProfit;
    }

    private function updateProductQuantity($product, $quantity)
    {
        if ($product->qte >= $quantity) {
            $newQte = $product->qte - $quantity;
            $product->update(['qte' => $newQte]);

            if($product->type == 2){
                foreach ($product->MenuProducts as $item){
                    $MenuProduct = Product::findOrFail($item->product_id);
                    $this->updateProductQuantity($MenuProduct, $item->quantity);
                }
            }

            // check if security margin is affected
            // if($product->email == 0){
                if ($newQte <= $product->margin) {
                    $users = User::where('status', 1)->where('user_type','!=', 1)->get();
                    foreach ($users as $user) {
                        $this->sendEmailMargin($user->name, $user->email, $product->name, $product->margin, $newQte);
                    }
                }
                $product->update(['email' => 1]);
            // }
        }else {
            throw new \Exception("Le produit " . $product->name . " n'a plus de stock disponible pour la quantité demandée.");
            DB::rollBack();
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "VENTE ECHOUEE",
                "msg" => "Le produit ". $Product->name. " n'a plus de stock disponible pour la quantité demandée"
            ]);
        }
    }

    public function sendSms($number, $message)
    {
        $smsService = new SmsService ();
        $response = $smsService->send($number, $message);
        log::info($response);
        return response()->json($response);

        // response example in format json
        // array (
        //     'status' => true,
        //     'message' => 'MESSAGE_SENT_SUCCESSFULLY',
        //     'data' => 
        //     array (
        //         'status' => 1,
        //         'response_token' => 'push_sms_afgrchw6re2bjnr',
        //     ),
        //     'status_code' => 200,
        // )
    }

    private function generatePdfInvoice($sale)
    {
        $pdf = Pdf::loadView('pos.invoice', [
            'sale' => $sale,
            'saleDetails' => $sale->saleDetails,
        ])
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'dpi' => 45,
        ])
        ->setPaper([0, 0, 390, 1000], 'portrait'); // Largeur 58mm (~203 points) // Dimensions personnalisées

        //return PDF in base64
        return base64_encode($pdf->output());
    }

    // send security margin mail
    public function sendEmailMargin($user_name, $email, $product_name, $margin, $newQte)
    {
        $text = "Le produit '".strtoupper($product_name)."' a atteint sa marge de sécurité(".$margin.")";
        $text2 = "La nouvelle quantitée du produit : ".$newQte;
        // Envoyez l'e-mail avec le code généré
        Mail::send('emails.user.marginMail', ['user_name' => $user_name, 'text' => $text, 'text2' => $text2, 'product_name' => $product_name], function($message) use ($email){
            $message->to($email);
            $message->subject(config('app.name'));
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Sale = Sale::findOrFail($id);
        return view('pos.sale.show_detail', compact('Sale'));
    }

    public function history(Request $request)
    {
        if ($request->ajax()) {
            $daterange = $request->daterange; // Exemple : "01/10/2024 - 31/10/2024"
        
            if ($daterange) {
                [$startDate, $endDate] = explode(' - ', $daterange);
                $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay()->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d 23:59:59');
            }else {
                // Plage de date par défaut : aujourd'hui
                $startDate = Carbon::today()->startOfDay()->format('Y-m-d');
                $endDate = Carbon::today()->format('Y-m-d 23:59:59');
            }
        
            $Object = Sale::with('saleDetails.product')->whereBetween('created_at', [$startDate, $endDate])->latest()->get();

            // somme calcul on sale
            $total_sale = $Object->count();
            $total_amount = 0;
            $total_profit = 0;
            $product_count = 0 ;
            foreach ($Object as $sale) {
                $total_amount += $sale->total_amount;
                $total_profit += $sale->total_profit;
                $product_count += $sale->saleDetails->count();
            }

            //top-selling product 
            $mostSoldProducts = DB::table('sale_details')
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(10)
            ->get();

            $mostSoldProducts = $mostSoldProducts->map(function ($saleDetail) {
                $saleDetail->product = Product::find($saleDetail->product_id);
                return $saleDetail;
            });
        
            return DataTables::of($Object)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<a data-id="'.$row->id.'" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>';
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->with([
                    'totalSale' => $total_sale,
                    'totalAmount' => $total_amount,
                    'totalProfit' => $total_profit,
                    'productCount' => $product_count,
                    'mostSoldProducts' => $mostSoldProducts,
                ])
                ->rawColumns(['action'])
                ->make(true);
        }
        
        return view('pos.sale.history');
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
