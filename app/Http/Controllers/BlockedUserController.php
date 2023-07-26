<?php

namespace App\Http\Controllers;

use App\Models\BlockedUser;
use Illuminate\Support\Facades\Http;

class BlockedUserController extends Controller
{
    public function blockUser($id)
    {
        $leadvertex_id = $id;

        $url = sprintf("%s/getOrdersByIds.html?token=%s&ids=%d", env('LEADVERTEX_API_URL'), env('TOKEN'), $leadvertex_id);

        $response = Http::get($url);
        $response = json_decode($response);

        foreach ($response as $order) {
            $phone = $order->phone;
            try {
                BlockedUser::create([
                    'phone' => $phone,
                ]);

                $msg = sprintf("User with phone %s blocked successfully.", $phone);

            } catch (\Exception $e) {
                $msg = sprintf("%s is already blocked.", $phone);
            }

            dd($msg);

        }

    }

}
