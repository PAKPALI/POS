<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SmsQuotaController extends Controller
{
    public function index()
    {
        $company = CompanySetting::first();
        return view('sms_quota.index', compact('company'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sms_count' => ['required', 'integer', 'min:0'],
            'whatsapp_count' => ['required', 'integer', 'min:0'],
        ], [
            'sms_count.required' => 'Le nombre de SMS est requis.',
            'whatsapp_count.required' => 'Le nombre de WhatsApp est requis.',
            'sms_count.integer' => 'Le nombre de SMS doit être un entier.',
            'whatsapp_count.integer' => 'Le nombre de WhatsApp doit être un entier.',
            'sms_count.min' => 'Le nombre de SMS doit être au moins 0.',
            'whatsapp_count.min' => 'Le nombre de WhatsApp doit être au moins 0.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'title' => 'Validation échouée',
                'msg' => $validator->errors()->first(),
            ]);
        }

        $company = CompanySetting::first();
        if (!$company) {
            return response()->json([
                'status' => false,
                'title' => 'Configuration manquante',
                'msg' => 'Aucune configuration de société trouvée.',
            ]);
        }

        $company->update([
            'sms_count' => $request->sms_count,
            'whatsapp_count' => $request->whatsapp_count,
        ]);

        return response()->json([
            'status' => true,
            'title' => 'Mise à jour réussie',
            'msg' => 'Les quotas ont été mis à jour.',
        ]);
    }
}
