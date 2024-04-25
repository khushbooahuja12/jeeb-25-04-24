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
</style>
    <div class="container-fluid">
        @include('partials.errors')
        @include('partials.success')
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Order Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/active-orders') ?>">Orders</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        @php
                            $amount_from_cod = $order->payment_type=='cod' ? $order->amount_from_card : 0;
                            $amount_from_cod_suborders= $order->getSubOrder ? $order->getOrderPaidByCOD($order->id) : 0;
                            $amount_from_card = $order->payment_type=='online' ? $order->amount_from_card : 0;
                            $amount_from_card_suborders= $order->getSubOrder ? $order->getOrderPaidByCart($order->id) : 0;
                            $amount_from_wallet = $order->amount_from_wallet;
                            $amount_from_wallet_suborders = $order->getSubOrder ? $order->getOrderPaidByWallet($order->id) : 0;
                        @endphp
                        <table class="table table-bordered dt-responsive nowrap" style="width: 100%;">
                            <thead>
                                <tr>
                                    {{-- <th>Store</th> --}}
                                    {{-- <td>{{ $order->getStore->name ? $order->getStore->company_name.' - '.$order->getStore->name : '' }}</td> --}}
                                </tr>
                                <tr>
                                    <th>Order Number</th>
                                    <td>{{ $order->orderId }}</td>
                                </tr>
                                <tr>
                                    <th>Payment Type</th>
                                    <td>{{ $order->payment_type }}</td>
                                </tr>
                                <tr>
                                    <th>Sub Total</th>
                                    <td>{{ $order->getSubOrder ? $order->sub_total + $order->getOrderSubTotal($order->id) : $order->sub_total }}
                                        QAR
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivery Charge</th>
                                    <td>{{ $order->delivery_charge }} QAR</td>
                                </tr>
                                <tr>
                                    <th>Coupon Discount (if any)</th>
                                    <td>{{ $order->coupon_discount }} QAR</td>
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
                                    <th>Order placed on</th>
                                    <td>{{ date('d-m-Y h:i A', strtotime($order->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <th>Delivery Date</th>
                                    <td><?= date('d-m-Y', strtotime($order->getOrderDeliverySlot->delivery_date)) ?></td>
                                </tr>
                                <tr>
                                    <th>Delivery Time</th>
                                    <td>
                                        @php
                                        $deliverySlot = $order->getOrderDeliverySlot;
                                        if ($deliverySlot) {
                                            if ($deliverySlot->delivery_time==1) {
                                                echo 'within ' . getDeliveryIn($deliverySlot->expected_eta);
                                            } else {
                                                echo $deliverySlot->later_time;
                                            }
                                        } 
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <th>Delivered Time</th>
                                    <td>
                                        @php
                                        if ($order->status == 7) {
                                            echo date('h:i A', strtotime($order->updated_at));
                                        } else {
                                            echo 'not yet';
                                        }
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ $order_status[$order->status] }}</td>
                                </tr>
                                <tr>
                                    <th>Storekeeper sharing link</th>
                                    <td><a href="{{ route('storekeeper-sharing', base64url_encode($order->id)) }}" target="_blank">
                                        {{ route('storekeeper-sharing', base64url_encode($order->id)) }}</a>
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
                                    <th>Payment response</th>
                                    <td style="width: auto;word-break: break-all;">
                                        {{ $order->getOrderPayment ? $order->getOrderPayment->status : 'not detected' }}<br/>
                                        {{ $order->getOrderPayment ? $order->getOrderPayment->paymentResponse : 'not detected' }}<br/>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>User detail</h6>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                @php 
                                if ($order->getUser) :
                                @endphp
                                <tr>
                                    <th>ID</th>
                                    <td>
                                        @if ($order->getUser->id)
                                            <a href="/admin/users/show/{{ $order->getUser->id }}">{{ $order->getUser->id }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td><a
                                            href="<?= url('admin/users/show/'.$order->fk_user_id) ?>">{{ $order->getUser->name ?? 'N/A' }}</a>
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
                                            width="75" height="50"></td>
                                </tr>
                                @php 
                                else :
                                @endphp
                                <tr>
                                    <td colspan="2">User is not found!</td>
                                </tr>
                                @php 
                                endif;
                                @endphp
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Update Status</h6>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        <div class="success_alert"></div>
                        <form method="post" id="updateStatus"
                            action="{{ route('admin.orders.update_status', [base64url_encode($order->id)]) }}">
                            @csrf
                            <input type="hidden" value="<?= $order->id ?>" name="order_id">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Select status</label>
                                        <select class="form-control" name="status">
                                            <option value="" disabled selected>Select</option>
                                            @if ($order_status)
                                                @foreach ($order_status as $key => $value)
                                                    @if (!($key == 2 || $key == 5))
                                                        <option value="{{ $key }}"
                                                            <?= $key == $order->status ? 'selected' : '' ?>
                                                            <?= $key == $order->status ? 'disabled' : '' ?>>
                                                            {{ $value }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div id="refundable_amount">

                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div>
                                            <button type="submit" class="btn btn-success waves-effect waves-light">
                                                Update
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-header">
                        <h6>Order Delivery location</h6>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Address type</th>
                                    <td>
                                        <?php
                                        if ($order->getOrderAddress->address_type == 1) {
                                            echo 'Office';
                                        } elseif ($order->getOrderAddress->address_type == 2) {
                                            echo 'Home';
                                        } elseif ($order->getOrderAddress->address_type == 3) {
                                            echo 'Others';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $order->getOrderAddress->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile</th>
                                    <td>{{ $order->getOrderAddress->mobile ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Address Line 1</th>
                                    <td>{{ $order->getOrderAddress->address_line1 ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Address Line 2</th>
                                    <td>{{ $order->getOrderAddress->address_line2 ?? 'N/A' }}</td>
                                </tr>
                                {{-- <tr>
                                    <th>Expected Delivery Date</th>
                                    <td>{{ $order->getOrderDeliverySlot ? date('d-m-Y', strtotime($order->getOrderDeliverySlot->delivery_date)) : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Expected Delivery Time</th>
                                    <td><?php
                                    // if ($order->getOrderDeliverySlot) {
                                    //     $slotarr = explode('-', $order->getOrderDeliverySlot->delivery_slot);
                                    //     $from = date('h:i A', strtotime($slotarr[0]));
                                    //     $to = date('h:i A', strtotime($slotarr[1]));
                                    //     echo "$from" . ' to ' . "$to";
                                    // } else {
                                    //     echo 'N/A';
                                    // }
                                    ?>
                                    </td>
                                </tr> --}}
                                <tr>
                                    <th>Delivery Location</th>
                                    <th><a target="_blank"
                                            href="https://www.google.com/maps/search/?api=1&query=<?= $order->getOrderAddress->latitude ?>,<?= $order->getOrderAddress->longitude ?>">Go
                                            to map</a></th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Order Items</h6>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product Name</th>
                                    <th>Product Image</th>
                                    <th>Weight/Unit</th>
                                    <th>No. of items</th>
                                    <th>Product Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($order->getOrderProducts->where('is_out_of_stock', 0))
                                    @foreach ($order->getOrderProducts->where('is_out_of_stock', 0) as $key => $value)
                                        @php 
                                        if ($value->is_missed_product==1) {
                                            $product_class = 'product_missed';
                                        } else {
                                            $product_class = '';
                                        }
                                        @endphp
                                        <tr class="{{$product_class}}">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $value->product_name_en ?? 'N/A' }}
                                                <?= $value->product_weight != 0 ? '(' . (int) $value->product_weight . ' ' . $value->product_unit . ')' : '' ?>
                                                @if ($value->sub_products)
                                                    <br/>
                                                    @php
                                                        $sub_products = json_decode($value->sub_products);
                                                    @endphp
                                                    @foreach ($sub_products as $item)
                                                    &nbsp;&nbsp;- {{ $item->product_name_en ?? "" }} ({{ $item->unit ?? "" }}) - {{ $item->price ?? "" }}  * {{ $item->product_quantity ?? "" }}<br/>
                                                    @endforeach
                                                @endif
                                            </td>
                                            <td><img src="{{ !empty($value->product_image_url) ? $value->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                    width="75" height="50"></td>
                                            <td>{{ $value->product_weight && $value->product_unit ? $value->product_weight . ' ' . $value->product_unit : $value->unit }}
                                            </td>
                                            <td>{{ $value->product_quantity }}</td>
                                            <td>{{ $value->total_product_price }}</td>
                                            <td><a
                                                    href="<?= url('admin/orders/product-detail/' . base64url_encode($value->id)) ?>"><i
                                                        class="fa fa-eye"></i></a></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Out of stock items
                            @if ($order->getOrderProducts->where('is_out_of_stock', 1)->count())
                                @if ($order->getTechnicalSupportProduct->where('in_stock', 1)->count())
                                    @if ($order->getCheckedProducts->count()==$order->getOrderProducts->count())
                                    <button class="btn btn-primary" id="ticketBtn"
                                        onclick="createTechnicalSupportTicket(<?= $order->id ?>)"
                                        {{ $order->getTechnicalSupport ? 'disabled' : '' }}
                                        style="cursor:{{ $order->getTechnicalSupport ? 'not-allowed' : 'pointer' }}">{{ $order->getTechnicalSupport ? 'Ticket created' : 'Create Ticket' }}
                                    </button>
                                    @else
                                        <button class="btn btn-primary" disabled
                                            style="cursor: not-allowed">Create Ticket
                                        </button> <br/><br/><small>(Storekeeper not collected all the products yet to create ticket)</small>
                                    @endif
                                @endif
                                {{-- <a href="<?//= url('admin/orders/replacement_options') . '/' . $order->id ?>"
                                    target="_blank"><button class="btn btn-primary">Replacement Options</button>
                                </a> --}}
                            @endif
                        </h6>
                    </div>
                    @if ($order->getOrderProducts->where('is_out_of_stock', 1)->count())
                        <div class="card-body" style="overflow-x: scroll;">
                            <table class="table table-bordered dt-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Product Name</th>
                                        <th>Product Image</th>
                                        <th>Weight/Unit</th>
                                        <th>No. of items</th>
                                        <th>Product Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($order->getOrderProducts->where('is_out_of_stock', 1))
                                        @foreach ($order->getOrderProducts->where('is_out_of_stock', 1) as $key => $value)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $value->product_name_en ?? 'N/A' }}
                                                    <?= $value->product_weight != 0 ? '(' . (int) $value->product_weight . ' ' . $value->product_unit . ')' : '' ?>
                                                </td>
                                                <td><img src="{{ !empty($value->product_image_url) ? $value->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                        width="75" height="50"></td>
                                                <td>{{ $value->product_weight && $value->product_unit ? $value->product_weight . ' ' . $value->product_unit : $value->unit }}
                                                </td>
                                                <td>{{ $value->product_quantity }}</td>
                                                <td>{{ $value->total_product_price }}</td>
                                                <td><a
                                                        href="<?= url('admin/orders/product-detail/' . base64url_encode($value->id)) ?>"><i
                                                            class="fa fa-eye"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><b>Total</b></td>
                                            <td>{{ $totalOutOfStockProduct }}</td>
                                            <td 
                                            {{-- onclick="refundOutOfStockProductAmount(this)" --}}
                                            >
                                                {{-- <button class="btn btn-danger">Refund</button> --}}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="card-body">
                            No product is out of stock
                        </div>
                    @endif
                    <div class="card-header">
                        <h6>Replaced Items</h6>
                    </div>
                    @if ($order->getOrderProducts->where('is_out_of_stock', 1)->count())
                        <div class="card-body" style="overflow-x: scroll;">
                            <table class="table table-bordered dt-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Product Name</th>
                                        <th>Product Image</th>
                                        <th>Weight/Unit</th>
                                        <th>Product Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($replaced_products)
                                        @foreach ($replaced_products as $key => $value)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $value->product_name_en ?? 'N/A' }}
                                                    <?= $value->product_weight != 0 ? '(' . (int) $value->product_weight . ' ' . $value->product_unit . ')' : '' ?>
                                                </td>
                                                <td><img src="{{ !empty($value->product_image_url) ? $value->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                        width="75" height="50"></td>
                                                <td>{{ $value->product_weight && $value->product_unit ? $value->product_weight . ' ' . $value->product_unit : $value->unit }}
                                                </td>
                                                <td>{{ $value->product_price }}</td>
                                                <td><a
                                                        href="<?= url('admin/orders/product-detail/' . base64url_encode($value->fk_order_id)) ?>" target="_blank">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="card-body">
                            No product is out of stock
                        </div>
                    @endif
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
                    <div class="card-header">
                        <h6>Order driver detail</h6>
                    </div>
                    @if ($order->status<=3)
                        <div class="card-body">
                            <form method="post" id="updateDriver"
                                action="{{ route('admin.orders.update_driver', [base64url_encode($order->id)]) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Assign driver</label>
                                            <select class="form-control select2" name="fk_driver_id">
                                                <option disabled selected>Select</option>
                                                @if ($drivers)
                                                    @foreach ($drivers as $key => $value)
                                                        <option value="{{ $value->id }}">
                                                            {{ $value->name ? $value->name : '+' . $value->country_code . '-' . $value->mobile }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div>
                                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                    Update
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                    @if (!empty($order->getOrderDriver) && $order->getOrderDriver->fk_driver_id != 0)
                        <div class="card-body">
                            <table class="table table-bordered dt-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <td><a
                                                href="<?= url('admin/drivers/show/' . base64url_encode($order->getOrderDriver->getDriver->id)) ?>">{{ $order->getOrderDriver->getDriver->name ?? 'N/A' }}</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $order->getOrderDriver->getDriver->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile</th>
                                        <td>{{ $order->getOrderDriver->getDriver->mobile ? '+' . $order->getOrderDriver->getDriver->country_code . '-' . $order->getOrderDriver->getDriver->mobile : 'N/A' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Image</th>
                                        <td><img src="{{ !empty($order->getOrderDriver->getDriver->getDriverImage) ? asset('images/common_images') . '/' . $order->getOrderDriver->getDriver->getDriverImage->file_name : asset('assets/images/dummy_user.png') }}"
                                                width="75" height="50"></td>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    @else
                        <div class="card-body">
                            <p>Driver not assigned yet</p>
                        </div>
                    @endif
                </div>
                <div class="card-header">
                    <h6>Scratch Card</h6>
                </div>
                <div class="card-body" style="overflow-x: scroll;">
                    <table class="table table-bordered dt-responsive nowrap">
                        <thead>
                            @if ($scratch_cards)
                                @foreach ($scratch_cards as $item)
                                    @php
                                        $item_status = 'Unknown';
                                        if ($item->status==0) {
                                            $item_status = 'Inactive';
                                        } elseif ($item->status==1) {
                                            $item_status = 'Active-Unscratched';
                                        } elseif ($item->status==2) {
                                            $item_status = 'Scratched';
                                        } elseif ($item->status==3) {
                                            $item_status = 'Redeemed';
                                        } elseif ($item->status==4) {
                                            $item_status = 'Cancelled';
                                        }
                                    @endphp
                                    <tr>
                                        <th>ID</th>
                                        <td>
                                            {{ $item->id }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Created At</th>
                                        <td>
                                            {{ $item->created_at }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card Type</th>
                                        <td>
                                            {{ $item->scratch_card_type == 0 ? 'Onspot Reward' : 'Coupon' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Coupon Code</th>
                                        <td>
                                            {{ $item->coupon_code }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>
                                            {{ $item_status }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Scratched At</th>
                                        <td> {{ $item->scratched_at }}</td>
                                    </tr>
                                    <tr>
                                        <th>Redeemed Amount</th>
                                        <td> {{ $item->redeem_amount }}</td>
                                    </tr>
                                    <tr>
                                        <th>Redeemed At</th>
                                        <td> {{ $item->redeemed_at }}</td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card ID</th>
                                        <td> {{ $item->fk_scratch_card_id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Title</th>
                                        <td>{{ $item->title_en }}</td>
                                    </tr>
                                    <tr>
                                        <th>Discount</th>
                                        <td>{{ $item->discount . ($item->type == 1 ? ' %' : '') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Expiry Date</th>
                                        <td>{{ $item->expiry_date }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="2">No scratch card is added to this order!</td>
                                </tr>
                            @endif
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="modal fade cancelConfirmModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0">Choose your preference</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="error_alert"></div>
                    <form method="post" id="cancelConfirmForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Refund ?</label>&ensp;&ensp;
                                    <input type="radio" name="refund_type" value="partial"> Partial &ensp;
                                    <input type="radio" name="refund_type" value="full"> Full &ensp;
                                    <input type="radio" name="refund_type" value="no_refund" checked> No &ensp;
                                    <label id="refund_type-error" class="error" for="refund_type"></label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Wallet/Card</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <select class="form-control" name="refund_source" disabled>
                                        <option value="" disabled selected>Select</option>
                                        <option value="wallet">Wallet</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Amount (QAR)</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <input type="text" name="refund_amount" value="0"
                                        class="form-control floatOnly" disabled>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" value="<?= $order->id ?>" name="order_id">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-success waves-effect waves-light">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
    {{-- <div class="modal fade refundModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0">Choose your preference</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="error_alert"></div>
                    <form method="post" id="refundForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Wallet/Card</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <select class="form-control" name="refund_source">
                                        <option value="" disabled selected>Select</option>
                                        <option value="wallet">Wallet</option>
                                        <option value="card">Card</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Amount (QAR)</label>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <input type="text" name="refund_amount" value="{{ $totalOutOfStockProduct }}"
                                        class="form-control floatOnly">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" value="<?= $order->id ?>" name="order_id">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-success waves-effect waves-light">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
    <script>
        function refundOutOfStockProductAmount() {
            $(".refundModal").modal('show');
        }
        $('#refundForm').validate({
            rules: {
                refund_source: {
                    required: true
                },
                refund_amount: {
                    required: true
                }
            }
        });

        $("#refundForm").on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= url('admin/orders/refund_order_amount') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                if (response.status_code === 200) {
                    $(".refundModal").modal('hide');

                    var success_str =
                        '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".success_alert").html(success_str);
                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".error_alert").html(error_str);
                }
            });
        });

        // $("select[name=status]").on('change', function() {
        //     if ($(this).val() === '4') {
        //         $(".cancelConfirmModal").modal('show');
        //     }
        // });

        $("select[name=status]").on('change', function() {
            if ($(this).val() === '4') {
                $.ajax({
                    url: '<?= url('admin/orders/cancel_order_detail') ?>',
                    type: 'POST',
                    data: $('#updateStatus').serialize(),
                    dataType: 'JSON',
                    cache: false
                }).done(function(response) {
                    if (response.status_code === 200) {
                        $("#refundable_amount").append('<input type="hidden" name="refund_amount" value="'+response.data.refundable_amount+'"><table class="table table-bordered dt-responsive nowrap"><tr><th>Total Order Amount</th><th>Total Gift Card Amount</th><th>Total Cancelling Amount (If online, it will be refunded)</th></tr><tr style="background-color: yellow"><td>'+response.data.total_amount+'</td><td>'+response.data.giftcard_total_amount+'</td><td>'+response.data.refundable_amount+'</td></tr></table>');
                    }
                });
            } 
        });

        $("input[name=refund_type]").on('click', function() {
            if ($(this).val() === 'no_refund') {
                $("select[name=refund_source]").attr('disabled', 'true');
                $("input[name=refund_amount]").attr('disabled', 'true');
            } else if ($(this).val() === 'full') {
                $("select[name=refund_source]").removeAttr('disabled');
                $("input[name=refund_amount]").val(<?= $order->total_amount ?>);
                $("input[name=refund_amount]").attr('disabled', 'true');
            } else if ($(this).val() === 'partial') {
                $("select[name=refund_source]").removeAttr('disabled');
                $("input[name=refund_amount]").val('');
                $("input[name=refund_amount]").removeAttr('disabled');
            }
        });

        $('#cancelConfirmForm').validate({
            rules: {
                refund_type: {
                    required: true
                },
                refund_source: {
                    required: true
                },
                refund_amount: {
                    required: true
                }
            }
        });

        $("#updateStatus").on('submit', function(e) {

            if ($("select[name=status]").val()==4) {
                e.preventDefault();
                $.ajax({
                    url: '<?= url('admin/orders/cancel_order') ?>',
                    type: 'POST',
                    data: $(this).serialize(),
                    dataType: 'JSON',
                    cache: false
                }).done(function(response) {
                    if (response.status_code === 200) {
                        $(".cancelConfirmModal").modal('hide');

                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".success_alert").html(success_str);
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".error_alert").html(error_str);
                    }
                });
            } 

        });

        $('#updateStatus').validate({
            rules: {
                status: {
                    required: true
                }
            }
        });
        $('#updateDriver').validate({
            rules: {
                status: {
                    required: true
                }
            }
        });
    </script>
@endsection
