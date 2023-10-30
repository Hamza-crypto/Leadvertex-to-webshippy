<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class ArbitrageUpLeadsController extends Controller
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
            'referer' => "arbitrage_up",
            'additional2' => $request->additional2 ?? '',
            'additional1' => $request->additional1 ?? '',
            'additional3' => $request->additional3 ?? '',

        ];

        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $request_body);

        return $lv_response->json();
    }

    public function send_status_update($subid, $status)
    {
        if ($status == 'accepted') {
            $arbitrage_status = 'SALE';
        }
        else{
            $arbitrage_status = 'Rejected';
        }

        $arbitrage_url = sprintf("%s%s&status=%s&payout=36", env('ARBITRAGE_API_URL'), $subid, $arbitrage_status); // $36 for each approved lead
        Http::post($arbitrage_url);
    }
}