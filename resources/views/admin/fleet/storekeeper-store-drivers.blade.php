@extends('admin.layouts.dashboard_layout_for_fleet_panel')
@section('content')
<style>
    .text_mode {
        display: block;
    }
    .edit_mode {
        display: none;
    }
    .driver_edit {
        display: block;
    }
    .driver_saving {
        display: none;
    }
    .driver_save {
        display: none;
    }
    .product_btn {
        width: 70px;
        height: 35px;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Store Drivers</h4><a class="btn btn-primary " href="{{route('storekeeper-driver-list')}}">Driver Stores</a>
                </div>
                <div class="col-sm-6">  
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{route('fleet-storekeeper-orders')}}">Go back to all Orders</a>
                </div>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                   
                    <div class="card-body" style="overflow-x: scroll;">
                        <br clear="all"/><br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Company Name</th>
                                    <th>Store Name</th>
                                    <th>Drivers</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->company_name }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>
                                                @php $store_drivers = \App\Model\DriverGroup::where('fk_store_id',$row->id)->pluck('fk_driver_id')->toArray(); @endphp
                                                @if ($drivers)
                                                
                                                @foreach ($drivers as $driver)
                                                    <p>{{ in_array($driver->id, $store_drivers) ? $driver->name  : '' }}</p>
                                                @endforeach
                                                
                                                <form name="form_driver_<?= $row->id ?>" id="form_driver_<?= $row->id ?>">
                                                    <input type="hidden" name="fk_store_id" value="{{ $row->id }}">
                                                    <div class="edit_mode edit_mode_driver_{{ $row->id }}">
                                                        <select class="" name="fk_driver_id" id="fk_driver_id">
                                                            <option value="">NULL</option>
                                                            @if ($drivers)
                                                                @foreach ($drivers as $driver)
                                                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <button title="Edit" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn driver_edit driver_edit_{{ $row->id }} btn-sm btn-primary waves-effect"><i class="icon-pencil"></i> Edit</button>
                                                    <button title="Saving" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn driver_saving driver_saving_{{ $row->id }} btn-sm btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                    <button title="Save" href="javascript:void(0);" id="{{ $row->id }}" class="product_btn driver_save driver_save_{{ $row->id }} btn-sm btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $stores->count() }} of {{ $stores->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $stores->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    // Change driver
    $('.driver_edit').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.edit_mode_driver_'+id).show();
        // $('.text_mode_driver_'+id).hide();
        $('.driver_edit_'+id).hide();
        $('.driver_saving_'+id).hide();
        $('.driver_save_'+id).show();
    });
    $('.driver_save').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.driver_edit_'+id).hide();
        $('.driver_saving_'+id).show();
        $('.driver_save_'+id).hide();
        if (id) {
            let form = $("#form_driver_"+id);
            let token = "{{ csrf_token() }}";
            let formData = new FormData(form[0]);
            formData.append('id', id);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/fleet/storekeeper/store/assign_driver') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        $('.edit_mode_driver_'+id).hide();
                        $('.text_mode_driver_'+id).show();
                        $('.driver_edit_'+id).show();
                        $('.driver_saving_'+id).hide();
                        $('.driver_save_'+id).hide();
                        if (data.data) {
                            let driver = data.data;
                            $('.driver_name_text_'+id).html(driver.name);
                            window.location.reload();
                        }else{
                            window.location.reload();
                        }
                    } else {
                        alert(data.message);
                    }
                }
            });
        } else {
            alert("Something went wrong");
        }
    });
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAQXKQdhn1KRWpRAF9wWp3fkKzjehGm6M&callback=initMap">
    </script>
    
@endsection
