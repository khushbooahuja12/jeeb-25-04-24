@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Storekeepers
                        <a href="<?= url('admin/storekeepers/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/storekeepers') ?>">Storekeepers</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Store</label>
                                <select class="form-control" name="fk_store_id" id="fk_store_id">
                                    <option disabled selected>--Select Store--</option>
                                    @if ($stores)
                                        @foreach ( $stores as $store)
                                            <option value="{{ $store->id }}">{{ $store->name }} - {{ $store->company_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <button type="submit" id="assign_storekeeper" class="btn btn-primary"><i class="fa fa-hand-pointer"></i> Assign Storekeeper</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Banner"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('name', 'Name')</th>
                                    <th>@sortablelink('email', 'Email')</th>
                                    <th>@sortablelink('mobile', 'Mobile')</th>
                                    <th>Test Store  keeper</th>
                                    <th>Status</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($storekeepers)
                                    @foreach ($storekeepers as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->email }}</td>
                                            <td>{{ '+' . $row->country_code . ' ' . $row->mobile }}</td>
                                            <td>
                                                @if ($row->is_test_user == 0)
                                                    <p class="text-danger">No</p>
                                                @elseif($row->is_test_user == 1)
                                                    <p class="text-success">Yes</p>
                                                @endif
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input set_test_storekeeper<?= $row->id ?>"
                                                            type="checkbox" value="{{ $row->is_test_user }}"
                                                            <?= $row->is_test_user == 1 ? 'checked' : '' ?> id="<?= $row->id ?>"
                                                            onchange="checkTestStorekeeper(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($row->status == 0)
                                                    <p class="text-danger">Blocked</p>
                                                @elseif($row->status == 1)
                                                    <p class="text-success">Active</p>
                                                @endif
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input set_status<?= $row->id ?>"
                                                            type="checkbox" value="{{ $row->status }}"
                                                            <?= $row->status == 1 ? 'checked' : '' ?> id="<?= $row->id ?>"
                                                            onchange="checkStatus(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?= url('admin/storekeepers/show/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-eye"></i>
                                                </a>&ensp;
                                                <a
                                                    href="<?= url('admin/storekeepers/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i>
                                                </a>&ensp;
                                                <a title="Remove Storekeeper"
                                                    href="<?= url('admin/storekeepers/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to remove this storekeeper ?')"><i
                                                        class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $storekeepers->count() }} of {{ $storekeepers->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $storekeepers->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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

            if (confirm("Are you sure, you want to " + statustext + " this Storekeeper ?")) {
                $.ajax({
                    url: '<?= url('admin/change_status') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        method: 'changeStorekeeperStatus',
                        status: status,
                        id: id
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Status updated successfully</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);

                        $('.driver' + id).find("p").text(response.result.status);
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Some error found</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }
                });
            } else {
                if (status == 1) {
                    $(".set_status" + id).prop('checked', false);
                } else if (status == 0) {
                    $(".set_status" + id).prop('checked', true);
                }
            }
        }
        
        function checkTestStorekeeper(obj) {
            var id = $(obj).attr("id");

            var checked = $(obj).is(':checked');
            if (checked == true) {
                var status = 1;
                var statustext = 'Test';
            } else {
                var status = 0;
                var statustext = 'Normal';
            }

            if (confirm("Are you sure, you want to make this Storekeeper for " + statustext + "  ?")) {
                $.ajax({
                    url: '<?= url('admin/change_status') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        method: 'checkTestStorekeeper',
                        status: status,
                        id: id
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Status updated successfully</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);

                        $('.driver' + id).find("p").text(response.result.status);
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Some error found</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);
                    }
                });
            } else {
                if (status == 1) {
                    $(".set_test_storekeeper" + id).prop('checked', false);
                } else if (status == 0) {
                    $(".set_test_storekeeper" + id).prop('checked', true);
                }
            }
        }

        $('#assign_storekeeper').click(function(){
            var store = $('#fk_store_id').val();
           if(store !=null){
                location.href = 'store/'+store+'/storekeepers';
           }else{
                alert('please select a store')
           }
        })
    </script>
@endsection
