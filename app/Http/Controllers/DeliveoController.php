<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class DeliveoController extends Controller
{
    public function webhook(Request $request){
        $deliveo_id = $request['deliveo_id'];

        $url = sprintf("%s/package/%s?licence=naturprime&api_key=%s", env('DELIVEO_BASE_URL'), $deliveo_id, env('DELIVEO_API_KEY'));

        // $jsonFilePath = public_path('deliveo.json');
        // $response = File::get($jsonFilePath);

        $response = Http::timeout(30)->get($url);

        $response = json_decode($response);

        if($response->type != 'success') return;

        $szamlacontroller = new SzamlaController();
        $szamlacontroller->create_invoice($response);
    }

    public function get_product_details($item_id){

        $url = sprintf("%s/item/%s?licence=naturprime&api_key=%s", env('DELIVEO_BASE_URL'), $item_id, env('DELIVEO_API_KEY'));

        // $jsonFilePath = public_path('deliveo.json');
        // $response = File::get($jsonFilePath);

        $response = Http::timeout(30)->get($url);

        $response = json_decode($response);
dump($response);
        if ($response->type != 'success') {
            return null;
        } else {
            return $response->data[0];
        }
    }
}