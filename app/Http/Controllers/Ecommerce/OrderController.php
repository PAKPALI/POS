<?php

namespace App\Http\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $orders = Order::latest()->get();
            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="'.route('ecommerce.orders.show', $row->id).'" class="btn btn-dark btn-sm"><i class="fas fa-eye"></i></a>';
                    return $btn;
                })
                ->editColumn('status', function ($row) {
                    $badges = [
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'processing' => 'primary',
                        'shipped' => 'secondary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                    ];
                    $color = $badges[$row->status] ?? 'secondary';
                    return '<span class="badge bg-'.$color.'">'.ucfirst($row->status).'</span>';
                })
                ->editColumn('total', function ($row) {
                    return number_format($row->total, 0, ',', ' ').' FCFA';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y H:i');
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('ecommerce.admin.orders');
    }

    public function show($id)
    {
        $order = Order::with('items.product')->findOrFail($id);
        return view('ecommerce.admin.order-show', compact('order'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json([
            'status' => true,
            'title' => 'MISE A JOUR',
            'msg' => 'Le statut de la commande a ete mis a jour.'
        ]);
    }
}
