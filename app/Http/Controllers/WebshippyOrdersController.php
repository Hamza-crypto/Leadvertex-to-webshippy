<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\WebshippyOrders;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeadVertexNotification;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

class WebshippyOrdersController extends Controller
{
    function UpdateOrders()
    {
        $data['to'] = 'webshippy';
        $url = sprintf("%s/GetOrder/json", env('WEBSHIPPY_API_URL'));

        $count = 1;
        $threeDaysAgo = Carbon::now()->subDays(3);
        $oneDaysAgo = Carbon::now()->subDays(1);

        $orders = WebshippyOrders::where('status', 'new')
            ->whereDate('created_at', '<',  $threeDaysAgo)
            ->whereDate('updated_at', '<',  $oneDaysAgo)
            ->take(10)
            ->get();

        foreach ($orders as $order) {
            // sleep(2);
            $order->touch();
            $msg = "";
            $request_body = [
                'apiKey' => env('TOKEN'),
                'filters' => [
                    'wspyId' => $order->order_id,
                    'referenceId' => '',
                    'referenceName' => '',
                    'paymentStatus' => '',
                    'paymentGateway' => '',
                    'lastMod' => ''
                ]
            ];

            dump($order->order_id);

            $request_body = ['request' => json_encode($request_body)];

            $webshippy_main_response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post($url, $request_body);

            $webshippy_response = json_decode($webshippy_main_response);
            app('log')->channel('webshippy')->info($webshippy_main_response->json());
            $count++;

            $status = $webshippy_response->status;
            if ($status == 'success') {
                $message = $webshippy_response->message;
                if (count($message) > 0 && $message[0] == 'Orders not found') {
                    $order->delete();
                    dump('Order Deleted from DB');
                    $msg = "Orders not found in WebShippy";
                    continue;
                }

                $response_array = $webshippy_response->result[0];
                $order_status = $response_array->status;

                if ($order_status == 'new') {
                    dump('Order is new');
                    continue;
                } elseif ($order_status == 'refused') {
                    $lead_vertex_id =  $response_array->referenceId;
                    $lead_vertex_id = substr($lead_vertex_id, strpos($lead_vertex_id, '#') + 1);

                    $lv_response_status = $this->update_status_on_leadvertex($lead_vertex_id);
                    if ($lv_response_status == 'OK') {
                        $order->delete();
                        dump('Order Deleted from DB after updating on LV');
                    }

                    $msg = sprintf("Webshippy Order %s refused : Order status updated on Leadvertex ID %d", $order->order_id, $lead_vertex_id);
                } elseif ($order_status == 'fulfilled') {
                    $payment_status = $response_array->paymentStatus;
                    $cod_status = $response_array->codStatus;
                    if ($payment_status == 'paid' && $cod_status == 'received') {
                        dump('Order deleted, Fulfilled and Paid');
                        $order->delete();
                        $msg = sprintf("Webshippy Order %s fulfilled", $order->order_id);
                    }
                }

                // DiscordAlert::message($msg);

                if($msg == '') continue;
                $data['msg'] = $msg;
                dump($data);
                Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
            } else {
                $result = '';
                $responseArray = json_decode($webshippy_main_response, true);
                foreach ($responseArray as $key => $value) {
                    $result .= $key . ': ' . (is_array($value) ? json_encode($value) : $value) . ', ';
                }

                $result = rtrim($result, ', ');

                $result = substr($result, 0, 2000);
                // DiscordAlert::message($result);

                $data['msg'] = $result;
                Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
            }


        }
    }

    function update_status_on_leadvertex($lead_vertex_id){

        $url = sprintf("%s/updateOrder.html?token=%s&id=%d", env('LEADVERTEX_API_URL'), env('TOKEN'), $lead_vertex_id);

        $request_body = [
            'status' => 7 // 7 = Return
        ];

        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->asForm()->post($url, $request_body);

        $lv_response = json_decode($lv_response);

        return $lv_response->$lead_vertex_id;

    }
}
