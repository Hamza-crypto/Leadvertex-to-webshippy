<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


class WebshopPriceController extends Controller
{
    public function index()
    {
        $url = sprintf("%s/getOfferGoods.html?token=%s", env('LEADVERTEX_API_URL'), env('TOKEN'));
        $response = Http::get($url);
        return response()->json($response->json());
    }

}