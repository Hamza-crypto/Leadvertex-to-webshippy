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

        $subtotal = 0;
        foreach ($response as $order) {
            $products = [];
            $order_products = collect($order->goods)->sortBy('quantity')->toArray(); //sorting the array by quantity
                                                                                            // so that greater quantity product comes at the end
            foreach ( $order_products as $product ) {
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
                    $shippingPrice = 0;
                }
                elseif ($product->quantity == 2){
                    $shippingPrice = 1500;
                }
                elseif ($product->quantity == 1){
                    $shippingPrice = 3500;
                }
            }

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

           $request_body['order']['payment']['shippingPrice'] = $shippingPrice; //if quantity > 2, then set shipping price to 0
           $request_body['order']['payment']['codAmount'] += $shippingPrice;


            $url = sprintf("%s/CreateOrder/json", env('WEBSHIPPY_API_URL'));
            $request_body = ['request' => json_encode($request_body)];

            echo "test";

            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($url, $request_body);
            app('log')->channel('webhooks')->info($response->json());

            return $response->json();
        }

        }
}
//        app('log')->channel('webhooks')->info($data);
