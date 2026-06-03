<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Jobs\SendMarginEmailJob;
use App\Jobs\SendSaleWhatsappJob;
use App\Models\Action;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\AMS\Transaction;
use App\Models\Category;
use App\Models\CodePromo;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SmsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

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
        $Product = Product::where('status', 1)->where('qte', '>', 0)->get();
        $company = CompanySetting::first();

        $mainCash = CashAccount::where('is_default', 1)->first();
        $taxCash = CashAccount::where('is_tax', 1)->first();
        $setting  = Setting::first();

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
        return view('pos.sale.index',
            compact(
                'Category','Product','mostSoldProducts','Object','sale_total_profit',
                'product_count','total_amount','company','mainCash','taxCash','setting'
            ));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        // $category = $request->get('category'); // id ou nom selon ton choix

        $products = Product::where('status', 1)->where('qte', '>', 0)->where('name', 'like', "%{$query}%")
            // ->when($category && $category !== 'all', function ($q) use ($category) {
            //     $q->where('category_id', $category); // si tu as category_id dans Product
            // })
            // ->when(strlen($query) >= 2, function ($q) use ($query) {
            //     $q->where('name', 'like', "%{$query}%");
            // })
            ->get();

        return response()->json($products);
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
           if ($request->code_promo && strlen($request->code_promo) == 6) {
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

            // comptabilty run code
            $this->handleAmsAccounting($sale);

            // Generate PDF invoice
            $pdfBase64 = $this->generatePdfInvoice($sale);

            // send sale email notification to users
            // $this->sendSaleEmailNotification($sale);
            dispatch(new \App\Jobs\SendSaleEmailJob($sale->id));

            // send sms to client
            $number = '90859488';
            $message = 'Vous venez de faire un achat au total de '.$request->total_amount.' FCFA au niveau de LUX-GRILL et nous vous remercions.';
            // $this->sendSms($number, $message);
            dispatch(new SendSaleWhatsappJob($sale->id));

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
                    dispatch(new SendMarginEmailJob($product->name,$product->margin,$newQte));
                }
                // $product->update(['email' => 1]);
            // }
        }else {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "VENTE ECHOUEE",
                "msg" => "Le produit ". $product->name. " n'a plus de stock disponible pour la quantité demandée"
            ]);
        }
    }

    public function sendSms($number, $message)
    {
        $smsService = new SmsService ();
        $response = $smsService->sendSms($number, $message);
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

    public function sendWhatsappSms($number, $title, $message)
    {
        $smsService = new SmsService ();
        $response = $smsService->sendWhatsappSms($number, $title, $message);
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
        $company = CompanySetting::first();
        $pdf = Pdf::loadView('pos.invoice', [
            'sale' => $sale,
            'saleDetails' => $sale->saleDetails,
            'company' => $company,
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

    public function generatePDF($id)
    {
        $sale = Sale::findOrFail($id);
        $saleDetails = $sale->saleDetails;

        $company = CompanySetting::first();

        $pdf = Pdf::loadView('pos.invoice',compact('sale', 'saleDetails','company'));
        return $pdf->download('Facture' . $sale->code . '.pdf');
    }

    // send security margin mail
    // public function sendEmailMargin($user_name, $email, $product_name, $margin, $newQte)
    // {
    //     $text = "Le produit '".strtoupper($product_name)."' a atteint sa marge de sécurité(".$margin.")";
    //     $text2 = "La nouvelle quantitée du produit : ".$newQte;
    //     $company = CompanySetting::first();
    //     // Envoyez l'e-mail avec le code généré
    //     Mail::send('emails.user.marginMail', ['company' => $company, 'user_name' => $user_name, 'text' => $text, 'text2' => $text2, 'product_name' => $product_name], function($message) use ($email){
    //         $message->to($email);
    //         $message->subject($company->name ?? config('app.name'));
    //     });
    // }

    private function sendSaleEmailNotification($sale)
    {
        $company = CompanySetting::first();
        $users = User::where('status', 1)->where('user_type','!=', 1)->get();

        foreach ($users as $user) {
            Mail::send('emails.sale.saleNotification', [
                'sale' => $sale,
                'company' => $company,
            ], function ($message) use ($user, $sale, $company) {
                $message->to($user->email);
                $message->subject("Nouvelle vente #" . $sale->code . " - " . $company->name ?? config('app.name'));
            });
        }
    }

    private function handleAmsAccounting($sale)
    {
        $setting = Setting::first();
        if(!$setting){
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "ERREUR COMPTABILITEE",
                "msg" => "Pas de configuration comptable trouvée",
            ]);
        }

        $mainCash = CashAccount::find($setting->default_cash_id);
        $taxCash = CashAccount::find($setting->tax_cash_id);

        $taxPercent = $setting->default_tax ?? 0;

        // calculate taxe
        $taxAmount = 0;
        $netAmount = $sale->total_amount;
        if($taxPercent > 0){
            $netAmount = $sale->total_amount / (1 + ($taxPercent / 100));
        }

        //tax amount
        $taxAmount = $sale->total_amount - $netAmount;

        // update sale
        $sale->update([
            'tax_amount' => $taxAmount
        ]);

        // =====================
        // PRINCIPAL CASH ACCOUNT
        // =====================
        if($mainCash){
            $mainCash->increment('balance', $netAmount);
            Transaction::create([
                'type' => 'IN',
                'to_cash_id' => $mainCash->id,
                'amount' => $netAmount,
                'description' => 'Vente #' . $sale->code,
                'created_by' => auth()->id(),
            ]);
        }else{
            return response()->json([
                "status" => false,
                "reload" => false,
                "title" => "ERREUR COMPTABILITEE",
                "msg" => "Pas de caisse principale trouvée",
            ]);
        }

        // =====================
        // TAXE CASH ACCOUNT
        // =====================
        if($taxAmount > 0) {
            if($taxCash ){
                $taxCash->increment('balance', $taxAmount);
                Transaction::create([
                    'type' => 'IN',
                    'to_cash_id' => $taxCash->id,
                    'amount' => $taxAmount,
                    'description' => 'Taxe vente #' . $sale->code,
                    'created_by' => auth()->id(),
                ]);
            }else{
                return response()->json([
                    "status" => false,
                    "reload" => false,
                    "title" => "ERREUR COMPTABILITEE",
                    "msg" => "Pas de caisse taxe trouvée",
                ]);
            }
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
                    $company = \App\Models\CompanySetting::first();
                    $buttons = '<a data-id="'.$row->id.'" class="btn btn-dark btn-sm view">
                        <i class="fas fa-lg fa-fw me-0 fa-eye"></i>
                    </a>';

                    if ($company AND $company->count() > 0) {
                        $buttons .= ' <a data-id="'.$row->id.'" data-toggle="modal" data-target="#pdf" class="btn btn-info btn-sm pdf"> <i class="fas fa-file-pdf"></i> PDF</a>';
                    }
                    return $buttons;
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
