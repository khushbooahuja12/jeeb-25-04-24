@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Technical Support</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Technical Support</li>
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
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search..."
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>User</th>
                                    <th>@sortablelink('subject', 'Subject')</th>
                                    <th>@sortablelink('description', 'Description')</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($support)
                                    @foreach ($support as $key => $value)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td><a href="<?= url('admin/users/show/' . base64url_encode($value->fk_user_id)) ?>"
                                                    target="_blank">{{ $value->getUser ? $value->getUser->name : 'N/A' }}</a>
                                            </td>
                                            <td>{{ $value->subject }}</td>
                                            <td style="word-break: break-all">{{ $value->description }}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= 'switchBtn' . $value->id ?>"
                                                            type="checkbox" <?= $value->status == 1 ? 'checked' : '' ?>
                                                            id="<?= $value->id ?>" onchange="openCloseTicket(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <a
                                                    href="<?= url('admin/technical-support/detail/' . base64url_encode($value->id)) ?>"><i
                                                        class="icon-eye"></i>
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
                                    Showing {{ $support->count() }} of {{ $support->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $support->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function openCloseTicket(obj) {
            var id = $(obj).attr("id");

            var checked = $(obj).is(':checked');
            if (checked == true) {
                var is_switched_on = 1;
                var statustext = 'open this ticket';
                var message = 'Ticket opened';
            } else {
                var is_switched_on = 0;
                var statustext = 'Ticket closed';
                var message = 'Ticket closed';
            }

            if (confirm("Are you sure you want to " + statustext + " ?")) {
                $.ajax({
                    url: '<?= url('admin/support/open_close_ticket') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: id,
                        type:'technical_support'
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code === 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + message + ' successfully</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);
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
                if (is_switched_on === 1) {
                    $(".switchBtn" + id).prop('checked', false);
                } else if (is_switched_on === 0) {
                    $(".switchBtn" + id).prop('checked', true);
                }
            }
        }
    </script>
@endsection
