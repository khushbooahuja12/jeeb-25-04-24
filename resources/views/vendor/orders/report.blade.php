@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Sales Report</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Sales Report</li>
                </ol>
            </div>
        </div>
    </div>    
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="col-md-12">
                    <form method="get" action="">
                        <div class="row" style="padding: 10px">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="text" name="start_date" class="form-control delivery_date" autocomplete="off" value="<?= isset($_GET['start_date']) && $_GET['start_date'] != '' ? date('m/d/Y', strtotime($_GET['start_date'])) : ""; ?>" placeholder="Start date" readonly>
                                </div>                           
                            </div>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="text" name="end_date" class="form-control delivery_date" autocomplete="off" value="<?= isset($_GET['end_date']) && $_GET['end_date'] != '' ? date('m/d/Y', strtotime($_GET['end_date'])) : ""; ?>" placeholder="End date" readonly>
                                </div>                           
                            </div>
                            <div class="col-md-3">
                                <a href="<?= url('vendor/reports'); ?>"><button type="button" class="btn btn-danger">Clear</button></a>
                                <button type="submit" class="btn btn-success">Filter</button>
                            </div>
                        </div>                        
                    </form>
                </div>
                <div class="card-body">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Order Number</th>
                                <th>Status</th>
                                <th>Delivery date</th>
                                <th>Total Amount</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sum = 0; ?>
                            @if($orders)
                            @foreach($orders as $key=>$row)
                            <?php
                            $sum = $sum + $row->total_distributor_price;
                            ?>
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{'#'.$row->orderId}}</td>
                                <td>{{$order_status[$row->status]}}</td>
                                <td>{{date('d-m-Y',strtotime($row->updated_at))}}</td>
                                <td>{{$row->total_distributor_price}}</td>
                                <td>
                                    <a href="<?= url('vendor/orders/detail/' . base64url_encode($row->id)); ?>"><i class="icon-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                            <tr>
                                <td colspan="3"></td>
                                <td><b>Sub Total</b></td>
                                <td><?= "QAR " . $sum; ?></td>                                
                            </tr>
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
    });
</script>
@endsection