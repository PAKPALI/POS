<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\CompanySetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\EcommerceManager;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class FrontController extends Controller
{
    protected function getCompany()
    {
        return CompanySetting::where('ecommerce_active', true)->first();
    }

    public function index()
    {
        $company = $this->getCompany();
        if (!$company) {
            return response('No active company');
        }
        $categories = Category::where('status', 1)->get();
        $products = Product::where('status', 1)->where('type', 1)
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
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $category = Category::findOrFail($id);
        $categories = Category::where('status', 1)->get();
        $products = Product::where('category_id', $id)
            ->where('status', 1)->where('type', 1)
            ->where('qte', '>', 0)
            ->get();
        return view('ecommerce.public.category', [
            'company' => $company,
            'categories' => $categories,
            'category' => $category,
            'products' => $products,
        ]);
    }

    public function product($id)
    {
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $product = Product::with('category')->findOrFail($id);
        $categories = Category::where('status', 1)->get();
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
        $categories = Category::where('status', 1)->get();
        $productImages = Product::query()
            ->pluck('image', 'id')
            ->map(function ($image) {
                return $image && $image !== 'null'
                    ? asset('images/'.$image)
                    : asset('icons/product-placeholder.svg');
            });

        return view('ecommerce.public.checkout', [
            'company' => $company,
            'categories' => $categories,
            'productImages' => $productImages,
        ]);
    }

    public function placeOrder(Request $request)
    {
        $company = $this->getCompany();

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:50',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string',
            'notes' => 'nullable|string',
            'cart' => 'required|json',
        ]);

        $cart = json_decode($request->cart, true);
        if (empty($cart)) {
            return response()->json([
                'status' => false,
                'msg' => 'Votre panier est vide.'
            ]);
        }

        $subtotal = 0;
        $items = [];
        foreach ($cart as $item) {
            $product = Product::find($item['product_id']);
            if (!$product || $product->qte < $item['quantity']) {
                return response()->json([
                    'status' => false,
                    'msg' => 'Stock insuffisant pour : '.($product->name ?? 'produit inconnu')
                ]);
            }
            $unitPrice = $product->price_ttc ?? $product->price;
            $totalPrice = $unitPrice * $item['quantity'];
            $subtotal += $totalPrice;
            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];
        }

        $tax = 0;
        $total = $subtotal + $tax;

        $code = 'CMD-'.strtoupper(\Illuminate\Support\Str::random(8));

        $order = Order::create([
            'company_id' => $company->id ?? null,
            'code' => $code,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,
            'customer_address' => $request->customer_address,
            'notes' => $request->notes,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($items as $item) {
            OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            $product = Product::find($item['product_id']);
            if ($product) {
                $product->decrement('qte', $item['quantity']);
            }
        }

        $this->notifyManagers($company, $order, $items);

        return response()->json([
            'status' => true,
            'code' => $code,
            'msg' => 'Votre commande a ete enregistree avec succes.'
        ]);
    }

    public function success(Request $request)
    {
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $code = $request->code;
        $categories = Category::where('status', 1)->get();
        return view('ecommerce.public.success', [
            'company' => $company,
            'categories' => $categories,
            'code' => $code,
        ]);
    }

    public function allProducts()
    {
        $company = $this->getCompany();
        if (!$company) {
            return view('ecommerce.public.closed');
        }
        $categories = Category::where('status', 1)->get();
        $products = Product::where('status', 1)->where('type', 1)
            ->where('qte', '>', 0)
            ->paginate(12);
        return view('ecommerce.public.products', [
            'company' => $company,
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    protected function notifyManagers($company, $order, $items)
    {
        if (!$company) return;

        $managers = EcommerceManager::with('user')
            ->where('company_id', $company->id)
            ->get();

        foreach ($managers as $manager) {
            $user = $manager->user;
            if ($user && $user->email) {
                try {
                    \Illuminate\Support\Facades\Mail::raw(
                        "Nouvelle commande #{$order->code}\n\n".
                        "Client: {$order->customer_name}\n".
                        "Tel: {$order->customer_phone}\n\n".
                        "Produits:\n".
                        collect($items)->map(function($i) {
                            return "- {$i['product_name']} x{$i['quantity']} = ".number_format($i['total_price'], 0, ',', ' ')." FCFA";
                        })->implode("\n").
                        "\n\nTotal: ".number_format($order->total, 0, ',', ' ')." FCFA",
                        function ($message) use ($user, $order) {
                            $message->to($user->email)
                                ->subject("Nouvelle commande #{$order->code}");
                        }
                    );
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Erreur envoi email commande: '.$e->getMessage());
                }
            }
        }
    }
}
