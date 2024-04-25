@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .ui-timepicker-wrapper{
        width:217px;
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Slot settings</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Slot settings</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="alert_msg"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Start Date</th>
                                <th>Order Limit</th>
                                <th>Slot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($slots)
                            @foreach($slots as $key=>$row)
                            <tr class="slot<?= $row->id; ?>">
                                <td>{{$key+1}}</td>
                                <td class="from">{{date('d-m-Y',strtotime($row->start_date))}}</td>
                                <td>{{$row->order_limit??"N/A"}}</td>
                                <td class="to">
                                    <?php
                                    $slots = get_slot_settings($row->start_date);
                                    if ($slots->count()):
                                        foreach ($slots as $key => $value):
                                            ?>
                                            <p>
                                                <?= date('g:i A', strtotime($value->from)) . '-' . date('g:i A', strtotime($value->to)); ?>
                                                &ensp;&ensp;
                                            </p>
                                            <?php
                                        endforeach;
                                    else:
                                        ?>
                                        <p>N/A</p>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('admin/edit-delivery-slots/' . base64url_encode($row->id)); ?>" title="Edit"><i class="icon-pencil"></i></a>
                                    <a href="javascript:void(0)" onclick="delete_slot(<?= $row->id; ?>)" title="Delete"><i class="icon-trash-bin"></i></a>
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
    $('#fromtime').timepicker({
        'timeFormat': 'h:i A',
        'step': 15
    });
    $('#totime').timepicker({
        'timeFormat': 'h:i A',
        'step': 15
    });

    function delete_slot(id) {
        if (confirm("Are you sure you want to remove this slot ?")) {
            $.ajax({
                url: '<?= url('admin/delete_slot_setting'); ?>',
                data: {id: id},
                type: 'post',
                dataType: 'json',
                cache: false
            }).done(function (response) {
                if (response.status_code == 200) {
                    $(".slot" + id).remove();
                    var success_str = '<div class="alert alert-success fade show alert-dismissible">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + response.message + '</strong>.'
                            + '</div>';
                    $(".alert_msg").html(success_str);
                } else {
                    var error_str = '<div class="alert alert-danger fade show alert-dismissible">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + response.message + '</strong>.'
                            + '</div>';
                    $(".alert_msg").html(error_str);
                }
            })
        }
    }
</script>
@endsection