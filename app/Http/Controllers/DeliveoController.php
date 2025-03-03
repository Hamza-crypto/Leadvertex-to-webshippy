<?php

namespace App\Http\Controllers;

use App\Models\APILog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

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

    public function create_shipment($data){

        $url = sprintf(
            "%spackage/create?licence=%s&api_key=%s",
            env('DELIVEO_BASE_URL'),
            env('DELIVEO_LICENCE'),
            env('DELIVEO_API_KEY')
        );

        $deliveo_data = $this->transform($data);

        try {
            $apiLog = APILog::create([
                'api_name' => 'Deliveo',
                'request_body' => json_encode($deliveo_data),
                'response_body' => null,
            ]);

            dd($deliveo_data);
            $response = Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post($url, $deliveo_data);

            $statusCode = $response->status();
            $responseBody = $response->body();

            $apiLog->update([
                'response_body' => $responseBody,
            ]);

            Log::info('Deliveo API response:', [
                'status_code' => $statusCode,
                'body' => $responseBody,
            ]);

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('Successfully created shipment with Deliveo.');
               return [ 'message' => 'Shipment created successfully', 'deliveo_response' => json_decode($responseBody, true) ];
            } else {
                Log::error('Failed to create shipment with Deliveo. Status code: ' . $statusCode . ', Body: ' . $responseBody);
                 throw new \Exception('Failed to create shipment with Deliveo. Status code: ' . $statusCode . ', Body: ' . $responseBody);
            }
        } catch (\Exception $e) {
            Log::error('Error creating shipment with Deliveo: ' . $e->getMessage(), [
                'url' => $url,
                'data' => $deliveo_data,
                'trace' => $e->getTraceAsString(),
            ]);
            if (isset($apiLog)) { 
                $apiLog->update(['response_body' => 'Error: ' . $e->getMessage()]);
            }
            return null;
        }
    }

    public function transform(array $webhookData): array
    {
        $firstName = Arr::get($webhookData, 'data.humanNameFields.0.value.firstName');
        $lastName = Arr::get($webhookData, 'data.humanNameFields.0.value.lastName');

        $phoneRaw = Arr::get($webhookData, 'data.phoneFields.0.value.raw');
        $postcode = Arr::get($webhookData, 'data.addressFields.0.value.postcode');
        
        $city = Arr::get($webhookData, 'data.addressFields.0.value.city');
        $address_1 = Arr::get($webhookData, 'data.addressFields.0.value.address_1');

        $apartment = Arr::get($webhookData, 'data.addressFields.0.value.apartment');
        $country = Arr::get($webhookData, 'data.addressFields.0.value.country');

        $totalCodValue = $this->calculateTotalCodValue($webhookData);

        $transformedData = [
            'sender' => 'Supreme Pharmatech Hungary',
            'sender_country' => 'HU',
            'sender_zip' => '1134',
            'sender_city' => 'Budapest',
            'sender_address' => 'Lóportár utca 12',
            'sender_phone' => '36304374237',
            'sender_email' => 'szabovk@supremepharmatech.hu',
            'consignee' => trim($firstName . ' ' . $lastName), 
            'consignee_country' => $country,
            'consignee_zip' => $postcode,
            'consignee_city' => $city,
            'consignee_address' => $address_1,
            'consignee_apartment' => $apartment,
            'consignee_phone' => $phoneRaw,
           
            'delivery' => 89, //89: FámaFutár , 185: FoxPost
            'cod' => $totalCodValue,
        ];

        $transformedData['packages'] = $this->transformPackages($webhookData);

        return $transformedData;
    }

    private function calculateTotalCodValue(array $webhookData): float
    {
        $total = 0.0;

        foreach (Arr::get($webhookData, 'cart.items', []) as $cartItem) {
            $total += (float)Arr::get($cartItem, 'pricing.totalPrice', 0);
        }

        foreach (Arr::get($webhookData, 'cart.promotions', []) as $promotion) {
            foreach (Arr::get($promotion, 'items', []) as $item) {
                $total += (float)Arr::get($item, 'pricing.unitPrice', 0);
            }
        }

        return $total;
    }
    private function transformPackages(array $webhookData): array
    {
        $packages = [];
        
        foreach (Arr::get($webhookData, 'cart.promotions', []) as $promotion) {
            $packages[] = [
                'customcode' => $promotion['promotion']['name'],
                'item_no' => $promotion['promotion']['id'],
                ];
        }  

        foreach (Arr::get($webhookData, 'cart.items', []) as $cartItem) {
            $packages[] = [
              'customcode' => $cartItem['sku']['item']['name'],
              'item_no' => $cartItem['sku']['item']['id'],
              ];
        }

        return $packages;
    }
}