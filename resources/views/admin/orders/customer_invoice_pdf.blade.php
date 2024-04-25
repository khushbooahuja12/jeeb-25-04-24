<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="robots" content="noindex,nofollow">
    <title>Customer Invoice</title>
    <style type="text/css">
        body {
            font-family: 'DejaVu Sans', serif;
            margin: 10mm auto;
            font-size: 14px;
            text-align: center;
            width: 100%;
            border: initial;
            border-radius: initial;
            width: initial;
            min-height: initial;
            page-break-after: always;
        }

        @page { 
            size: a4 portrait; margin: 140px 0px 210px; }
        #header { 
            position: fixed; left: 0px; top: -140px; bottom: auto; right: 0px; height: 140px; 
            background: url("{{ asset('assets/images/letterhead-header.jpg') }}") repeat-y top center #ffffff;
            background-size: cover;
            text-align: center; 
        }
        #footer { 
            position: fixed; left: 0px; top: auto; bottom: -210px; right: 0px; height: 210px; 
            background: url("{{ asset('assets/images/letterhead-footer.jpg') }}") repeat-y top center #ffffff;
            background-size: cover;
        }

        .page {
            position: relative;
            padding: 0px;
            border: 0px;
        }

        .page-content {
            margin: 0px 70px;
            position: relative;
            z-index: 11;
            width: auto;
            border: 0px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            border: 0px;
        }

        hr {
            background-color: black;
            width: 200px;
            max-width: 100%;
            height: 2px;
            float: left;
        }

        .hr2 {
            background-color: black;
            width: 200px;
            max-width: 100%;
            height: 2px;
            float: right;
        }

        .hg {
            font-size: 11px !important;
        }

        td,
        th {
            text-align: left;
            padding: 10px 5px;
        }

        .account-d {
            font-size: 10px !important;
        }

        .account-d .span2 {
            margin-left: 30px;
        }

        .account-d .span1 {
            width: 200px !important;
        }

        tr:nth-child(even) {
            background-color: #f6f8f8;
            border-bottom: 2px solid #b9b9b8;
        }

        tr:nth-child(odd) {
            border-bottom: 2px solid #b9b9b8;
        }

        tr:nth-child(1) {
            border-bottom: none !important;
        }

        .row {
            display: flex;
        }

        .col-6 {
            width: 50%;
        }

        tr,
        th,
        td {
            font-size: 12px !important;
            letter-spacing: 2px;
        }

        tr th:nth-child(1) {
            width: 105px;
            padding-right: 0px !important;
        }

        .spnu {
            font-size: 11px;
            font-weight: 100;
            line-height: 18px !important;
        }
        
        .last-row {
            display: flex;
            flex-grow: 1;
        }
        .last-row div {
            flex-grow: 1;
        }
        tr:last-child {
            background-color: #f6f8f8;
            border-bottom: 2px solid #b9b9b8;
        }
    </style>
</head>

<body>
    <div id="header"></div>
    <div id="footer"></div>
    <div class="page">
        <div class="page-content">
            <table>
                <tbody>
                    <tr>
                        <th><br/>Invoice to:</th>
                        <th>
                            {{ $order->getOrderAddress->name ? $order->getOrderAddress->name : $order->getOrderAddress->mobile }}<br>
                            <span class="spnu">{{ $order->getOrderAddress->address_line1 }}<br>
                                {{ $order->getOrderAddress->address_line2 }}<br>
                                {{ $order->getOrderAddress->landmark ?? '' }}
                            </span>
                        </th>
                        <th></th>
                        <th></th>
                        <th>Order ID:{{ '#' . $order->orderId }}<br>
                            Date:{{ date('d/m/Y', strtotime($order->created_at)) }}
                        </th>
                    </tr>
                    <tr>
                        <th>SL.</th>
                        <th>Item Description</th>
                        <th>Price</th>
                        <th>Qty.</th>
                        <th>Total</th>
                    </tr>
                    @if ($order->getOrderActiveProducts->count())
                        @foreach ($order->getOrderActiveProducts as $key => $value)
                            <tr style="background-color: #ffffff;">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $value->product_name_en }}
                                    <?= $value->product_weight != 0 ? '(' . (int) $value->product_weight . ' ' . $value->product_unit . ')' : '' ?>
                                </td>
                                <td style="text-align: right;">{{ number_format($value->single_product_price, 2) . 'QR' }}</td>
                                <td>{{ $value->product_quantity }}</td>
                                <td style="text-align: right;">{{ number_format($value->total_product_price, 2) . 'QR' }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td colspan="5">
                            <div style="clear: both;" class="last-row">
                                <div style="text-align: left; paddin-top: 10px;">
                                    Thank you for your business.
                                </div>
                                <div style="text-align: right;">
                                    @php
                                        $sub_total = $order->getSubOrder ? $order->sub_total + $order->getOrderSubTotal($order->id) : $order->sub_total;
                                        $deliver_charge = $order->delivery_charge;
                                        $total = $order->getSubOrder ? $order->total_amount + $order->getOrderSubTotal($order->id) : $order->total_amount;
                                    @endphp
                                    <span class="span1">Sub Total:</span>
                                    <span style="display: inline-block; min-width: 200px; margin-left: 20px;"
                                        class="span2">{{ number_format($sub_total, 2) }}
                                        QR</span>
                                    <br>
                                    <span class="span1">Delivery Cost:</span>
                                    <span style="display: inline-block; min-width: 200px; margin-left: 20px;"
                                        class="span2">{{ number_format($order->delivery_charge, 2) . ' QR' }}</span>
                                    <br clear="all">
                                    <hr class="hr2" style="width: 350px; max-width: 100%;">
                                    <br clear="all"><br>
                                    <span style="font-size: 16px;font-weight: bold;" class="span1">Total:</span>
                                    <span style="font-size: 16px;font-weight: bold; display: inline-block; min-width: 200px; margin-left: 20px;" class="span2">
                                        {{ number_format($total, 2) }}
                                        QR</span><br>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
