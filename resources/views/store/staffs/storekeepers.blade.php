@extends('store.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Storekeepers</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="<?= url('store/' . $storeId . '/storekeepers') ?>">Storekeepers</a>
                        </li>
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
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Storekeeper"
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
                                    <th>@sortablelink('address', 'Address')</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($storekeepers)
                                    @foreach ($storekeepers as $key => $row)
                                        <tr class="hide<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name . ' (' . $row->id . ')' }}</td>
                                            <td>{{ $row->email }}</td>
                                            <td>{{ '+' . $row->country_code . ' ' . $row->mobile }}</td>
                                            <td>{{ $row->address }}</td>
                                            <td>
                                                <a
                                                    href="<?= url('store/' . $storeId . '/storekeepers/detail/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-eye"></i>
                                                </a>&ensp;
                                                <a>
                                                    <button
                                                        class="btn btn-<?= $row->is_available ? 'success' : 'danger' ?> btn-sm"><?= $row->is_available ? 'Available' : 'Occupied' ?>
                                                    </button>
                                                </a>
                                                <a href="javascript:void" onclick="UnassignStorekeeper(<?= $row->id ?>)">
                                                    <button class="btn btn-danger btn-sm"
                                                        <?= $row->is_available ? '' : 'disabled' ?>
                                                        style="cursor:<?= $row->is_available ? 'pointer' : 'not-allowed' ?>">Unassign
                                                    </button>
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
        function UnassignStorekeeper(storekeeper_id) {
            if (confirm("Are you sure you want to un-assign this storekeeper?")) {
                $.ajax({
                    url: '<?= url('store/' . $storeId . '/unassign_storekeeper') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        storekeeper_id: storekeeper_id
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);

                        $(".hide" + storekeeper_id).remove();
                    } else {
                        alert(response.message);
                    }
                });
            }
        }
    </script>
@endsection
