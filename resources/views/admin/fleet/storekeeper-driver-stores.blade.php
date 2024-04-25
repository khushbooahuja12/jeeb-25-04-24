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
                    <h4 class="page-title">Driver Stores</h4><a class="btn btn-primary " href="{{route('storekeeper-store-list',['id' => 0])}}">Store Drivers</a>
                </div>
                <br clear="all"/>
                <div class="col-sm-6">  
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{route('fleet-storekeeper-orders')}}">Go back to all Orders</a>
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
                                    <th>Driver Name</th>
                                    <th>Group</th>
                                    <th>Store</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($drivers)
                                    @foreach ($drivers as $key => $row)
                                        <tr>
                                            <form class="collector_store" action="{{ route('storekeeper-update-driver-store') }}" method="POST" id="collector_store_{{ $row->id }}">
                                                @csrf
                                                <input type="hidden" name="fk_driver_id" value="{{ $row->id }}">
                                                <td>{{ $key + 1 }}</td>
                                                <td style="width:20%;">{{ $row->name }}</td>
                                                <td style="width:30%;">
                                                    @php 
                                                    $collector_driver_group = \App\Model\DriverGroup::where('fk_driver_id',$row->id)->groupBy('group_id')->first();
                                                    @endphp
                                                    @if($store_groups)
                                                    <select class="form-control select2 collector_driver_group" name="collector_driver_group" id="{{ $row->id }}" data-id="{{ $row->id }}" style="width: 100%;" required>
                                                        <option value="">--Select--</option>
                                                        @foreach ($store_groups as $store_group)
                                                            <option value="{{ $store_group->group_id }}" {{ (isset($collector_driver_group) && ($collector_driver_group->group_id == $store_group->group_id) ? 'selected': '') }}>{{ $store_group->group_name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php 
                                                    $collector_driver_stores = \App\Model\DriverGroup::where('fk_driver_id',$row->id)->pluck('fk_store_id')->toArray(); 
                                                    @endphp
                                                    <select class="form-control select2" name="collector_driver_group_store[]" id="collector_driver_group_store_{{ $row->id }}" multiple style="width: 100%;" required>
                                                        @if($stores)
                                                        @foreach ($stores as $store)
                                                            <option {{ in_array($store->id,$collector_driver_stores) ? 'selected' : '' }} value="{{ $store->id }}">{{ $store->name }}-{{  $store->company_name  }}</option>
                                                        @endforeach
                                                        @endif
                                                    </select>
                                                </td>
                                                <td style="width:13%;">
                                                    <button type="submit" class="btn btn-primary">save</button>&nbsp;
                                                    <a href="{{ route('storekeeper-delete-driver-store',['id' => $row->id]) }}" onclick="return confirm('Are you sure you want to delete this?')" class="btn btn-danger">Delete</button>
                                                </td>
                                            </form>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $drivers->count() }} of {{ $drivers->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $drivers->links() }}
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

    $('.collector_driver_group').change(function(){

        var driver_group = $(this).val();
        var fk_driver_id = $(this).data("id");
        $.ajax({
            url: '<?= url('admin/fleet/driver/get_group_stores') ?>',
            type: 'GET',
            dataType: 'JSON',
            data: {
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
                console.log(response.data.fk_driver_id);
                $("#collector_driver_group_store_"+response.data.fk_driver_id).html(html);
            }
        })
        })
</script>
@endsection
