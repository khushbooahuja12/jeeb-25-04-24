@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Orders</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('vendor/active_orders') ?>">Orders</a></li>
                    <li class="breadcrumb-item active">List</li>
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
                <div class="col-md-12">
                    <form method="get" action="">
                        <div class="row" style="padding: 10px">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" name="order_date" class="form-control delivery_date" autocomplete="off" value="<?= isset($_GET['order_date']) && $_GET['order_date'] != '' ? date('m/d/Y', strtotime($_GET['order_date'])) : ""; ?>" placeholder="mm/dd/yyyy">
                                </div>                           
                            </div>                            
                            <div class="col-md-4">
                                <div class="input-group">
                                    <input type="text" name="orderId" class="form-control" autocomplete="off" value="<?= isset($_GET['orderId']) && $_GET['orderId'] != '' ? $_GET['orderId'] : ""; ?>" placeholder="Order Number">
                                </div>                           
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-control">
                                    <option selected disabled>Select Status</option>
                                    @if($order_status)
                                    @foreach($order_status as $key=>$value)
                                    <option value="{{$key}}"<?= isset($_GET['status']) && $_GET['status'] == $key ? "selected" : ""; ?>>{{$value}}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                            {{-- @if($slot) --}}
                            <div class="col-md-3">
                                <a href="<?= url('admin/orders'); ?>"><button type="button" class="btn btn-danger">Clear</button></a>
                                <button type="submit" class="btn btn-success">Filter</button>
                            </div>
                            {{-- @endif --}}
                        </div>
                        {{-- @if(!$slot) --}}
                        {{-- <div class="row" style="padding: 10px">
                            <div class="col-md-2">
                                <a href="<?= //url('vendor/orders'); ?>"><button type="button" class="btn btn-danger">Clear</button></a>
                                <button type="submit" class="btn btn-success">Filter</button>
                            </div>
                        </div> --}}
                        {{-- @endif --}}
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
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
                            @if($orders)
                            @foreach($orders as $key=>$row)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{'#'.$row->orderId}}</td>
                                <td>{{$row->grand_total}}</td>
                                <td>{{date('d-m-Y H:i',strtotime($row->created_at))}}</td>
                                <td style="color:<?= $row->status == 2 ? '#f35254' : '#57c68e' ?>">{{$order_status[$row->status]}}</td>                               
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
<script>
    $('input.delivery_date').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false
    }).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY'));

        $.ajax({
            url: '<?= url('admin/orders/get_slots'); ?>',
            type: 'post',
            dataType: 'json',
            data: {date: $(this).val()},
            cache: false
        }).done(function (response) {
            if (response.status_code == 200) {
                var html = '<option disabled selected>Select Slot</option>'
                $.each(response.slots, function (i, v) {
                    html += '<option value=' + v.slot + '>' + v.slot + '</option>';
                });
            } else {
                var html = '<option disabled selected>No slot found</option>'
            }
            $("#slots").html(html);
        });
    });
</script>
@endsection