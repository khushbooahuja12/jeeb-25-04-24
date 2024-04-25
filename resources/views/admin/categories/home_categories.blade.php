@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Categories</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/categories'); ?>">Category</a></li>
                    <li class="breadcrumb-item active">List</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">                
                <div class="card-body">
                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>ID</th>
                                <th>Category (Eng)</th>
                                <th>Category (Arb)</th>
                                <th>Image</th>    
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($categories)
                            @foreach($categories as $key=>$row)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$row->id}}</td>
                                <td>{{$row->category_name_en}}</td>
                                <td>{{$row->category_name_ar}}</td>
                                <td><img src="{{!empty($row->getCategoryImage)?asset('images/category_images').'/'.$row->getCategoryImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                <td>
                                    <a title="Remove from home" href="<?= url('admin/categories/remove_from_home/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure you want to remove this category from home ?')"><i class="icon-trash-bin"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection