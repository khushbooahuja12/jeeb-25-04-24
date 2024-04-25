<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Customer Invoice</title>
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('home_assets/uploads/2019/10/jeeb_square_logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Condensed&family=Rubik+Moonrocks&family=Ubuntu:ital,wght@0,400;1,700&display=swap"
        rel="stylesheet">
    <style type="text/css">
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        hr {
            background-color: black;
            width: 200px;
            height: 2px;
            float: left;
        }

        .hr2 {
            background-color: black;
            width: 200px;
            height: 2px;
            float: right;
        }

        .hg {
            font-size: 14px !important;
        }

        td,
        th {
            text-align: left;
            padding: 25px;
        }

        .account-d {
            font-size: 13px !important;
        }

        .account-d .span2 {
            margin-left: 30px;
        }

        .account-d .span1 {
            width: 300px !important;
        }

        .account-y {
            font-size: 23px !important;
            font-weight: bold;
        }

        .account-y .span2 {
            margin-left: 50px;
        }

        .account-y .span1 {
            width: 300px !important;
        }

        tr:nth-child(even) {
            background-color: #f6f8f8;
            border-bottom: 2px solid #b9b9b8;
        }

        tr:nth-child(6) {
            background-color: #f6f8f8;
            border-bottom: none !important;
        }

        tr:nth-child(odd) {
            border-bottom: 2px solid #b9b9b8;
        }

        tr:nth-child(1) {
            border-bottom: none !important;
        }

        .content {
            margin-top: -120px;
        }

        .t-width {
            width: 800px;
            margin: auto;
        }

        .t-width2 {
            width: 800px;
            margin: auto;
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
            font-size: 20px !important;
            font-family: 'Roboto Condensed', sans-serif !important;
            letter-spacing: 2px;
        }

        tr th:nth-child(1) {
            width: 105px;
            padding-right: 0px !important;
        }

        .t-width tr th:nth-child(1) {
            width: 20px;
            padding-right: 0px !important;
        }

        .t-width tr td:nth-child(1) {
            width: 20px;
            padding-right: 0px !important;
        }

        .t-width2 tr th:nth-child(2) {
            padding-left: 0px !important;
            padding-top: 80px !important;
        }

        .t-width2 tr th:nth-child(4) {
            padding-top: 55px !important;
        }

        .t-width2 tr th:nth-child(5) {
            padding-top: 55px !important;
            letter-spacing: 4px;
            font-weight: 600;
            font-size: 15px !important;
        }

        .t-width2 tr th {
            line-height: 25px;
        }

        .spnu {
            font-size: 14px;
            font-weight: 100;
            line-height: 18px !important;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <td>
                    <div class="header-space">&nbsp;</div>
                </td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="content">
                        <table class="t-width2">
                            <tr>
                                <th>Invoice to:</th>
                                <th>{{ $order->getOrderAddress->name ? $order->getOrderAddress->name : $order->getOrderAddress->mobile }}<br>
                                    <span class="spnu">{{ $order->getOrderAddress->address_line1 }}<br>
                                        {{ $order->getOrderAddress->address_line2 }}<br>
                                        {{ $order->getOrderAddress->landmark ?? '' }}
                                    </span>
                                </th>
                                <th></th>
                                <th>Order ID:<br>
                                    Date
                                </th>
                                <th>{{ '#' . $order->orderId }}<br>
                                    {{ date('d/m/Y', strtotime($order->created_at)) }}
                                </th>
                            </tr>
                        </table>
                        <table class="t-width">
                            <tr>
                                <th>SL.</th>
                                <th>Item Description</th>
                                <th>Price</th>
                                <th>Qty.</th>
                                <th>Total</th>
                            </tr>
                            @if ($order->getOrderProducts->count())
                                @foreach ($order->getOrderProducts as $key => $value)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $value->product_name_en }}
                                            <?= $value->product_weight != 0 ? '(' . (int) $value->product_weight . ' ' . $value->product_unit . ')' : '' ?>
                                        </td>
                                        <td>{{ number_format($value->single_product_price, 2) . ' QR' }}</td>
                                        <td>{{ $value->product_quantity }}</td>
                                        <td>{{ number_format($value->total_product_price, 2) . ' QR' }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </table>
                        <div style="margin-top: 50px;" class="row t-width">
                            <div style="text-align: left;" class="col-6">
                                Thank you for your business.<br><br>
                                <b>Payment Info:</b>
                                @if (!empty($order->getOrderPayment->paymentResponse))
                                    <p class="account-d">
                                        <span class="span1">Invoice ID:</span><span
                                            class="span2">&ensp;&ensp;{{ $invoiceId }}</span><br>
                                        @if ($order->getUser->name != '')
                                            <span class="span1">A/C Name:</span><span
                                                class="span2">&ensp;&ensp;{{ $order->getUser->name ?? 'N/A' }}</span><br>
                                        @endif
                                        <span class="span1">Card Number:</span><span
                                            class="span2">{{ $cardNumber }}</span><br>
                                    </p>
                                @else
                                    <p class="account-d">
                                        Through Wallet
                                    </p>
                                @endif
                                <hr>
                                <br>
                            </div>
                            <div style="text-align: right;" class="col-6">
                                <p class="account-y">
                                    <span class="span1">Sub Total:</span>
                                    <span
                                        class="span2">{{ number_format($order->getSubOrder ? $order->sub_total + $order->getOrderSubTotal($order->id) : $order->sub_total, 2) }}
                                        QR</span><br>
                                    <br>
                                    <span class="span1">Delivery Cost:</span>
                                    <span
                                        class="span2">{{ number_format($order->delivery_charge, 2) . ' QR' }}</span><br>
                                    <br>
                                    <hr class="hr2">
                                    <br>
                                    <span style="font-size: 23px;font-weight: bold;" class="span1">Total:</span>
                                    <span style="margin-left: 100px;font-size: 23px;font-weight: bold;"
                                        class="span2">{{ $order->getSubOrder ? $order->total_amount + $order->getOrderSubTotal($order->id) : $order->total_amount }}
                                        QR</span><br>
                                </p>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <!--<a href="javascript:void(0)" onclick="downloadCustomerInvoice()">Download pdf</a>-->
            <tr>
                <td>
                    <div class="footer-space">&nbsp;</div>
                </td>
            </tr>
        </tfoot>
    </table>
    <div class="header">
        <div class="center" height="100%" width="25%"><img src="{{ asset('assets/images/Receipt png-01.png') }}"
                height="50%"></div>
    </div>
    <div class="footer"><img class="tuy" src="{{ asset('assets/images/Receipt png-03-01.png') }}" width="100%"
            height="100%"></div>
    <script>
        //        var link = document.createElement('a');
        //        link.href = 'http://localhost/jeeb/public/index.php/vendor/customer-invoice/Ng';
        //        link.download = 'file.pdf';
        //        link.dispatchEvent(new MouseEvent('click'));
    </script>
</body>

</html>
<style>
    .tuy {
        margin-top: -50px;
        height: 200px;
    }

    .footer,
    .footer-space {
        height: 4cm;
        width: 100%;
    }

    .header,
    .header-space {
        height: 4cm;
        width: 100%;
    }

    .header {
        position: fixed;
        top: 0;
    }

    .footer {
        position: fixed;
        bottom: 0;
    }

    .center {
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        -ms-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }

    @page {
        size: auto;
        /* auto is the initial value */
        /* this affects the margin in the printer settings */
        margin: 25mm 25mm 25mm 25mm;
    }

    body {
        /* this affects the margin on the content before sending to printer */
        margin: 0px;
    }
</style>
