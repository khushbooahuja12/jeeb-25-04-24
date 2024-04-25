@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Hidden Coupon List
                        <a href="<?= url('admin/coupons_hidden/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                        <a href="<?= url('admin/coupons_hidden/create_multiple') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add Multiple
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/coupons_hidden') ?>">Hidden Coupons</a></li>
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
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Coupon"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('coupon_code', 'Coupon Code')</th>
                                    <th>Offer Apply On</th>
                                    <th>@sortablelink('title_en', 'Coupon Title')</th>
                                    <th>Coupon Title (Ar)</th>
                                    <th>Coupon Discount</th>
                                    <th>@sortablelink('expiry_date', 'Expiry Date')</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($coupons)
                                    @foreach ($coupons as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->coupon_code }}</td>
                                            <td>{{ $row->offer_type == 1 ? 'Minimum purchase' : ($row->offer_type == 2 ? 'Category' : 'Brand') }}
                                            </td>
                                            <td>{{ $row->title_en }}</td>
                                            <td>{{ $row->title_ar }}</td>
                                            <td>{{ $row->discount . ($row->type == 1 ? ' %' : '') }}</td>
                                            <td><span
                                                    class="{{ $row->expiry_date >= date('Y-m-d') ? 'text-success' : 'text-danger' }}">{{ date('d M Y', strtotime($row->expiry_date)) }}</span>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <span
                                                        style="color:<?= $row->status == 1 ? 'green' : 'red' ?>"><?= $row->status == 1 ? 'Active' : 'Inactive' ?>
                                                    </span>
                                                    <label class="switch">
                                                        <input type="checkbox" <?= $row->status == 1 ? 'checked' : '' ?>
                                                            onchange="changeCouponStatus(this,<?= $row->id ?>);">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a title="view details"
                                                    href="<?= url('admin/coupons_hidden/show/' . base64url_encode($row->id)) ?>"><i
                                                        class="fa fa-eye"></i></a>&ensp;
                                                @if ($row->expiry_date > date('Y-m-d'))
                                                    <a title="view details"
                                                        href="<?= url('admin/coupons_hidden/edit/' . base64url_encode($row->id)) ?>"><i
                                                            class="fa fa-edit"></i></a>&ensp;
                                                @endif
                                                @if ($row->expiry_date < date('Y-m-d'))
                                                    &nbsp;&nbsp;<a
                                                        href="<?= url('admin/coupons_hidden/recreate/' . base64url_encode($row->id)) ?>"
                                                        class="btn btn-primary btn-sm">recreate</a>
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
                                    Showing {{ $coupons->count() }} of {{ $coupons->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $coupons->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function changeCouponStatus(obj, id) {
            var confirm_chk = confirm('Are you sure to change coupon status?');
            if (confirm_chk) {
                var checked = $(obj).is(':checked');
                if (checked == true) {
                    var status = 1;
                } else {
                    var status = 0;
                }
                if (id) {
                    $.ajax({
                        url: "<?= url('admin/coupons/change_coupon_status') ?>",
                        type: 'post',
                        data: 'id=' + id + '&action=' + status + '&_token=<?= csrf_token() ?>',
                        success: function(data) {
                            if (data.error_code == "200") {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert(data.message);
                            }
                        }
                    });
                } else {
                    alert("Something went wrong");
                }
            } else {
                return false;
            }
        }
    </script>
@endsection
