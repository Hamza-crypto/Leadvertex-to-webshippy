<?php

namespace App\Console\Commands;

use App\Http\Controllers\SalesRenderController;
use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;

class ImportOrders extends Command
{
    protected $signature = 'graphql:fetch-orders';
    protected $description = 'Fetch orders from GraphQL API using IDs';

    public function handle()
    {
        $orderIds = range(12936, 13139);
        $salesRenderController = new SalesRenderController();

        foreach ($orderIds as $orderId) {
            $this->info("Fetching order ID: $orderId");
            $response = $salesRenderController->get_order_info($orderId);

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
