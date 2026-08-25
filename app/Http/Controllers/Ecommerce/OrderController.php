<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Order;
use App\Services\CompanyContext;
use App\Services\SaleCreationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use RuntimeException;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function __construct(
        private CompanyContext $context,
        private SaleCreationService $saleCreationService,
    ) {}

    public function index()
    {
        $this->authorize('viewAny', Order::class);
        $canConvertToSale = $this->context->hasPermission('sales.manage');
        if (request()->ajax()) {
            $orders = Order::query()->latest();

            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('action', function ($row) use ($canConvertToSale) {
                    $buttons = '<a href="'.route('ecommerce.orders.show', $row->id).'" class="btn btn-dark btn-sm" title="Consulter"><i class="fas fa-eye"></i></a>';
                    if (in_array($row->status, ['pending', 'confirmed'], true)) {
                        if ($canConvertToSale) {
                            $buttons .= ' <button class="btn btn-success btn-sm execute-order" data-id="'.$row->id.'" data-code="'.e($row->code).'" data-no-server-loader title="Passer en vente"><i class="fas fa-cash-register"></i></button>';
                        }
                        $buttons .= ' <button class="btn btn-danger btn-sm cancel-order" data-id="'.$row->id.'" data-code="'.e($row->code).'" data-no-server-loader title="Annuler"><i class="fas fa-ban"></i></button>';
                    }

                    return $buttons;
                })
                ->editColumn('status', fn ($row) => $this->statusBadge($row->status))
                ->editColumn('total', fn ($row) => number_format($row->total, 0, ',', ' ').' FCFA')
                ->editColumn('created_at', fn ($row) => $row->created_at->format('d-m-Y H:i'))
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('ecommerce.admin.orders', compact('canConvertToSale'));
    }

    public function show(int $id)
    {
        $order = Order::with(['items.product', 'sale', 'convertedBy', 'cancelledBy'])->findOrFail($id);
        $this->authorize('view', $order);
        $canConvertToSale = $this->context->hasPermission('sales.manage');

        return view('ecommerce.admin.order-show', compact('order', 'canConvertToSale'));
    }

    public function execute(int $id)
    {
        try {
            [$order, $sale] = DB::transaction(function () use ($id) {
                $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
                $this->authorize('convert', $order);
                if ($order->sale_id || $order->status === 'converted') {
                    throw new RuntimeException('Cette commande a déjà été transformée en vente.');
                }
                if ($order->status === 'cancelled') {
                    throw new RuntimeException('Une commande annulée ne peut pas être transformée en vente.');
                }
                if (! in_array($order->status, ['pending', 'confirmed'], true)) {
                    throw new RuntimeException('Cette commande ne peut plus être transformée en vente dans son état actuel.');
                }

                $order->load('items');
                $sale = $this->saleCreationService->create([
                    'products' => $order->items->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ])->all(),
                    'received_amount' => $order->total,
                    'total_amount' => $order->total,
                    'discount' => 0,
                    'client_id' => null,
                ], request()->user());

                $order->update([
                    'status' => 'converted',
                    'sale_id' => $sale->id,
                    'converted_at' => now(),
                    'converted_by' => request()->user()->id,
                ]);
                Action::create([
                    'user_id' => request()->user()->id,
                    'function' => 'COMMANDE E-COMMERCE',
                    'text' => 'La commande '.$order->code.' a été transformée en vente '.$sale->code.'.',
                ]);

                return [$order, $sale];
            }, 3);

            return response()->json([
                'status' => true,
                'title' => 'VENTE CRÉÉE',
                'msg' => 'La commande '.$order->code.' a été transformée en vente '.$sale->code.'. Le stock et les caisses ont été mis à jour.',
                'sale_id' => $sale->id,
            ]);
        } catch (RuntimeException $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            return response()->json([
                'status' => false,
                'title' => 'CONVERSION IMPOSSIBLE',
                'msg' => $exception->getMessage(),
            ], 422);
        }
    }

    public function cancel(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => ['required', 'string', 'max:500'],
        ], [
            'reason.required' => 'Indiquez pourquoi le client n’a pas confirmé sa commande.',
            'reason.max' => 'Le motif ne peut pas dépasser 500 caractères.',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'title' => 'ANNULATION IMPOSSIBLE',
                'msg' => $validator->errors()->first(),
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($id, $request) {
                $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();
                $this->authorize('cancel', $order);
                if ($order->sale_id || $order->status === 'converted') {
                    throw new RuntimeException('Cette commande est déjà devenue une vente et ne peut plus être annulée ici.');
                }
                if ($order->status === 'cancelled') {
                    throw new RuntimeException('Cette commande est déjà annulée.');
                }

                $order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                    'cancelled_by' => $request->user()->id,
                    'cancellation_reason' => $request->string('reason')->trim()->toString(),
                ]);
                Action::create([
                    'user_id' => $request->user()->id,
                    'function' => 'COMMANDE E-COMMERCE',
                    'text' => 'La commande '.$order->code.' a été annulée : '.$order->cancellation_reason,
                ]);

                return $order;
            });

            return response()->json([
                'status' => true,
                'title' => 'COMMANDE ANNULÉE',
                'msg' => 'La commande '.$order->code.' a été annulée sans modifier le stock.',
            ]);
        } catch (RuntimeException $exception) {
            if ($exception instanceof ModelNotFoundException) {
                throw $exception;
            }
            return response()->json([
                'status' => false,
                'title' => 'ANNULATION IMPOSSIBLE',
                'msg' => $exception->getMessage(),
            ], 422);
        }
    }

    private function statusBadge(string $status): string
    {
        $statuses = [
            'pending' => ['warning', 'En attente'],
            'confirmed' => ['info', 'Confirmée'],
            'converted' => ['success', 'Passée en vente'],
            'cancelled' => ['danger', 'Annulée'],
            'processing' => ['primary', 'En cours'],
            'shipped' => ['secondary', 'Expédiée'],
            'delivered' => ['success', 'Livrée'],
        ];
        [$color, $label] = $statuses[$status] ?? ['secondary', ucfirst($status)];

        return '<span class="badge bg-'.$color.'">'.$label.'</span>';
    }
}
