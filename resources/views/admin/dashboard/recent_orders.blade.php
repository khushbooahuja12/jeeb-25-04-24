@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Recent Orders (<?= $slot_date_time; ?>)</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
        @include('partials.errors_array')
        @include('partials.success')
    </div>
    <div class="row">
        <div class="col-xl-12">
            <div class="card m-b-30">
                <form method="post" id="changeAssignedDriverForm" action="{{route('admin.dashboard.update_assigned_driver')}}">
                    @csrf
                    <div class="card-body">                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Order Number</th>
                                        <th width="30%">Driver</th>  
                                        <th>Status</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($orders->count())
                                    @foreach($orders as $key=>$row)                                
                                    <tr>
                                        <td>{{$key+1}}</td>
                                        <td>{{'#'.$row->orderId}}</td>
                                        <td>
                                            <select class="form-control select2" onchange="check_driver_availability(this)" name="driverIds[]" <?= $row->status == 7 || $row->status == 4 ? "disabled" : ""; ?>>
                                                <option value="0">Select</option>
                                                @if($drivers)
                                                @foreach($drivers as $key=>$value)
                                                <option value="{{$value->id}}" <?= $value->id == $row->fk_driver_id ? "selected" : ""; ?>>{{$value->name?$value->name.', +'.$value->country_code.'-'.$value->mobile:'+'.$value->country_code.'-'.$value->mobile}}</option>
                                                @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td style="color: <?= $row->status == 0 || $row->status == 1 || $row->status == 4 ? "red" : "green"; ?>">
                                            <!-- <?//= $row->status == 7?"Delivered":($row->assign_status == 1 ? "Assigned" : "Pending"); ?> -->
                                            <?php 
                                            echo ($row->status==0||$row->status==1)?'Pending':$order_status[$row->status];                                        
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?= url('admin/orders/detail/' . base64url_encode($row->id)); ?>"><i class="icon-eye"></i></a>
                                        </td>
                                    </tr>
                                    <?php if ($row->status == 1 || $row->status == 2): ?>
                                    <input type="hidden" name="orderIds[]" value="{{$row->id}}">
                                    <?php endif; ?>
                                @endforeach
                                @endif    
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($orders->count())
                    <div class="card-body">
                        <button type="button" class="btn btn-success" onclick="change_assigned_driver()">Update</button>
                    </div>
                    @endif
                    <input type="hidden" name="slot" value="{{$slot}}">
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function change_assigned_driver() {
        if (confirm("Are you sure you want to update?")) {
            $("#changeAssignedDriverForm").submit();
        }
    }

    function allocate_order(obj, order_id) {
        if (confirm("Are you sure you want to allocate this order?")) {
            $.ajax({
                url: '<?= url('admin/orders/allocate_order'); ?>',
                type: 'post',
                dataType: 'json',
                data: {order_id: order_id},
                cache: false
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + response.message + '</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);

                    $(obj).find('button').attr('disabled', 'disabled');
                    $(obj).find('button').text('Allocated');
                } else {
                    var error_str = '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + response.message + '</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        }
    }

    function check_driver_availability(obj) {
//        alert($(obj).val())
//        console.log($("#select2").val())
    }
</script>
@endsection