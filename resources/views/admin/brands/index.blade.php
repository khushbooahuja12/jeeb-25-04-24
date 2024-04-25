@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Brands 
                    <a href="<?= url('admin/brands/create'); ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add New
                    </a>
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/brands'); ?>">Brands</a></li>
                    <li class="breadcrumb-item active">List</li>
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
                    <form class="form-inline searchForm" method="GET">
                        <div class="form-group mb-2">
                            <input type="text" class="form-control" name="filter" placeholder="Search Brand Name" value="{{$filter}}">
                        </div>
                        <button type="submit" class="btn btn-dark">Filter</button>
                    </form>
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>@sortablelink('id', 'ID')</th>
                                <th>@sortablelink('brand_name_en', 'Brand (Eng)')</th>
                                <th>@sortablelink('brand_name_ar', 'Brand (Arb)')</th>
                                <th class="nosort">Image</th>
                                <th class="nosort">Image 2</th>    
                                <th class="nosort">Action</th>
                                <th class="nosort">Home</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($brands)
                            @foreach($brands as $key=>$row)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$row->id}}</td>
                                <td>{{$row->brand_name_en}}</td>
                                <td>{{$row->brand_name_ar}}</td>
                                <td><img src="{{!empty($row->getBrandImage)?asset('images/brand_images').'/'.$row->getBrandImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                <td><img src="{{!empty($row->getBrandImage2)?asset('images/brand_images').'/'.$row->getBrandImage2->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                <td>
                                    <a title="view details" href="<?= url('admin/brands/show/' . base64url_encode($row->id)); ?>"><i class="fa fa-eye"></i></a>&ensp;
                                    <a href="<?= url('admin/brands/edit/' . base64url_encode($row->id)); ?>"><i class="icon-pencil"></i></a>&ensp;
                                    <a title="Delete" href="<?= url('admin/brands/destroy/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure you want to delete this brand ?')"><i class="icon-trash-bin"></i></a>
                                </td>
                                <td>                                    
                                    <div class="mytoggle">
                                        <label class="switch">
                                            <input class="switch-input set_status<?= $row->id; ?>" type="checkbox" value="{{$row->is_home_screen}}" <?= $row->is_home_screen == 1 ? 'checked' : ''; ?> 
                                                   id="<?= $row->id; ?>" 
                                                   onchange="checkHome(this)">
                                            <span class="slider round"></span> 
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$brands->count()}} of {{ $brands->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $brands->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function checkHome(obj) {
        var id = $(obj).attr("id");

        var checked = $(obj).is(':checked');
        if (checked == true) {
            var is_home_screen = 1;
            var statustext = 'move this to home screen';
            var message = 'Brand moved to home';
        } else {
            var is_home_screen = 0;
            var statustext = 'remove from home screen';
            var message = 'Brand removed from home';
        }

        if (confirm("Are you sure you want to " + statustext + "?")) {
            $.ajax({
                url: '<?= url('admin/brands/add_remove_home'); ?>',
                type: 'post',
                dataType: 'json',
                data: {is_home_screen: is_home_screen, id: id},
                cache: false,
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + message + ' successfully</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);
                } else {
                    var error_str = '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Some error found</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        } else {
            if (is_home_screen == 1) {
                $(".set_status" + id).prop('checked', false);
            } else if (is_home_screen == 0) {
                $(".set_status" + id).prop('checked', true);
            }
        }
    }
</script>
@endsection