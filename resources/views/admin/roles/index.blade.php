@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .row_sending_push {
        display: none;
    }
    .row_send_push {
        display: block;
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Roles List 
                    <a href="<?= url('admin/roles/create'); ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/admins'); ?>">roles</a></li>
                    <li class="breadcrumb-item active">List</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body" style="overflow-x: scroll">
                    {{-- <div class="form-group">
                        All Users - {{ $users_count->all_users }}<br/>
                        All English Users - {{ $users_count->english_users }}<br/>
                        All Arabic Users - {{ $users_count->arabic_users }}
                    </div> --}}
                    <form class="form-inline searchForm" method="GET">
                        <div class="form-group mb-2">
                            <input type="text" class="form-control" name="filter" placeholder="Search Roles" value="{{$filter}}">
                        </div>
                        <button type="submit" class="btn btn-dark">Filter</button>
                    </form>
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>@sortablelink('name', 'Role Name')</th>
                                <th>@sortablelink('description', 'Description')</th>    
                                <th>Is active?</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($roles)
                            @foreach($roles as $key=>$row)
                            <tr class="admin<?= $row->id; ?>">
                                <td>{{$key+1}}</td>
                                <td>{{$row->name}}</td>
                                <td>{{$row->description?$row->description:'N/A'}}</td>
                                <td><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="status" id="<?= $row->id ?>" onchange="checkStatus(this)" value="<?= $row->status?>" <?= $row->status == 1 ? 'checked' : ''?>><span class="slider round"></span></label></div></td>
                                <td>
                                    <div style="width: 100px;">
                                        <a title="Edit Role" href="<?= url('admin/roles/edit/' . base64url_encode($row->id)); ?>"><i class="fa fa-pen"></i></a>
                                        &nbsp;
                                        <a title="Remove Role" href="<?= url('admin/roles/delete/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure, you want to completely delete this role?')"><i class="fa fa-trash"></i></a>
                                    </div>  
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$roles->count()}} of {{ $roles->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $roles->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    
    // Role actions
    function checkStatus(obj) {
        var id = $(obj).attr("id");

        var checked = $(obj).is(':checked');
        if (checked == true) {
            var status = 1;
            var statustext = 'activate';
        } else {
            var status = 0;
            var statustext = 'block';
        }

        if (confirm("Are you sure, you want to " + statustext + " this role ?")) {
            $.ajax({
                url: '<?= url('admin/change_status'); ?>',
                type: 'post',
                dataType: 'json',
                data: {method: 'changeRoleStatus', status: status, id: id},
                cache: false,
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Status updated successfully</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);

                    $('.user' + id).find("p").text(response.result.status);
                } else {
                    var error_str = '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Some error found</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        } else {
            if (status == 2) {
                $(".set_status" + id).prop('checked', false);
            } else if (status == 3) {
                $(".set_status" + id).prop('checked', true);
            }
        }
    }
</script>
@endsection