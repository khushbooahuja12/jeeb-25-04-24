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
                <h4 class="page-title">Add delivery slots</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Add delivery slots</li>
                </ol>
            </div>
        </div>
    </div>    
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="col-md-12">
                    <div class="row" style="padding: 10px">
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="start_date" class="form-control delivery_date" autocomplete="off" value="<?= isset($_GET['start_date']) && $_GET['start_date'] != '' ? date('m/d/Y', strtotime($_GET['start_date'])) : ""; ?>" placeholder="Start date" readonly>
                            </div>                           
                        </div>                           
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="no_of_slots" class="form-control numericOnly" maxlength="1"  autocomplete="off" placeholder="No. of slots in a day">
                            </div>                           
                        </div>
                        <div class="col-md-3">
                            <button type="button" onclick="makeSlotRows(this)" class="btn btn-success">Make Slots</button>
                            <button type="button" onclick="clearSlotRows(this)" class="btn btn-danger">Clear</button>
                        </div>
                    </div>                        
                </div>
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

                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <div class="row" style="padding: 10px">                        
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success">Submit</button>
                            </div>
                        </div>                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    $('input.delivery_date').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        minDate: '<?= date('m/d/Y', strtotime($slots[0]->date . ' +1 day')); ?>'
    }).on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY'));
    });

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