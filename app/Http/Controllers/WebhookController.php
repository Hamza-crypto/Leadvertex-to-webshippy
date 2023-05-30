<?php

namespace App\Http\Controllers;

use App\Models\ProductMapping;
use App\Models\ProductWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

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
        $total_number_of_products = 0;
        foreach ($response as $order) {
            $products = [];

            foreach ($order->goods as $product) {
                $product_sku = ProductMapping::where('product_id_lv', $product->goodID)->first();
                if (!$product_sku) continue;

                $products[] = [
                    'sku' => $product_sku->webshippy_sku,
                    'productName' => $product->name,
                    'priceGross' => $product->price,
                    'vat' => 0.27,
                    'quantity' => $product->quantity
                ];
                $subtotal += $product->price * $product->quantity;
                $total_number_of_products+= $product->quantity;
            }

            if ($total_number_of_products > 2) {
                $shippingPrice = 0;
            } elseif ($total_number_of_products == 2) {
                $shippingPrice = 1500;
            } elseif ($total_number_of_products == 1) {
                $shippingPrice = 3500;
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
                        'zip' => $order->postIndex,
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
                        'zip' => $order->postIndex,
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

            print_r( $request_body);
            die();
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($url, $request_body);
            app('log')->channel('webhooks')->info($response->json());

            return $response->json();
        }

    }

    public function createRecordOnComnica(Request $request){
        $data = $request->all();
        DiscordAlert::message("Webhook Recieved for " . $data['id']);

        app('log')->channel('webhooks')->info($data);

        $url = sprintf("%s/getOrdersByIds.html?token=%s&ids=%d", env('LEADVERTEX_API_URL'), env('TOKEN'), $data['id']);

        $response = Http::get($url);
        // $response = file_get_contents(public_path('vertex.json'));

        $response = json_decode($response);

        foreach ($response as $order) {
                $name = $order->fio;
                $phone = $order->phone;
                $productName = "";

                foreach ($order->goods as $product) {
                    $productName .= $product->name . ',';
                }

        }

         $this->sendData($name, $phone, $productName, $data['id'], $order->datetime);

    }

    public function sendData($name, $phone, $productName, $id, $date){

        if (strlen($phone) == 9) {
            $phone = "+36" . $phone;
        }

        //If starting from 0, then append 3 at the begining
        if (strlen($phone) > 11) {
            $phone = substr($phone, -11);
        }

        if (substr($phone, 0, 1) === "0") {
            $phone = "3" . substr($phone, 1);
        }

        $data = [
            'rq_sent' => '',
            'payload' => [
                'comments' => [],
                'contacts' => [
                    [
                        'active' => true,
                        'contact' => $phone,
                        'name' => '',
                        'preferred' => true,
                        'priority' => 1,
                        'source_column' => 'phone',
                        'type' => 'phone'
                    ]
                ],
                'custom_data' => [
                    'name' => $name,
                    'phone' => $phone,
                    'termek' => $productName,
                    'sp_id' => $id,
                    'date' => $date,
                ],
                'system_columns' => [
                    'callback_to_user_id' => null,
                    'dial_from' => null,
                    'dial_to' => null,
                    'manual_redial' => null,
                    'next_call' => null,
                    'priority' => 1,
                    'project_id' => 76
                ]
            ]
        ];

        $response = Http::withBasicAuth(env('COMNICA_USER'), env('COMNICA_PASS'))->post( env('COMNICA_API_URL') .  '/integration/cc/record/save/v1', $data);
        //$response = file_get_contents(public_path('comnica.json'));

        $main_response = json_decode($response);
        #run loop on response->json and create string for each array element

        $result = '';
        if(isset($main_response->payload->errors)){

            $responseArray = json_decode($response, true);
            foreach ($responseArray as $key => $value) {
                $result .= $key . ': ' . (is_array($value) ? json_encode($value) : $value) . ', ';
            }

            $result = rtrim($result, ', ');

            $result = substr($result, 0, 2000);
        }
        else{
            $result = "Comnica ID: ";
            $result .= $main_response->payload->id;
        }

        DiscordAlert::message($result);

    }


}

