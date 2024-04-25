@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Dashboard</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-briefcase-check bg-success text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-16">No. of users</h5>
                        </div>
                        <h3 class="mt-4">{{ 3000+$user_count }}</h3>
                        {{-- <hr/>
                        <h3 class="font-16">OTP verified status</h3>
                        <span class="mt-4" style="font-size: 14px; line-height: 18px">
                            <br/>
                            No. of verified users: {{ $user_verified_count }}<br/>
                            No. of non-verified users: {{ $user_non_verified_count }}
                        </span> --}}
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-tag-text-outline bg-warning text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-16">No. of orders delivered</h5>
                        </div>
                        <h3 class="mt-4">{{ $orders_count }}</h3>
                        {{-- <hr/>
                        <h3 class="font-16">Order status</h3>
                        <span class="mt-4" style="font-size: 14px; line-height: 18px">
                            <br/>
                            No. of orders in progress: {{ $orders_count_inprogress }}<br/>
                            No. of orders cancelled: {{ $orders_count_cancelled }}<br/>
                            No. of test orders delivered: {{ $orders_count_test }}<br/>
                        </span>
                        <hr/>
                        <h3 class="font-16">Forgot something feature stats</h3>
                        <span class="mt-4" style="font-size: 14px; line-height: 18px">
                            <br/>
                            No. of orders: {{ $forgot_orders_count }}<br/>
                            No. of products: {{ $forgot_products_count }}
                        </span> --}}
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-buffer bg-danger text-white"></i>
                        </div>
                        <div>
                            <h6 class="font-16">No. of products</h6>
                        </div>
                        <h3 class="mt-4">{{ $products_count }}</h3>
                        {{-- <hr/>
                        <h3 class="font-16">Products instock</h3>
                        <span class="mt-4" style="font-size: 14px; line-height: 18px">
                            <br/>
                            No. of instock products: {{ $products_count_active }}
                        </span> --}}
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-cube-outline bg-primary  text-white"></i>
                        </div>
                        <div>
                            <h6 class="font-16">No. of storekeepers</h6>
                        </div>
                        <h3 class="mt-4">{{ $storekeeper_count }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-cube-outline bg-primary  text-white"></i>
                        </div>
                        <div>
                            <h6 class="font-16">No. of drivers</h6>
                        </div>
                        <h3 class="mt-4">{{ $driver_count }}</h3>
                    </div>
                </div>
            </div>

        </div>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-12">
                    <h4 class="page-title">
                        {{ $filter_date_text }} &nbsp;&nbsp;&nbsp;
                        <select name="date" id="date" class="form-control" style="width: 150px; display: inline-block;">
                            <option value=""></option>
                            @if ($dates)
                                @foreach ($dates as $date)
                                    <option value="{{ $date }}" {{ ($filter_date==$date) ? 'selected="true"' : '' }}>{{ $date }}</option>
                                @endforeach
                            @endif
                        </select>
                    </h4><br/>
                    <p> Now the time is: {{ $time_now }} </p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-xl-6">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-briefcase-check bg-success text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-16">No. of users</h5>
                        </div>
                        <h3 class="mt-4">{{ $users_count_today }}</h3>
                        {{-- <hr/>
                        <h3 class="font-16">OTP verified status</h3>
                        <span class="mt-4" style="font-size: 14px; line-height: 18px">
                            <br/>
                            No. of verified users: {{ $users_verified_count_today }}<br/>
                            No. of non-verified users: {{ $users_non_verified_count_today }}
                        </span> --}}
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-6">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-tag-text-outline bg-warning text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-16">No. of orders</h5>
                        </div>
                        <h3 class="mt-4">{{ $orders_count_today_delivered }}</h3>
                        Total amount:  {{ $orders_total_today_delivered }}
                        {{-- <hr/>
                        <h3 class="font-16">Order status</h3>
                        <span class="mt-4" style="font-size: 14px; line-height: 18px">
                            <br/>
                            No. of delivered orders: {{ $orders_count_today_delivered }} Total amount:  {{ $orders_total_today_delivered }}<br/>
                            No. of orders in progress: {{ $orders_count_today_inprogress }} Total amount:  {{ $orders_total_today_inprogress }}<br/>
                            No. of orders cancelled: {{ $orders_count_today_cancelled }} Total amount:  {{ $orders_total_today_cancelled }}<br/>
                            No. of orders test_order: {{ $orders_count_today_test }}
                        </span> --}}
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-buffer bg-danger text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-16">No. of new products</h5>
                        </div>
                        <h3 class="mt-4">{{ $products_count_today }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-briefcase-check bg-success text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-16">No. of downloads and reviews</h5>
                        </div>
                        <a href="https://play.google.com/store/apps/details?id=com.jeeb.user&hl=en_US&gl=US&pli=1" target="_blank">Playstore</a>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card">
                    <div class="card-heading p-4">
                        <div class="mini-stat-icon float-right">
                            <i class="mdi mdi-briefcase-check bg-success text-white"></i>
                        </div>
                        <div>
                            <h5 class="font-16">No. of downloads and reviews</h5>
                        </div>
                        <a href="https://apps.apple.com/qa/app/jeeb-grocery-service/id1614152924" target="_blank">AppStore</a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <script>
        $('#date').on('change',function(e){
            e.preventDefault();
            let date = $(this).val();
            // alert(company_id);
            window.location.href = '/admin/report?date='+date; 
        });
    </script>
@endsection
