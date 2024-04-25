@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Driver</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/drivers') ?>">Drivers</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" autocomplete="off" enctype="multipart/form-data"
                            action="{{ route('admin.drivers.store') }}" id="regForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                                            class="form-control characterOnly" placeholder="Driver Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mobile Number</label>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="text" name="country_code" value="{{ old('country_code') }}"
                                                    class="form-control numericOnly" placeholder="Code">
                                            </div>
                                            <div class="col-md-9">
                                                <input type="text" name="mobile" value="{{ old('mobile') }}"
                                                    class="form-control numericOnly" placeholder="Mobile">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" autocomplete="new-password"
                                            value="{{ old('email') }}" class="form-control" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Role</label>
                                        <select class="form-control select2" name="driver_role" id="driver_role">
                                            <option disabled selected>--select--</option>
                                            <option value="0">Collector</option>
                                            <option value="1">Dispatcher</option>
                                            <option value="2">Instant Driver</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" id="collector_driver_group_div" style="display: none">
                                    <div class="form-group">
                                        <label>Groups (collector)</label><br>
                                        <select class="form-control select2" name="driver_group" id="collector_driver_group" style="width: 100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" id="collector_driver_group_store_div" style="display: none">
                                    <div class="form-group">
                                        <label>Group Stores (collector)</label><br>
                                        <select class="form-control select2" name="driver_group_store[]" id="collector_driver_group_store" multiple style="width: 100%;">
                                            @if($stores)
                                            @foreach ($stores as $store)
                                                <option>{{ $store->name }}-{{  $store->company_name  }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" id="instant_driver_group_div" style="display: none">
                                    <div class="form-group">
                                        <label>Groups (instant)</label><br>
                                        <select class="form-control select2" name="driver_group" id="instant_driver_group" style="width: 100%;">
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" id="instant_driver_group_store_div" style="display: none">
                                    <div class="form-group">
                                        <label>Group Stores (instant)</label><br>
                                        <select class="form-control select2" name="driver_group_store[]" id="instant_driver_group_store" multiple style="width: 100%;">
                                            @if($stores)
                                            @foreach ($stores as $store)
                                                <option>{{ $store->name }}-{{  $store->company_name  }}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6" id="dispatcher_driver_store_div" style="display: none">
                                    <div class="form-group">
                                        <label>Stores (dispatcher)</label>
                                        <select class="form-control select2" name="fk_store_id" id="fk_store_id" style="width: 100%;">
                                            <option disabled selected>--select--</option>
                                            @if ($stores)
                                                @foreach ($stores as $key => $value)
                                                    <option value="{{ $value->id }}">
                                                        {{ $value->company_name.' - '.$value->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Driving License No.</label>
                                        <input type="text" name="driving_licence_no"
                                            value="{{ old('driving_licence_no') }}" class="form-control"
                                            placeholder="Driving License Number">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Driver Image</label>
                                        <input type="file" accept="image/*" name="driver_image"
                                            class="form-control dropify">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>License Image</label>
                                        <input type="file" accept="image/*" name="driving_licence"
                                            class="form-control dropify">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>National ID</label>
                                        <input type="file" accept="image/*" name="national_id"
                                            class="form-control dropify">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Submit
                                    </button>

                                    <button type="button" class="btn btn-secondary waves-effect m-l-5"
                                        onclick="cancelForm(1);">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#regForm').validate({
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    mobile: {
                        required: true,
                        rangelength: [6, 15]
                    },
                    driving_licence_no: {
                        required: true
                    }
                }
            });

        });
    </script>
    <script>
        $('#driver_role').change(function(){

            var driver_role = $("#driver_role").val();
            
                if(driver_role == 0){
                    $('#collector_driver_group_div').show();
                    $('#collector_driver_group_store_div').show();
                    $('#instant_driver_group_div').hide();
                    $('#instant_driver_group_store_div').hide();
                    $('#dispatcher_driver_store_div').hide();
                }else if(driver_role == 1){
                    $('#collector_driver_group_div').hide();
                    $('#collector_driver_group_store_div').hide();
                    $('#instant_driver_group_div').hide();
                    $('#instant_driver_group_store_div').hide();
                    $('#dispatcher_driver_store_div').show();
                }else if(driver_role == 2){
                    $('#collector_driver_group_div').hide();
                    $('#collector_driver_group_store_div').hide();
                    $('#dispatcher_driver_store_div').hide();
                    $('#instant_driver_group_div').show();
                    $('#instant_driver_group_store_div').show();
                }
                    
                $.ajax({
                url: '<?= url('admin/drivers/get_store_groups') ?>',
                type: 'GET',
                dataType: 'JSON',
                data: {
                    driver_role: driver_role
                },
                cache: false,
                success:function(response){ console.log(response);
                    if (response.status_code == 200) {
                        var html = '<option disabled selected>--select--</option>';
                        $.each(response.data.store_groups, function(i, v) {
                            if(driver_role == 0){
                                html += '<option value=' + v.group_id + '>' + v.group_name + '</option>';
                            }else if(driver_role == 2){
                                html += '<option value=' + v.id + '>' + v.name+ '</option>';
                            }
                            
                        });
                        
                    } else {
                        var html = '<option disabled selected>No group found</option>'
                    }

                    if(driver_role == 0){
                        $("#collector_driver_group").html(html);
                    }else if(driver_role == 2){
                        $("#instant_driver_group").html(html);
                    }
                    
                }
            })

        })
    </script>

    <script>
        $('#collector_driver_group').change(function() {
        var driver_group = $("#collector_driver_group").val();
        var driver_role = $("#driver_role").val();

        $.ajax({
            url: '<?= url('admin/drivers/get_group_stores') ?>',
            type: 'GET',
            dataType: 'JSON',
            data: {
                driver_group: driver_group,
                driver_role: driver_role
            },
            cache: false,
            success: function(response) {
                console.log(response);

                if (response.status_code == 200) {
                    var html = '';
                    $.each(response.data.stores, function(i, v) {
                        // Check if the store ID is in the group_stores array and add 'selected' if true
                        var selected = $.inArray(v.id, response.data.group_stores) !== -1 ? 'selected' : '';

                        html += '<option value="' + v.id + '" ' + selected + '>' + v.name + '-' + v.company_name + '</option>';
                    });
                    $('#collector_driver_group_store_div').show();
                } else {
                    html = '<option disabled selected>No group found</option>';
                    $('#collector_driver_group_store_div').hide(); // Hide the select if no group is found
                }

                $("#collector_driver_group_store").html(html);
            }
        });
    });

    $('#instant_driver_group').change(function(){
        var driver_group = $("#instant_driver_group").val();
        var driver_role = $("#driver_role").val();
        $.ajax({
            url: '<?= url('admin/drivers/get_group_stores') ?>',
            type: 'GET',
            dataType: 'JSON',
            data: {
                driver_group: driver_group,
                driver_role: driver_role
            },
            cache: false,
            success: function(response){
                console.log(response);
                var html = '';

                if (response.status_code == 200) {
                    $.each(response.data.stores, function(i, v) { console.log(response.data.group_stores);
                        if(response.data.group_stores){
                            var selected = $.inArray(v.id, response.data.group_stores) !== -1 ? 'selected' : '';
                            html += '<option value="' + v.id + '" ' + selected + '>' + v.name + '-' + v.company_name + '</option>';
                        }else{
                            html += '<option value="' + v.id + '">' + v.name + '-' + v.company_name + '</option>';
                        }
                        
                    });
                    $('#driver_group_store_div').show();
                } else {
                    html = '<option disabled selected>No group found</option>';
                    $('#driver_group_store_div').hide();
                }

                $("#instant_driver_group_store").html(html);
            }
        });
    });
    </script>
@endsection
