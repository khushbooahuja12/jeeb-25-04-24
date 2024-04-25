@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Store Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/stores') ?>">Stores</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="row">
                            <div class=" offset-11 col-md-1">
                                <a href="<?= url('admin/stores/edit/' . base64url_encode($store->id)) ?>"
                                    class="btn btn-primary waves-effect waves-light text-white">
                                    Edit
                                </a>
                            </div>
                        </div>
                        @csrf
                        <div class="row">
                            <div class="offset-3 col-md-9 justify-center">
                                <table class="table table-borderless table-responsive">
                                    <tr>
                                        <th>Store Name </th>
                                        <td> {{ $store->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email </th>
                                        <td> {{ $store->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile </th>
                                        <td> {{ $store->mobile }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address </th>
                                        <td> {{ $store->address }}</td>
                                    </tr>
                                    <tr>
                                        <th>City </th>
                                        <td> {{ $store->city }}</td>
                                    </tr>
                                    <tr>
                                        <th>State </th>
                                        <td> {{ $store->state }}</td>
                                    </tr>
                                    <tr>
                                        <th>Country </th>
                                        <td> {{ $store->country }}</td>
                                    </tr>
                                    <tr>
                                        <th>Pin </th>
                                        <td>{{ $store->pin }}</td>
                                    </tr>
                                    <tr>
                                        <th>Storekeepers </th>
                                        <td>
                                            <?php
                                            if ($store->getStorekeeper) {
                                                $store_arr = [];
                                                foreach ($store->getStorekeeper as $key => $value) {
                                                    array_push($store_arr, $value->name);
                                                }
                                                echo implode(', ', $store_arr);
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function approve_reject_entity(obj) {
            var id = $(obj).attr('id');
            var status = $(obj).attr('value');

            if (status == 2) {
                var statustext = 'approve';
            } else if (status == 4) {
                var statustext = 'reject';
            }
            if (confirm("Are you sure, you want to " + statustext + " this vendor ?")) {
                $.ajax({
                    url: '<?= url('admin/approve_reject_entity') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        type: 'vendor',
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

                        $(obj).parent().parent('tr').remove();
                        $(".upd_status").text(response.result.status);
                        $(".approvetr").show();
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
                if (status == 2) {
                    $(".set_status" + id).prop('checked', false);
                } else if (status == 4) {
                    $(".set_status" + id).prop('checked', true);
                }
            }
        }
    </script>
@endsection
