@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Classifications
                    <a href="<?= url('admin/classifications/create'); ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Classification</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th width="5%">S.No.</th>
                                <th>ID</th>
                                <th>Classification (Eng)</th>
                                <th>Classification (Arb)</th>
                                <th>Banner Image</th>    
                                <th>Stamp Image</th>    
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($classifications)
                            @foreach($classifications as $key=>$row)
                            <tr>
                                <td width="5%">{{$key+1}}</td>
                                <td>{{$row->id}}</td>
                                <td>{{$row->name_en}}</td>
                                <td>{{$row->name_ar}}</td>
                                <td><img src="{{!empty($row->getBannerImage)?asset('images/classification_images').'/'.$row->getBannerImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                <td><img src="{{!empty($row->getStampImage)?asset('images/classification_images').'/'.$row->getStampImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                <td>
                                    <a title="view details" href="<?= url('admin/classification_detail/' . base64url_encode($row->id)); ?>"><i class="fa fa-eye"></i></a>
                                    <a href="<?= url('admin/classifications/edit/' . base64url_encode($row->id)); ?>"><i class="icon-pencil"></i></a>
                                    <a title="Delete" href="<?= url('admin/categories/destroy_classification/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure you want to delete this classification along with the containing products ?')"><i class="icon-trash-bin"></i></a>
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