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
                    <h4 class="page-title">Orders Of Group : {{ $group ? $group->name : 'All Group' }}</h4>
                    <a class="btn btn-primary" href="{{route('fleet-instant-model-orders',$group_id)}}">Orders</a>
                    <a class="btn btn-success" href="{{route('fleet-instant-model-active-orders',$group_id)}}">Active Orders</a>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="{{route('fleet')}}">Stores</a></li>
                        <li class="breadcrumb-item active">{{ $group ? $group->name : 'All Stores' }}</li>
                    </ol>
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{route('fleet-instant-model')}}">Go back to all groups</a>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="col-md-12">
                        <form method="get" action="">
                            <div class="row" style="padding: 10px">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" name="delivery_date" class="form-control delivery_date"
                                            autocomplete="off"
                                            value="<?= isset($_GET['delivery_date']) && $_GET['delivery_date'] != '' ? date('m/d/Y', strtotime($_GET['delivery_date'])) : '' ?>"
                                            placeholder="mm/dd/yyyy">
                                    </div>
                                </div>
                                {{-- <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" name="orderId" class="form-control" autocomplete="off"
                                            value="<?= isset($_GET['orderId']) && $_GET['orderId'] != '' ? $_GET['orderId'] : '' ?>"
                                            placeholder="JEEB1000">
                                    </div>
                                </div> --}}
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option selected disabled>Select Status</option>
                                        @if ($order_status)
                                            @foreach ($order_status as $key => $value)
                                                @if ($key != 5)
                                                    <option
                                                        value="{{ $key }}"<?= isset($_GET['status']) && $_GET['status'] == $key ? 'selected' : '' ?>>
                                                        {{ $value }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= url('admin/active-orders') ?>">
                                        <button type="button" class="btn btn-danger">Clear</button>
                                    </a>
                                    <button type="submit" class="btn btn-success">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
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
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Detail</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Payment Type</th>
                                    <th>COD</th>
                                    <th>Cash from Card</th>
                                    <th>By Wallet</th>
                                    <th>Store Group</th>
                                    <th>Delivery Date</th>
                                    <th>Delivery Time</th>
                                    <th>Driver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($orders)
                                    @foreach ($orders as $key => $row)
                                        <tr>
                                            @php
                                                $amount_from_cod = $row->payment_type=='cod' ? $row->amount_from_card : 0;
                                                $amount_from_cod_suborders= $row->getSubOrder ? $row->getOrderPaidByCOD($row->id) : 0;
                                                $amount_from_card = $row->payment_type=='online' ? $row->amount_from_card : 0;
                                                $amount_from_card_suborders= $row->getSubOrder ? $row->getOrderPaidByCart($row->id) : 0;
                                                $amount_from_wallet = $row->amount_from_wallet;
                                                $amount_from_wallet_suborders = $row->getSubOrder ? $row->getOrderPaidByWallet($row->id) : 0;
                                            @endphp
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ '#' . $row->orderId }} {{ isset($row->test_order) && $row->test_order==1 ? '(Test order)' : '' }}</td>
                                            <td>
                                                <a href="{{ route('fleet-instant-model-order-detail', $row->id) }}"><i
                                                        class="icon-eye"></i>
                                                </a>
                                            </td>
                                            <td>{{ $row->getSubOrder ? $row->total_amount + $row->getOrderSubTotal($row->id) : $row->total_amount }}</td>
                                            <td>{{ $order_status[$row->status] }}</td>
                                            <th>{{ $row->payment_type }}</th>
                                            <td>{{ floor($amount_from_cod + $amount_from_cod_suborders) }}</td>
                                            <td>{{ $amount_from_card + $amount_from_card_suborders }}</td>
                                            <td>{{ $amount_from_wallet + $amount_from_wallet_suborders }}</td>
                                            @php
                                                $instant_store_group = \App\Model\InstantStoreGroup::where('id',$row->fk_store_id)->first();
                                            @endphp
                                            <td>{{ isset($instant_store_group) && $instant_store_group && isset($instant_store_group->name) ? $instant_store_group->name : '' }}  ({{ $row->fk_store_id }})</td>
                                            <td>{{ $row->delivery_date }}</td>
                                            <td>{{ strtok($row->later_time,'-') }}</td>
                                            <td>
                                                {{ $row->getOrderDriver && $row->getOrderDriver->getDriver ? $row->getOrderDriver->getDriver->name : '' }}
                                                @if ($row->status < 4) 
                                                <form name="form_driver_<?= $row->id ?>" id="form_driver_<?= $row->id ?>">
                                                    <div class="edit_mode edit_mode_driver_{{ $row->id }}">
                                                        <input type="hidden" name="fk_order_id" id="fk_order_id" value="{{ $row->id }}"/>
                                                        <select class="" name="fk_driver_id" id="fk_driver_id">
                                                            <option value="">NULL</option>
                                                            @if ($drivers)
                                                                @foreach ($drivers as $driver_to_assign)
                                                                    <option value="{{ $driver_to_assign->id }}">{{ $driver_to_assign->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <button title="Edit" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn driver_edit driver_edit_{{ $row->id }} btn-sm btn-primary waves-effect"><i class="icon-pencil"></i> Edit</button>
                                                    <button title="Saving" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn driver_saving driver_saving_{{ $row->id }} btn-sm btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                    <button title="Save" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn driver_save driver_save_{{ $row->id }} btn-sm btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                                </form>
                                                @endif
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
    // Change driver
    $('.driver_edit').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.edit_mode_driver_'+id).show();
        // $('.text_mode_driver_'+id).hide();
        $('.driver_edit_'+id).hide();
        $('.driver_saving_'+id).hide();
        $('.driver_save_'+id).show();
    });
    $('.driver_save').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.driver_edit_'+id).hide();
        $('.driver_saving_'+id).show();
        $('.driver_save_'+id).hide();
        if (id) {
            let form = $("#form_driver_"+id);
            let token = "{{ csrf_token() }}";
            let formData = new FormData(form[0]);
            formData.append('id', id);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/fleet/assign_driver') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        $('.edit_mode_driver_'+id).hide();
                        $('.text_mode_driver_'+id).show();
                        $('.driver_edit_'+id).show();
                        $('.driver_saving_'+id).hide();
                        $('.driver_save_'+id).hide();
                        if (data.data) {
                            let driver = data.data;
                            $('.driver_name_text_'+id).html(driver.name);
                            window.location.href = '/admin/fleet/store/orders/'+id;
                        }
                    } else {
                        alert(data.message);
                    }
                }
            });
        } else {
            alert("Something went wrong");
        }
    });
</script>
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
