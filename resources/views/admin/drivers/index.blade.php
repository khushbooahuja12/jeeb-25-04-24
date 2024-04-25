@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Driver List
                        <a href="<?= url('admin/drivers/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/drivers') ?>">Drivers</a></li>
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
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Driver Name</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Vehicle</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($drivers)
                                    @foreach ($drivers as $key => $row)
                                        <tr class="driver<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ '+' . $row->country_code . ' ' . $row->mobile }}</td>
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
                                                <select class="form-control" onchange="update_vehicle(this)"
                                                    vehicle_id="{{ $row->fk_vehicle_id }}" driver_id="{{ $row->id }}">
                                                    <option value="" selected>Select</option>
                                                    @if ($vehicles)
                                                        @foreach ($vehicles as $key => $value)
                                                            <option value="{{ $value->id }}"
                                                                <?= $row->fk_vehicle_id && $row->fk_vehicle_id == $value->id ? 'selected' : '' ?>>
                                                                {{ $value->vehicle_number }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                            <td>
                                                <a title="view details"
                                                    href="<?= url('admin/drivers/show/' . base64url_encode($row->id)) ?>"><i
                                                        class="fa fa-eye"></i>
                                                </a>&ensp;
                                                <a href="<?= url('admin/drivers/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i>
                                                </a>&ensp;
                                                <a title="Remove Driver"
                                                    href="<?= url('admin/drivers/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to remove this driver ?')"><i
                                                        class="fa fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
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

            if (confirm("Are you sure, you want to " + statustext + " this driver ?")) {
                $.ajax({
                    url: '<?= url('admin/change_status') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        method: 'changeDriverStatus',
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

        function update_vehicle(obj) {
            let id = $(obj).val();
            let driver_id = $(obj).attr('driver_id');
            let vehicle_id = $(obj).attr('vehicle_id');

            if (confirm("Are you sure you want to change vehicle?")) {
                $.ajax({
                    url: '<?= url('admin/update_driver_vehicle') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: id,
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
                    } else if (response.status_code == 201) {
                        $(obj).val(vehicle_id);

                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }
                });
            } else {
                $(obj).val(vehicle_id);
            }
        }
    </script>
@endsection
