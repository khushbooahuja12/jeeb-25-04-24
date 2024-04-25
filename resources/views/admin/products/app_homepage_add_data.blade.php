@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add Items into '<?= $homepage->title; ?>'</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/app_homepage') ?>"><?= $homepage->title; ?></a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        @if ($homepage->ui_type == 1)
            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-b-30">
                        <div class="card-body">
                            <form method="post" id="addForm" enctype="multipart/form-data"
                                action="{{ route('admin.app_homepage_store_data') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="text" name="title" value="{{ old('title') }}"
                                                class="form-control demo" placeholder="Title">
                                            <button type="button" class="btn btn-primary btn-sm trigger">Emoji</button>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Banner Image</label>
                                            <input type="file" name="image" class="form-control dropify" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Banner Image (Inside)</label>
                                            <input type="file" name="image2" class="form-control dropify" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <label>Redirection Type</label>
                                            <select class="form-control" name="redirection_type">
                                                <option disabled selected>Select Type</option>
                                                <option value="1">Search</option>
                                                <option value="2">Brands</option>
                                                <option value="3">Offers</option>
                                                <option value="4">Products</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group keywordfield" style="display: none">
                                            <label>Keyword</label>
                                            <input type="text" name="keyword" value="{{ old('keyword') }}"
                                                class="form-control" placeholder="Keyword">
                                        </div>
                                        <div class="form-group brandfield" style="display: none">
                                            <label>Brands</label><br>
                                            <select name="brand_id" class="form-control select2">
                                                <option value="">Select Brand</option>
                                                @if ($brands)
                                                    @foreach ($brands as $row)
                                                        <option value="{{ $row->id }}">{{ $row->brand_name_en }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <p class="errorPrint" id="fk_brand_idError"></p>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="homepage_id" value="{{ $id }}">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="form-group">
                                            <div>
                                                <button type="submit" class="btn btn-success waves-effect waves-light">
                                                    Submit
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-md-12">
                    <div class="card m-b-30">
                        <div class="card-body">
                            <form class="form-inline searchForm" method="GET">
                                <div class="form-group mb-2">
                                    <input type="text" class="form-control" name="filter"
                                        placeholder="Search Product Name" value="{{ $filter }}">
                                </div>
                                <button type="submit" class="btn btn-dark">Filter</button>
                            </form>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Category</th>
                                        <th>@sortablelink('product_name_en', 'Product Name')</th>
                                        <th>Quantity </th>
                                        <th class="nosort">Product Image </th>
                                        <th class="nosort">Add/Remove</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($products)
                                        @foreach ($products as $key => $row)
                                            <tr class="product<?= $row->id ?>">
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ !empty($row->getProductCategory) ? $row->getProductCategory->category_name_en : 'N/A' }}
                                                </td>
                                                <td><a
                                                        href="<?= url('admin/products/show/' . base64url_encode($row->id)) ?>">{{ $row->product_name_en }}</a>
                                                </td>
                                                <td>{{ $row->unit }}</td>
                                                <td>
                                                    <a href="javascript:void(0)" fk_product_id="{{ $row->id }}">
                                                        <img src="{{ !empty($row->product_image_url) ? $row->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                            width="75" height="50">
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="mytoggle">
                                                        <label class="switch">
                                                            <input class="switch-input <?= $row->id . 'popular' ?>"
                                                                type="checkbox"
                                                                <?= !empty($row->existOnHomepage) && $row->existOnHomepage->fk_homepage_id == $id ? 'checked' : '' ?>
                                                                id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                            <span class="slider round"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                        Showing {{ $products->count() }} of {{ $products->count() }} entries.
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate" style="float:right">
                                        {{ $products->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script>
        $("select[name=redirection_type]").on('change', function() {
            if ($(this).val() == 1 || $(this).val() == 4) {
                $(".brandfield").hide();
                $(".keywordfield").show();
            } else if ($(this).val() == 2) {
                $(".brandfield").show();
                $(".keywordfield").hide();
            } else {
                $(".brandfield").hide();
                $(".keywordfield").hide();
            }
        });

        $('#addForm').validate({
            rules: {
                image: {
                    required: true
                },
                redirection_type: {
                    required: true
                }
            }
        });

        function checkHomeType(obj) {
            var id = $(obj).attr("id");

            var checked = $(obj).is(':checked');
            if (checked == true) {
                var is_switched_on = 1;
                var statustext = 'move this to home screen';
                var message = 'Product moved to home';
            } else {
                var is_switched_on = 0;
                var statustext = 'remove from home screen';
                var message = 'Product removed from home';
            }

            $.ajax({
                url: '<?= url('admin/app_homepage_add_remove_item') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    product_id: id,
                    is_switched_on: is_switched_on,
                    homepage_id: '<?= $id ?>'
                },
                cache: false,
            }).done(function(response) {
                if (response.status_code === 200) {
                    // var success_str =
                    //     '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                    //     '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                    //     '<strong>' + response.message + '</strong>.' +
                    //     '</div>';
                    // $(".ajax_alert").html(success_str);
                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        }
    </script>
@endsection
