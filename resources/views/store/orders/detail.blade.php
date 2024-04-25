@extends('store.layouts.dashboard_layout')
@section('content')
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
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/all-orders') ?>">Orders</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Order Number</th>
                                    <td>{{ $order->orderId }}</td>
                                </tr>
                                <tr>
                                    <th>Sub Total</th>
                                    <td>{{ $sub_order ? $order->sub_total + $sub_order->sub_total : $order->sub_total }}
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
                                    <th>Total Amount</th>
                                    <td>{{ $sub_order ? $order->total_amount + $sub_order->total_amount : $order->total_amount }}
                                        QAR
                                    </td>
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
                                    <td><?= $order->status == 7 ? ($order->getOrderStatus ? date('h:i A', strtotime($order->updated_at)) : 'N/A') : 'within ' . getDeliveryIn($order->getOrderDeliverySlot->expected_eta) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>{{ $order_status[$order->status] }}</td>
                                </tr>
                                @if ($order->status >= 3)
                                    <tr>
                                        <th>Customer Invoice</th>
                                        <td><a href="<?= url('/admin/customer-invoice') . '/' . base64url_encode($order->id) ?>"
                                                target="_blank">Click here</a>
                                        </td>
                                    </tr>
                                @endif
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>User detail</h6>
                    </div>
                    <div class="card-body">
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
                                            width="75" height="50"></td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="card-header">
                        <h6>Update Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="success_alert"></div>
                        <form method="post" id="updateStatus"
                            action="{{ route('store.orders.update_status', [$storeId, base64url_encode($order->id)]) }}">
                            @csrf
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
                    <div class="card-body">
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
                                    echo 'N/A';
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
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product Name</th>
                                    <th>Product Image</th>
                                    <th>Weight/Unit</th>
                                    <th>No. of items</th>
                                    <th>Product Price</th>
                                    {{-- <th>Out of stock?</th> --}}
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($order->getOrderProducts->where('is_out_of_stock', 0))
                                    @foreach ($order->getOrderProducts->where('is_out_of_stock', 0) as $key => $value)
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
                                            {{-- <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input" type="checkbox"
                                                            <?= $value->is_out_of_stock == 1 ? 'checked' : '' ?>
                                                            id="<?= $value->id ?>" onchange="makeOutOfStock(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td> --}}
                                            <td><a
                                                    href="<?= url('admin/orders/product-detail/' . base64url_encode($value->id)) ?>">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    

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
                    @if (!empty($order->getOrderDriver) ? $order->getOrderDriver->fk_driver_id == 0 : empty($order->getOrderDriver))
                        <div class="card-header">
                            <h6>Assign driver</h6>
                        </div>
                        <div class="card-body">
                            <form method="post" id="updateDriver"
                                action="{{ route('store.orders.update_driver', [$storeId, base64url_encode($order->id)]) }}">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <label>Select driver</label>
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
                    <div class="card-header">
                        <h6>Order driver detail</h6>
                    </div>
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
            </div>
        </div>
    </div>
    <div class="modal fade cancelConfirmModal" data-backdrop="static" tabindex="-1" role="dialog"
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
    </div>
    <div class="modal fade refundModal" data-backdrop="static" tabindex="-1" role="dialog"
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
    </div>
    <script>
        function makeOutOfStock(obj) {
            var id = $(obj).attr("id");

            var checked = $(obj).is(':checked');
            if (checked === true) {
                var is_switched_on = 1;
                var message = 'Product marked as out of stock';
            } else {
                var is_switched_on = 0;
                var message = 'Product marked as out of stock';
            }

            $.ajax({
                url: '<?= url('store/mark_out_of_stock') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    id: id,
                    is_switched_on: is_switched_on
                },
                cache: false,
            }).done(function(response) {
                if (response.status_code === 200) {
                    var success_str =
                        '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + message + ' successfully</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(success_str);
                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>Some error found</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        }

        // function confirmInStock(obj) {
        //     var id = $(obj).attr("id");

        //     var checked = $(obj).is(':checked');
        //     if (checked === true) {
        //         var is_switched_on = 1;
        //         var message = 'Item confirmed';
        //     } else {
        //         var is_switched_on = 0;
        //         var message = 'Item unconfirmed';
        //     }

        //     $.ajax({
        //         url: '<?= url('store/confirm_in_stock') ?>',
        //         type: 'post',
        //         dataType: 'json',
        //         data: {
        //             id: id,
        //             is_switched_on: is_switched_on
        //         },
        //         cache: false,
        //     }).done(function(response) {
        //         if (response.status_code === 200) {
        //             var success_str =
        //                 '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
        //                 '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
        //                 '<strong>' + message + ' successfully</strong>.' +
        //                 '</div>';
        //             $(".ajax_alert").html(success_str);
        //         } else {
        //             var error_str =
        //                 '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
        //                 '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
        //                 '<strong>Some error found</strong>.' +
        //                 '</div>';
        //             $(".ajax_alert").html(error_str);
        //         }
        //     });
        // }

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

        $("select[name=status]").on('click', function() {
            if ($(this).val() === '4') {
                $(".cancelConfirmModal").modal('show');
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

        $("#cancelConfirmForm").on('submit', function(e) {
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
