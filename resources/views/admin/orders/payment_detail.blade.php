@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/payments') ?>">Payments</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-header">
                        <h6>Payment Detail</h6>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <td>{{ $payment->getOrder ? '#' . $payment->getOrder->orderId : '' }}</td>
                                </tr>
                                <tr>
                                    <th>Total Amount</th>
                                    <td>{{ $payment->getSubPayment ? $payment->amount + $payment->getSubPayment->amount : $payment->amount }}
                                        QAR</td>
                                </tr>
                                <tr>
                                    <th>Card Type</th>
                                    <td>{{ $payment->cardType ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Card Number</th>
                                    <td>{{ $payment->maskedCardNumber ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Transaction ID</th>
                                    <td>{{ $payment->transactionId ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ $payment->status ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Transaction Status</th>
                                    <td>{{ $payment->transactionStatus ?? 'N/A' }}</td>
                                </tr>
                                @if ($payment->status != 'success')
                                    <tr>
                                        <th>Reason</th>
                                        <td>{{ $payment->reason ?? 'N/A' }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <th>Date & Time</th>
                                    <td created_at="<?= strtotime($payment->created_at) ?>" class="changeUtcDateTime">
                                        {{ date('d-m-Y H:i', strtotime($payment->created_at)) }}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Order Detail</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <td>{{ $payment->orderId }}</td>
                                </tr>
                                <tr>
                                    <th>Sub Total</th>
                                    <td>{{ $payment->getOrder->getSubOrder ? $payment->getOrder->sub_total + $payment->getOrder->getSubOrder->sub_total : $payment->getOrder->sub_total }}
                                        QAR</td>
                                </tr>
                                <tr>
                                    <th>Delivery Charge</th>
                                    <td>{{ $payment->getOrder->delivery_charge ? 'QAR ' . $payment->getOrder->delivery_charge : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Coupon Discount (if any)</th>
                                    <td>{{ $payment->getOrder->coupon_discount ? 'QAR ' . $payment->getOrder->coupon_discount : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total Amount</th>
                                    <td>{{ $payment->getOrder->getSubOrder ? $payment->getOrder->total_amount + $payment->getOrder->getSubOrder->total_amount : $payment->getOrder->total_amount }}
                                        QAR</td>
                                </tr>
                                <tr>
                                    <th>Date & Time</th>
                                    <td created_at="<?= strtotime($payment->getOrder->created_at) ?>"
                                        class="changeUtcDateTime">
                                        {{ date('d-m-Y H:i', strtotime($payment->getOrder->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <th>Order Status</th>
                                    <td>{{ $order_status[$payment->getOrder->status] }}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Order Delivery location</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Address type</th>
                                    <td>
                                        <?php
                                        if ($payment->getOrder->getOrderAddress->address_type == 1) {
                                            echo 'Office';
                                        } elseif ($payment->getOrder->getOrderAddress->address_type == 2) {
                                            echo 'Home';
                                        } elseif ($payment->getOrder->getOrderAddress->address_type == 3) {
                                            echo 'Others';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Name</th>
                                    <td>{{ $payment->getOrder->getOrderAddress->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Address Line 1</th>
                                    <td>{{ $payment->getOrder->getOrderAddress->address_line1 ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Address Line 2</th>
                                    <td>{{ $payment->getOrder->getOrderAddress->address_line2 ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Expected Delivery Date</th>
                                    <td>{{ $payment->getOrder->getOrderDeliverySlot ? date('d-m-Y', strtotime($payment->getOrder->getOrderDeliverySlot->delivery_date)) : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Expected Delivery Time</th>
                                    <td><?php
                                    // if ($payment->getOrder->getOrderDeliverySlot) {
                                    //     $slotarr = explode('-', $payment->getOrder->getOrderDeliverySlot->delivery_slot);
                                    //     $from = date('h:i A', strtotime($slotarr[0]));
                                    //     $to = date('h:i A', strtotime($slotarr[1]));
                                    //     echo "$from" . " to " . "$to";
                                    // } else {
                                    //     echo "N/A";
                                    // }
                                    ?>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Order Items</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product Name</th>
                                    <th>Product Image</th>
                                    <th>Quantity</th>
                                    <th>No. of items</th>
                                    <th>Product Price</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($payment->getOrder->getOrderProducts)
                                    @foreach ($payment->getOrder->getOrderProducts as $key => $value)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $value->getProduct->product_name_en }}</td>
                                            <td><img src="{{ !empty($value->getProduct->getProductImage) ? asset('images/product_images') . '/' . $value->getProduct->getProductImage->file_name : asset('assets/images/dummy-product-image.jpg') }}"
                                                    width="75" height="50"></td>
                                            <td>{{ $value->getProduct->quantity . ' ' . $value->getProduct->unit }}</td>
                                            <td>{{ $value->product_quantity }}</td>
                                            <td>{{ $value->total_product_price }}</td>
                                            <td><a href="<?= url('admin/products/show/' . base64url_encode($value->fk_product_id)) ?>"
                                                    target="_blank"><i class="fa fa-eye"></i></a></td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Order driver detail</h6>
                    </div>
                    @if (!empty($payment->getOrder->getOrderDriver) && $payment->getOrder->getOrderDriver->fk_driver_id != 0)
                        <div class="card-body">
                            <table class="table table-bordered dt-responsive nowrap">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <td>{{ $payment->getOrder->getOrderDriver->getDriver->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $payment->getOrder->getOrderDriver->getDriver->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile</th>
                                        <td>{{ $payment->getOrder->getOrderDriver->getDriver->mobile ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Photo</th>
                                        <td><img src="{{ !empty($payment->getOrder->getOrderDriver->getDriver->getDriverImage) ? asset('images/common_images') . '/' . $payment->getOrder->getOrderDriver->getDriver->getDriverImage->file_name : asset('assets/images/dummy_user.png') }}"
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
            </div>
        </div>
    </div>
@endsection
