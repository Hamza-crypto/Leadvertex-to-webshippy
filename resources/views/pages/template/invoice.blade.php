<!DOCTYPE html>
<html lang="hu">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>Számla #{{ $invoice_id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 40px;
            color: #333;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
        }
        h1, h2, h3 {
            margin: 0;
        }
        .header, .section {
            margin-bottom: 20px;
        }
        .row {
            display: flex;
            justify-content: space-between;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f8f8f8;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #777;
        }

        .table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }
    .table th, .table td {
        border: 1px solid #000;
        padding: 5px;
        font-size: 12pt;
    }
    .table th {
        background-color: #f2f2f2;
    }
    .table td {
        vertical-align: top;
    }

    body {
        font-size: 12pt;
        margin: 20pt;
    }
    .invoice-box {
        max-width: 200mm;
        margin: 0 auto;
    }
    .header, .section {
        margin-bottom: 15pt;
    }

    .footer {
        margin-top: 30pt;
        font-size: 10pt;
        page-break-after: always;
    }

    body {
        font-family: 'DejaVu Sans', 'Liberation Sans', Arial, sans-serif;
    }
    @media print {
        .footer {
            margin-top: 0;
        }
        .main-content {
            margin-bottom: 50pt; /* Space for footer */
        }
    }
        @media print {
        body {
            margin: 0 !important;
            padding: 0 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .invoice-box {
            max-width: 100% !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .table {
            page-break-inside: avoid;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    }


    </style>
</head>
<body>

<div class="invoice-box">

    <div class="header row">
        <div>
            <h2>Eladó</h2>
            <p><strong>{{ $seller_name }}</strong></p>
            <p>{{ $seller_address_line1 }}</p>
            <p>{{ $seller_address_line2 }}</p>
            <p>{{ $seller_country }}</p>
            <p>Adószám: {{ $seller_tax_id }}</p>
            <p>Cégjegyzékszám: {{ $seller_company_reg_id }}</p>
        </div>
        <div>
            <h2>Vevő</h2>
            <p><strong>{{ $buyer_name }}</strong></p>
            <p>{{ $buyer_address_line1 }}</p>
            <p>{{ $buyer_address_line2 }}</p>
            <p>{{ $buyer_city_zip }}, {{ $region}}</p>
        </div>
    </div>

    <div class="section">
        <h1>Számla #{{ $invoice_id }}</h1>
        <p>Számla kelte: {{ $invoice_date }}</p>
        <p>Teljesítés dátuma: {{ $fulfillment_date }}</p>
        <p>Fizetési határidő: {{ $due_date }}</p>
        
        <p>Rendelésszám: {{ $order_id }}</p>
    </div>

    <div class="section">
        <h2>Tételek</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Tétel</th>
                    <th>Mennyiség</th>
                    <th>Egységár (nettó)</th>
                    <th>Összeg (bruttó)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @if($item['name'] !== 'Delivery fee')
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td class="text-right">{{ $item['quantity'] }}</td>
                        <td class="text-right">{{ $item['unit_price_net'] }} Ft</td>
                        <td class="text-right">{{ $item['total_price_gross'] }} Ft</td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section text-right">
        <p>Összesen (nettó): <strong>{{ number_format($grand_total, 2, ',', ' ') }} Ft</strong></p>
        
        <p><strong>Fizetendő végösszeg: {{ $grand_total }} Ft</strong></p>
    </div>

    

    <div class="footer">
        <p>{{ $footer_legal_text_1 }}</p>
        <p>{{ $footer_legal_text_2 }}</p>
        <p>{{ $billing_service_promo_1 }}</p>
        <p>{{ $billing_service_promo_2 }}</p>
        <p>{{ $page_number_info }}</p>
    </div>

</div>

</body>
</html>
