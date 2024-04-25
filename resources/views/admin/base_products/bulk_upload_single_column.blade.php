@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Bulk upload product</h4>
                </div>
                <div class="col-sm-6">

                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="createMultipleForm" enctype="multipart/form-data"
                            action="{{ route('admin.base_products.bulk_upload_single_column_post') }}">
                            @csrf
                            <h5 class="page-title">Base product</h5>
                            <hr/>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select CSV File</label>
                                        <input type="file" name="file" accept=".csv" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Key Column to update</label>
                                        <select name="key" class="form-control">
                                            <option value=""></option>
                                            <option value="unit">unit</option>
                                            <option value="product_name_en">product_name_en</option>
                                            <option value="product_name_ar">product_name_ar</option>
                                            <option value="fk_category_id">fk_category_id</option>
                                            <option value="fk_sub_category_id">fk_sub_category_id</option>
                                            <option value="fk_brand_id">fk_brand_id</option>
                                            <option value="main_tags">main_tags</option>
                                            <option value="_tags">_tags</option>
                                            <option value="custom_tag_bundle">custom_tag_bundle</option>
                                            <option value="country_code">country_code</option>
                                            <option value="country_icon">country_icon</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="createMultipleForm" enctype="multipart/form-data"
                            action="{{ route('admin.base_products.bulk_upload_single_column_store_post') }}">
                            @csrf
                            <h5 class="page-title">Base product store</h5>
                            <hr/>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select CSV File</label>
                                        <input type="file" name="file" accept=".csv" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Key Column to update</label>
                                        <select name="key" class="form-control">
                                            <option value=""></option>
                                            <option value="itemcode">itemcode</option>
                                            <option value="barcode">barcode</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="createMultipleForm" enctype="multipart/form-data"
                            action="{{ route('admin.recipes.bulk_upload_single_column_post') }}">
                            @csrf
                            <h5 class="page-title">Recipe</h5>
                            <hr/>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select CSV File</label>
                                        <input type="file" name="file" accept=".csv" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Key Column to update</label>
                                        <select name="key" class="form-control">
                                            <option value=""></option>
                                            <option value="_tags">_tags</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
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
            $('#createMultipleForm').validate({
                rules: {
                    file: {
                        required: true
                    }
                }
            });
        });
    </script>
@endsection
