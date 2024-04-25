@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add New News</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/news'); ?>">News</a></li>
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
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.news.store')}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" value="{{old('title')}}" class="form-control" placeholder="Title">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label>Image</label>
                                    <input type="file" accept="image/*"  name="image" class="form-control dropify">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea type="text" name="description" rows='5' cols="10" value="{{old('description')}}" class="form-control" placeholder="Description"></textarea>
                                </div>  
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
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
    $(document).ready(function () {

        $('#addForm').validate({
            rules: {
                title: {
                    required: true
                },
                image: {
                    required: true
                },
                description: {
                    required: true
                }
            }
        });

    });
</script>
@endsection