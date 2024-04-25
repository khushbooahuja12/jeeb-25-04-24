@extends('vendor.layouts.shared_link_layout')
@section('content')
    <div class="container-fluid">
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
                                    <td created_at="<?= strtotime($order->created_at) ?>" class="changeUtcDateTime">
                                        {{ date('d-m-Y H:i', strtotime($order->created_at)) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Price</th>
                                    <td>{{ $order->total_distributor_price . ' QAR' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td style="color:<?= $order->status == 2 ? '#f35254' : '#57c68e' ?>">
                                        {{ $order->status ? $order_status[$order->status] : 'N/A' }}</td>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Order Items</h4>
                </div>
                <div class="col-sm-6">

                </div>
            </div>
        </div>
        <div class="row">
            @if ($order_products)
                @foreach ($order_products as $key => $value)
                    <div class="col-md-6 col-xl-3">
                        <div class="card m-b-30">
                            <img class="card-img-top img-fluid"
                                src="{{ !empty($value->product_image_url) ? $value->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                style="height:295px" alt="Card image cap">
                            <div class="card-body">
                                <h4 class="card-title font-16 mt-0">
                                    {{ $value->product_quantity . ' x ' . $value->product_name_en }}</h4>
                                <h6>Unit Weight: {{ $value->unit }}</h6>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection
