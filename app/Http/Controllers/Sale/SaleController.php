<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Jobs\SendMarginEmailJob;
use App\Jobs\SendSaleEmailJob;
use App\Jobs\SendSaleWhatsappJob;
use App\Models\Action;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\AMS\Transaction;
use App\Models\Category;
use App\Models\Client;
use App\Models\CodePromo;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SmsService;
use App\Services\CompanyContext;
use App\Services\SaleCreationService;
use App\Services\SaleInvoiceDeliveryService;
use App\Services\StreamingTabularExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SaleController extends Controller
{
    public function __construct(private SaleCreationService $saleCreationService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Sale::class);
        $canViewFinancials = app(CompanyContext::class)->hasPermission('reports.view_margin');
        $today = Carbon::today();
        $dayStart = $today->copy()->startOfDay();
        $dayEnd = $today->copy()->endOfDay();

        if (request()->ajax()) {
            $columns = [
                'id', 'company_id', 'code', 'received_amount', 'total_amount',
                'remaining_amount', 'code_promo', 'discount', 'client_id',
                'cashier', 'created_at',
            ];
            if ($canViewFinancials) {
                $columns[] = 'total_profit';
            }

            $sales = Sale::query()
                ->select($columns)
                ->with('client:id,name,phone,country_code')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->latest();

            return DataTables::of($sales)
                ->addIndexColumn()
                ->filterColumn('client', function ($query, $keyword) {
                    $query->whereHas('client', fn ($client) => $client->where('name', 'like', "%{$keyword}%"));
                })
                ->addColumn('action', function($row){
                    return ' <a data-id="'.$row->id.'" data-name="" data-original-title="Detail" class="btn btn-dark btn-sm view"><i class="fas fa-lg fa-fw me-0 fa-eye"></i></a>
                        <button type="button" data-id="'.$row->id.'" class="btn btn-success btn-sm deliver-invoice" data-loading-text="Chargement…" title="Envoyer la facture"><i class="bi bi-whatsapp"></i></button>';
                })
                ->editColumn('client', fn ($sale) => $sale->client->name ?? 'Aucun')
                ->rawColumns(['action'])
                ->make(true);
        }

        $Category = Category::query()
            ->where('status', 1)
            ->withCount(['products as available_products_count' => fn ($products) => $products
                ->where('status', 1)
                ->where('qte', '>', 0)])
            ->orderBy('name')
            ->get();
        $productCount = Product::where('status', 1)->where('qte', '>', 0)->count();
        $company = CompanySetting::first();

        $mainCash = CashAccount::where('is_default', 1)->first();
        $taxCash = CashAccount::where('is_tax', 1)->first();
        $setting  = Setting::first();

        $salesSummary = Sale::query()
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->selectRaw('COUNT(*) as sale_count, COALESCE(SUM(total_amount), 0) as total_amount')
            ->selectRaw('COALESCE(SUM(total_profit), 0) as total_profit')
            ->first();
        $saleCount = (int) $salesSummary->sale_count;
        $total_amount = (float) $salesSummary->total_amount;
        $sale_total_profit = $canViewFinancials ? (float) $salesSummary->total_profit : 0;
        $product_count = SaleDetail::whereBetween('created_at', [$dayStart, $dayEnd])->count();

        $mostSoldProducts = SaleDetail::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_quantity')
            ->with('product:id,name,image,price')
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->take(10)
            ->get();

        // Send data to view
        return view('pos.sale.index',
            compact(
                'Category','productCount','mostSoldProducts','saleCount','sale_total_profit',
                'product_count','total_amount','company','mainCash','taxCash','setting',
                'canViewFinancials'
            ));
    }

    public function searchClients(Request $request)
    {
        $this->authorize('viewAny', Sale::class);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'client_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $clients = Client::query()
            ->select(['id', 'name'])
            ->where('status', 1)
            ->when($validated['client_id'] ?? null, fn ($query, $clientId) => $query->whereKey($clientId))
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'results' => $clients->getCollection()
                ->map(fn (Client $client) => ['id' => $client->id, 'text' => $client->name])
                ->values(),
            'pagination' => ['more' => $clients->hasMorePages()],
        ]);
    }

    public function search(Request $request)
    {
        $this->authorize('viewAny', Product::class);
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(
                    fn ($query) => $query
                        ->where('company_id', app(CompanyContext::class)->getCompanyId())
                        ->where('status', 1)
                ),
            ],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $products = Product::query()
            ->select(['id', 'category_id', 'name', 'qte', 'price', 'price_ttc', 'image'])
            ->where('status', 1)
            ->where('qte', '>', 0)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($validated['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->orderBy('name')
            ->paginate(24);

        $products->getCollection()->transform(function (Product $product) {
            $hasImage = $product->image
                && $product->image !== 'null'
                && file_exists(public_path('images/'.$product->image));

            $product->image_url = $hasImage
                ? asset('images/'.$product->image)
                : asset('icons/product-placeholder.svg');
            $product->sale_price = $product->price_ttc ?: $product->price;

            return $product;
        });

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
        $this->authorize('create', Sale::class);
        $error_messages = [
            'products.required' => "Choisir un produit",
            'products.*.quantity.min' => "La quantité doit être supérieure à 0 pour chaque produit",
            'total_amount.required' => "Remplir le total",
            'client_id.exists' => "Le client sélectionné n'est pas disponible dans la compagnie active.",
        ];
        
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*.quantity' => 'required|integer|min:1',
            'total_amount' => 'required|numeric',
            'client_id' => [
                'nullable',
                'integer',
                Rule::exists('clients', 'id')->where(
                    fn ($query) => $query
                        ->where('company_id', app(CompanyContext::class)->getCompanyId())
                        ->where('status', 1)
                ),
            ],
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
            $sale = $this->saleCreationService->create([
                'products' => $request->products,
                'received_amount' => $request->received_amount,
                'total_amount' => $request->total_amount,
                'code_promo' => $request->code_promo,
                'discount' => $request->discount,
                'client_id' => $request->client_id ?: null,
            ], $request->user());

            $company = CompanySetting::first();
            $receiptHtml = view('pos.receipt', [
                'sale' => $sale,
                'saleDetails' => $sale->saleDetails,
                'company' => $company,
            ])->render();
            return response()->json([
                "status" => true,
                "reload" => true,
                "title" => "VENTE EFFECTUEE",
                "msg" => "",
                'receiptHtml' => $receiptHtml,
                'saleId' => $sale->id,
                'hasClient' => (bool) $sale->client_id,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "msg" => "Erreur survenue lors de la vente liée au produit ou au menu. " . $th->getMessage(),
            ]);
        }
    }

    public function sendInvoice(Request $request, Sale $sale, SaleInvoiceDeliveryService $delivery)
    {
        $this->authorize('view', $sale);
        $validated = $request->validate([
            'phone' => ['required', 'digits_between:6,15'],
            'country_code' => ['nullable', Rule::in(array_keys(config('african_countries', [])))],
            'whatsapp' => ['required', 'boolean'],
            'sms' => ['required', 'boolean'],
        ], [
            'phone.required' => 'Saisissez le numéro du client.',
            'phone.digits_between' => 'Le numéro doit contenir entre 6 et 15 chiffres, sans indicatif.',
        ]);
        try {
            $channels = $delivery->deliver(
                $sale->loadMissing('saleDetails.product'),
                $validated['phone'],
                $validated['country_code'] ?? (CompanySetting::first()?->country_code ?? 'TG'),
                (bool) $validated['whatsapp'],
                (bool) $validated['sms']
            );
            $company = CompanySetting::firstOrFail()->fresh();
            return response()->json([
                'status' => true,
                'message' => 'Facture envoyée par '.implode(' et ', $channels).'.',
                'smsQuota' => (int) $company->sms_count,
                'whatsappQuota' => (int) $company->whatsapp_count,
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Échec envoi manuel facture client', ['sale_id' => $sale->id, 'error' => $exception->getMessage()]);
            $company = CompanySetting::first();
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'smsQuota' => (int) ($company?->sms_count ?? 0),
                'whatsappQuota' => (int) ($company?->whatsapp_count ?? 0),
            ], 422);
        }
    }

    public function receipt(Sale $sale)
    {
        $this->authorize('view', $sale);
        $sale->loadMissing(['client', 'saleDetails.product']);
        $company = CompanySetting::firstOrFail();

        return response()->json([
            'status' => true,
            'saleId' => $sale->id,
            'hasClient' => (bool) $sale->client_id,
            'clientPhone' => $sale->client?->phone,
            'clientCountryCode' => $sale->client?->country_code ?: $company->country_code,
            'allowDelivery' => true,
            'receiptHtml' => view('pos.receipt', compact('sale', 'company') + ['saleDetails' => $sale->saleDetails])->render(),
            'smsQuota' => (int) $company->sms_count,
            'whatsappQuota' => (int) $company->whatsapp_count,
        ]);
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
        $this->authorize('export', $sale);
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
        $companyId = app(CompanyContext::class)->getCompanyId();
        $users = User::where('status', 1)
            ->whereHas('memberships', function ($query) use ($companyId) {
                $query->where('company_id', $companyId)
                    ->where('status', 'active')
                    ->whereHas('role', fn ($role) => $role->whereIn('key', ['owner', 'admin']));
            })->get();

        foreach ($users as $user) {
            Mail::send('emails.sale.saleNotification', [
                'sale' => $sale,
                'company' => $company,
            ], function ($message) use ($user, $sale, $company) {
                $message->from(config('mail.from.address'), $company->name ?? config('app.name'));
                $message->to($user->email);
                $message->subject(($company->name ?? config('app.name')).' — Nouvelle vente #'.$sale->code);
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
        $this->authorize('view', $Sale);
        return view('pos.sale.show_detail', compact('Sale'));
    }

    public function history(Request $request)
    {
        $this->authorize('viewAny', Sale::class);
        $canViewFinancials = app(CompanyContext::class)->hasPermission('reports.view_margin');
        if ($request->ajax()) {
            $filters = $this->validatedHistoryFilters($request);
            $daterange = $filters['daterange'] ?? null;
        
            if ($daterange) {
                [$startDate, $endDate] = explode(' - ', $daterange);
                $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay()->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d 23:59:59');
            }else {
                // Plage de date par défaut : aujourd'hui
                $startDate = Carbon::today()->startOfDay()->format('Y-m-d');
                $endDate = Carbon::today()->format('Y-m-d 23:59:59');
            }
        
            $salesFilter = fn ($query) => $this->applyHistorySaleFilters(
                $query,
                $startDate,
                $endDate,
                $filters['client_id'] ?? null,
                $filters['supplier_id'] ?? null
            );

            $summary = $salesFilter(Sale::query())
                ->selectRaw('COUNT(*) as sale_count, COALESCE(SUM(total_amount), 0) as total_amount')
                ->selectRaw('COALESCE(SUM(total_profit), 0) as total_profit')
                ->first();
            $totalProfit = $canViewFinancials
                ? (float) $summary->total_profit
                : null;
            $productDetails = SaleDetail::query()
                ->whereHas('sale', $salesFilter)
                ->when($filters['supplier_id'] ?? null, fn ($query, $supplierId) => $query
                    ->whereHas('product', fn ($product) => $product->where('supplier_id', $supplierId)));
            $productCount = (int) (clone $productDetails)->sum('quantity');

            $mostSoldProducts = $productDetails
                ->select('product_id')
                ->selectRaw('SUM(quantity) as total_quantity')
                ->with('product:id,name,image,price')
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->take(10)
                ->get();

            $columns = [
                'id', 'company_id', 'code', 'total_amount', 'discount',
                'client_id', 'cashier', 'created_at',
            ];
            if ($canViewFinancials) {
                $columns[] = 'total_profit';
            }
            $sales = $salesFilter(Sale::query())
                ->select($columns)
                ->with('client:id,name,phone,country_code')
                ->latest();
            $hasCompany = CompanySetting::query()->exists();

            return DataTables::of($sales)
                ->addIndexColumn()
                ->filterColumn('client', function ($query, $keyword) {
                    $query->whereHas('client', fn ($client) => $client->where('name', 'like', "%{$keyword}%"));
                })
                ->addColumn('action', function ($row) use ($hasCompany) {
                    $buttons = '<a data-id="'.$row->id.'" class="btn btn-dark btn-sm view">
                        <i class="fas fa-lg fa-fw me-0 fa-eye"></i>
                    </a>';

                    if ($hasCompany) {
                        $buttons .= ' <a data-id="'.$row->id.'" data-toggle="modal" data-target="#pdf" class="btn btn-info btn-sm pdf"> <i class="fas fa-file-pdf"></i> PDF</a>';
                    }
                    $buttons .= ' <button type="button" data-id="'.$row->id.'" data-phone="'.e($row->client?->phone ?? '').'" data-country="'.e($row->client?->country_code ?? '').'" class="btn btn-success btn-sm deliver-invoice" title="Envoyer par WhatsApp ou SMS"><i class="bi bi-whatsapp"></i></button>';
                    return $buttons;
                })
                ->editColumn('created_at', function ($Object) {
                    return $Object->created_at->format('d-m-Y H:i:s');
                })
                ->editColumn('client', function ($Object) {
                    return $Object->client->name ?? 'Aucun';
                })
                ->with([
                    'totalSale' => (int) $summary->sale_count,
                    'totalAmount' => (float) $summary->total_amount,
                    'totalProfit' => $totalProfit,
                    'productCount' => $productCount,
                    'mostSoldProducts' => $mostSoldProducts,
                ])
                ->rawColumns(['action'])
                ->make(true);
        }
        
        $company = CompanySetting::first();
        $clients = Client::query()->where('status', 1)->orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::query()->where('status', 1)->orderBy('name')->get(['id', 'name']);
        return view('pos.sale.history', compact('canViewFinancials', 'company', 'clients', 'suppliers'));
    }

    private function validatedHistoryFilters(Request $request): array
    {
        $companyId = app(CompanyContext::class)->getCompanyId();

        return $request->validate([
            'daterange' => ['nullable', 'string', 'max:50'],
            'client_id' => ['nullable', 'integer', Rule::exists('clients', 'id')->where(
                fn ($query) => $query->where('company_id', $companyId)->where('status', 1)
            )],
            'supplier_id' => ['nullable', 'integer', Rule::exists('suppliers', 'id')->where(
                fn ($query) => $query->where('company_id', $companyId)->where('status', 1)
            )],
        ]);
    }

    private function applyHistorySaleFilters($query, $startDate, $endDate, ?int $clientId, ?int $supplierId)
    {
        return $query
            ->whereBetween('sales.created_at', [$startDate, $endDate])
            ->when($clientId, fn ($sales, $id) => $sales->where('sales.client_id', $id))
            ->when($supplierId, fn ($sales, $id) => $sales->whereHas(
                'saleDetails.product',
                fn ($product) => $product->where('supplier_id', $id)
            ));
    }

    public function exportHistoryPdf(Request $request)
    {
        $this->authorize('export', Sale::class);
        $canViewFinancials = app(CompanyContext::class)->hasPermission('reports.view_margin');
        $filters = $this->validatedHistoryFilters($request);
        $daterange = $filters['daterange'] ?? null;
        if ($daterange) {
            [$startDate, $endDate] = explode(' - ', $daterange);
            $startDate = Carbon::createFromFormat('d-m-Y', $startDate)->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', $endDate)->endOfDay();
        } else {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        $query = $this->applyHistorySaleFilters(
            Sale::with('saleDetails.product','client'),
            $startDate,
            $endDate,
            $filters['client_id'] ?? null,
            $filters['supplier_id'] ?? null
        );

        $search = trim((string) $request->get('search'));
        if ($search !== '') {
            $query->where(function ($query) use ($search, $canViewFinancials) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhere('cashier', 'like', "%{$search}%")
                    ->orWhere('total_amount', 'like', "%{$search}%");

                if ($canViewFinancials) {
                    $query->orWhere('total_profit', 'like', "%{$search}%");
                }
            });
        }

        $maxRows = (int) config('performance.pdf_exports.sales_max_rows', 100);
        if ((clone $query)->limit($maxRows + 1)->pluck('id')->count() > $maxRows) {
            return back()->with('error', "L’export PDF est limité à {$maxRows} ventes. Réduisez la période ou précisez la recherche.");
        }

        $sales = $query->latest()->get();
        $summary = [
            'sales_count' => $sales->count(),
            'products_quantity' => $sales->sum(function ($sale) {
                return $sale->saleDetails->sum('quantity');
            }),
            'total_amount' => $sales->sum('total_amount'),
            'total_received' => $sales->sum('received_amount'),
            'total_profit' => $canViewFinancials ? $sales->sum('total_profit') : null,
        ];

        $supplierId = $filters['supplier_id'] ?? null;
        $topProducts = $sales
            ->flatMap(function ($sale) {
                return $sale->saleDetails;
            })
            ->when($supplierId, fn ($details) => $details->filter(
                fn ($detail) => (int) $detail->product?->supplier_id === (int) $supplierId
            ))
            ->groupBy(function ($detail) {
                return $detail->product_id ?: 'deleted';
            })
            ->map(function ($details) {
                $firstDetail = $details->first();

                return [
                    'name' => $firstDetail->product->name ?? 'Produit supprimé',
                    'quantity' => $details->sum('quantity'),
                    'total_amount' => $details->sum('total_price'),
                ];
            })
            ->sortByDesc('quantity')
            ->values();

        $company = CompanySetting::first();
        $pdf = Pdf::loadView('pos.sale.history_pdf', compact(
            'sales',
            'summary',
            'topProducts',
            'company',
            'startDate',
            'endDate',
            'search',
            'canViewFinancials'
        ))->setPaper('a4', 'portrait');

        return $pdf->download(
            'historique-ventes-'.$startDate->format('Y-m-d').'-'.$endDate->format('Y-m-d').'.pdf'
        );
    }

    public function exportHistoryTabular(Request $request, string $format, StreamingTabularExport $export)
    {
        $this->authorize('export', Sale::class);
        $canViewFinancials = app(CompanyContext::class)->hasPermission('reports.view_margin');
        $filters = $this->validatedHistoryFilters($request);
        if (!empty($filters['daterange'])) {
            [$start, $end] = explode(' - ', $filters['daterange']);
            $startDate = Carbon::createFromFormat('d-m-Y', trim($start))->startOfDay();
            $endDate = Carbon::createFromFormat('d-m-Y', trim($end))->endOfDay();
        } else {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        $query = $this->applyHistorySaleFilters(
            Sale::query(),
            $startDate,
            $endDate,
            $filters['client_id'] ?? null,
            $filters['supplier_id'] ?? null
        )
            ->leftJoin('clients', 'clients.id', '=', 'sales.client_id')
            ->select([
                'sales.code', 'sales.total_amount', 'sales.received_amount',
                'sales.remaining_amount', 'sales.discount', 'sales.cashier',
                'sales.created_at', 'clients.name as client_name',
            ]);
        if ($canViewFinancials) $query->addSelect('sales.total_profit');
        $search = trim((string) $request->get('search'));
        if ($search !== '') {
            $query->where(function ($query) use ($search, $canViewFinancials) {
                $query->where('sales.code', 'like', "%{$search}%")
                    ->orWhere('sales.cashier', 'like', "%{$search}%")
                    ->orWhere('sales.total_amount', 'like', "%{$search}%")
                    ->orWhere('clients.name', 'like', "%{$search}%");
                if ($canViewFinancials) $query->orWhere('sales.total_profit', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('sales.id')->cursor()->map(function ($sale) use ($canViewFinancials) {
            $row = [
                (string) $sale->code,
                $sale->client_name ?? 'Aucun',
                (float) $sale->total_amount,
                (float) $sale->received_amount,
                (float) $sale->remaining_amount,
                (float) $sale->discount,
                $sale->cashier,
                Carbon::parse($sale->created_at)->format('d-m-Y H:i:s'),
            ];
            if ($canViewFinancials) $row[] = (float) $sale->total_profit;
            return $row;
        });
        $headers = ['Code', 'Client', 'Total', 'Reçu', 'Monnaie', 'Remise', 'Caissier', 'Date'];
        if ($canViewFinancials) $headers[] = 'Bénéfice';
        Action::create([
            'user_id' => auth()->id(),
            'function' => 'EXPORTER VENTES '.strtoupper($format),
            'text' => auth()->user()->name.' a exporté l’historique des ventes en '.strtoupper($format),
        ]);

        return $export->download(
            $format,
            'ventes-'.$startDate->format('Y-m-d').'-'.$endDate->format('Y-m-d'),
            $headers,
            $rows
        );
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

    private function hideFinancialFields($sales): void
    {
        $sales->each(function (Sale $sale) {
            $sale->makeHidden(['total_profit']);
            $sale->saleDetails->each(function ($detail) {
                $detail->makeHidden(['profit']);
                $detail->product?->makeHidden(['purchase_price', 'profit']);
            });
        });
    }
}
