@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Category</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/recipe_categories') ?>">Recipe Categories</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="addForm" enctype="multipart/form-data"
                            action="{{ route('admin.recipe_categories.update', [base64url_encode($category->id)]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Category Name (En)</label>
                                        <input type="text" name="name_en" value="{{ $category->name_en }}"
                                            class="form-control" placeholder="Category Name (Eng)">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Category Name (Ar)</label>
                                        <input type="text" name="name_ar" value="{{ $category->name_ar }}"
                                            class="form-control" placeholder="Category Name (Arb)">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Category Image</label>
                                        @php
                                            $recipe_cat_image_url = $category->image ? $category->image : asset('assets/images/dummy-product-image.jpg')
                                        @endphp
                                        <input type="file" name="recipe_cat_img" class="form-control dropify"
                                            data-default-file="{{ $recipe_cat_image_url }}"
                                            accept="image/*">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Tag</label>
                                        <input type="text" name="tag" value="{{ $category->tag }}"
                                            class="form-control" placeholder="Tag">
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div>
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                Update
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
    </div>
    <script>
        $(document).ready(function() {

            $('#addForm').validate({
                rules: {
                    name_en: {
                        required: true
                    },
                    name_ar: {
                        required: true
                    }
                }
            });

        });
    </script>
@endsection
