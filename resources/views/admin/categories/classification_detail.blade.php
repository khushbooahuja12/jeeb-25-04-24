@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">
                    <?= $classification->parent_id == 0 ? 'Classification Detail' : 'Sub Classification Detail'; ?>
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item">
                        <a href="<?= $classification->parent_id == 0 ? url('admin/classifications') : url('admin/classification_detail') . '/' . base64url_encode($classification->parent_id); ?>">
                            <?= $classification->parent_id == 0 ? 'Classification' : ($classification->getParent ? $classification->getParent->name_en : 'Classification Detail'); ?>
                        </a>
                    </li>
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
                                    <th>Classification Name (En) </th>
                                    <td>{{$classification->name_en}}</td>
                                </tr>
                                <tr>
                                    <th>Classification Name (Ar) </th>
                                    <td>{{$classification->name_ar}}</td>
                                </tr>
                                <tr>
                                    <th>Banner Image</th>
                                    <td><img src="{{!empty($classification->getBannerImage)?asset('images/classification_images').'/'.$classification->getBannerImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                </tr>
                                <tr>
                                    <th>Stamp Image</th>
                                    <td><img src="{{!empty($classification->getBannerImage)?asset('images/classification_images').'/'.$classification->getBannerImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                </tr>                                
                            </table>
                        </div>
                    </div>                    
                </div>
            </div>
        </div>
    </div>
    <div class="ajax_alert"></div>
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Classified Products</h4>
            </div>            
        </div>
    </div>
    <div class="card m-b-30">
        <div class="card-body">
            <div class="row">
                @if($classified_products->count())
                @foreach($classified_products as $key=>$value)
                <div class="col-md-3 product<?= $value->id; ?>">
                    <div class="">
                        <h5 class="card-title font-12 mt-0">
                            {{$value->getProduct->product_name_en}}
                            <a href="javascript:void(0)" onclick="deleteClassified(this,<?= $value->id; ?>)"><i class="fa fa-trash"></i></a>
                        </h5>
                    </div>
                </div>
                @endforeach
                @else
                <div>No product exist!</div>
                @endif
            </div>
        </div>
    </div>
    <?php if ($classification->parent_id == 0): ?>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Sub Classification</h4>
                </div>            
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="add_new_btn" style="margin: 10px 20px 0px 0px;">                   
                        <a href="<?= url('admin/classifications/create_sub_classification/' . base64url_encode($classification->id)); ?>"><button type="button" class="btn btn-primary float-right"><i class="fa fa-plus"></i> Add New</button></a>
                    </div>
                    <div class="card-body">
                        <table id="datatable" class="table table-bordered" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>Classification (Eng)</th>
                                    <th>Classification (Arb)</th>
                                    <th>Banner Image</th>    
                                    <th>Stamp Image</th>    
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($classification->getSubClassification->where('deleted','=',0))
                                @foreach($classification->getSubClassification->where('deleted','=',0) as $key=>$row)
                                <tr>
                                    <td>{{$row->name_en}}</td>
                                    <td>{{$row->name_ar}}</td>
                                    <td><img src="{{!empty($row->getBannerImage)?asset('images/classification_images').'/'.$row->getBannerImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                    <td><img src="{{!empty($row->getStampImage)?asset('images/classification_images').'/'.$row->getStampImage->file_name:asset('assets/images/no_img.png')}}" width="75" height="50"></td>
                                    <td>
                                        <a title="view details" href="<?= url('admin/classification_detail/' . base64url_encode($row->id)); ?>"><i class="fa fa-eye"></i></a>
                                        <a href="<?= url('admin/classifications/edit_sub_classification/' . base64url_encode($row->id)); ?>"><i class="icon-pencil"></i></a>
                                        <a title="Delete" href="<?= url('admin/categories/destroy_classification/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure you want to delete this sub classification ?')"><i class="icon-trash-bin"></i></a>
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
    <?php endif; ?>
</div>
<script>
    function deleteClassified(obj, id) {
        if (confirm("Are you sure you want to remove this product?")) {
            $.ajax({
                url: '<?= url('admin/classifications/deleteClassified'); ?>',
                type: 'post',
                dataType: 'json',
                data: {id: id},
                cache: false
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>' + response.message + '</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);
                    $(".product" + id).remove();
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
</script>
@endsection