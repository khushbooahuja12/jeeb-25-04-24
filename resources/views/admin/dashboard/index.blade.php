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
                        <a href="/admin/report">For more details, visit to reporting page</a>
                    </h4>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#date').on('change',function(e){
            e.preventDefault();
            let date = $(this).val();
            // alert(company_id);
            window.location.href = '/admin/dashboard?date='+date; 
        });
    </script>
@endsection
