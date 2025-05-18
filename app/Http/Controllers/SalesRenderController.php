<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class SalesRenderController extends Controller
{
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
      $response = $this->get_order_info($orderid);
      $order = $response->json('data.ordersFetcher.orders.0');

      // Initialize data array
      $data = [
        'invoice_id' => $order['id'] ?? null,
        'seller_name' => 'Supreme Pharmatech Hungary Kft.',
        'seller_address_line1' => 'Budapest',
        'seller_address_line2' => 'Corvin sétány 1/A 8. em. 4.',
        'seller_city_zip' => '1082',
        'seller_country' => 'Magyarország',
        'seller_tax_id' => '29191888-2-42',
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
      $promotion = $order['cart']['promotions'][0] ?? null;
      if ($promotion) {
          $item = $promotion['items'][0] ?? null;
          if ($item) {
              $unitPrice = $item['pricing']['unitPrice'] ?? 0;
              $quantity = $item['promotionItem'] ?? 1;
              $totalNet = $unitPrice * $quantity;
              $vatRate = 27;

              $data['item_name_1'] = $promotion['promotion']['name'] ?? '';
              $data['item_sub_description_1'] = $item['sku']['item']['name'] ?? '';
              $data['item_quantity_1'] = $quantity;
              $data['item_unit_price_net_1'] = number_format($unitPrice, 2, ',', ' ');
              $data['item_total_price_net_1'] = number_format($totalNet, 2, ',', ' ');
              $data['item_vat_rate_1'] = $vatRate;

              $totalGross = round($totalNet * (1 + $vatRate / 100));
              $data['item_total_price_gross_1'] = number_format($totalGross, 2, ',', ' ');

              // Totals
              $data['subtotal_net'] = $totalNet;
              $data['vat_rate_summary'] = $vatRate;
              $data['total_vat_amount'] = round($totalNet * ($vatRate / 100));
              $data['grand_total_summary'] = $data['subtotal_net'] + $data['total_vat_amount'];
              $data['grand_total_amount'] = number_format($data['grand_total_summary'], 0, ',', ' ');
          }
      }
      // dump($data);
      return view('pages.template.invoice', $data);

    }   
    
    public function show_invoice()
    {
        $data = [
            'invoice_id' => '2022-8',
            'seller_name' => 'Supreme Pharmatech Hungary Kft.',
            'seller_address_line1' => 'Budapest',
            'seller_address_line2' => 'Corvin sétány 1/A 8. em. 4.',
            'seller_city_zip' => '1082',
            'seller_country' => 'Magyarország',
            'seller_tax_id' => '29191888-2-42',
            'seller_company_reg_id' => '01-09-382870',

            'buyer_name' => 'Horvathne Erika',
            'buyer_address_line1' => 'Hatvan',
            'buyer_address_line2' => 'Robert Bosch 3',
            'buyer_city_zip' => '3000',
            'buyer_country' => 'Magyarország',

            'invoice_date' => '2022. 10. 15.',
            'fulfillment_date' => '2022. 10. 15.',
            'due_date' => '2022. 10. 15.',
            'payment_method' => 'Utánvét',

            'subtotal_net' => 30685.04,
            'vat_rate_summary' => 27, // Just the number for the percentage

            'note_line_1' => 'Köszönjük, hogy nálunk vásárolt!',
            'order_id' => '1010',

            'page_number_info' => '1/1 Oldal',
            'footer_legal_text_1' => 'A számla tartalma mindenben megfelel a hatályos',
            'footer_legal_text_2' => 'törvényekben foglaltaknak',
            'billing_service_promo_1' => 'Ez a számla Billingo online számlázó programmal készült.',
            'billing_service_promo_2' => 'Gyors és élvezetes számlázás bármikor, bárhonnan: billingo.hu',

            // Item #1
            'item_name_1' => 'D252 Estrodim',
            'item_sub_description_1' => 'D252',
            'item_quantity_1' => 3,
            'item_unit_price_net_1' => number_format(10228.3465, 2, ',', ' '),
            'item_total_price_net_1' => number_format(30685.04, 2, ',', ' '),
            'item_vat_rate_1' => 27,
            'item_total_price_gross_1' => number_format(38970.00, 2, ',', ' '),
        ];

        // Calculate VAT and grand totals
        $data['total_vat_amount'] = round($data['subtotal_net'] * ($data['vat_rate_summary'] / 100));
        $data['grand_total_summary'] = $data['subtotal_net'] + $data['total_vat_amount'];
        $data['grand_total_amount'] = number_format($data['grand_total_summary'], 0, ',', ' '); // No decimals

        return view('pages.template.invoice', $data);
    }
}
