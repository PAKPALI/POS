<?php

namespace App\Http\Controllers\AMS;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\AMS\CashAccount;
use App\Models\AMS\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::latest()->get();

        if(request()->ajax()){
            return DataTables::of($transactions)
                ->addIndexColumn()

                ->editColumn('type', function ($row) {
                    if($row->type == 'IN'){
                        return '<span class="btn btn-success btn-sm">Entrée</span>';
                    }elseif($row->type == 'OUT'){
                        return '<span class="btn btn-danger btn-sm">Sortie</span>';
                    }else{
                        return '<span class="btn btn-info btn-sm">Transfert</span>';
                    }
                })
                ->addColumn('from_cash', function ($row) {
                    return $row->fromCash->name ?? '-';
                })
                ->addColumn('to_cash', function ($row) {
                    return $row->toCash->name ?? '-';
                })
                ->addColumn('action', function($row){
                    return '
                        <a data-id="'.$row->id.'" class="btn btn-dark btn-sm view">
                            <i class="fas fa-eye"></i>
                        </a>
                    ';
                })
                ->editColumn('amount', function ($row) {
                    return number_format($row->amount, 2, ',', ' ') . ' FCFA';
                })
                // ->editColumn('description', function ($row) {
                //     return $row->description??'-';
                // })
                ->editColumn('created_by', function ($row) {
                    return $row->user->name;
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at->format('d-m-Y H:i:s');
                })

                ->rawColumns(['type','action'])
                ->make(true);
        }
        $cashes = CashAccount::where('status',1)->get();

        $totalTransactions = Transaction::selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as total')->first();

        $inTransactions = Transaction::where('type', 'IN')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as total')
            ->first();

        $outTransactions = Transaction::where('type', 'OUT')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as total')
            ->first();

        $transferTransactions = Transaction::where('type', 'TRANSFER')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(amount),0) as total')
            ->first();

        $totalIn = Transaction::where('type', 'IN')->sum('amount');
        $totalOut = Transaction::where('type', 'OUT')->sum('amount');
        $netBalance = $totalIn - $totalOut;

        return view('ams.transaction.index', compact('cashes','netBalance', 'totalTransactions', 'inTransactions', 'outTransactions', 'transferTransactions'));
    }

    public function store(Request $request)
    {
        $error_messages = [
            "type.required" => "Sélectionnez le type de transaction!",
            "amount.required" => "Le montant est requis!",
            "amount.numeric" => "Le montant doit être un nombre!",
            "amount.min" => "Le montant doit être supérieur à 0!",
            "from_cash_id.required_if" => "Sélectionnez la caisse source!",
            "to_cash_id.required_if" => "Sélectionnez la caisse destination!",
        ];
        $validator = Validator::make($request->all(), [
            'type' => 'required',
            'amount' => 'required|numeric|min:1',
            'from_cash_id' => 'required_if:type,OUT,TRANSFER',
            'to_cash_id' => 'required_if:type,IN,TRANSFER',
        ], $error_messages);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "msg" => $validator->errors()->first()
            ]);
        }
        DB::beginTransaction();
        try {
            $amount = $request->amount;
            // IN
            if($request->type == 'IN'){
                $cash = CashAccount::findOrFail($request->to_cash_id);
                $cash->increment('balance', $amount);
                Transaction::create([
                    'type' => 'IN',
                    'to_cash_id' => $cash->id,
                    'amount' => $amount,
                    'description' => $request->description,
                    'created_by' => auth()->id(),
                ]);
            }

            // OUT
            if($request->type == 'OUT'){
                $cash = CashAccount::findOrFail($request->from_cash_id);
                if($cash->balance < $amount){
                    return response()->json([
                        "status" => false,
                        "msg" => "Solde insuffisant dans la caisse source"
                    ]);
                }
                $cash->decrement('balance', $amount);
                Transaction::create([
                    'type' => 'OUT',
                    'from_cash_id' => $cash->id,
                    'amount' => $amount,
                    'description' => $request->description,
                    'created_by' => auth()->id(),
                ]);
            }

            // TRANSFER
            if($request->type == 'TRANSFER'){
                $from = CashAccount::findOrFail($request->from_cash_id);
                $to = CashAccount::findOrFail($request->to_cash_id);
                if(!$from || !$to){
                    return response()->json([
                        "status" => false,
                        "msg" => "Caisse source ou caisse destination introuvable"
                    ]);
                }
                if($from->id == $to->id){
                    return response()->json([
                        "status" => false,
                        "msg" => "Impossible de transférer vers la même caisse"
                    ]);
                }
                if($from->balance < $amount){
                    return response()->json([
                        "status" => false,
                        "msg" => "Solde insuffisant dans la caisse source"
                    ]);
                }
                $from->decrement('balance', $amount);
                $to->increment('balance', $amount);
                Transaction::create([
                    'type' => 'TRANSFER',
                    'from_cash_id' => $from->id,
                    'to_cash_id' => $to->id,
                    'amount' => $amount,
                    'description' => $request->description,
                    'created_by' => auth()->id(),
                ]);
            }
            // LOG
            Action::create([
                'user_id' => auth()->id(),
                'function' => 'TRANSACTION AMS',
                'text' => auth()->user()->name . " a effectué une opération de type ".$request->type." de ".$amount,
            ]);
            DB::commit();
            return response()->json([
                "status" => true,
                "msg" => "Opération effectuée avec succès"
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "msg" => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        $transaction = Transaction::with(['fromCash','toCash','user'])->findOrFail($id);
        return view('ams.transaction.show', compact('transaction'));
    }
}
