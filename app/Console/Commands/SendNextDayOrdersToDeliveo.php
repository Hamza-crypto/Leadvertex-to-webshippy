<?php

namespace App\Console\Commands;

use App\Http\Controllers\DeliveoController;
use Illuminate\Support\Facades\Http;
use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class SendNextDayOrdersToDeliveo extends Command
{
  protected $signature = 'orders:send-tomorrow';
  protected $description = 'Send orders with delivery date as tomorrow to next CRM';
  public function handle()
  {
      $today = Carbon::now();
      
      if ($today->isFriday()) {
          // On Friday, target orders with delivery on Monday
          $targetDate = $today->copy()->addDays(3)->startOfDay();
      } else {
          // On other days, send orders scheduled for tomorrow
          $targetDate = $today->copy()->addDay()->startOfDay();
      }

      $orders = Order::whereNull('destination_id')
        ->whereDate('delivery_date', $targetDate)
        ->get();

      // $orders = Order::whereNull('destination_id')->whereNotNull('delivery_date')->get();

      $deliveoController = new DeliveoController();
      
      $query = <<<GQL
query GetOrderById(\$orderId: ID!) {
  ordersFetcher(filters: { include: { ids: [\$orderId] } }) {
    orders {
      id
      status { name }
      createdAt
      data {
        dateTimeFields { value }
        humanNameFields { value { firstName lastName } }
        phoneFields { value { raw } }
        addressFields { value {
          postcode region city address_1 address_2 building apartment country
          location { latitude longitude }
        }}
      }
      cart {
        items {
          id
          sku {
            item { id name }
            variation { number property }
          }
          quantity
          pricing { unitPrice totalPrice }
        }
        promotions {
          id
          promotion { id name }
          items {
            promotionItem
            sku {
              item { id name }
              variation { number property }
            }
            pricing { unitPrice }
          }
        }
      }
    }
  }
}
GQL;

      foreach ($orders as $order) {
        $response = Http::withHeaders([
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . env('GRAPHQL_API_TOKEN'),
        ])->post(env('GRAPHQL_API_URL'), [
            'query' => $query,
            'variables' => ['orderId' => $order->source_id],
        ]);

        if ($response->failed()) {
            $this->error("Request failed: " . $response->body());
            continue;
        }

        $data = $response->json();

        $order_data = data_get($data, 'data.ordersFetcher.orders.0');

        if (!$order_data || !isset($order_data['id'], $order_data['status']['name'])) {
            return response()->json(['error' => 'Invalid order structure'], 422);
        }

        $order_id = $order_data['id'];
        $status = data_get($order_data, 'status.name');
        $createdAt = data_get($order_data, 'createdAt', now());

        $rawDeliveryDate = data_get($order_data, 'data.dateTimeFields.0.value');
        $deliveryTimestamp = $rawDeliveryDate ? \Carbon\Carbon::parse($rawDeliveryDate)->toDateTimeString() : null;
    
        Order::updateOrCreate(
            ['source_id' => $order_id],
            [
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => now(),
                'delivery_date' => $deliveryTimestamp,
            ]
        );

        $deliveoController->create_shipment($order_data);
        dump("Sending order ID {$order->source_id} with delivery_date: {$order->delivery_date}");
      }

      dump("Total orders sent: " . $orders->count());
  }
}
