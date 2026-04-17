<?php

namespace App\Http\Controllers\AMS;

use App\Http\Controllers\Controller;
use App\Models\AMS\Setting;
use App\Models\AMS\CashAccount;
use App\Models\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Setting::first();
        $cashes = CashAccount::where('status',1)->get();

        return view('ams.settings.index', compact('setting','cashes'));
    }

    public function store(Request $request)
    {
        $error_messages = [
            "default_cash_id.required" => "Sélectionnez la caisse par défaut!",
            "default_tax.required" => "La taxe est requise!",
            "default_tax.numeric" => "La taxe doit être un nombre!",
        ];

        $validator = Validator::make($request->all(), [
            'default_cash_id' => 'required',
            'tax_cash_id' => 'nullable',
            'default_tax' => 'required|numeric|min:0',
        ], $error_messages);

        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "msg" => $validator->errors()->first()
            ]);
        }

        DB::beginTransaction();

        try {

            Setting::updateOrCreate(
                ['id' => 1],
                [
                    'default_cash_id' => $request->default_cash_id,
                    'tax_cash_id' => $request->tax_cash_id,
                    'default_tax' => $request->default_tax
                ]
            );
             CashAccount::setDefaultCash($request->default_cash_id);

            Action::create([
                'user_id' => auth()->id(),
                'function' => 'PARAMETRES AMS',
                'text' => auth()->user()->name." a modifié les paramètres AMS"
            ]);

            DB::commit();

            return response()->json([
                "status" => true,
                "msg" => "Paramètres enregistrés"
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                "status" => false,
                "msg" => $e->getMessage()
            ]);
        }
    }
}