@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Section</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/app_homepage') ?>"><?= $homepage->title ?></a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
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
                            action="{{ route('admin.app_homepage_update', [base64url_encode($homepage->id)]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="title"
                                            value="{{ old('title') ? old('title') : $homepage->title }}"
                                            class="form-control demo" placeholder="Title">
                                        <button type="button" class="btn btn-primary btn-sm trigger">Emoji</button>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>UI Type</label>
                                        <select class="form-control" name="ui_type">
                                            <option disabled selected>Select Type</option>
                                            <option value="1"<?= $homepage->ui_type == 1 ? 'selected' : '' ?>>Full
                                                width banner</option>
                                            <option value="2"<?= $homepage->ui_type == 2 ? 'selected' : '' ?>>Product
                                                listing layout</option>
                                            <option value="3"<?= $homepage->ui_type == 3 ? 'selected' : '' ?>>
                                                Categories</option>
                                            <option value="4"<?= $homepage->ui_type == 4 ? 'selected' : '' ?>>Featured
                                                Brands</option>
                                            <option value="5"<?= $homepage->ui_type == 5 ? 'selected' : '' ?>>Extra
                                                goodies</option>
                                            <option value="6"<?= $homepage->ui_type == 6 ? 'selected' : '' ?>>Recent
                                                Orders</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4 bannertype"
                                    style="display: <?= $homepage->ui_type == 1 ? 'block' : 'none' ?>">
                                    <div class="form-group">
                                        <label>Banner Type</label>
                                        <select class="form-control" name="banner_type">
                                            <option disabled selected>Select Type</option>
                                            <option value="1"<?= $homepage->banner_type == 1 ? 'selected' : '' ?>>100
                                                pecent width</option>
                                            <option value="2"<?= $homepage->banner_type == 2 ? 'selected' : '' ?>>75
                                                pecent width</option>
                                            <option value="3"<?= $homepage->banner_type == 3 ? 'selected' : '' ?>>50
                                                pecent width</option>
                                            <option value="4"<?= $homepage->banner_type == 4 ? 'selected' : '' ?>>
                                                banner 1000 * 300</option>
                                            <option value="5"<?= $homepage->banner_type == 5 ? 'selected' : '' ?>>40
                                                pecent width</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Index</label>
                                        <input type="text" name="index"
                                            value="{{ old('index') ? old('index') : $homepage->index }}"
                                            class="form-control numericOnly" placeholder="Index">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Background Color</label>
                                        <input type="text" name="background_color"
                                            value="{{ old('background_color') ? old('background_color') : $homepage->background_color }}"
                                            class="form-control" placeholder="#FFF" data-coloris>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Background Image</label>
                                        <input type="file" name="background_image" data-show-remove="false"
                                            data-default-file="{{ !empty($homepage->getBackgroundImage) ? asset('/') . $homepage->getBackgroundImage->file_path . $homepage->getBackgroundImage->file_name : asset('assets/images/no_img.png') }}"
                                            class="form-control dropify" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div>
                                            <button type="submit" class="btn btn-success waves-effect waves-light">
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
