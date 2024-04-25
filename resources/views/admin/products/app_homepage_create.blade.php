@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Homepage Data</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/app_homepage') ?>">App Homepage</a></li>
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
                        <form method="post" id="addForm" enctype="multipart/form-data"
                            action="{{ route('admin.app_homepage_store') }}">
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
                                        <label>UI Type</label>
                                        <select class="form-control" name="ui_type">
                                            <option disabled selected>Select Type</option>
                                            <option value="1">Full width banner</option>
                                            <option value="2">Product listing layout</option>
                                            <option value="3">Categories</option>
                                            <option value="4">Featured Brands</option>
                                            <option value="5">Extra goodies</option>
                                            <option value="6">Recent orders</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 bannertype" style="display: none">
                                    <div class="form-group">
                                        <label>Banner Type</label>
                                        <select class="form-control" name="banner_type">
                                            <option disabled selected>Select Type</option>
                                            <option value="1">100 pecent width</option>
                                            <option value="2">75 pecent width</option>
                                            <option value="3">50 pecent width</option>
                                            <option value="4">banner 1000 * 300</option>
                                            <option value="5">40 pecent width</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Index</label>
                                        <input type="text" name="index" value="{{ old('index') }}"
                                            class="form-control numericOnly" placeholder="Index">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Background Color</label>
                                        <input type="text" name="background_color" value="{{ old('background_color') }}"
                                            class="form-control" placeholder="#FFF" data-coloris>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Background Image</label>
                                        <input type="file" name="background_image" class="form-control dropify"
                                            accept="image/*">
                                    </div>
                                </div>
                            </div>
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
    </div>
    <script>
        new EmojiPicker({
            trigger: [{
                selector: '.trigger',
                insertInto: ['.demo'] // '.selector' can be used without array
            }, ],
            closeButton: true,
        });

        $('#addForm').validate({
            rules: {
                title: {
                    required: true
                },
                ui_type: {
                    required: true
                },
                banner_type: {
                    required: true
                },
                index: {
                    required: true
                },
                background_color: {
                    required: true
                }
            }
        });

        $("select[name=ui_type]").on('change', function() {
            if ($(this).val() == 1) {
                $(".bannertype").css('display', 'block');
            } else {
                $(".bannertype").css('display', 'none');
            }
        });
    </script>
@endsection
