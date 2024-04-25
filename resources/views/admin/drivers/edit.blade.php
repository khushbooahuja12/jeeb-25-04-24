@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Driver</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/drivers'); ?>">Drivers</a></li>
                    <li class="breadcrumb-item active">Edit</li>
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
                    <form method="post" autocomplete="off" enctype="multipart/form-data" action="{{route('admin.drivers.update',[base64url_encode($driver->id)])}}" id="regForm">
                        @csrf
                        <input type="hidden" name="fk_driver_id" id="fk_driver_id" value="{{ $driver->id }}">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="name" id="name" value="{{old('name')?old('name'):$driver->name}}" class="form-control characterOnly" placeholder="Driver Name">                                    
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mobile Number</label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <input type="text" name="country_code" value="{{old('country_code')?old('country_code'):$driver->country_code}}" class="form-control numberOnly" placeholder="Code"> 
                                        </div>
                                        <div class="col-md-9">
                                            <input type="text" name="mobile" value="{{old('mobile')?old('mobile'):$driver->mobile}}" class="form-control numberOnly" placeholder="Mobile">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" autocomplete="new-password" value="{{old('email')?old('email'):$driver->email}}" class="form-control" placeholder="Email">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Role</label>
                                    <select class="form-control select2" name="driver_role" id="driver_role">
                                        <option disabled selected>--select--</option>
                                        <option value="0" {{ $driver->role ==0 ? 'selected':'' }}>Collector</option>
                                        <option value="1" {{ $driver->role ==1 ? 'selected':'' }}>Dispatcher</option>
                                        <option value="2" {{ $driver->role ==2 ? 'selected':'' }}>Instant Driver</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Collector Driver Start -->
                            <div class="col-lg-6" id="collector_driver_group_div" @if($driver->role == 1 || $driver->role == 2) style="display: none" @endif>
                                <div class="form-group">
                                    <label>Group (collector)</label><br>
                                    <select class="form-control select2" name="collector_driver_group" id="collector_driver_group" style="width: 100%;">
                                        <option selected disabled value="" >--select--</option>
                                        @if ($collector_driver_store_groups)
                                            @foreach ($collector_driver_store_groups as $key => $collector_driver_store_group)
                                                <option value="{{ $collector_driver_store_group->group_id }}" {{ isset($collector_driver_group->group_id) && $collector_driver_group->group_id == $collector_driver_store_group->group_id ? 'selected' : '' }}>
                                                    {{ $collector_driver_store_group->group_name}}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6" id="collector_driver_group_store_div" @if($driver->role == 1 || $driver->role == 2) style="display: none" @endif>
                                <div class="form-group">
                                    <label>Group Stores (collector)</label><br>
                                    <select class="form-control select2" name="collector_driver_group_store[]" id="collector_driver_group_store" style="width: 100%;"  multiple>
                                        @if ($stores)
                                            @foreach ($stores as $key => $store)
                                                <option value="{{ $store->id }}"
                                                    <?= in_array($store->id, $collector_driver_stores) ? 'selected' : '' ?>>
                                                    {{ $store->name }}-{{ $store->company_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <!-- Collector Driver End -->

                            <!-- Instant Driver Start-->
                            <div class="col-lg-6" id="instant_driver_group_div" @if($driver->role == 1 || $driver->role == 0) style="display: none" @endif>
                                <div class="form-group">
                                    <label>Group (instant)</label>
                                    <select class="form-control select2" name="instant_driver_group" id="instant_driver_group" style="width: 100%;">
                                        <option value="">--select---</option>
                                        @if ($instant_driver_store_groups)
                                            @foreach ($instant_driver_store_groups as $key => $instant_driver_store_group)
                                                <option value="{{ $instant_driver_store_group->id }}"
                                                    <?= $instant_driver_group && $instant_driver_group->fk_group_id == $instant_driver_store_group->id ? 'selected' : '' ?>>
                                                    {{ $instant_driver_store_group->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6" id="instant_driver_group_store_div" @if($driver->role == 1 || $driver->role == 0) style="display: none" @endif>
                                <div class="form-group">
                                    <label>Group Stores (instant)</label>
                                    <select class="form-control select2" name="instant_driver_group_store[]" id="instant_driver_group_store" style="width: 100%;" multiple>
                                        @if ($stores)
                                            @foreach ($stores as $key => $store)
                                                <option value="{{ $store->id }}"
                                                    <?= in_array($store->id, $instant_driver_store_group_stores) ? 'selected' : '' ?>>
                                                    {{ $store->name }}-{{ $store->company_name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <!-- Instant Driver End -->

                            <div class="col-lg-6" id="dispatcher_driver_store_div" @if($driver->role == 0 || $driver->role == 2) style="display: none" @endif>
                                <div class="form-group">
                                    <label>Store (dispatcher)</label>
                                    <select class="form-control select2" name="fk_store_id" id="fk_store_id" style="width: 100%;" >
                                        <option disabled selected>--select--</option>
                                        @if ($stores)
                                            @foreach ($stores as $key => $store)
                                                <option value="{{ $store->id }}"
                                                    <?= $driver->fk_store_id == $store->id ? 'selected' : '' ?>>
                                                    {{ $store->company_name.' - '.$store->name }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Driving License No.</label>
                                    <input type="text" name="driving_licence_no" value="{{old('driving_licence_no')?old('driving_licence_no'):$driver->driving_licence_no}}" class="form-control" placeholder="Driving License Number">
                                </div>
                            </div>    
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Driver Image</label>
                                    <input type="file" accept="image/*"  name="driver_image" data-default-file="{{!empty($driver->getDriverImage)?asset('images/driver_images').'/'.$driver->getDriverImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>License Image</label>
                                    <input type="file" accept="image/*"  name="driving_licence" data-default-file="{{!empty($driver->getDrivingLicence)?asset('images/driver_images').'/'.$driver->getDrivingLicence->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>National ID</label>
                                    <input type="file" accept="image/*"  name="national_id" data-default-file="{{!empty($driver->getNationalId)?asset('images/driver_images').'/'.$driver->getNationalId->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Update
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
    $(document).ready(function () {
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
        var fk_driver_id = $("#fk_driver_id").val();
        
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
                fk_driver_id:fk_driver_id,
                driver_role:driver_role
            },
            cache: false,
            success:function(response){
                if (response.status_code == 200) { console.log(response.data);
                    var html = '<option disabled selected>--select--</option>';
                    $.each(response.data.store_groups, function(i, v) {
                        if(driver_role == 0){
                            html += '<option value="' + v.group_id + '>' + v.group_name + '</option>';
                            
                        }else if(driver_role == 2){
                            html += '<option value="' + v.id + '>' + v.name + '</option>';
                        }
                        
                    });
                    
                } else {
                    var html = '<option disabled selected>No group found</option>'
                }
            }
        })
        

        
    })
</script>

<script>
    $('#collector_driver_group').change(function(){

        var driver_role = $("#driver_role").val();
        var fk_driver_id = $("#fk_driver_id").val();
        var driver_group = $("#collector_driver_group").val();

        $('#collector_driver_group_store_div').show();
        
        $.ajax({
            url: '<?= url('admin/drivers/get_group_stores') ?>',
            type: 'GET',
            dataType: 'JSON',
            data: {
                driver_role: driver_role,
                driver_group: driver_group,
                fk_driver_id: fk_driver_id
            },
            cache: false,
            success:function(response){
                if (response.status_code == 200) {
                    var html = '';
                    $.each(response.data.stores, function(i, v) {
                        // Check if the store ID is in the group_stores array and add 'selected' if true
                        var selected = $.inArray(v.id, response.data.group_stores) !== -1 ? 'selected' : '';

                        html += '<option value="' + v.id + '" ' + selected + '>' + v.name + '-' + v.company_name + '</option>';
                    });
                    
                } else {
                    var html = '<option disabled selected>No group found</option>'
                }
                $("#collector_driver_group_store").html(html);
            }
        })
    })

    $('#instant_driver_group').change(function(){

        var driver_role = $("#driver_role").val();
        var fk_driver_id = $("#fk_driver_id").val();
        var driver_group = $("#instant_driver_group").val();

        $('#instant_driver_group_store_div').show();

        $.ajax({
            url: '<?= url('admin/drivers/get_group_stores') ?>',
            type: 'GET',
            dataType: 'JSON',
            data: {
                driver_role: driver_role,
                driver_group: driver_group,
                fk_driver_id: fk_driver_id
            },
            cache: false,
            success:function(response){
                if (response.status_code == 200) {
                    var html = '';
                    $.each(response.data.stores, function(i, v) {
                        // Check if the store ID is in the group_stores array and add 'selected' if true
                        var selected = $.inArray(v.id, response.data.group_stores) !== -1 ? 'selected' : '';

                        html += '<option value="' + v.id + '" ' + selected + '>' + v.name + '-' + v.company_name + '</option>';
                    });
                    
                } else {
                    var html = '<option disabled selected>No group found</option>'
                }
                $("#instant_driver_group_store").html(html);
            }
        })
        })
</script>

@endsection