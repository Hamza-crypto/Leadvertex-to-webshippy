<?php

namespace App\Http\Controllers;

use App\Models\APILog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

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

        $deliveo_data = [
            'sender'              => 'Shop name',
            'sender_country'      => 'HU',
            'sender_zip'          => '1222',
            'sender_city'         => 'Budapest',
            'sender_address'      => 'Nagytétényi út 112 L3 (MXP)',
            'sender_apartment'    => '',
            'sender_phone'        => '+36201111111',
            'sender_email'        => 'info@support.com',
            'consignee'           => 'Test Customer',
            'consignee_country'   => 'HU',
            'consignee_zip'         => '2030',
            'consignee_city'      => 'Érd',
            'consignee_address' => 'Fő utca 1.',
            'consignee_apartment' => '',
            'consignee_phone'     => '+36201234567',
            'consignee_email'     => 'customer@address.com',
            'shop_id'             => '480072',
            'delivery'            => 185,
            'cod'                 => 14700,
            'currency'            => 'HUF',
            'comment'             => 'If possible deliver in the morning',
            'tracking'            => '11723480',
            'colli'               => 1,
            'packages'            => [
                [
                    'weight' => 0.2,
                ],
            ],
        ];

        try {
            // 1. Create the API log *before* the request
            $apiLog = APILog::create([
                'api_name' => 'Deliveo',
                'request_body' => json_encode($deliveo_data),
                'response_body' => null, // Initially null
            ]);


            // 2. Send the API request
            $response = Http::post($url, $deliveo_data);


            // 3. Get the status code and body *after* the request
            $statusCode = $response->status();
            $responseBody = $response->body();

            // 4. Update the API log
            $apiLog->update([
                'response_body' => $responseBody,
            ]);

            Log::info('Deliveo API response:', [
                'status_code' => $statusCode,
                'body' => $responseBody,
            ]);

            if ($statusCode >= 200 && $statusCode < 300) {
                Log::info('Successfully created shipment with Deliveo.');
                // Process the successful response
               return [ 'message' => 'Shipment created successfully', 'deliveo_response' => json_decode($responseBody, true) ]; // Return the decoded JSON
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
            // Update API log with the error message:
            if (isset($apiLog)) { // Check if apiLog was created before the exception
                $apiLog->update(['response_body' => 'Error: ' . $e->getMessage()]);
            }
            return null; // Or throw the exception if you want it to bubble up
        }
    }
}