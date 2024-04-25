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
                        {{-- <div class="row">
                            <div class=" offset-11 col-md-1">
                                <a href="<?= url('admin/stores/edit/' . base64url_encode($store->id)) ?>"
                                    class="btn btn-primary waves-effect waves-light text-white">
                                    Edit
                                </a>
                            </div>
                        </div> --}}
                        @csrf
                        <div class="row">
                            <div class="offset-3 col-md-9 justify-center">
                                <table class="table table-borderless table-responsive">
                                    <tr>
                                        <th>Store Name </th>
                                        <td> {{ $store->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Company </th>
                                        <td> {{ $store->getCompany->name }}</td>
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
                                        <th>Latitude </th>
                                        <td> {{ $store->latitude }}</td>
                                    </tr>
                                    <tr>
                                        <th>Longitute </th>
                                        <td> {{ $store->longitude }}</td>
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
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 justify-center">
                            <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        {{-- <th>Category ID</th> --}}
                                        <th>Category Name</th>
                                        <th>Active Products</th>
                                        <th>All Products</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($categories)
                                        @foreach ($categories as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            {{-- <td>{{ $row->id }}</td> --}}
                                            <td>{{ $row->category_name_en }}</td>
                                            <td>{{ $store->getAllBaseProducts()->where('fk_category_id','=',$row->id)->where('product_store_stock','>',0)->where('deleted',0)->count() }}</td>
                                            <td>{{ $store->getAllBaseProducts()->where('fk_category_id','=',$row->id)->count() }}</td>
                                        </tr>
                                        <tr>
                                            <td>
                                            </td>
                                            <td colspan="3">
                                                @if ($row->getSubCategory)
                                                    <table class="table table-bordered dt-responsive nowrap"
                                                    style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                                        <thead>
                                                            <tr>
                                                                <th>S.No.</th>
                                                                {{-- <th>Category ID</th> --}}
                                                                <th>Subcategory Name</th>
                                                                <th>Active Products</th>
                                                                <th>All Products</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($row->getSubCategory as $key2 => $row2)
                                                                <tr class="vendor<?= $row2->id ?>">
                                                                    <td>{{ $key2 + 1 }}</td>
                                                                    {{-- <td>{{ $row2->id }}</td> --}}
                                                                    <td>{{ $row2->category_name_en }}</td>
                                                                    <td>{{ $store->getAllBaseProducts()->where('fk_sub_category_id','=',$row2->id)->where('product_store_stock','>',0)->where('deleted',0)->count() }}</td>
                                                                    <td>{{ $store->getAllBaseProducts()->where('fk_sub_category_id','=',$row2->id)->count() }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                @endif
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
