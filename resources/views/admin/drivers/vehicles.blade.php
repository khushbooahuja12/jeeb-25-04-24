@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">All Vehicles
                    <a href="<?= url('admin/add-vehicle'); ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Vehicles</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Vehicle Number</th>
                                <th>Vehicle type</th>
                                <th>Vehicle Capacity</th> 
                                <th>Driver</th> 
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($vehicles)
                            @foreach($vehicles as $key=>$value)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$value->vehicle_number}}</td>
                                <td>{{$value->vehicle_type==1?"Car":"Others"}}</td>
                                <td>{{$value->vehicle_capacity}}</td>
                                <td>{{$value->getDriver && $value->getDriver->name?$value->getDriver->name:($value->getDriver?$value->getDriver->country_code.$value->getDriver->mobile:"Not assigned")}}</td>
                                <td>
                                    <a title="Edit Vehicle" href="<?= url('admin/edit-vehicle/' . base64url_encode($value->id)); ?>"><i class="fa fa-edit"></i></a>
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
@endsection