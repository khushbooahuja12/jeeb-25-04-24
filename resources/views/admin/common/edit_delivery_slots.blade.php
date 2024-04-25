@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .start_end_date{
        display: flex;
    }
    .start_end_date select{
        width: 70px;
    }
    .start_end_date p{
        margin: 5px 10px 0 5px;
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit delivery slots</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Edit delivery slots</li>
                </ol>
            </div>
        </div>
    </div>    
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">                
                <form method="post" action="{{route('admin.store_delivery_slots')}}">
                    @csrf
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive">
                            <thead>
                                <tr>
                                    <th width="25">Date</th>
                                    <th width="25">Start Time</th>
                                    <th width="25">End Time</th>
                                    <th width="25">Order Limit</th>
                                </tr>
                            </thead>
                            <tbody id="append_location">
                                <tr>
                                    <td>{{date('d-m-Y',strtotime($slot->start_date))}}</td>
                                    <td>
                                        <div class="start_end_date">
                                            <select class="form-control" name="start_time_hours[]">
                                                <?php for ($i = 0; $i < 24; $i++): ?>
                                                    <option value="<?= $i; ?>"><?= $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <p>hrs</p>
                                            <select class="form-control" name="start_time_minutes[]">
                                                <?php for ($i = 0; $i < 4; $i++): ?>
                                                    <option value="<?= $i; ?>"><?= $i * 15; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <p>min</p>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="start_end_date">
                                            <select class="form-control" name="end_time_hours[]">
                                                <?php for ($i = 0; $i < 24; $i++): ?>
                                                    <option value="<?= $i; ?>"><?= $i; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <p>hrs</p>
                                            <select class="form-control" name="end_time_minutes[]">
                                                <?php for ($i = 0; $i < 4; $i++): ?>
                                                    <option value="<?= $i; ?>"><?= $i * 15; ?></option>
                                                <?php endfor; ?>
                                            </select>
                                            <p>min</p>
                                        </div>
                                    </td>
                                    <td><input type="text" name="order_limit[]" value="<?= $slot->order_limit; ?>" class="form-control numericOnly" maxlength="2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <div class="row" style="padding: 10px">                        
                            <div class="col-md-3">
                                <button type="button" class="btn btn-success">Update</button>
                            </div>
                        </div>                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    function makeSlotRows(obj) {
        let start_date = $("input[name=start_date]").val();
        let no_of_slots = $("input[name=no_of_slots]").val();
        var slot_str = '';

        let hours = '';
        for (let i = 0; i < 24; i++) {
            hours += '<option>' + i + '</option>';
        }
        let minutes = '';
        for (let i = 0; i < 4; i++) {
            minutes += '<option>' + parseInt(i) * 15 + '</option>';
        }

        for (var i = 0; i < no_of_slots; i++) {
            slot_str += '<tr>'
                    + '<td>' + start_date + '</td>'
                    + '<input type="hidden" name="start_date[]" value="' + start_date + '">'
                    + '<td>'
                    + '<div class="start_end_date">'
                    + '<select class="form-control" name="start_time_hours[]">'
                    + hours
                    + '</select>'
                    + '<p>hrs</p>'
                    + '<select class="form-control" name="start_time_minutes[]">'
                    + minutes
                    + '</select>'
                    + '<p>min</p>'
                    + '</div>'
                    + '</td>'
                    + '<td>'
                    + '<div class="start_end_date">'
                    + '<select class="form-control" name="end_time_hours[]">'
                    + hours
                    + '</select>'
                    + '<p>hrs</p>'
                    + '<select class="form-control" name="end_time_minutes[]">'
                    + minutes
                    + '</select>'
                    + '<p>min</p>'
                    + '</div>'
                    + '</td>'
                    + '<td><input type="text" name="order_limit[]" class="form-control numericOnly" maxlength="2"></td>'
                    + '</tr>';
        }
        $("#append_location").html(slot_str);
    }

    function clearSlotRows(obj) {
        if (confirm("Are you sure?")) {
            window.location.reload();
        }
    }
</script>
@endsection