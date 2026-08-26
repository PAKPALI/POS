<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Jobs\SendEcommerceOrderEmailJob;
use App\Models\Category;
use App\Models\Product;
use App\Models\Company;
use App\Services\CompanyContext;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FrontController extends Controller
{
    public function __construct(private CompanyContext $context) {}

    protected function getCompany(bool $requireExplicitCompany = false)
    {
        $routeCompany = request()->route('company');
        if ($routeCompany instanceof Company) {
            $company = $routeCompany;
        } elseif (is_string($routeCompany) && $routeCompany !== '') {
            $company = Company::where('slug', $routeCompany)->first();
        } elseif (! $requireExplicitCompany) {
            $company = Company::where('ecommerce_active', true)->active()->orderBy('id')->first();
        } else {
            $company = null;
        }

        if ($company && (!$company->ecommerce_active || !$company->isActive())) {
            $company = null;
        }
        if ($company) {
            $this->context->setPublicCompany($company);
        }

        return $company;
    }

    public function index()
    {
        $company = $this->getCompany();
        if (!$company) {
            return response('No active company');
        }
        $categories = $this->activeCategories();
        $products = Product::with('category:id,name')->where('status', 1)->where('type', 1)
            ->where('qte', '>', 0)
            ->latest()
            ->take(12)
            ->get();

        return view('ecommerce.public.index', [
            'company' => $company,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function category($id)
    {
        $id = request()->route('id', $id);
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $category = Category::findOrFail($id);
        $categories = $this->activeCategories();
        $products = Product::where('category_id', $id)
            ->where('status', 1)->where('type', 1)
            ->where('qte', '>', 0)
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
        return view('ecommerce.public.category', [
            'company' => $company,
            'categories' => $categories,
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function product($id)
    {
        $id = request()->route('id', $id);
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $product = Product::with('category')->findOrFail($id);
        $categories = $this->activeCategories();
        return view('ecommerce.public.product', [
            'company' => $company,
            'categories' => $categories,
            'product' => $product,
        ]);
    }

    public function checkout()
    {
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $categories = $this->activeCategories();

        return view('ecommerce.public.checkout', [
            'company' => $company,
            'categories' => $categories,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $company = $this->getCompany(true);
        if (! $company) {
            return response()->json([
                'status' => false,
                'msg' => 'Cette boutique est introuvable ou n’accepte pas actuellement de commandes.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:1000',
            'delivery_location_url' => [
                'nullable', 'string', 'max:2048',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (! $this->isAllowedGoogleMapsUrl((string) $value)) {
                        $fail('Le lien de localisation doit être un lien HTTPS Google Maps valide.');
                    }
                },
            ],
            'delivery_latitude' => 'nullable|numeric|between:-90,90|required_with:delivery_longitude',
            'delivery_longitude' => 'nullable|numeric|between:-180,180|required_with:delivery_latitude',
            'notes' => 'nullable|string|max:2000',
            'cart' => 'required|json',
        ], [
            'customer_name.required' => 'Indiquez le nom du client.',
            'customer_phone.required' => 'Indiquez le numéro de téléphone du client.',
            'customer_email.email' => 'L’adresse e-mail du client n’est pas valide.',
            'cart.required' => 'Votre panier est vide.',
            'cart.json' => 'Le contenu du panier est invalide.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        $cart = json_decode($request->cart, true);
        $cartValidator = Validator::make(['items' => $cart], [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:10000'],
        ], [
            'items.required' => 'Votre panier est vide.',
            'items.array' => 'Le contenu du panier est invalide.',
            'items.max' => 'Le panier ne peut pas contenir plus de 100 produits différents.',
            'items.*.product_id.required' => 'Un produit du panier est invalide.',
            'items.*.product_id.distinct' => 'Un produit apparaît plusieurs fois dans le panier.',
            'items.*.quantity.min' => 'La quantité commandée doit être supérieure à zéro.',
            'items.*.quantity.max' => 'La quantité demandée est trop élevée.',
        ]);

        if ($cartValidator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $cartValidator->errors()->first(),
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($request, $cart, $company) {
                $requestedItems = collect($cart)->keyBy(fn ($item) => (int) $item['product_id']);
                $productIds = $requestedItems->keys()->sort()->values();
                $products = Product::query()
                    ->where('company_id', $company->id)
                    ->whereIn('id', $productIds)
                    ->where('status', 1)
                    ->where('type', 1)
                    ->orderBy('id')
                    ->get()
                    ->keyBy('id');

                if ($products->count() !== $productIds->count()) {
                    throw ValidationException::withMessages([
                        'cart' => 'Un produit du panier n’appartient pas à cette boutique ou n’est plus disponible.',
                    ]);
                }

                $subtotal = 0;
                $items = [];
                foreach ($productIds as $productId) {
                    $product = $products->get($productId);
                    $quantity = (int) $requestedItems->get($productId)['quantity'];
                    if ((int) $product->qte < $quantity) {
                        throw ValidationException::withMessages([
                            'cart' => 'Stock insuffisant pour « '.$product->name.' ». Quantité disponible : '.$product->qte.'.',
                        ]);
                    }

                    $unitPrice = round((float) ($product->price_ttc ?? $product->price), 2);
                    $totalPrice = round($unitPrice * $quantity, 2);
                    $subtotal = round($subtotal + $totalPrice, 2);
                    $items[] = [
                        'company_id' => $company->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice,
                    ];
                }

                $order = Order::create([
                    'company_id' => $company->id,
                    'code' => 'CMD-'.$company->id.'-'.Str::upper(Str::random(8)),
                    'customer_name' => $request->string('customer_name')->trim()->toString(),
                    'customer_phone' => $request->string('customer_phone')->trim()->toString(),
                    'customer_email' => $request->filled('customer_email')
                        ? mb_strtolower($request->string('customer_email')->trim()->toString()) : null,
                    'customer_address' => $request->string('customer_address')->trim()->toString() ?: null,
                    'delivery_location_url' => $this->deliveryLocationUrl($request),
                    'delivery_latitude' => $request->filled('delivery_latitude')
                        ? round((float) $request->input('delivery_latitude'), 7) : null,
                    'delivery_longitude' => $request->filled('delivery_longitude')
                        ? round((float) $request->input('delivery_longitude'), 7) : null,
                    'notes' => $request->string('notes')->trim()->toString() ?: null,
                    'subtotal' => $subtotal,
                    'tax' => 0,
                    'total' => $subtotal,
                    'status' => 'pending',
                ]);

                foreach ($items as $item) {
                    $order->items()->create($item);
                }

                SendEcommerceOrderEmailJob::dispatch($order->id, $company->id)->afterCommit();

                return $order;
            }, 3);
        } catch (ValidationException $exception) {
            return response()->json([
                'status' => false,
                'msg' => collect($exception->errors())->flatten()->first() ?? 'La commande ne peut pas être enregistrée.',
            ], 422);
        }

        return response()->json([
            'status' => true,
            'code' => $order->code,
            'msg' => 'Votre commande a été enregistrée avec succès.',
        ]);
    }

    public function success(Request $request)
    {
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $code = $request->code;
        $categories = $this->activeCategories();
        return view('ecommerce.public.success', [
            'company' => $company,
            'categories' => $categories,
            'code' => $code,
        ]);
    }

    private function deliveryLocationUrl(Request $request): ?string
    {
        if ($request->filled('delivery_latitude') && $request->filled('delivery_longitude')) {
            $latitude = number_format((float) $request->input('delivery_latitude'), 7, '.', '');
            $longitude = number_format((float) $request->input('delivery_longitude'), 7, '.', '');

            return 'https://www.google.com/maps/search/?api=1&query='.$latitude.','.$longitude;
        }

        $url = $request->string('delivery_location_url')->trim()->toString();

        return $url !== '' ? $url : null;
    }

    private function isAllowedGoogleMapsUrl(string $url): bool
    {
        if ($url === '') {
            return true;
        }

        $parts = parse_url($url);
        if (($parts['scheme'] ?? null) !== 'https' || empty($parts['host'])) {
            return false;
        }

        $host = strtolower(rtrim($parts['host'], '.'));
        $allowedHosts = ['google.com', 'maps.google.com', 'maps.app.goo.gl', 'goo.gl'];

        return collect($allowedHosts)->contains(
            fn (string $allowed) => $host === $allowed || str_ends_with($host, '.'.$allowed)
        );
    }

    public function allProducts(Request $request)
    {
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:100']]);
        $search = trim((string) ($validated['q'] ?? ''));
        $categories = $this->activeCategories();
        $products = Product::with('category:id,name')->where('status', 1)->where('type', 1)
            ->where('qte', '>', 0)
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();
        return view('ecommerce.public.products', [
            'company' => $company,
            'categories' => $categories,
            'products' => $products,
            'search' => $search,
        ]);
    }

    private function activeCategories()
    {
        return Category::query()
            ->select(['id', 'name'])
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

}
