<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Dompdf\Dompdf;

class SalesRenderController extends Controller
{
  protected $googleDrive;

    public function __construct(GoogleDriveService $googleDrive)
    {
        $this->googleDrive = $googleDrive;
    }

    public function get_order_info($order_id)
    {
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

        return Http::withHeaders([
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . env('GRAPHQL_API_TOKEN'),
        ])->post(env('GRAPHQL_API_URL'), [
            'query' => $query,
            'variables' => ['orderId' => $order_id],
        ]);
    }

    public function create_invoice($orderid)
    {
      $cacheKey = 'order_' . $orderid;

      $order = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($orderid) {
        $response = $this->get_order_info($orderid);
        return $response->json('data.ordersFetcher.orders.0');
    });

      // Initialize data array
      $data = [
        'invoice_id' => $order['id'] ?? null,
        'seller_name' => 'Supreme Pharmatech Hungary Kft.',
        'seller_address_line1' => '1134 Budapest',
        'seller_address_line2' => 'Lőportár utca 12. fszt',
        'seller_city_zip' => '1082',
        'seller_country' => 'Magyarország',
        'seller_tax_id' => '29191888-2-41',
        'seller_company_reg_id' => '01-09-382870',
        'page_number_info' => '1/1 Oldal',
        'footer_legal_text_1' => 'A számla tartalma mindenben megfelel a hatályos',
        'footer_legal_text_2' => 'törvényekben foglaltaknak',
        'billing_service_promo_1' => 'Ez a számla Billingo online számlázó programmal készült.',
        'billing_service_promo_2' => 'Gyors és élvezetes számlázás bármikor, bárhonnan: billingo.hu',
        'buyer_name' => '',
        'buyer_phone' => '',
        'buyer_address_line1' => '',
        'buyer_address_line2' => '',
        'buyer_city_zip' => '',
        'buyer_country' => 'Magyarország',
        'invoice_date' => '',
        'due_date' => '',
        'fulfillment_date' => '',
        'order_id' => '',
        'item_name_1' => '',
        'item_sub_description_1' => '',
        'item_quantity_1' => 0,
        'item_unit_price_net_1' => '0,00',
        'item_total_price_net_1' => '0,00',
        'item_vat_rate_1' => 27,
        'item_total_price_gross_1' => '0,00',
        'subtotal_net' => 0,
        'vat_rate_summary' => 27,
        'total_vat_amount' => 0,
        'grand_total_summary' => 0,
        'grand_total_amount' => '0',
      ];

      // Safe extraction of buyer name
      $nameField = $order['data']['humanNameFields'][0]['value'] ?? null;
      if ($nameField) {
          $data['buyer_name'] = trim(($nameField['lastName'] ?? '') . ' ' . ($nameField['firstName'] ?? ''));
      }

      // Phone number
      $data['buyer_phone'] = $order['data']['phoneFields'][0]['value']['raw'] ?? null;

      // Address
      $address = $order['data']['addressFields'][0]['value'] ?? [];
      if (!empty($address)) {
          $data['buyer_address_line1'] = $address['city'] ?? '';
          $data['region'] = $address['region'] ?? '';
          $data['buyer_address_line2'] = implode(' ', array_filter([
              $address['address_1'] ?? '',
              $address['address_2'] ?? '',
          ]));
          $data['buyer_city_zip'] = $address['postcode'] ?? '';
          $data['buyer_country'] = $address['country'] ?? 'Magyarország';
      }

      // Dates
      $createdAt = $order['createdAt'] ?? null;
      if ($createdAt) {
          $data['invoice_date'] = date('Y. m. d.', strtotime($createdAt));
          $data['due_date'] = date('Y. m. d.', strtotime($createdAt));
      }

      $data['fulfillment_date'] = null;
      $fulfillmentDate = $order['data']['dateTimeFields'][0]['value'] ?? null;
      if ($fulfillmentDate) {
          $data['fulfillment_date'] = date('Y. m. d.', strtotime($fulfillmentDate));
      }

      // Order ID
      $data['order_id'] = $order['id'] ?? null;

      // Item (if available)
      // Items loop
    $items = $order['cart']['items'] ?? [];
    $promotions = $order['cart']['promotions'] ?? [];
    $allItems = array_merge($items, $promotions);

    $subtotalNet = 0;
    $totalVat = 0;
    $grandTotal = 0;
    $vatRate = 0.27;

    $itemsData = [];

    foreach ($items as $item) {
      $name = $item['sku']['item']['name'] ?? 'Unknown Item';
      $quantity = $item['quantity'] ?? 1;
      $unitPrice = $item['pricing']['unitPrice'] ?? 0;
      $totalPrice = $item['pricing']['totalPrice'] ?? 0;
      $net = round($totalPrice / (1 + $vatRate), 2);
      $vat = $totalPrice - $net;

      $itemsData[] = [
        'name' => $name,
        'description' => '',
        'quantity' => $quantity,
        'unit_price_net' => number_format($unitPrice , 2, ',', ''),
        'total_price_net' => number_format($net, 2, ',', ''),
        'vat_rate' => 27,
        'total_price_gross' => number_format($totalPrice, 2, ',', ''),
      ];

      $subtotalNet += $net;
      $totalVat += $vat;
      $grandTotal += $totalPrice;
    }

    foreach ($promotions as $promotion) {
      $promotionName = $promotion['promotion']['name'] ?? 'Unknown Promotion';

      foreach ($promotion['items'] as $item) {
          $quantity = $item['promotionItem'] ?? 1;
          $unitPrice = $item['pricing']['unitPrice'] ?? 0;
          $totalPrice = $unitPrice * $quantity;
          $net = round($totalPrice / (1 + $vatRate), 2);
          $vat = $totalPrice - $net;

          $itemsData[] = [
              'name' => $promotionName,
              'description' => $promotionName,
              'quantity' => $quantity,
              'unit_price_net' => number_format($unitPrice, 2, ',', ''),
              'total_price_net' => number_format($net, 2, ',', ''),
              'vat_rate' => 27,
              'total_price_gross' => number_format($totalPrice, 2, ',', ''),
          ];

          $subtotalNet += $net;
          $totalVat += $vat;
          $grandTotal += $totalPrice;
      }
    }


      $data['items'] = $itemsData;
      $data['grand_total'] = $grandTotal;

      $fileName = sprintf('Invoice_%s.html', $data['order_id']);

      $localPath = 'google/' . $fileName;
      \Storage::put($localPath, view('pages.template.invoice', $data)->render());

      $this->googleDrive->uploadFile(
          $localPath, 
          $fileName, 
          env('GOOGLE_DRIVE_FOLDER_ID')
      );

      return view('pages.template.invoice', $data);
    }   
}
