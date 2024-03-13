<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use zoparga\SzamlazzHuSzamlaAgent\Buyer;
use zoparga\SzamlazzHuSzamlaAgent\BuyerLedger;
use zoparga\SzamlazzHuSzamlaAgent\Currency;
use zoparga\SzamlazzHuSzamlaAgent\Document\Invoice\Invoice;
use zoparga\SzamlazzHuSzamlaAgent\Item\InvoiceItem;
use zoparga\SzamlazzHuSzamlaAgent\Language;
use zoparga\SzamlazzHuSzamlaAgent\Ledger\InvoiceItemLedger;
use zoparga\SzamlazzHuSzamlaAgent\Log;
use zoparga\SzamlazzHuSzamlaAgent\Response\SzamlaAgentResponse;
use zoparga\SzamlazzHuSzamlaAgent\Seller;
use zoparga\SzamlazzHuSzamlaAgent\SzamlaAgentAPI;
use zoparga\SzamlazzHuSzamlaAgent\TaxPayer;
use Carbon\Carbon;

class SzamlaController extends Controller
{
    public function create_invoice($response)
    {
        $data = $response->data[0];


        //Agent
        $agent = SzamlaAgentAPI::create(env('SZAMLAZZ_API_KEY'), false, Log::LOG_LEVEL_DEBUG);
        $agent->setResponseType(SzamlaAgentResponse::RESULT_AS_XML);
        // $agent->setAggregator('WooCommerce');
        $agent->setXmlFileSave(false);

        $invoice = new Invoice(Invoice::INVOICE_TYPE_P_INVOICE);

            $header = $invoice->getHeader();
            $header->setPaymentMethod(Invoice::PAYMENT_METHOD_CASH);
            $header->setCurrency(Currency::CURRENCY_FT);
            $header->setLanguage(Language::LANGUAGE_EN);
            $header->setPaid(false);
            $header->setFulfillment($this->parse_date($data->dropped_off));
            $header->setPaymentDue($this->parse_date($data->dropped_off));
            $header->setInvoiceTemplate(Invoice::INVOICE_TEMPLATE_DEFAULT);
            $header->setPreviewPdf(false);
            $header->setEuVat(false);

            $seller = new Seller('OBER', '11111111-22222222-33333333');
            $seller->setEmailReplyTo('seller@example.org');
            $seller->setSignatoryName('Seller signatory');
            $seller->setEmailSubject('Invoice notification');
            $seller->setEmailContent('Pay the bill, otherwise the bank interest will be...');

        // $invoice->setSeller($seller);

            $buyer = new Buyer($data->consignee, $data->consignee_zip ?? '', $data->consignee_city ?? '', $data->consignee_address ?? '' . $data->consignee_apartment ?? '');
            $buyer->setPhone($data->consignee_phone ?? '');
            $buyer->setTaxPayer(TaxPayer::TAXPAYER_NO_TAXNUMBER);

            $buyerLedger = new BuyerLedger('123456', '2022-05-01', '123456', true);
                $buyerLedger->setSettlementPeriodStart('2022-04-01');
                $buyerLedger->setSettlementPeriodEnd('2022-04-30');
            // $buyer->setLedgerData($buyerLedger);
            // $buyer->setEmail('buyer@example.org');
            $buyer->setSendEmail(false);

        $invoice->setBuyer($buyer);


        $deliveo_controller = new DeliveoController();
        foreach($data->packages as $pkg){

            $item_detail = $deliveo_controller->get_product_details($pkg->item_no);
            $item = new InvoiceItem($item_detail->item_name, 0);
            $item->setGrossAmount(0.0);
            $item->setVatAmount(0.0);
            $item->setNetPrice(0.0);
            // $itemLedger = new InvoiceItemLedger('economic event type', 'vat economic event type', 'revenue ledger number', 'vat ledger number');
            // $itemLedger->setSettlementPeriodStart('2022-04-01');
            // $itemLedger->setSettlementPeriodEnd('2022-04-30');
            // $item->setLedgerData($itemLedger);

            $invoice->addItem($item);
            // break;

        }
        try{
            $result = $agent->generateInvoice($invoice);
            $json = $result->toJson();
        dump( json_decode($json));
        }

 catch(\Exception $e){
    dd($e->getMessage());
 }


    }

    private function parse_date($date_time_string){
        return Carbon::parse($date_time_string)?->toDateString();
    }
}