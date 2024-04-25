<!-- Billing Invoice Receipt -->
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8"/>
        <title>Invoice Receipt</title>
        <style>
            td{
                border: none;
            }
        </style>
    </head>
    <body>
        <section align="center">
            <img src="{{asset('home_assets/uploads/2019/10/jeeb_square_logo.png')}}" alt="Logo image" width="100" height="75"/>
        </section>
        <hr>
        <section style="margin-left:30px;">
            <h2>Invoice</h2>
            <p><b>Date & Time:</b> {{date('d-m-Y H:i',strtotime($order->created_at))}}</p>
            <p><b>Invoice Id:</b> <?= $order->orderId; ?></p>
        </section>
        <hr>
        <table border="2" border-color="ff0000" border-collapse="collapse" style=" width:100%; min-height:50%; padding:10px; colspan:2px; cellspacing:5px;">
            <tr style="background-color:#3fb7ba; color:white">
                <th>Sr. No.</th>
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
            @if($order_products->count())
            @foreach($order_products as $key=>$value)
            <tr>
                <td>{{$key+1}}</td>
                <td>
                    <div>
                        <p>{{$value->product_name_en}} <?= $value->product_weight != 0 ? ('(' . (int) $value->product_weight . ' ' . $value->product_unit . ')') : ''; ?></p>
                        <p>{{$value->unit}}</p>
                    </div>
                </td>
                <td>{{$value->barcode??''}}</td>
                <td>{{$value->product_quantity}}</td>
                <td>{{$value->distributor_price}}</td>
            </tr>
            @endforeach
            @endif            
            <tr>
                <td colspan="3"></td>
                <td><b>Sub Total</b></td>
                <td>{{$order->total_distributor_price}}</td>
            </tr>
        </table>

    </body>

</html>