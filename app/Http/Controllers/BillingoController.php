<?php

namespace App\Http\Controllers;

use App\Notifications\LeadVertexNotification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;

class BillingoController extends Controller
{

    public function createPartner($contact_info)
    {
        $data['to'] = 'billingo';

        try {

            $body = [
                "name" => "Hamza",
                "phone" => "+923115483343",
                "address" => [
                    "country_code" => "HU",
                    "post_code" => "47080",
                    "city" => "Taxila",
                    "address" => "Post Office UK",
                ],
            ];

            $response = $this->callBillingo('/partners', 'POST', $contact_info);
            return $response['id'];
        } catch (\Exception $e) {
            $data['msg'] = sprintf("Function: %s, \n%s\n%s", 'CreatePartner', $e->getMessage(), convertResponseToString($response));

            Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
        }

    }

    public function createInvoice($lv_response)
    {

        $data['to'] = 'billingo';

        try {

            foreach ($lv_response as $order) {
                $items = [];

                foreach ($order->goods as $product) {
                    $items[] = [
                        'productName' => $product->name,
                        'quantity' => $product->quantity,
                        'priceGross' => $product->price,
                        'vat' => "27%",
                        'unit' => "db",
                        'unit_price_type' => "gross",
                    ];

                }

                $contact_info = [
                    'name' => $order->fio,
                    'phone' => $order->phone,
                    'address' => [
                        'country_code' => $order->country == 'Hungary' ? 'HU' : 'other  ',
                        'post_code' => $order->postIndex,
                        'city' => $order->city,
                        'address' => $order->address,
                    ],
                ];
                $partner_id = $this->createPartner($contact_info);

                $body = [
                    "block_id" => "151450",
                    "type" => "invoice",
                    "language" => "hu",
                    "currency" => "HUF",
                    "conversion_rate" => 1,
                    "payment_method" => "cash_on_delivery",

                    "partner_id" => '1793988733', //$this->createPartner(),
                    "fulfillment_date" => $order->approvedAt,
                    "due_date" => $order->approvedAt,

                    "items" => $items,
                ];

            }

            $response = $this->callBillingo('/documents', 'POST', $body);
            $data['msg'] = convertResponseToString($response);

            Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));

        } catch (\Exception $e) {
            $data['msg'] = sprintf("Function: %s, \n%s\n%s", 'CreateInvoice', $e->getMessage(), convertResponseToString($response));

            Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
        }

    }

    public function callBillingo($url, $method = "GET", $body = [])
    {
        $url = env('BILLINGO_URL') . $url;
        $header = [
            'X-API-KEY' => env('BILLINGO_TOKEN'),
        ];
        if ($method == "POST") {
            $response = Http::withHeaders($header)->post($url, $body);

        } else {
            $response = Http::withHeaders($header)->get($url);
        }

        return $response->json();
    }
}
