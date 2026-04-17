<?php

namespace App\Http\Controllers\AMS;

use App\Http\Controllers\Controller;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Setting;
use App\Models\AMS\Transaction;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $Category = Category::all();
        $Product = Product::all();

        $cashAccounts = CashAccount::all();
        $mainCash = CashAccount::where('is_default', 1)->first();
        $taxCash = CashAccount::where('is_tax', 1)->first();
        
        $transactions = Transaction::all();
        $latestTransactions = Transaction::latest()->take(20)->get();

        $sales = Sale::all();
        $totalProfit = Sale::sum('total_profit');
        $totalSalesAmount = Sale::sum('total_amount');
        $sale_total_profit = Sale::sum('total_profit');
        
        $settings = Setting::first();

        return view('ams.dashboard', compact(
            'Category',
            'Product',
            'sale_total_profit',
            'totalSalesAmount',
            'mainCash',
            'settings',
            'cashAccounts',
            'taxCash',
            'transactions',
            'sales',
            'totalProfit',
            'latestTransactions'
        ));
    }

    public function transactionStats(Request $request)
    {
        $query = Transaction::query();
        if ($request->daterange && strpos($request->daterange, ' - ') !== false) {
            [$start, $end] = explode(' - ', $request->daterange);

            try {
                $start = Carbon::createFromFormat('d-m-Y', trim($start))->startOfDay();
                $end = Carbon::createFromFormat('d-m-Y', trim($end))->endOfDay();

                $query->whereBetween('created_at', [$start, $end]);
            } catch (\Exception $e) {
                // ignore erreur format
            }
        }
        // dynamic group
        $groupBy = $request->group_by ?: 'day';

        switch ($groupBy) {
            case 'week':
                $format = '%Y-%u'; // week 
                break;
            case 'month':
                $format = '%Y-%m';
                break;
            case 'year':
                $format = '%Y';
                break;
            default:
                $format = '%Y-%m-%d'; // Day
        }

        $data = $query
            ->selectRaw("
                DATE_FORMAT(created_at, '{$format}') as period,
                SUM(CASE WHEN type = 'IN' THEN amount ELSE 0 END) as total_in,
                SUM(CASE WHEN type = 'OUT' THEN amount ELSE 0 END) as total_out,
                SUM(CASE WHEN type = 'TRANSFER' THEN amount ELSE 0 END) as total_transfer
            ")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return response()->json($data);
    }
}