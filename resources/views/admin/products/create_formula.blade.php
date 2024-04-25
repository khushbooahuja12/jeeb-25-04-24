@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                @if($id != 0)
                    <h4 class="page-title">Add New Row ({{$store->name}} - {{$store->company_name}})</h4>
                @else
                    <h4 class="page-title">Add New Row (Default Store)</h4>
                @endif
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/products/price-formula'); ?>">Price Formula</a></li>
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
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.products.store_price_formula',['id' => $id, 'subcategory' => $subcategory, 'brand' => $brand])}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X1</label>
                                    <input type="text" name="x1" value="" class="form-control" placeholder="x1">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X2</label>
                                    <input type="text" name="x2" value="" class="form-control" placeholder="x2">
                                </div>  
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X3</label>
                                    <input type="text" name="x3" value="" class="form-control" placeholder="x3">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>X4</label>
                                    <input type="text" name="x4" value="" class="form-control" placeholder="x4">
                                </div>  
                            </div>
                        </div>
                        <div class="row">                            
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