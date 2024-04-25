<!DOCTYPE html>
<html lang="ar">
<!-- <html lang="ar"> for arabic only -->
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <title>Jeeb Vendor Invoice</title>
    <style>
        @media print {
            @page {
                margin: 0 auto; /* imprtant to logo margin */
                sheet-size: 300px 250mm; /* imprtant to set paper size */
            }
            html {
                direction: ltr;
            }
            html,body{margin:0;padding:0}
            #printContainer {
                width: 350px;
                margin: auto;
                /*padding: 10px;*/
                /*border: 2px dotted #000;*/
                text-align: justify;
            }

           .text-center{text-align: center;}
        }
    </style>
</head>
<body onload="window.print();">
<h1 id="logo" class="text-center"><img src="https://jeeb.tech/assets_v3/img/logo.webp" alt="Logo" width="10%" class="text-center"></h1>

<div id='printContainer'>
    <h2 id="slogan" style="margin-top:0" class="text-center">{{ $company->company_name }}</h2>

    <table style="font-size: 14px; font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif">
        <tr>
            <td>Created At</td>
            <td><?= date("d-m-Y H:i:s", time()); ?><br></td>
        </tr>
        <tr>
            <td>Order ID</td>
            <td>#{{ $order->orderId }}</td>
        </tr>
        <tr>
            <td>Order Date</td>
            <td>{{ date('d/m/Y', strtotime($order->created_at)) }}</td>
        </tr>
        @if ($order->getOrderDeliverySlot->delivery_time == 2)
        <tr>
            <td>Delivery Slot</td>
            <td>{{ $order->getOrderDeliverySlot->later_time }}</td>
        </tr>
        @endif
    </table>
    <hr>

    <table style="font-size: 14px; font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif">
        <tr>
            <td>Item</td>
            <td>Price</td>
            <td>Qty.</td>
            <td>Total</td>
        </tr>
        @php
            $total = 0;
        @endphp
        @if ($order->getOrderProducts->where('fk_store_id', $store)->count() > 0)
            @foreach ($order->getOrderProducts->where('fk_store_id', $store) as $key => $value)
            @php
                $storekeeper_collected_product = \App\Model\StorekeeperProduct::where('fk_order_product_id',$value->id)->first();
            @endphp
            @if($storekeeper_collected_product->status == 1)
            <tr><td colspan="5"><hr></td></tr>
            <tr>
                <td>{{ $value->product_name_en }}
                    <?= $value->product_weight != 0 ? '(' . (int) $value->product_weight . ' ' . $value->product_unit . ')' : '' ?>
                    <br>
                    <br>
                    <span style="direction:rtl;">{{ $value->product_name_ar }}</span>
                </td>
                <td>{{ number_format($value->distributor_price, 2) }}</td>
                <td>{{ $value->product_quantity }}</td>
                <td>{{ number_format($value->distributor_price * $value->product_quantity, 2) }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @php
                $total += $value->distributor_price * $value->product_quantity;
            @endphp
        @endif
        @endforeach
    @endif
    </table>
    <hr>

    <table style="font-size: 14px; font-family:'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif">
        <tr>
            <td>Total</td>
            <td>{{ number_format($total, 2) }} QR</td>
        </tr>
    </table>
    <hr>

</div>
</body>
</html>