@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Dashboard</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-4 col-xl-4">
            <div class="card">
                <div class="card-heading p-4">
                    <div class="mini-stat-icon float-right">
                        <i class="fa fa-shopping-cart bg-primary  text-white"></i>
                    </div>
                    <div>
                        <h5 class="font-16">Total Revenue</h5>
                    </div>
                    <h3 class="mt-4"></h3>
                </div>
            </div>
        </div>       
        <div class="col-sm-4 col-xl-4">
            <div class="card">
                <div class="card-heading p-4">
                    <div class="mini-stat-icon float-right">
                        <i class="fa fa-shopping-cart bg-primary  text-white"></i>
                    </div>
                    <div>
                        <h5 class="font-16">Total Products</h5>
                    </div>
                    <h3 class="mt-4">{{$all_products}}</h3>
                </div>
            </div>
        </div>       
        <div class="col-sm-4 col-xl-4">
            <div class="card">
                <div class="card-heading p-4">
                    <div class="mini-stat-icon float-right">
                        <i class="fa fa-shopping-cart bg-primary  text-white"></i>
                    </div>
                    <div>
                        <h5 class="font-16">Total Orders</h5>
                    </div>
                    <h3 class="mt-4">{{$order_count}}</h3>
                </div>
            </div>
        </div>       
        <div class="col-sm-4 col-xl-4">
            <div class="card">
                <div class="card-heading p-4">
                    <div class="mini-stat-icon float-right">
                        <i class="fa fa-shopping-cart bg-primary  text-white"></i>
                    </div>
                    <div>
                        <h5 class="font-16">Total Users</h5>
                    </div>
                    <h3 class="mt-4">{{$vendors}}</h3>
                </div>
            </div>
        </div>       

    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h4 class="mt-0 header-title mb-4">Recent Orders</h4>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Total Price (QAR)</th>
                                    <th>Order date</th>
                                    <th>Status</th>    
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($total_orders))
                                @foreach($total_orders as $key=>$row)
                                <tr>
                                    <td>{{$key+1}}</td>
                                    <td>{{'#'.$row->orderId}}</td>
                                    <td>{{$row->grand_total}}</td>
                                    <td>{{date('d-m-Y H:i',strtotime($row->created_at))}}</td>
                                    <td>{{$row->status?$order_status[$row->status]:""}}</td>                                
                                    <td>
                                        <a href="<?= url('vendor/orders/detail/' . base64url_encode($row->id)); ?>"><i class="icon-eye"></i></a>
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

    <!-- START ROW -->
    <!-- END ROW -->

</div>
@endsection