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

    public function send_status_update($subid, $status)
    {
        if ($status == 'accepted') {
            $arknetStatus = 'approve';
        }
        elseif ($status == 'spam') {
            $arknetStatus = 'trash';
        }
        else{
            $arknetStatus = 'reject';
        }

        $arknet_url = sprintf("%s%s&status=%s&wm=35", 'https://webhook.site/100d83cb-f46e-4780-a10e-d144325d48ca?', $subid, $arknetStatus);

        Http::get($arknet_url);
    }
}