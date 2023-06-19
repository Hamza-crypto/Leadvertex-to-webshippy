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

        $twoDaysAgo = Carbon::now()->subDays(2);
        $oneDaysAgo = Carbon::now()->subDay();

        $orders = WebshippyOrders::where('status', 'new')
            ->whereDate('created_at', '<', $twoDaysAgo)
            ->whereDate('updated_at', '<', $oneDaysAgo)
            ->take(20)
            ->get();

        foreach ($orders as $order) {
            sleep(2);
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
                $lead_vertex_id = $response_array->referenceId;
                $lead_vertex_id = substr($lead_vertex_id, strpos($lead_vertex_id, '#') + 1);
                $payment_status = $response_array->paymentStatus;

                if ($order_status == 'new') {
                    dump('Order is new');
                    continue;
                } elseif ($order_status == 'refused') {

                    $lv_response_status = $this->update_status_on_leadvertex($lead_vertex_id, 'refused');
                    if ($lv_response_status == 'OK') {
                        $order->delete();
                        dump('Order Deleted from DB after updating on LV');
                    }

                    $msg = sprintf("Webshippy Order %s refused : Leadvertex status %d RETURN", $order->order_id, $lead_vertex_id);
                } elseif ($order_status == 'fulfilled') {

                    if ($payment_status == 'paid') {
                        // Paid in LV
                        $lv_response_status = $this->update_status_on_leadvertex($lead_vertex_id, 'paid');
                        if ($lv_response_status == 'OK') {
                            $order->delete();
                            dump('Order deleted, Fulfilled and Paid');
                            $msg = sprintf("Webshippy Order %s paid : Leadvertex status %d PAID", $order->order_id, $lead_vertex_id);
                        }
                    } elseif ($payment_status == 'pending') {
                        // Sent to
                        $lv_response_status = $this->update_status_on_leadvertex($lead_vertex_id, 'fulfilled');
                        if ($lv_response_status == 'OK') {
                            dump('Order status SENT TO');
                            $msg = sprintf("Webshippy Order %s fulfilled : Leadvertex status %d SENT-TO", $order->order_id, $lead_vertex_id);
                        }
                    }
                }
                if ($msg == '') continue;
                $data['msg'] = $msg;
                dump($data);
                Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
//                DiscordAlert::message($msg);
            } else {
                $result = '';
                $responseArray = json_decode($webshippy_main_response, true);
                foreach ($responseArray as $key => $value) {
                    $result .= $key . ': ' . (is_array($value) ? json_encode($value) : $value) . ', ';
                }

                $result = rtrim($result, ', ');

                $result = substr($result, 0, 2000);

                $data['msg'] = $result;
                Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data));
//                DiscordAlert::message($result);
            }


        }
    }

    function update_status_on_leadvertex($lead_vertex_id, $status)
    {
        $statuses = [
            'fulfilled' => 3, //Sent to
            'paid' => 5, //Paid
            'refused' => 7, //Return
        ];

        $url = sprintf("%s/updateOrder.html?token=%s&id=%d", env('LEADVERTEX_API_URL'), env('TOKEN'), $lead_vertex_id);

        $request_body = [
            'status' => $statuses[$status]
        ];
        dump($request_body);
        $lv_response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded'
        ])->asForm()->post($url, $request_body);

        $lv_response = json_decode($lv_response);

        return $lv_response->$lead_vertex_id;
    }

    public function chartData()
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(6);

        $data = WebshippyOrders::whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $labels = $data->pluck('created_at')->map(function ($date) {
            return $date->format('Y-m-d');
        });

        $createdCount = $data->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        });

        $updatedCount = $data->groupBy(function ($item) {
            return $item->updated_at->format('Y-m-d');
        })->map(function ($group) {
            return $group->count();
        });

        return response()->json([
            'labels' => $labels,
            'createdData' => $createdCount,
            'updatedData' => $updatedCount,
        ]);
    }
}
