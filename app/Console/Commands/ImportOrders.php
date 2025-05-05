<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use Carbon\Carbon;

class ImportOrders extends Command
{
    protected $signature = 'graphql:fetch-orders';
    protected $description = 'Fetch orders from GraphQL API using IDs';

    public function handle()
    {
        $orderIds = range(12728, 13139);

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

        foreach ($orderIds as $orderId) {
            $this->info("Fetching order ID: $orderId");

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('GRAPHQL_API_TOKEN'),
            ])->post(env('GRAPHQL_API_URL'), [
                'query' => $query,
                'variables' => ['orderId' => $orderId],
            ]);

            if ($response->failed()) {
                $this->error("Request failed: " . $response->body());
                continue;
            }

            $data = $response->json();
            $order = data_get($data, 'data.ordersFetcher.orders.0');

            if (!$order || !isset($order['id'])) {
                $this->error("Invalid order structure for ID $orderId");
                continue;
            }

            // Store order data
            $order_id = $order['id'];
            $status = data_get($order, 'status.name');
            $createdAt = data_get($order, 'createdAt', now());
            $rawDeliveryDate = data_get($order, 'data.dateTimeFields.0.value');
            $deliveryTimestamp = $rawDeliveryDate ? Carbon::parse($rawDeliveryDate)->toDateTimeString() : null;

            Order::updateOrCreate(
                ['source_id' => $order_id],
                [
                    'status' => $status,
                    'created_at' => $createdAt,
                    'updated_at' => now(),
                    'delivery_date' => $deliveryTimestamp,
                ]
            );

        }
    }
}
