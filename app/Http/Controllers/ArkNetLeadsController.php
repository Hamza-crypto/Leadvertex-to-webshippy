<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ArkNetLeadsController extends Controller
{
    public function add_new_order(Request $request){

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
            'referer' => "arknet"
        ];

        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);

        return $lv_response->json();
    }

    public function send_status_update(Request $request)
    {

    }
}
