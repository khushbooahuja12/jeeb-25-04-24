@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add Bundle</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/tag_bundles') ?>">Tag Bundles</a></li>
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
                            enctype="multipart/form-data" action="{{ route('admin.tag_bundles.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Bundle Name (En)</label>
                                        <input type="text" name="name_en" value="{{ old('name_en') }}"
                                            class="form-control" placeholder="Bundle Name (Eng)">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Bundle Name (Ar)</label>
                                        <input type="text" name="name_ar" value="{{ old('name_ar') }}"
                                            class="form-control" placeholder="Bundle Name (Arb)">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <label>Bundle Image</label>
                                    <div class="form-group">
                                        <input type="file" accept="image/*" class="dropify" data-show-remove="false"
                                            id="image" name="image"
                                            data-default-file="" />
                                        <p class="errorPrint" id="imageError"></p>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <label>Bundle Banner Image</label>
                                    <div class="form-group">
                                        <input type="file" accept="image/*" class="dropify" data-show-remove="false"
                                            id="banner_image" name="banner_image"
                                            data-default-file="" />
                                        <p class="errorPrint" id="banner_imageError"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Tags</label>
                                        <select type="text" name="bundle_tags[]" class="form-control select2" multiple>
                                            <option disabled>Select</option>
                                            @foreach ($product_tags as $product_tag)
                                                <option value="{{ $product_tag->id }}">{{ $product_tag->title_en }}</option>
                                            @endforeach
                                            
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-6">
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
                    name_en: {
                        required: true
                    },
                    name_ar: {
                        required: true
                    },
                }
            });

        });
    </script>
@endsection
