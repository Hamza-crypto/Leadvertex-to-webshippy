<?php

namespace App\Http\Controllers;

use App\Models\EmailSchedule;
use App\Models\EmailSchedules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    function store(Request $request)
    {
        $data = $request->all();

        app('log')->channel('webhooks')->info($data);
        if ($data['status'] == 'accepted') {

            $url = sprintf("%s/getOrdersByIds.html?token=%s&ids=%d", env('LEADVERTEX_API_URL'), env('TOKEN'), $data['id']);

            $response = Http::get($url);
            $response = json_decode($response);;

//            $json = file_get_contents(public_path('vertex.json'));
//            $response = json_decode($json);

            foreach ($response as $order) {
                $products = [];
                foreach ($order->goods as $product) {
                    $products[] = [
                        'sku' => $product->goodID,
                        'productName' => $product->name,
                        'priceGross' => $product->price,
                        'vat' => 0,
                        'quantity' => $product->quantity
                    ];
                }

                $request_body = json_encode([
                    'apiKey' => env('TOKEN'),
                    'order' => [
                        'referenceId' => "LV#" . $data['id'],
                        'createdAt' => $order->datetime,
                        'shipping' => [
                            'name' => $order->fio,
                            'email' => $order->email,
                            'phone' => $order->phone ?? "",
                            'countryCode' => $order->country,
                            'zip' => "",
                            'city' => $order->city,
                            'country' => $order->country,
                            'address1' => $order->address,
                            'note' => $order->comment
                        ],
                        'billing' => [
                            'name' => $order->fio,
                            'email' => $order->email,
                            'phone' => $order->phone ?? "",
                            'countryCode' => $order->country,
                            'zip' => "",
                            'city' => $order->city,
                            'country' => $order->country,
                            'address1' => $order->address

                        ],
                        'payment' => [
                            'paymentMode' => "card",
                            'codAmount' => 0,
                            'paymentStatus' => "paid",
                            'paidDate' => $order->lastUpdate,
                            'shippingVat' => 0,
                            'currency' => "HUF",
                            'discount' => 0
                        ],
                        'products' => $products
                    ]
                ]);

                $url = sprintf("%s/CreateOrder/json", env('WEBSHIPPY_API_URL'));
                $request_body = ['request' => $request_body];

                $response = Http::withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ])->asForm()->post($url, $request_body);
                app('log')->channel('webhooks')->info($response->json());

            }

        }


    }


}
//        app('log')->channel('webhooks')->info($data);
