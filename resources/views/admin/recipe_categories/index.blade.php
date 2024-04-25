@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Recipe Categories
                        <a href="<?= url('admin/recipe_categories/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Recipe Categories</li>
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
                                    <th width="5%">S.No.</th>
                                    <th>Category (Eng)</th>
                                    <th>Category (Arb)</th>
                                    <th>Tag</th>
                                    <th>Image</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($categories)
                                    @foreach ($categories as $key => $row)
                                        <tr>
                                            <td width="5%">{{ $key + 1 }}</td>
                                            <td>{{ $row->name_en }}</td>
                                            <td>{{ $row->name_ar }}</td>
                                            <td>{{ $row->tag }}</td>
                                            <td>
                                            @php
                                                $recipe_cat_image_url = $row->image ? $row->image : asset('assets/images/dummy-product-image.jpg')
                                            @endphp
                                            <a href="javascript:void(0)" data-src="{{ $recipe_cat_image_url }}" fk_product_id="{{ $row->id }}"
                                                onclick="openImageModal(this)" style="max-width: 90%; height: 110px; display: block; background: #ffffff; border: 1px #dee2e6 solid; text-align: center;">
                                                <img class="product_image_change_{{ $row->id }}" src="{{ $recipe_cat_image_url }}" height="100">
                                            </a>
                                        </td>
                                            <td>                                                
                                                <a href="<?= url('admin/recipe_categories/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                <a title="Delete"
                                                    href="<?= url('admin/recipe_categories/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to delete this category ?')"><i
                                                        class="icon-trash-bin"></i></a>
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
        function openImageModal(obj) {
            let product_id = $(obj).attr('fk_product_id');
            let product_image_url = $(obj).data('src');
            $('.product_image').prop('src', product_image_url);
            $(".imageModal").modal('show');
        }
    </script>
@endsection
