@extends('admin.layouts.dashboard_layout_for_fleet_panel')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-8">
                    <h4 class="page-title">Orders Of Store: {{$store->name}}, {{$store->company_name}}</h4>
                    <h2>{{$storekeeper->name}} ({{$storekeeper->id}})</h2>
                    <a class="btn btn-primary" href="{{route('fleet-orders',$store->id)}}">Orders</a>
                    <a class="btn btn-success" href="{{route('fleet-active-orders',$store->id)}}">Active Orders</a>
                    <a class="btn btn-secondary" href="{{route('fleet-drivers',$store->id)}}">Drivers</a>
                    <a class="btn btn-secondary" href="{{route('fleet-storekeepers',$store->id)}}">Storekeepers</a>
                    <a class="btn btn-warning" href="{{route('fleet-products-panel',$store->id)}}">Products</a>
                </div>
                <div class="col-sm-4">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="{{route('fleet')}}">Stores</a></li>
                        <li class="breadcrumb-item active">{{$store->name}}, {{$store->company_name}}</li>
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
                        <div id="map" style="width: 100%; height: 450px;"></div>

                        <br clear="all"/><br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    {{-- <th>Order Nearby Store</th> --}}
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($orders)
                                    @foreach ($orders as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ '#' . $row->orderId }}</td>
                                            <td>{{ $row->getSubOrder ? $row->total_amount + $row->getOrderSubTotal($row->id) : $row->total_amount }}</td>
                                            <td>{{ $order_status[$row->status] }}</td>
                                            {{-- <td>{{ $row->getStore && $row->getStore->name ? $row->getStore->company_name.' - '.$row->getStore->name : '' }}</td> --}}
                                            <td>
                                                <a href="{{ route('fleet-order-detail', $row->id) }}"><i
                                                        class="icon-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $orders->count() }} of {{ $orders->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $orders->links() }}
                                </div>
                            </div>
                        </div>
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
        @if (isset($orders) && !empty($orders))
            @foreach ($orders as $key => $order)
                @if($order->status!=0 && $order->status!=4 && $order->status!=7)
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
                @endif
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
