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
                <h4 class="page-title">Delivery Slots</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Delivery Slots</li>
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
                <div class="add_new_btn" style="margin: 10px 20px 0px 0px;">                   
                    <a href="<?= url('admin/add-delivery-slots'); ?>"><button type="button" class="btn btn-primary float-right">Slot settings</button></a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Date</th>
                                <th>Order Limit</th>
                                <th>Slot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($slots)
                            @foreach($slots as $key=>$row)
                            <tr class="slot<?= $row->id; ?>">
                                <td>{{$key+1}}</td>
                                <td class="from">{{date('d-m-Y',strtotime($row->date))}}</td>
                                <td>{{$row->order_limit}}</td>
                                <td class="to">
                                    <?php
                                    $slots = get_slots($row->date);
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
</script>
@endsection