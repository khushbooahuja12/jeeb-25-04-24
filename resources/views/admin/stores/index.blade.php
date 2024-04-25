@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">All Stores
                        <a href="<?= url('admin/stores/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/stores') ?>">Stores</a></li>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Store Name"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>@sortablelink('name', 'Store Name')</th>
                                    <th>Company Name</th>
                                    {{-- <th>@sortablelink('email', 'Email')</th>
                                    <th>@sortablelink('mobile', 'Mobile')</th> --}}
                                    <th>Address</th>
                                    {{-- <th>All Products</th>
                                    <th>Active Products</th>
                                    <th>All Recipes</th>
                                    <th>Active Recipes</th> --}}
                                    <th>Back Margin (%)</th>
                                    <th>Status (Active)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->getCompany ? $row->getCompany->name : '' }}</td>
                                            {{-- <td>{{ $row->email }}</td>
                                            <td>{{ $row->mobile }}</td> --}}
                                            <td>{{ $row->address }}</td>
                                            {{-- <td>{{ $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->where('base_products.product_type','=','product')->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProducts() ? $row->getAllBaseProducts()->join('base_products_store', 'base_products.id', '=', 'base_products_store.fk_product_id')->where('base_products.deleted',0)->where('base_products_store.deleted',0)->where('base_products.product_type','=','product')->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProductsStore() ? $row->getAllBaseProductsStore()->join('base_products', 'base_products_store.fk_product_id','=','base_products.id')->whereIn('base_products.product_type',['recipe','pantry_item'])->count() : '' }}</td>
                                            <td>{{ $row->getAllBaseProducts() ? $row->getAllBaseProducts()->join('base_products_store', 'base_products.id', '=', 'base_products_store.fk_product_id')->where('base_products.deleted',0)->where('base_products_store.deleted',0)->whereIn('base_products.product_type',['recipe','pantry_item'])->count() : '' }}</td> --}}
                                            <td>{{ $row->back_margin }}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input set_status<?= $row->id ?>"
                                                            type="checkbox" value="{{ $row->status }}"
                                                            <?= $row->status == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkStatus(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                                <br/>
                                                <!-- Disable / endable the products from the store -->
                                                <form method="POST" action="{{ route('admin.stores.activate_all_store_products') }}">
                                                    @csrf
                                                    <input type="hidden" name="store_id" value="{{ $row->id }}"/>
                                                    <button type="submit" name="base_products_store_activate" value="base_products_store_activate" class="btn btn-primary" style="width: 100%;">Activate all products</button>
                                                    <br/><br/>
                                                    <button type="submit" name="base_products_store_deactivate" value="base_products_store_deactivate" class="btn btn-danger" style="width: 100%;">Deactivate all products</button>
                                                </form>
                                                <br/>
                                                <button onclick="getProductStatus({{ $row->id }})" type="button" name="base_products_store_status" value="base_products_store_status" class="btn btn-secondary" style="width: 100%;" data-id="{{ $row->id }}">Check products status</button>
                                                <br/><br/>
                                                <div id="products_status_{{ $row->id }}"></div>
                                                <hr/>
                                                <a href="<?= url('admin/fleet/stores/schedules/' .$row->id) ?>" class="btn btn-success" style="width: 100%;">Edit Schedule</a>
                                            </td>
                                            <td>
                                                <a title="edit"
                                                    href="<?= url('admin/stores/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i>
                                                </a>&ensp;
                                                <a title="view details"
                                                    href="<?= url('admin/stores/' . $row->id . '/categories_bp') ?>"><i
                                                        class="fa fa-eye"></i>
                                                </a>&ensp;                                                
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $stores->count() }} of {{ $stores->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $stores->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function getProductStatus(rid) {
            $('#products_status_'+rid).html("Loading");
            $.ajax({
                url: '/admin/stores/get_base_products_store_status',
                type: 'GET',
                data: {store_id:rid},
                dataType: 'JSON',
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend:function(){
                    $('#products_status_'+rid).addClass('pm-loading-shimmer');
                },
                complete:function(response) {
                    console.log(response.responseJSON);
                    $('#products_status_'+rid).removeClass('pm-loading-shimmer');
                    $('#products_status_'+rid).html(
                        "Activated:"+response.responseJSON.products_active+", Deactived:"+response.responseJSON.products_inactive
                        );
                }
            });
        }
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

            if (confirm("Are you sure, you want to " + statustext + " this vendor ?")) {
                $.ajax({
                    url: '<?= url('admin/change_status') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        method: 'changeStoreStatus',
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
        }
    </script>
@endsection
