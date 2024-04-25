@extends('admin.layouts.dashboard_layout_for_fleet_panel')
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
.product_select {
        border: 1px #eeeeee solid;
        background: #ffffff;
        text-align: center;
        padding: 10px;
        height: 100%;
        flex-grow: 1;
    }
    .product_select img {
        display: block;
        margin: 5px auto 20px;
        max-width: 85%; 
        height: 120px;
    }
    .product_select p {
        display: block;
        margin: 5px auto;
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
                    {{-- <h4 class="page-title">Order #{{$order->orderId}} - Nearby Store: {{$store->name}}, {{$store->company_name}}</h4> --}}
                    <h4 class="page-title">Order #{{$order->orderId}}</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="{{route('fleet')}}">Stores</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                        <li class="breadcrumb-item active">Order #{{$order->orderId}}</li>
                    </ol>
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{route('fleet-instant-model')}}">Go back to all groups</a>
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
                                <tr>
                                    <th>User Details</th>
                                    <td>
                                        @if ($order->getUser)  
                                        <table class="table table-bordered dt-responsive nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <td><a
                                                            href="<?= url('admin/users/show/' . base64url_encode($order->fk_user_id)) ?>">{{ $order->getUser->name ?? 'N/A' }}</a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Email</th>
                                                    <td>{{ $order->getUser->email ?? 'N/A' }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Mobile</th>
                                                    <td>{{ $order->getUser->mobile ? '+' . $order->getUser->country_code . '-' . $order->getUser->mobile : 'N/A' }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <th>Image</th>
                                                    <td><img src="{{ !empty($order->getUser->getDriverImage) ? asset('images/user_images') . '/' . $order->getUser->getDriverImage->file_name : asset('assets/images/dummy_user.png') }}"
                                                            width="auto" height="75"></td>
                                                </tr>
                                            </thead>
                                        </table>
                                        @else
                                            User is not found!
                                        @endif
                                    </td>
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
                        <a href="https://maps.google.com/?q={{$order->latitude}},{{$order->longitude}}" target="_blank">
                        @if ($order->status == 0) 
                        <img src="https://jeeb.tech/images/icons/order-0.png" alt=""/> 'Order placed' &nbsp;&nbsp;
                        @elseif ($order->status == 1) 
                        <img src="https://jeeb.tech/images/icons/order-1.png" alt=""/> 'Order confirmed' &nbsp;&nbsp;
                        @elseif ($order->status == 2) 
                        <img src="https://jeeb.tech/images/icons/order-2.png" alt=""/> 'Order assigned' &nbsp;&nbsp;
                        @elseif ($order->status == 3) 
                        <img src="https://jeeb.tech/images/icons/order-3.png" alt=""/> 'Order invoiced' &nbsp;&nbsp;
                        @elseif ($order->status == 4) 
                        <img src="https://jeeb.tech/images/icons/order-4.png" alt=""/> 'Cancelled' &nbsp;&nbsp;
                        @elseif ($order->status == 5) 
                        <img src="https://jeeb.tech/images/icons/order-5.png" alt=""/> 'Order in progress' &nbsp;&nbsp;
                        @elseif ($order->status == 6) 
                        <img src="https://jeeb.tech/images/icons/order-6.png" alt=""/> 'Out for delivery' &nbsp;&nbsp;
                        @elseif ($order->status == 7) 
                        <img src="https://jeeb.tech/images/icons/order-7.png" alt=""/> 'Delivered'<br/>
                        @endif 
                        </a>
                        <br clear="all"/><br/>
                        <div id="map" style="width: 100%; height: 450px;"></div>

                        <br clear="all"/><br/>
                        <h3>Ordered Products</h3>
                        {{-- Order Invoice Button --}}
                        <div class="order-invoice" style="width: 100%;max-width: 350px;float: left;">
                            <form name="form_order_invoice" id="form_order_invoice">
                                <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}"/>
                                @if ($order->status == 1)
                                    <button title="Invoice Order" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn order_invoice_save btn-sm btn-warning waves-effect"><i class="icon-check"></i> Invoice Order</button>
                                    <button title="Order Invoiced" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn order_invoiced btn-sm btn-success waves-effect"><i class="icon-check"></i> Order Invoiced</button>
                                @else
                                    <button title="Invoice Order" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn order_invoice_save btn-sm btn-warning waves-effect" style="display: none;"><i class="icon-check"></i> Invoice Order</button>
                                    <button title="Order Invoiced" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn order_invoiced btn-sm btn-success waves-effect" style="display: block;">
                                        @if ($order->status == 0) 
                                        <img src="https://jeeb.tech/images/icons/order-0.png" alt=""/> 'Order placed' &nbsp;&nbsp;
                                        @elseif ($order->status == 1) 
                                        <img src="https://jeeb.tech/images/icons/order-1.png" alt=""/> 'Order confirmed' &nbsp;&nbsp;
                                        @elseif ($order->status == 2) 
                                        <img src="https://jeeb.tech/images/icons/order-2.png" alt=""/> 'Order assigned' &nbsp;&nbsp;
                                        @elseif ($order->status == 3) 
                                        <img src="https://jeeb.tech/images/icons/order-3.png" alt=""/> 'Order invoiced' &nbsp;&nbsp;
                                        @elseif ($order->status == 4) 
                                        <img src="https://jeeb.tech/images/icons/order-4.png" alt=""/> 'Cancelled' &nbsp;&nbsp;
                                        @elseif ($order->status == 5) 
                                        <img src="https://jeeb.tech/images/icons/order-5.png" alt=""/> 'Order in progress' &nbsp;&nbsp;
                                        @elseif ($order->status == 6) 
                                        <img src="https://jeeb.tech/images/icons/order-6.png" alt=""/> 'Out for delivery' &nbsp;&nbsp;
                                        @elseif ($order->status == 7) 
                                        <img src="https://jeeb.tech/images/icons/order-7.png" alt=""/> 'Delivered'<br/>
                                        @else 
                                        <img src="https://jeeb.tech/images/icons/order-1.png" alt=""/> 'Not invoiced'<br/>
                                        @endif 
                                    </button>
                                @endif
                                <button title="Saving" href="javascript:void(0);" id="{{ $order->id }}" class="product_btn order_invoice_saving btn-sm btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                            </form>
                        </div>
                        
                        <br clear="all"/><br/>
                        <div class="order_product_status_colours">
                            <div class="colour product_out_of_stock"></div> <strong>Out of stock products</strong><br/>
                            <div class="colour product_collected"></div> <strong>Collected Products</strong><br/>
                            <div class="colour product_replaced"></div> <strong>Replaced products</strong><br/>
                            <div class="colour product_missed"></div> <strong>Forgot products by customer</strong><br/>
                            <div class="colour product_added"></div> <strong>Added products by admin</strong><br/>
                        </div>
                        @if($order->status < 3 )
                        <div class="float-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#searchOrderProductModal">
                                <i class="fa fa-plus"></i> Add Product
                            </button>
                        </div>
                        @endif
                        <!-- Modal -->
                        <div class="modal fade" id="searchOrderProductModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                <h5 class="modal-title">Add products to the order</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                </div>
                                
                                <div class="modal-body">
                                    <div class="col-lg-12">
                                        <div class="alert alert-success" role="alert" id="order_product_added_success_alert" style="display: none;">
                                            Product successfully added to the order
                                        </div>
                                        <form class="form-inline" id="searchOrderProduct">
                                            <div class="form-group mx-sm-3 mb-2">
                                              <input type="text" class="form-control" id="search" placeholder="Search Products..">
                                            </div>
                                        </form>
                                        <br>
                                        <div class="form-group" id="list_order_product">
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
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
                                                    {{-- {{$store->name}} {{$store->company_name}} --}}
                                                @endif
                                            </td>
                                            <td>{{ isset($order_product_status[$row->collected_status]) ? $order_product_status[$row->collected_status] : '' }} - ({{ $row->collected_status}})</td>
                                            <form name="form_<?= $row->id ?>" id="form_<?= $row->id ?>">
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
                                                {{-- Mark Out Of Stock --}}
                                                @if ($row->is_out_of_stock==1 && $order->status < 3) 
                                                    <button title="Mark Out Of Stock" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn mark_out_of_stock_save mark_out_of_stock_save_{{ $row->id }} btn-sm btn-warning waves-effect" style="display: none;"><i class="icon-check"></i> Mark Out Of Stock</button>
                                                    <button title="Marked Out Of Stock" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn marked_out_of_stock marked_out_of_stock_{{ $row->id }} btn-sm btn-danger waves-effect" style="display: block;"><i class="icon-check"></i> Marked Out Of Stock</button>
                                                    <button title="Revert Marked Out Of Stock" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn revert_marked_out_of_stock revert_marked_out_of_stock_save_{{ $row->id }} btn-sm btn-secondary waves-effect"><i class="icon-check"></i> Revert Out Of Stock</button>
                                                @elseif (isset($order_product_status[$row->collected_status]) && $order_product_status[$row->collected_status] == 'Collected' && $order->status < 3 )
                                                <button title="Revert Marked Out Of Stock" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn revert_mark_collected revert_mark_collected_save_{{ $row->id }} btn-sm btn-secondary waves-effect"><i class="icon-check"></i> Revert Collection</button>
                                                @else
                                                @if($row->fk_storekeeper_id != null && $order->status < 3)
                                                    <button title="Mark Out Of Stock" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn mark_out_of_stock_save mark_out_of_stock_save_{{ $row->id }} btn-sm btn-warning waves-effect"><i class="icon-check"></i> Mark Out Of Stock</button>
                                                    <button title="Marked Out Of Stock" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn marked_out_of_stock marked_out_of_stock_{{ $row->id }} btn-sm btn-danger waves-effect"><i class="icon-check"></i> Marked Out Of Stock</button>
                                                    <button title="Mark Out Of Stock" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn mark_collected mark_collected_save_{{ $row->id }} btn-sm btn-warning waves-effect"><i class="icon-check"></i> Mark Collected</button>
                                                @endif
                                                @endif
                                                <button title="Saving" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn mark_out_of_stock_saving mark_out_of_stock_saving_{{ $row->id }} btn-sm btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                            </td>
                                            </form>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>

                        <br clear="all"/><br/>
                        <div class="card-header">
                            <h5>Suggested Products To Customer For Out Of Stock Products
                                @if ($order->getOrderProducts->where('is_out_of_stock', 1)->count())
                                    @if ($order->getTechnicalSupport) 
                                        <button class="btn btn-primary" disabled style="cursor: not-allowed">Ticket Created</button>
                                    @else
                                    @if ($order->getTechnicalSupportProduct->where('in_stock', 1)->count())
                                        @if ($order->getCheckedProducts->count()==$order->getOrderProducts->count())
                                            <button class="btn btn-primary" id="ticketBtn"
                                                onclick="createTechnicalSupportTicket(<?= $order->id ?>)"
                                                style="cursor: pointer">Create Ticket
                                            </button>
                                            @else
                                                <button class="btn btn-primary" disabled
                                                    style="cursor: not-allowed">Create Ticket
                                                </button> <br/><br/><small>(Storekeeper not collected all the products yet to create ticket)</small>
                                            @endif
                                        @endif
                                     @endif
                                    {{-- <a href="<?//= url('admin/orders/replacement_options') . '/' . $order->id ?>"
                                        target="_blank"><button class="btn btn-primary">Replacement Options</button>
                                    </a> --}}
                                @endif
                            </h5>
                        </div>
                        <br clear="all"/><br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($replaced_products)
                                    @foreach ($replaced_products as $key => $row)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->product_name_en }} ({{ $row->unit }})</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        
                        <br clear="all"/><br/>
                        <div class="card-header">
                            <h5>Customer Support Ticket</h5>
                        </div>
                        @if ($technical_support)
                        <br clear="all"/><br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%; max-width: 600px;">
                            <tbody>
                                @foreach ($technical_support_chat as $key => $chat)
                                    <tr>
                                        <td><div class="chat {{$chat->message_type=='received' ? 'chat_system' : 'chat_user'}}">{{$chat->message}}</div></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
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
    // Mark out of stock
    $('.marked_out_of_stock').on('click',function(e){
        e.preventDefault();
    });
    $('.mark_out_of_stock_save').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure to mark this item as out of stock and refund to customer wallet?');
        if (confirmed) {
            let id = $(this).attr('id');
            $('.mark_out_of_stock_save_'+id).hide();
            $('.mark_out_of_stock_saving_'+id).show();
            if (id) {
                let form = $("#form_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/fleet/mark_out_of_stock') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            $('.marked_out_of_stock_'+id).show();
                            $('.mark_out_of_stock_save_'+id).hide();
                            $('.mark_out_of_stock_saving_'+id).hide();
                            if (data.data) {
                                let order_product = data.data;
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
        }
    });

    //Mark in stock
    $('.revert_marked_out_of_stock').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure to revert this item as marked out of stock?');
        if (confirmed) {
            let id = $(this).attr('id');
            $('.revert_marked_out_of_stock_save_'+id).hide();
            $('.revert_marked_out_of_stock_saving_'+id).show();
            if (id) {
                let form = $("#form_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/fleet/revert_marked_out_of_stock') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            $('.revert_marked_out_of_stock_'+id).show();
                            $('.revert_marked_out_of_stock_save_'+id).hide();
                            $('.revert_marked_out_of_stock_saving_'+id).hide();
                            if (data.data) {
                                let order_product = data.data;
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
        }
    });

    //Mark item collected
    $('.mark_collected').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure to mark this item as collected ?');
        if (confirmed) {
            let id = $(this).attr('id');
            $('.mark_collected_'+id).hide();
            $('.mark_collecting_'+id).show();
            if (id) {
                let form = $("#form_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
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

     //Revert Mark collected
     $('.revert_mark_collected').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure you want to revert this item marked collected ?');
        if (confirmed) {
            let id = $(this).attr('id');
            $('.revert_mark_collected_'+id).hide();
            $('.revert_mark_collecting_'+id).show();
            if (id) {
                let form = $("#form_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/fleet/revert_mark_collected') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            $('.revert_mark_collected_'+id).show();
                            $('.revert_mark_collected_'+id).hide();
                            $('.revert_mark_collecting_'+id).hide();
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

    // Invoice order
    $('.order_invoiced').on('click',function(e){
        e.preventDefault();
    });
    $('.order_invoice_save').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure to mark this order as invoiced?');
        if (confirmed) {
            let id = $(this).attr('id');
            $('.order_invoice_save').hide();
            $('.order_invoice_saving').show();
            if (id) {
                let form = $("#form_order_invoice");
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/fleet/invoice_order') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            $('.order_invoiced').show();
                            $('.order_invoice_save').hide();
                            $('.order_invoice_saving').hide();
                            if (data.data) {
                                let order_product = data.data;
                                location.reload();
                            }
                        } else {
                            alert(data.message);
                            location.reload();
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
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
    function createTechnicalSupportTicket(order_id) {
        $.ajax({
            url: '<?= url('admin/orders/create_customer_support_ticket') ?>',
            type: 'POST',
            data: {
                order_id: order_id
            },
            dataType: 'JSON',
            cache: false
        }).done(function(response) {
            if (response.error_code == 200) {
                alert(response.message);
                $("#ticketBtn").attr('disabled', 'disabled');
                $("#ticketBtn").css('cursor', 'not-allowed');
                $("#ticketBtn").text('Ticket Created');
            } else {
                alert(response.message);
            }
        })
    }
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
<script>
    $(document).ready(function() { 
        $("#searchOrderProduct").keyup(function(e){
            e.preventDefault();
            $('#list_order_product').html("");
            var search = $('#search').val();
            var order = $('#fk_order_id').val();
            $.ajax({
                url: '<?= url('admin/fleet/search_new_product') ?>',
                type: 'GET',
                data: {
                    search: search
                },
                dataType: 'JSON',
                cache: false,
                success: function(response) {
                    html = "";
                    html +='<div class="row">';
                    $.each(response,function(key,value){
                        
                        html +='<div class="col-md-4 product_select_con">';
                        html +='<div class="product_select">';
                        html +='<form method="POST" id="form_order_product_save_'+value.id+'">';
                        html +='<input type="hidden" value="'+order+'" name="fk_order_id">';
                        html +='<input type="hidden" value="'+value.id+'" name="fk_product_id">';
                        html +='<input type="hidden" value="'+value.fk_product_store_id+'" name="fk_product_store_id">';
                        html +='<img src="'+value.product_image_url+'"/>';
                        html +='<h6>'+value.product_name_en+'</h6>';
                        html +='<p>'+value.product_name_ar+'</p>';
                        html +='<p>Regular price: '+value.base_price+'</p>';
                        html +='<p>Selling price: '+value.product_store_price+'</p>';
                        html +='<p>Stock:'+value.product_store_stock+'</p>';
                        html +='<input type="number" name="product_qauntity" value="1" min="1" max="'+value.product_store_stock+'">';
                        html +='<p>';
                        html +='<button type="submit" id="'+value.id+'" class="btn btn-dark mb-2 order_product_save">Add</button>';
                        html +='</p>';
                        html +='</form>';
                        html +='</div>';
                        html +='</div>';
                    })
                    html +='</div>'
                    $('#list_order_product').append(html);
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log(xhr.status);
                    console.log(thrownError);
                }
            });
        })      
    });
</script>

<script>
    $('body').on('click','.order_product_save',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            if (id) {
                let form = $("#form_order_product_save_"+id);
                let token = "{{ csrf_token() }}";
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/fleet/add_order_product') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response.error_code == 200){
                            
                            $('#order_product_added_success_alert').css("display", "block");
                            setTimeout(function() { 
                                $('#order_product_added_success_alert').css("display", "none");
                            }, 2000);
                            
                        }else{
                            alert(response.message)
                        }
                        
                    }
                });
            } else {
                alert("Something went wrong");
            }
        });
</script>

<script>
$('#searchOrderProductModal').on('hidden.bs.modal', function () {
  location.reload();
})
</script>

@endsection
