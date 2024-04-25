@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
.order_product_status_colours {
    
}
.order_product_status_colours .colour {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 1px #333333 solid;
}
.product_out_of_stock {
    background: #f83838;
}
.product_replaced {
    background: #48f838;
}
.product_missed {
    background: #f5f838;
}
.table thead th {
    vertical-align: top;
}
.chat_system {
    text-align: left;
    background: url("{{asset('assets/images/dummy_user.png')}}") top left no-repeat;
    background-size: 50px auto;
    padding-left: 70px;
    min-height: 50px;
}
.chat_user {
    text-align: right;
    background: url("{{asset('assets/images/dummy_user.png')}}") top right no-repeat;
    background-size: 50px auto;
    padding-right: 70px;
    min-height: 50px;
}
</style>
<style>
    .text_mode {
        display: block;
    }
    .edit_mode {
        display: none;
    }
    .storekeeper_edit {
        display: block;
    }
    .storekeeper_saving {
        display: none;
    }
    .storekeeper_save {
        display: none;
    }
    .mark_out_of_stock_saving {
        display: none;
    }
    .mark_out_of_stock_save {
        display: block;
    }
    .marked_out_of_stock {
        display: none;
    }
    .order_invoice_saving {
        display: none;
    }
    .order_invoice_save {
        display: block;
    }
    .order_invoiced {
        display: none;
    }
    .driver_edit {
        display: block;
    }
    .driver_saving {
        display: none;
    }
    .driver_save {
        display: none;
    }
    .product_btn {
        width: 100%;
        min-width: 70px;
        height: 35px;
        margin-bottom: 10px;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Order #{{$order->orderId}}</h4>
                    @if($store)
                    <h4 class="page-title">Order Store: {{$store->name}}, {{$store->company_name}}</h4>
                    @endif
                    
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        
                        <li class="breadcrumb-item active">
                            @if($store)
                            <a href="{{route('fleet-orders',$store->id)}}">{{$store->name}}, {{$store->company_name}} Orders</a>
                            @else
                            <a href="{{route('fleet-store-orders')}}">All Orders</a>
                            @endif
                        </li>
                        <li class="breadcrumb-item active">Order #{{$order->orderId}}</li>
                    </ol>
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{url()->previous()}}">Go back </a>
                </div>
            </div>
            <br clear="all"/><br/>
            <div class="row">
                <div class="col-sm-12">
                    <div class="card-header">
                        <h6>Order delivery detail</h6>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        @php
                            $later_time = strtok($order->later_time, '-');
                            $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
                        @endphp
                        @php
                            $amount_from_cod = $order->payment_type=='cod' ? $order->amount_from_card : 0;
                            $amount_from_cod_suborders= $order->getSubOrder ? $order->getOrderPaidByCOD($order->id) : 0;
                            $amount_from_card = $order->payment_type=='online' ? $order->amount_from_card : 0;
                            $amount_from_card_suborders= $order->getSubOrder ? $order->getOrderPaidByCart($order->id) : 0;
                            $amount_from_wallet = $order->amount_from_wallet;
                            $amount_from_wallet_suborders = $order->getSubOrder ? $order->getOrderPaidByWallet($order->id) : 0;
                        @endphp
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Order Type</th>
                                    <td>{{ $order->delivery_time==1 ? 'Instant Order' : 'Later Order' }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Type</th>
                                    <th>{{ $order->payment_type }}</th>
                                </tr>
                                <tr>
                                    <th>Order Total Amount</th>
                                    <td>{{ $order->getSubOrder ? $order->total_amount + $order->getOrderSubTotal($order->id) : $order->total_amount }}</td>
                                </tr>
                                <tr>
                                    <th>COD</th>
                                    <td>{{ floor($amount_from_cod + $amount_from_cod_suborders) }}</td>
                                </tr>
                                <tr>
                                    <th>Cash from Card</th>
                                    <td>{{ $amount_from_card + $amount_from_card_suborders }}</td>
                                </tr>
                                <tr>
                                    <th>By Wallet</th>
                                    <td>{{ $amount_from_wallet + $amount_from_wallet_suborders }}</td>
                                </tr>
                                <tr>
                                    <th>Bring change for</th>
                                    <td>{{ $order->change_for }}</td>
                                </tr>
                                <tr>
                                    <th>Storekeeper sharing link</th>
                                    <td><a href="{{ route('storekeeper-sharing', base64url_encode($order->id)) }}" target="_blank">
                                        {{ route('storekeeper-sharing', base64url_encode($order->id)) }}</a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivery Date</th>
                                    <td>{{ $order->delivery_date }}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Time</th>
                                    <td>{{ $order->delivery_time==1 ? $later_time : $order->later_time }}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Location</th>
                                    <td>{{$order->latitude}},{{$order->longitude}}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Address</th>
                                    <td>
                                        {{ $order->name }} ({{ $order->address_type }}),<br/>
                                        {{ $order->address_line1 }},<br/>
                                        {!! $order->address_line2!='' ? $order->address_line2.',<br/>' : '' !!}
                                        {!! $order->landmark!='' ? 'Landmark: '.$order->landmark.',<br/>' : '' !!}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivery Phone Number</th>
                                    <td>{{ $order->mobile }}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Location</th>
                                    <td>{{$order->latitude}},{{$order->longitude}}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Preferences</th>
                                    <td>
                                        @if (is_array($delivery_preferences_selected) && !empty($delivery_preferences_selected) )
                                            @foreach ($delivery_preferences_selected as $key=>$delivery_pref)
                                                @if ($delivery_pref==1)
                                                    @if ($order_preferences && $order_preferences[$key])
                                                        {{$order_preferences[$key]}}<br/>
                                                    @endif
                                                @endif
                                            @endforeach
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Customer Invoice</th>
                                    <td>
                                        @if ($order->status >= 2)
                                            <a class="btn btn-primary" href="{{ route('customer-invoice-pdf', base64url_encode($order->id)) }}" target="_blank">Download PDF</a>
                                            <a class="btn btn-primary" href="{{ route('customer-invoice', base64url_encode($order->id)) }}" target="_blank">Print in letterhead</a>
                                        @else 
                                            Not invoiced yet
                                        @endif
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        <h4>Ordered Products</h4>
                        <br clear="all"/><br/>
                        <div class="order_product_status_colours">
                            <div class="colour product_out_of_stock"></div> <strong>Out of stock products</strong><br/>
                            <div class="colour product_replaced"></div> <strong>Replaced products</strong><br/>
                            <div class="colour product_missed"></div> <strong>Forgot products by customer</strong><br/>
                        </div>
                        <br clear="all"/><br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total Amount</th>
                                    <th>Forgot Product</th>
                                    <th>Replaced Product</th>
                                    <th>Store</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($order_products)
                                    @foreach ($order_products as $key => $row)
                                        @php 
                                        if ($row->is_out_of_stock==1 || $row->collected_status==2) {
                                            $product_class = 'product_out_of_stock';
                                        } elseif ($row->is_replaced_product==1) {
                                            $product_class = 'product_replaced';
                                        } elseif ($row->is_missed_product==1) {
                                            $product_class = 'product_missed';
                                        } else {
                                            $product_class = '';
                                        }
                                        @endphp
                                        <tr class="{{$product_class}}">
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $row->fk_product_id }}</td>
                                            <td>
                                                {{ $row->product_name_en }} ({{ $row->unit }})
                                                @if ($row->sub_products)
                                                    <br/>
                                                    @php
                                                        $sub_products = json_decode($row->sub_products);
                                                    @endphp
                                                    @foreach ($sub_products as $item)
                                                    &nbsp;&nbsp;- {{ $item->product_name_en ?? "" }} ({{ $item->unit ?? "" }}) - {{ $item->price ?? "" }}  * {{ $item->product_quantity ?? "" }}<br/>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td>{{ $row->single_product_price }}</td>
                                            <td>{{ $row->product_quantity }}</td>
                                            <td>{{ $row->total_product_price }}</td>
                                            <td>{{ $row->is_missed_product }}</td>
                                            <td>{{ $row->is_replaced_product }}</td>
                                            <td>
                                                @if ($row->fk_product_store_id)
                                                    {{ $row->getStore ? $row->getStore->name.' - '.$row->getStore->company_name : $row->fk_store_id }}
                                                @else
                                                    {{$store->name}} {{$store->company_name}}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <br clear="all"/><br/>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    $('input.delivery_date').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false
    }).on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY'));
    });
</script>
<script>
    // Initialize and add the map
    function initMap(){
        // Map options
        var options = {
            zoom:10,
            center:{lat:25.286106,lng:51.534817}
        }
        // New map
        var map = new google.maps.Map(document.getElementById('map'), options);
        // Listen for click on map
        google.maps.event.addListener(map, 'click', function(event){
            // Add marker
            addMarker({coords:event.latLng});
        });
        // Array of markers
        var markers = [
        @if (isset($store) && $store)
            {
                coords:{lat:{{$store->latitude}},lng:{{$store->longitude}}},
                content:'<h4>Store ID: {{$store->id}}</h4><p>{{$store->name}} - {{$store->company_name}}</p>'
            },
        @endif
        @if (isset($driver) && $driver && isset($driver_location) && $driver_location)
            {
                coords:{lat:{{$driver_location->latitude}},lng:{{$driver_location->longitude}}},
                content:'<h4>Driver ID: {{$driver->id}}</h4><p>{{$driver->name}} - {{$driver->mobile}}</p>',
                iconImage:'https://jeeb.tech/images/icons/car.png',
            },
        @endif
        @if (isset($order) && $order)
            {
                coords:{lat:{{$order->latitude}},lng:{{$order->longitude}}},
                content:'<h4>Order ID: {{$order->id}}</h4><p>{{$order_status[$order->status]}}</p>',
                @if ($order->status==0) 
                    iconImage:'https://jeeb.tech/images/icons/order-0.png',
                @elseif ($order->status==1) 
                    iconImage:'https://jeeb.tech/images/icons/order-1.png',
                @elseif ($order->status==2) 
                    iconImage:'https://jeeb.tech/images/icons/order-2.png',
                @elseif ($order->status==3) 
                    iconImage:'https://jeeb.tech/images/icons/order-3.png',
                @elseif ($order->status==4) 
                    iconImage:'https://jeeb.tech/images/icons/order-4.png',
                @elseif ($order->status==5) 
                    iconImage:'https://jeeb.tech/images/icons/order-5.png',
                @elseif ($order->status==6) 
                    iconImage:'https://jeeb.tech/images/icons/order-6.png',
                @elseif ($order->status==7) 
                    iconImage:'https://jeeb.tech/images/icons/order-7.png',
                @endif
            }
        @endif
        ];
        // Loop through markers
        for(var i = 0;i < markers.length;i++){
            // Add marker
            addMarker(markers[i]);
        }
        // Add Marker Function
        function addMarker(props){
        var marker = new google.maps.Marker({
            position:props.coords,
            map:map,
            // icon:props.iconImage
        });
        // Check for customicon
        if(props.iconImage){
            // Set icon image
            marker.setIcon(props.iconImage);
        }
        // Check content
        if(props.content){
            var infoWindow = new google.maps.InfoWindow({
            content:props.content
            });
            marker.addListener('click', function(){
            infoWindow.open(map, marker);
            });
        }
        }
    }
    window.initMap = initMap;
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAQXKQdhn1KRWpRAF9wWp3fkKzjehGm6M&callback=initMap">
    </script>
    
@endsection
