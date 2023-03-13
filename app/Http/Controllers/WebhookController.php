<?php

namespace App\Http\Controllers;

use App\Models\EmailSchedule;
use App\Models\EmailSchedules;
use App\Models\ProductMapping;
use App\Models\ProductWebhook;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    function store(Request $request)
    {
        $data = $request->all();

        app('log')->channel('webhooks')->info($data);
        if ($data['status'] != 'accepted') return;


        $url = sprintf("%s/getOrdersByIds.html?token=%s&ids=%d", env('LEADVERTEX_API_URL'), env('TOKEN'), $data['id']);

        $response = Http::get($url);

        ProductWebhook::create([
            'product_id' => $data['id'],
            'response' => $response
        ]);

        $response = json_decode($response);

//            $json = file_get_contents(public_path('vertex.json'));
//            $response = json_decode($json);


        $quantity = false;
        $subtotal = 0;
        foreach ($response as $order) {
            $products = [];
            foreach ($order->goods as $product) {
                $product_sku = ProductMapping::where('product_id_lv', $product->goodID)->first();
                if(!$product_sku) continue;

                $products[] = [
                    'sku' => $product_sku->webshippy_sku,
                    'productName' => $product->name,
                    'priceGross' => $product->price,
                    'vat' => 0.27,
                    'quantity' => $product->quantity
                ];
                $subtotal += $product->price * $product->quantity;
                if ($product->quantity > 2 ){
                    $quantity = true;
                }
            }

            $shippingPrice = 3500;

            $request_body = [
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
                        'paymentMode' => "cod",
                        'codAmount' => $subtotal,
                        'paymentStatus' => "pending",
                        'paidDate' => $order->lastUpdate,
                        "shippingPrice" => $shippingPrice,
                        'shippingVat' => 0,
                        'currency' => "HUF",
                        'discount' => 0
                    ],
                    'products' => $products
                ]
            ];

            if ($quantity) {
                $request_body['order']['payment']['shippingPrice'] = 0; //if quantity > 2, then set shipping price to 0
            }
            else{
                $request_body['order']['payment']['codAmount'] += $shippingPrice; // If this line removed, then COD amount will be 0 on Webshippy
            }

            $url = sprintf("%s/CreateOrder/json", env('WEBSHIPPY_API_URL'));
            $request_body = ['request' => json_encode($request_body)];


            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($url, $request_body);
            app('log')->channel('webhooks')->info($response->json());

            return $response->json();
        }

        }


}
//        app('log')->channel('webhooks')->info($data);
