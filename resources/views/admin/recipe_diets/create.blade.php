@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Diet</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/recipe_diets') ?>">Recipe Diets</a></li>
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
                            enctype="multipart/form-data" action="{{ route('admin.recipe_diets.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Diet Title (En)</label>
                                        <input type="text" name="title_en" value="{{ old('title_en') }}"
                                            class="form-control" placeholder="Diet Title (Eng)">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Diet Title (Ar)</label>
                                        <input type="text" name="title_ar" value="{{ old('title_ar') }}"
                                            class="form-control" placeholder="Diet Title (Arb)">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Tag</label>
                                        <input type="text" name="tag" value="{{ old('tag') }}"
                                            class="form-control" placeholder="Tag">
                                    </div>
                                </div>
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
