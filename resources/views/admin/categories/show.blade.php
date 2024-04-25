@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Category Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/categories'); ?>">Categories</a></li>
                    <li class="breadcrumb-item active">Detail</li>
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
                    <div class="row">
                        <div class="col-md-6 mt-3">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Category Name (En) </th>
                                    <td>{{$category->category_name_en}}</td>
                                </tr>
                                <tr>
                                    <th>Category Name (Ar) </th>
                                    <td>{{$category->category_name_ar}}</td>
                                </tr>
                                <tr>
                                    <th>Category Image</th>
                                    <td><img src="{{!empty($category->getCategoryImage)?asset('images/category_images').'/'.$category->getCategoryImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                </tr>  
                                <tr>
                                    <th>Category Image 2</th>
                                    <td><img src="{{!empty($category->getCategoryImage2)?asset('images/category_images').'/'.$category->getCategoryImage2->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                </tr>                                
                            </table>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Sub Categories</h4>
            </div>            
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="add_new_btn" style="margin: 10px 20px 0px 0px;">                   
                    <a href="<?= url('admin/categories/create_sub_category/' . base64url_encode($category->id)); ?>"><button type="button" class="btn btn-primary float-right"><i class="fa fa-plus"></i> Add New</button></a>
                </div>
                <div class="card-body">
                    <table id="datatable" class="table table-bordered" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>ID</th>
                                <th>Category (Eng)</th>
                                <th>Category (Arb)</th>
                                <th class="nosort">Image</th>    
                                <th class="nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($category->getSubCategory->where('deleted','=',0))
                            @foreach($category->getSubCategory->where('deleted','=',0) as $key=>$row)
                            <tr>
                                <td>{{$key++}}</td>
                                <td>{{$row->id}}</td>
                                <td>{{$row->category_name_en}}</td>
                                <td>{{$row->category_name_ar}}</td>
                                <td><img src="{{!empty($row->getCategoryImage)?asset('images/category_images').'/'.$row->getCategoryImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                <td>
                                    <a href="<?= url('admin/categories/edit_sub_category/' . base64url_encode($row->id)); ?>"><i class="icon-pencil"></i></a>&ensp;
                                    <a title="Delete" href="<?= url('admin/categories/destroy/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure you want to delete this sub category ?')"><i class="icon-trash-bin"></i></a>
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
<script>
    function delete_product(obj, product_id) {
        if (confirm("Are you sure you want to delete this product?")) {
            $.ajax({
                url: '<?= url('admin/products/delete_product'); ?>',
                type: 'post',
                dataType: 'json',
                data: {product_id: product_id},
                cache: false
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + response.message + '</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);
                    $(".product" + product_id).remove();
                } else {
                    var error_str = '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + response.message + '</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        }
    }

    function changeProductStatus(obj, id) {
        var confirm_chk = confirm('Are you sure to change product status?');
        if (confirm_chk) {
            var checked = $(obj).is(':checked');
            if (checked == true) {
                var status = 1;
            } else {
                var status = 0;
            }
            if (id) {
                $.ajax({
                    url: "<?= url('admin/products/change_product_status') ?>",
                    type: 'post',
                    data: 'id=' + id + '&action=' + status + '&_token=<?= csrf_token() ?>',
                    success: function (data) {
                        if (data.error_code == "200") {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert(data.message);
                        }
                    }
                });
            } else {
                alert("Something Wrong");
            }
        } else {
            return false;
        }
    }
</script>
@endsection