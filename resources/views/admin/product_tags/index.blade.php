@extends('admin.layouts.dashboard_layout_for_product_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Product Tags
                        <a href="<?= url('admin/product_tags/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Product Tags</li>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Tag"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th width="5%">S.No.</th>
                                    <th>Tag Title (Eng)</th>
                                    <th>Tag Title (Arb)</th>
                                    <th>Tag</th>
                                    <th>Tag Image</th>
                                    <th>Banner Image</th>
                                    <th>Is main tag?</th>
                                    <th>Tag Bundle</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($tags)
                                    @foreach ($tags as $key => $row)
                                        <tr>
                                            <td width="5%">{{ isset($_GET) && isset($_GET['page']) ? $_GET['page']*100+$key + 1 : $key + 1 }}</td>
                                            <td>{{ $row->title_en }}</td>
                                            <td>{{ $row->title_ar }}</td>
                                            <td>{{ $row->tag }}</td>
                                            <td>{!! $row->tag_image ? '<img src="'.$row->tag_image.'" style="height: 50px; width: auto; max-width: 100px;"/>' : 'N/A' !!}</td>
                                            <td>{!! $row->banner_image ? '<img src="'.$row->banner_image.'" style="height: 50px; width: auto; max-width: 100px;"/>' : 'N/A' !!}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input set_is_main_tag<?= $row->id ?>"
                                                            type="checkbox" value="{{ $row->is_main_tag }}"
                                                            <?= $row->is_main_tag == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="return checkStatus(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>{{ $row->getTagBundle ? $row->getTagBundle->name_en : '' }}</td>
                                            <td>                                                
                                                <a href="<?= url('admin/product_tags/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                <a title="Delete"
                                                    href="<?= url('admin/product_tags/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to delete this tag ?')"><i
                                                        class="icon-trash-bin"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $tags->count() }} of {{ $tags->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $tags->links() }}
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
            } else {
                var status = 0;
            }
            var return_status = confirm("Are you sure, you want to change the main_tag status of this tag?");
            if (return_status) {
                $.ajax({
                    url: '<?= url('admin/change_status') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        method: 'changeProductTagStatus',
                        status: status,
                        id: id
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Main tag status updated successfully</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);

                        $('.vendor' + id).find("p").text(response.result.status);
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
                if (status == 0) {
                    $(".set_status" + id).prop('checked', true);
                } else {
                    $(".set_status" + id).prop('checked', false);
                }
            }

            return return_status;

        }
    </script>
@endsection
