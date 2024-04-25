@extends('admin.layouts.dashboard_layout_for_product_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Tag</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/product_tags') ?>">Product Tags</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="addForm" class="form-prevent-multiple-submits"
                            enctype="multipart/form-data" action="{{ route('admin.product_tags.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Tag Title (En)</label>
                                        <input type="text" name="title_en" value="{{ old('title_en') }}"
                                            class="form-control" placeholder="Tag Title (Eng)">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Tag Title (Ar)</label>
                                        <input type="text" name="title_ar" value="{{ old('title_ar') }}"
                                            class="form-control" placeholder="Tag Title (Arb)">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label>Tag Image</label>
                                    <div class="form-group">
                                        <input type="file" accept="image/*" class="dropify" data-show-remove="false"
                                            id="image" name="image"
                                            data-default-file="" />
                                        <p class="errorPrint" id="imageError"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label>Tag Banner Image</label>
                                    <div class="form-group">
                                        <input type="file" accept="image/*" class="dropify" data-show-remove="false"
                                            id="banner_image" name="banner_image"
                                            data-default-file="" />
                                        <p class="errorPrint" id="banner_imageError"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="form-group">
                                        <label>Tags Bundle</label>
                                        <select name="fk_tag_bundle_id" data-title="Tags Bundle" class="form-control col-md-3 select2" style="width: 100%">
                                            <option value="">Select Tags Bundle</option>
                                            @foreach ($tag_bundles as $tag_bundle)
                                                <option value="{{ $tag_bundle->id }}">{{ $tag_bundle->name_en }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div>
                                            <button type="submit"
                                                class="btn btn-primary waves-effect waves-light button-prevent-multiple-submits">
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
    </div>
    <script>
        $(document).ready(function() {
            $('#addForm').validate({
                rules: {
                    title_en: {
                        required: true
                    },
                    title_en: {
                        required: true
                    }
                }
            });

        });
    </script>
@endsection
