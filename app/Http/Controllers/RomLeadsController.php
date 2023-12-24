<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class RomLeadsController extends Controller
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
            'country' => 'HU',
            'utm_term' => $request->subid ?? '',
            'referer' => "rom_leads",
            'webmasterID' => 13,
            'ip' => $request->ip ?? ''
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

        $arknet_url = sprintf("%s%s&status=%s&wm=36", env('ARKNET_BASE_URL'), $subid, $arknetStatus);

        Http::get($arknet_url);
    }
}