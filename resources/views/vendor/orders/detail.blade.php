@extends('vendor.layouts.dashboard_layout')
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
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/active_orders') ?>">Orders</a></li>
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
                                    <th>Date & Time</th>
                                    <td>{{ date('d-m-Y H:i', strtotime($order->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Price</th>
                                    <td>{{ $order->total_distributor_price . ' QAR' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td style="color:<?= $order->status == 2 ? '#f35254' : '#57c68e' ?>">
                                        {{ $order_status[$order->status] }}</td>
                                </tr>
                                <tr>
                                    <th>Change status</th>
                                    <td>
                                        <form method="post"
                                            action="{{ route('vendor.orders.update_status', [base64url_encode($order->id)]) }}">
                                            @csrf
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <select class="form-control" name="status">
                                                        <option selected disabled>Select</option>
                                                        <option value="3"<?= $order->status == 3 ? 'selected' : '' ?>>
                                                            Order Invoiced</option>
                                                    </select>
                                                </div>
                                                <div class="col-lg-4">
                                                    <button class="btn btn-success">Update</button>
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Link</th>
                                    <td>
                                        @if ($order->getVendorOrder)
                                            <a target="_blank"
                                                href="{{ url('/') . $order->getVendorOrder->order_link }}">{{ url('/') . $order->getVendorOrder->order_link }}</a>
                                        @else
                                            <button class="btn btn-small btn-primary">Generate Link</button>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Order bill</th>
                                    <td>
                                        {{-- <a
                                            href="{{ route('vendor.orders.pdfview', ['download' => 'pdf', 'id' => base64url_encode($order->id)]) }}">Download
                                            PDF</a></td> --}}
                                </tr>
                                @if ($order->status >= 3)
                                    <tr>
                                        <th>Customer Invoice</th>
                                        <td><a href="<?= url('/vendor/customer-invoice') . '/' . base64url_encode($order->id) ?>"
                                                target="_blank">Click here</a></td>
                                    </tr>
                                @endif
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
                                    <th>Product Price (QAR)</th>
                                    <th>Unit weight</th>
                                    <th>Out of stock?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($order->count())
                                    @foreach ($order as $key => $value)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $value->product_quantity . ' x ' . $value->product_name_en }}</td>
                                            <td>{{ $value->getProduct ? $value->getProduct->distributor_price : '' }}</td>
                                            <td>{{ $value->product_quantity . ' ' . $value->getProduct->unit }}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input" type="checkbox"
                                                            <?= $value->is_out_of_stock == 1 ? 'checked' : '' ?>
                                                            id="<?= $value->id ?>" onchange="makeOutOfStock(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="card-header">
                        <h6>Suggested Items by Admin</h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product Name</th>
                                    <th>Product Price (QAR)</th>
                                    <th>Unit weight</th>
                                    <th>Confirm In stock?</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($rep_options->count())
                                    @foreach ($rep_options as $key => $value)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $value->getProduct->product_name_en }}</td>
                                            <td>{{ $value->getProduct->distributor_price }}</td>
                                            <td>{{ $value->getProduct->unit }}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input" type="checkbox"
                                                            <?= $value->in_stock == 1 ? 'checked' : '' ?>
                                                            id="<?= $value->id ?>" onchange="confirmInStock(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
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
                url: '<?= url('vendor/mark_out_of_stock') ?>',
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

        function confirmInStock(obj) {
            var id = $(obj).attr("id");

            var checked = $(obj).is(':checked');
            if (checked === true) {
                var is_switched_on = 1;
                var message = 'Item confirmed';
            } else {
                var is_switched_on = 0;
                var message = 'Item unconfirmed';
            }

            $.ajax({
                url: '<?= url('vendor/confirm_in_stock') ?>',
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
    </script>
@endsection
