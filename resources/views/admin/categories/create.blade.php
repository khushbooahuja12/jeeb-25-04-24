@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add New Category</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/categories'); ?>">Category</a></li>
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
                    <form method="post" id="addForm" class="form-prevent-multiple-submits" enctype="multipart/form-data" action="{{route('admin.categories.store')}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Category Name (En)</label>
                                    <input type="text" name="category_name_en" value="{{old('category_name_en')}}" class="form-control" placeholder="Category Name (Eng)">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Category Name (Ar)</label>
                                    <input type="text" name="category_name_ar" value="{{old('category_name_ar')}}" class="form-control" placeholder="Category Name (Arb)">
                                </div>   
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Category Image</label>
                                    <input type="file" accept="image/*"  name="category_image" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Category Image 2</label>
                                    <input type="file" accept="image/*"  name="category_image2" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light button-prevent-multiple-submits">
                                            Submit
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(1);">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div> <!-- end col -->       
    </div> <!-- end row -->      
</div>
<script>
    $(document).ready(function () {

        $('#addForm').validate({// initialize the plugin
            rules: {
                category_name_en: {
                    required: true
                },
                category_name_ar: {
                    required: true
                },
                category_image: {
                    required: true
                }
            }
        });

    });
</script>
@endsection