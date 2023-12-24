<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;


class WebshopPriceController extends Controller
{
    public function index()
    {
        $url = sprintf("%s/getOfferGoods.html?token=%s", env('LEADVERTEX_API_URL'), env('TOKEN'));
        $response = Http::get($url);
        return response()->json($response->json());
    }

    public function get_xml()
    {
        $xmlFilePath = storage_path('products.xml');

        if (File::exists($xmlFilePath)) {
            $content = File::get($xmlFilePath);
            $response = response($content, 200)->header('Content-Type', 'text/xml');

            return $response;
        } else {
            return response()->json(['message' => 'XML file not found']);
        }
    }

    public function get_xml_for_arukereso()
    {
        $xmlFilePath = storage_path('products_arukereso.xml');

        if (File::exists($xmlFilePath)) {
            $content = File::get($xmlFilePath);
            $response = response($content, 200)->header('Content-Type', 'text/xml');

            return $response;
        } else {
            return response()->json(['message' => 'XML file not found']);
        }
    }

}
