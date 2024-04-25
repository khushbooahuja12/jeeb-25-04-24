@extends('store.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Driver List
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/drivers') ?>">Drivers</a></li>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Driver"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($drivers)
                                    @foreach ($drivers as $key => $row)
                                        <tr class="hide<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name . ' (' . $row->id . ')' }}</td>
                                            <td>{{ '+' . $row->country_code . ' ' . $row->mobile }}</td>
                                            <td>{{ $row->email }}</td>
                                            <td>
                                                <a title="view details"
                                                    href="<?= url('store/' . $storeId . '/drivers/detail/' . base64url_encode($row->id)) ?>"><i
                                                        class="fa fa-eye"></i>
                                                </a>
                                                <a>
                                                    <button
                                                        class="btn btn-<?= $row->is_available ? 'success' : 'danger' ?> btn-sm"><?= $row->is_available ? 'Available' : 'Occupied' ?>
                                                    </button>
                                                </a>
                                                <a href="javascript:void" onclick="UnassignDriver(<?= $row->id ?>)">
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
        function UnassignDriver(driver_id) {
            if (confirm("Are you sure you want to un-assign this driver?")) {
                $.ajax({
                    url: '<?= url('store/' . $storeId . '/unassign_driver') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        driver_id: driver_id
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

                        $(".hide" + driver_id).remove();
                    } else {
                        alert(response.message);
                    }
                });
            }
        }
    </script>
@endsection
