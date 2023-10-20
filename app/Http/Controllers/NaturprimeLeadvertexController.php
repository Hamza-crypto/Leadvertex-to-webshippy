<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\WebshippyOrders;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LeadVertexNotification;
use Spatie\DiscordAlerts\Facades\DiscordAlert;

class NaturprimeLeadvertexController extends Controller
{
    public function new_order_created_webhook(Request $request) //Whenever new order is created on Naturprime Leadvertex account
    {
        $data = $request->all();

        $url = sprintf("%s/getOrdersByIds.html?token=%s&ids=%d", env('NATURPRIME_LEADVERTEX_API_URL'), env('TOKEN'), $data['id']);
        $response = Http::get($url);

        $response = json_decode($response);

        foreach ($response as $order) {
            $name = $order->fio;
            $phone = $order->phone;
            $productName = "";

            foreach ($order->goods as $product) {
                $productName .= $product->name . ',';
            }

            $this->createRecordOnNaturprimeVCC($name, $phone, $productName, $data['id']);

        }

    }


    public function createRecordOnNaturprimeVCC($name, $phone, $productName, $id, $msg = "")
    {

        $data_array['to'] = 'naturprime_vcc';

        if (strlen($phone) == 9) {
            $phone = "36" . $phone;
        }

        if (strlen($phone) > 11) {
            $phone = substr($phone, -11);
        }

        //If starting from 0, then append 3 at the begining
        if (substr($phone, 0, 1) === "0") {
            $phone = "3" . substr($phone, 1);
        }

        $msg .= "Phone: ";
        $msg .= $phone;
        $msg .= " ";
        $result = $msg;

        $data['form'] = [
            'name' => $name,
            'termek' => $productName,
            'order_id' => $id
        ];

        $data['contacts']['1'] = [
            'title' => 'customer',
            'name' => $name,
            'phone' => $phone,
        ];

        $response = Http::withBasicAuth(env('VCC_NATURE_USER'), env('VCC_NATURE_PASS'))->post(env('VCC_NATURE_API_URL') . '/projects/143/records', $data);

        $data_array['msg'] = $this->convertResponseToString($response->json());

        Notification::route(TelegramChannel::class, '')->notify(new LeadVertexNotification($data_array));
        // DiscordAlert::message($result);

    }

    function convertResponseToString($response)
    {
        $result = "";
        foreach ($response as $key => $value) {
            $result .= $key . ': ' . (is_array($value) ? json_encode($value) : $value) . ', ';
        }

        $result = rtrim($result, ', ');
        $result = substr($result, 0, 2000);
        return $result;
    }
}
