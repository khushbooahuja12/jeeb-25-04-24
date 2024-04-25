@extends('admin.layouts.dashboard_layout_for_fleet_panel')
@section('content')
<style>
    .text_mode {
        display: block;
    }
    .edit_mode {
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
        width: 70px;
        height: 35px;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Orders Of Store: {{ $store ? $store->name.', '.$store->company_name : 'All Stores' }}</h4>
                    <a class="btn btn-primary" href="{{route('fleet-orders',$store_id)}}">Orders</a>
                    <a class="btn btn-success" href="{{route('fleet-active-orders',$store_id)}}">Active Orders</a>
                    <a class="btn btn-secondary" href="{{route('fleet-drivers',$store_id)}}">Drivers</a>
                    <a class="btn btn-secondary" href="{{route('fleet-storekeepers',$store_id)}}">Storekeepers</a>
                    <a class="btn btn-warning" href="{{route('fleet-products-panel',$store_id)}}">Products</a>
                    <a class="btn btn-info" href="{{route('fleet-store-order-map',$store_id)}}">Map</a>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="{{route('fleet')}}">Stores</a></li>
                        <li class="breadcrumb-item active">{{ $store ? $store->name.', '.$store->company_name : 'All Stores' }}</li>
                    </ol>
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{route('fleet')}}">Go back to all stores</a>
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
                        <img src="https://jeeb.tech/images/icons/order-0.png" alt=""/> 'Order placed' &nbsp;&nbsp;
                        <img src="https://jeeb.tech/images/icons/order-1.png" alt=""/> 'Order confirmed' &nbsp;&nbsp;
                        <img src="https://jeeb.tech/images/icons/order-2.png" alt=""/> 'Order assigned' &nbsp;&nbsp;
                        <img src="https://jeeb.tech/images/icons/order-3.png" alt=""/> 'Order invoiced' &nbsp;&nbsp;
                        <img src="https://jeeb.tech/images/icons/order-4.png" alt=""/> 'Cancelled' &nbsp;&nbsp;
                        <img src="https://jeeb.tech/images/icons/order-5.png" alt=""/> 'Order in progress' &nbsp;&nbsp;
                        <img src="https://jeeb.tech/images/icons/order-6.png" alt=""/> 'Out for delivery' &nbsp;&nbsp;
                        <img src="https://jeeb.tech/images/icons/order-7.png" alt=""/> 'Delivered'<br/>
                        <br clear="all"/><br/>
                        <div id="map" style="width: 100%; height: 450px;"></div>

                        <br clear="all"/><br/>
                    
                    </div>
                </div>
            </div>
        </div>
    </div>

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
        @if (isset($orders) && !empty($orders))
            @foreach ($orders as $key => $order)
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
            }@if ($key < count($orders)-1 ),@endif
            @endforeach
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
