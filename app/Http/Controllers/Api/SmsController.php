<?php

namespace App\Http\Controllers\Api\Sms;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // Log the incoming request for debugging purposes
        Log::info('SMS Callback Received', $request->all());

        // Process the request as needed
        // For example, you might want to validate the request data
        $status = $request->input('status');
        $responseToken = $request->input('response_token');
        Log::success('SMS Callback Received', $request->all());

        // Return a response to acknowledge receipt of the callback
        // return response()->json(['status' => 'success'], 200);
    }
}