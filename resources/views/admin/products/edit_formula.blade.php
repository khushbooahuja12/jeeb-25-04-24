@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Price Formula</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/products/price-formula'); ?>">Price Formula</a></li>
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
                    <form method="post" id="updateForm" enctype="multipart/form-data" action="{{route('admin.products.update_price_formula',[base64url_encode($formula->id)])}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X1</label>
                                    <input type="text" name="x1" value="{{$formula->x1}}" class="form-control" placeholder="x1">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X2</label>
                                    <input type="text" name="x2" value="{{$formula->x2}}" class="form-control" placeholder="x2">
                                </div>  
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X3</label>
                                    <input type="text" name="x3" value="{{$formula->x3}}" class="form-control" placeholder="x3">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X4</label>
                                    <input type="text" name="x4" value="{{$formula->x4}}" class="form-control" placeholder="x4">
                                </div>  
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
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
    $(document).ready(function () {

        $('#updateForm').validate({
            rules: {
                x1: {
                    required: true
                },
                x2: {
                    required: true
                },
                x3: {
                    required: true
                },
                x4: {
                    required: true
                }
            }
        });

    });
</script>
@endsection