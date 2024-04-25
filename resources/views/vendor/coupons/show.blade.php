@extends('vendor.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Coupon Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/coupon_list') ?>">Coupons</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @csrf
                        <div class="row">
                            <div class="offset-3 col-md-9 justify-center">
                                <table class="table table-borderless table-responsive">
                                    <tr>
                                        <th> Coupon Code</th>
                                        <td> {{ $coupon->coupon_code }}</td>
                                    </tr>
                                    <tr>
                                        <th> Apply Offer On</th>
                                        <td> {{ $coupon->offer_type == 1 ? 'Minimum purchase' : ($coupon->offer_type == 2 ? 'Category' : 'Brand') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Coupon Title </th>
                                        <td> {{ $coupon->title_en }}</td>
                                    </tr>
                                    <tr>
                                        <th>Coupon Title (Ar) </th>
                                        <td> {{ $coupon->title_ar }}</td>
                                    </tr>
                                    <tr>
                                        <th> Coupon Discount Type</th>
                                        <td> {{ $coupon->type == 1 ? 'Discount By Percentage' : 'Discount By Amount' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Discount </th>
                                        <td> {{ $coupon->discount . ($coupon->type == 1 ? ' %' : '') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Expiry Date </th>
                                        <td> <span
                                                class="{{ $coupon->expiry_date >= date('Y-m-d') ? 'text-success' : 'text-danger' }}">{{ date('d M Y', strtotime($coupon->expiry_date)) }}</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Use Limit </th>
                                        <td> {{ $coupon->uses_limit }} times only</td>
                                    </tr>
                                    <tr>
                                        <th>Model </th>
                                        <td> {{ $coupon->model == 1 ? 'Instant' : 'Plus' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Coupon Description </th>
                                        <td> {{ $coupon->description_en }}</td>
                                    </tr>
                                    <tr>
                                        <th>Coupon Description (Ar) </th>
                                        <td> {{ $coupon->description_ar }}</td>
                                    </tr>
                                    <tr>
                                        <th>Coupon Image</th>
                                        <td><img src="{{ $coupon->getCouponImage ? asset('/') . $coupon->getCouponImage->file_path . $coupon->getCouponImage->file_name : asset('assets/images/no_img.png') }}"
                                                width="150" height="100">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
@endsection
