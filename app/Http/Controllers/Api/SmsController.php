<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    /**
     * Handle the SMS callback.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleCallback(Request $request)
    {
        $secret = (string) config('services.kprimesms.callback_secret');
        if ($secret === '') {
            return response()->json(['message' => 'Callback indisponible.'], 503);
        }

        $providedSignature = (string) $request->header('X-KPrime-Signature', '');
        $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);
        if ($providedSignature === '' || ! hash_equals($expectedSignature, $providedSignature)) {
            return response()->json(['message' => 'Signature invalide.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'status' => ['required', 'string', 'max:50'],
            'response_token' => ['required', 'string', 'max:255'],
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Payload invalide.'], 422);
        }

        Log::info('SMS callback validated', [
            'status' => $request->string('status')->toString(),
            'response_token_hash' => hash('sha256', $request->string('response_token')->toString()),
        ]);

        return response()->json(['status' => 'accepted']);
    }
}
