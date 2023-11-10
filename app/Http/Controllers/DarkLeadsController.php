<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class DarkLeadsController extends Controller
{
    public function add_new_order(Request $request)
    {
        $url = sprintf("%s/addOrder.html?token=%s", env('LEADVERTEX_API_URL'), env('TOKEN'));

        $request_body = [
            'fio' => $request->name,
            'phone' => $request->phone,
            'goods' => [
                [
                    'goodID' => $request->product_id,
                    'quantity' => 1,
                    'price' => $request->price,
                ],

            ],
            'utm_term' => $request->subid ?? '',
            'referer' => "darkleads"
        ];

        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);

        app('log')->channel('darkleads')->info($lv_response->json());

        $statusCode = $lv_response->status();
        $responseJson = $lv_response->json();

        if ($statusCode == 200) {
            $responseJson = $lv_response->json();

            $dynamicId = key($responseJson);

            $response = [
                'result' => 'success',
                'id' => $dynamicId,
            ];

        } else {

            $response = [
                'result' => 'failed',
                'error' => $responseJson['error'] ?? 'Unknown Error',
                'code' => $responseJson['code'] ?? null,
            ];

        }

        return response()->json($response, $statusCode);
    }

    public function send_status_update(Request $request) {

        if ($status == 'accepted') {
            $arknetStatus = 'approve';
        }
        elseif ($status == 'spam') {
            $arknetStatus = 'trash';
        }
        else{
            $arknetStatus = 'reject';
        }

        $darklead_url = sprintf("%s?barcode=%s&subid=%s&status=%s", env('DARKLEADS_BASE_URL'), env('DARKLEADS_BARCODE'), $subid, $arknetStatus);

        Http::get($darklead_url);
    }
}