<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FacebookWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $token = env('FACEBOOK_VERIFY_TOKEN');

        if ($request->query('hub_verify_token') === $token) {
            return response($request->query('hub_challenge'));
        }

        return response('Invalid Verify Token', 403);
    }

    public function handleWebhook(Request $request)
    {
        $appSecret = env('FACEBOOK_APP_SECRET');
        $signature = $request->header('x-hub-signature');
        $payload = $request->getContent();

        if ($this->isValidSignature($signature, $appSecret, $payload)) {
            $data = json_decode($payload, true);

            // Process lead data
            if (isset($data['entry'][0]['changes'][0]['value'])) {
                $leadData = $data['entry'][0]['changes'][0]['value'];
                Log::info('New lead:', $leadData);
            }

            return response('Webhook Handled', 200);
        }

        return response('Invalid Signature', 403);
    }

    private function isValidSignature($signature, $appSecret, $payload)
    {
        $expectedSignature = 'sha1=' . hash_hmac('sha1', $payload, $appSecret, false);

        return hash_equals($expectedSignature, $signature);
    }
}