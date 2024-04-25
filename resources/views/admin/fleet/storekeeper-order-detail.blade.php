@extends('admin.layouts.dashboard_layout_for_fleet_panel')
@section('content')
<style>
.order_product_status_colours .colour {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 1px #333333 solid;
}
.product_out_of_stock {
    background: #f83838;
}
.product_collected {
    background: #48f838;
}

.product_replaced {
    background: #38cff8;
}
.product_missed {
    background: #f5f838;
}
.product_added {
    background: #f8388d;
}
.table thead th {
    vertical-align: top;
}
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
.storekeeper_edit {
        display: block;
}
.storekeeper_saving {
        display: none;
}
.storekeeper_save {
        display: none;
}
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Order #{{$order->orderId}}</h4>
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
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                @php
                                    $later_time = strtok($order->later_time, '-');
                                    $later_time = strlen($later_time)<5 ? '0'.$later_time : $later_time;
                                @endphp
                                <tr>
                                    <th>Order Type</th>
                                    <td>{{ $order->delivery_time==1 ? 'Instant Order' : 'Later Order' }}</td>
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
                                    <th>
                                        Driver<br/>
                                        @if ($order->status < 4) 
                                        <form name="form_driver_<?= $order->id ?>" id="form_driver_<?= $order->id ?>">
                                            <div class="edit_mode edit_mode_driver_{{ $order->id }}">
                                                <input type="hidden" name="fk_order_id" id="fk_order_id" value="{{ $order->id }}"/>
                                                <select class="" name="fk_driver_id" id="fk_driver_id">
                                                    <option value="">NULL</option>
                                                    {{-- @if ($order->fk_product_store_id) --}}
                                                        @if ($all_drivers)
                                                            @foreach ($all_drivers as $driver_to_assign)
                                                                <option value="{{ $driver_to_assign->id }}">{{ $driver_to_assign->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    {{-- @else
                                                        @if ($drivers)
                                                            @foreach ($drivers as $driver_to_assign)
                                                                <option value="{{ $driver_to_assign->id }}">{{ $driver_to_assign->name }}</option>
                                                            @endforeach
                                                        @endif
                                                    @endif --}}
                                                </select>
                                            </div>
                                            <button title="Edit" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn driver_edit driver_edit_{{ $order->id }} btn-sm btn-primary waves-effect"><i class="icon-pencil"></i> Edit</button>
                                            <button title="Saving" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn driver_saving driver_saving_{{ $order->id }} btn-sm btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                            <button title="Save" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn driver_save driver_save_{{ $order->id }} btn-sm btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                        </form>
                                        @endif
                                    </th>
                                    <td>
                                        <table class="table table-bordered dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <td>
                                                        <div class="text_mode text_mode_driver_{{ $order->id }} driver_name_text_{{ $order->id }}" id="driver_name">
                                                            {{ ($driver && $driver->getDriver && $driver->getDriver->name) ? $driver->getDriver->name : 'N/A' }}
                                                            {{ !$driver ? '(Not assigned yet)' : '' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Mobile</th>
                                                    <td>
                                                        <div class="text_mode text_mode_driver_{{ $order->id }} driver_mobile_text_{{ $order->id }}" id="driver_mobile">
                                                            {{ $driver && $driver->getDriver && $driver->getDriver->mobile ? '+' . $driver->getDriver->country_code . '-' . $driver->getDriver->mobile : 'N/A' }}
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Image</th>
                                                    <td><img src="{{ !empty($driver && $driver->getDriverImage) ? asset('images/driver_images') . '/' . $driver->getDriverImage->file_name : asset('assets/images/dummy_user.png') }}"
                                                            width="auto" height="75"></td>
                                                </tr>
                                                <tr>
                                                    <th>Latitude</th>
                                                    <td>{{ $driver_location ? $driver_location->latitude : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Longitude</th>
                                                    <td>{{ $driver_location ? $driver_location->longitude : 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Last Tracked At</th>
                                                    <td>{{ $driver_location ? $driver_location->updated_at : 'N/A' }}</td>
                                                </tr>
                                            </thead>
                                        </table>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                        <br clear="all"/><br/>
                        <div id="map" style="width: 100%; height: 450px;"></div>
                        <br clear="all"/><br/>

                        <h3>Ordered Products</h3>
                        
                        <div class="order_product_status_colours">
                            <div class="colour product_out_of_stock"></div> <strong>Out of stock products</strong><br/>
                            <div class="colour product_collected"></div> <strong>Collected Products</strong><br/>
                            <div class="colour product_replaced"></div> <strong>Replaced products</strong><br/>
                            <div class="colour product_missed"></div> <strong>Forgot products by customer</strong><br/>
                            <div class="colour product_added"></div> <strong>Added products by admin</strong><br/>
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
                                    <th>Added Product</th>
                                    <th>Store</th>
                                    <th>Collected Status</th>
                                    <th>Storekeeper</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($order_products)
                                    @foreach ($order_products as $key => $row)
                                        @php 
                                        if ($row->is_out_of_stock==1 || $row->collected_status==2) {
                                            $product_class = 'product_out_of_stock';
                                        }elseif ($row->collected_status==1) {
                                            $product_class = 'product_collected';
                                        } elseif ($row->is_replaced_product==1) {
                                            $product_class = 'product_replaced';
                                        } elseif ($row->is_missed_product==1) {
                                            $product_class = 'product_missed';
                                        } elseif ($row->is_added_product==1) {
                                            $product_class = 'product_added';
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
                                            <td>{{ $row->is_added_product }}</td>
                                            <td>
                                                @if ($row->fk_product_store_id)
                                                    {{ $row->getStore ? $row->getStore->name.' - '.$row->getStore->company_name : $row->fk_store_id }}
                                                @else
                                                    {{$store->name}} {{$store->company_name}}
                                                @endif
                                            </td>
                                            <td>{{ isset($order_product_status[$row->collected_status]) ? $order_product_status[$row->collected_status] : '' }} - ({{ $row->collected_status}})</td>
                                            <form name="form_<?= $row->id ?>" id="form_<?= $row->id ?>">
                                                <input type="hidden" name="fk_order_id" id="fk_order_id" value="{{ $row->fk_order_id }}"/>
                                                <input type="hidden" name="order_product_id" id="order_product_id" value="{{ $row->id }}"/>
                                                <input type="hidden" name="fk_product_id" id="fk_product_id" value="{{ $row->fk_product_id }}"/>
                                                <input type="hidden" name="fk_storekeeper_id" id="fk_storekeeper_id" value="{{ $row->fk_storekeeper_id }}"/>
                                            <td>
                                                @if ($row->is_out_of_stock==0) 
                                                <div class="text_mode text_mode_{{ $row->id }} storekeeper_name_text_{{ $row->id }}" id="storekeeper_name">{{ $row->storekeeper_name ? $row->storekeeper_name : 'Not assigned' }}</div>
                                                <div class="edit_mode edit_mode_{{ $row->id }}">
                                                    <input type="hidden" name="fk_order_id" id="fk_order_id" value="{{ $row->fk_order_id }}"/>
                                                    <input type="hidden" name="order_product_id" id="order_product_id" value="{{ $row->id }}"/>
                                                    <input type="hidden" name="fk_product_id" id="fk_product_id" value="{{ $row->fk_product_id }}"/>
                                                    <select class="" name="fk_storekeeper_id" id="fk_storekeeper_id">
                                                        <option value="">NULL</option>
                                                        @if ($row->fk_product_store_id)
                                                            @if ($all_storekeepers)
                                                                @foreach ($all_storekeepers as $storekeeper)
                                                                    <option value="{{ $storekeeper->id }}" {{ $row->storekeeper_id==$storekeeper->id ? 'selected="true"' : '' }}>{{ $storekeeper->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        @else
                                                            @if ($storekeepers)
                                                                @foreach ($storekeepers as $storekeeper)
                                                                    <option value="{{ $storekeeper->id }}" {{ $row->storekeeper_id==$storekeeper->id ? 'selected="true"' : '' }}>{{ $storekeeper->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        @endif
                                                    </select>
                                                </div>
                                                @if ($order->status < 3) 
                                                <button title="Edit" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn storekeeper_edit storekeeper_edit_{{ $row->id }} btn-sm btn-primary waves-effect"><i class="icon-pencil"></i> Edit</button>
                                                <button title="Saving" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn storekeeper_saving storekeeper_saving_{{ $row->id }} btn-sm btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                <button title="Save" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn storekeeper_save storekeeper_save_{{ $row->id }} btn-sm btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                                @endif
                                                @else
                                                    <input type="hidden" name="fk_order_id" id="fk_order_id" value="{{ $row->fk_order_id }}"/>
                                                    <input type="hidden" name="order_product_id" id="order_product_id" value="{{ $row->id }}"/>
                                                    <input type="hidden" name="fk_product_id" id="fk_product_id" value="{{ $row->fk_product_id }}"/>
                                                    <span class="storekeeper_name storekeeper_name_text_{{ $row->id }}">{{ $row->storekeeper_name ? $row->storekeeper_name : 'Not assigned' }}
                                                @endif
                                                <br>
                                                @if($order->status < 3 && $row->collected_status !=1)
                                                    <a title="Mark Collected" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn mark_collected mark_collected_save_{{ $row->id }} btn-sm btn-warning waves-effect"><i class="icon-check"></i> Mark Collected</a>
                                                @endif
                                            </td>
                                            <form>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
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
                url: "<?= url('admin/fleet/storekeeper/assign_driver') ?>",
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
         //Mark item collected
    $('.mark_collected').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure to mark this item as collected ?');
        if (confirmed) {
            let id = $(this).attr('id');
            var fk_storekeeper_id = $('#fk_storekeeper_id').val();
            $('.mark_collected_'+id).hide();
            $('.mark_collecting_'+id).show();
            if (id) {
                let form = $("#form_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('fk_storekeeper_id', fk_storekeeper_id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/fleet/mark_collected') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            $('.mark_collected_'+id).show();
                            $('.mark_collected_'+id).hide();
                            $('.mark_collecting_'+id).hide();
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
        }
    });

    // Change storekeeper
    $('.storekeeper_edit').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.edit_mode_'+id).show();
        $('.text_mode_'+id).hide();
        $('.storekeeper_edit_'+id).hide();
        $('.storekeeper_saving_'+id).hide();
        $('.storekeeper_save_'+id).show();
    });
    $('.storekeeper_save').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.storekeeper_edit_'+id).hide();
        $('.storekeeper_saving_'+id).show();
        $('.storekeeper_save_'+id).hide();
        if (id) {
            let form = $("#form_"+id);
            let token = "{{ csrf_token() }}";
            let formData = new FormData(form[0]);
            formData.append('id', id);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/fleet/assign_storekeeper') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) { console.log(data.data);
                    if (data.error_code == "200") {
                        $('.edit_mode_'+id).hide();
                        $('.text_mode_'+id).show();
                        $('.storekeeper_edit_'+id).show();
                        $('.storekeeper_saving_'+id).hide();
                        $('.storekeeper_save_'+id).hide();
                        if (data.data) {
                            let storekeeper = data.data;
                            $('.storekeeper_name_text_'+id).html(storekeeper.name);
                            location.reload();
                        }else{
                            location.reload();
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
