@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Banner Type</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="<?= url('admin/app_homepage_detail/' . base64url_encode($homepage_data->fk_homepage_id)) ?>">Banner
                                Image</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                        <form method="post" id="editForm" enctype="multipart/form-data"
                            action="{{ route('admin.app_homepage_update_data', [base64url_encode($homepage_data->id)]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="title"
                                            value="{{ old('title') ? old('title') : $homepage_data->title }}"
                                            class="form-control demo" placeholder="Title">
                                        <button type="button" class="btn btn-primary btn-sm trigger">Emoji</button>
                                    </div>
                                </div>
                                <div class="col-lg-4"> 
                                    <div class="form-group">
                                        <label>Banner Image</label>
                                        <input type="file" name="image"
                                            data-default-file="{{ !empty($homepage_data->getImage) ? asset('/') . $homepage_data->getImage->file_path . $homepage_data->getImage->file_name : asset('assets/images/no_img.png') }}"
                                            class="form-control dropify" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Banner Image (Inside)</label>
                                        <input type="file" name="image2"
                                            data-default-file="{{ !empty($homepage_data->getImage2) ? asset('/') . $homepage_data->getImage2->file_path . $homepage_data->getImage2->file_name : asset('assets/images/no_img.png') }}"
                                            class="form-control dropify" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Redirection Type</label>
                                        <select class="form-control" name="redirection_type">
                                            <option disabled selected>Select Type</option>
                                            <option
                                                value="1"<?= $homepage_data->redirection_type == 1 ? 'selected' : '' ?>>
                                                Search</option>
                                            <option
                                                value="2"<?= $homepage_data->redirection_type == 2 ? 'selected' : '' ?>>
                                                Brands</option>
                                            <option
                                                value="3"<?= $homepage_data->redirection_type == 3 ? 'selected' : '' ?>>
                                                Offers</option>
                                            <option
                                                value="4"<?= $homepage_data->redirection_type == 4 ? 'selected' : '' ?>>
                                                Products</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group keywordfield"
                                        style="display: <?= $homepage_data->redirection_type == 1 || $homepage_data->redirection_type == 4 ? 'block' : 'none' ?>">
                                        <label>Keyword</label>
                                        <input type="text" name="keyword"
                                            value="{{ old('keyword') ? old('keyword') : $homepage_data->keyword }}"
                                            class="form-control" placeholder="Keyword">
                                    </div>
                                    <div class="form-group brandfield"
                                        style="display: <?= $homepage_data->redirection_type == 2 ? 'block' : 'none' ?>">
                                        <label>Brands</label><br>
                                        <select name="brand_id" class="form-control select2">
                                            <option value="">Select Brand</option>
                                            @if ($brands)
                                                @foreach ($brands as $row)
                                                    <option
                                                        value="{{ $row->id }}"<?= $brand_id == $row->id ? 'selected' : '' ?>>
                                                        {{ $row->brand_name_en }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <p class="errorPrint" id="fk_brand_idError"></p>
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

        $('#editForm').validate({
            rules: {
                redirection_type: {
                    required: true
                }
            }
        });
    </script>
@endsection
