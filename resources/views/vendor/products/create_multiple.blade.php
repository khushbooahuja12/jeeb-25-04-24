@extends('vendor.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Update changes in multiple products</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('vendor/all_products'); ?>">Products</a></li>
                    <li class="breadcrumb-item active">Bulk Update</li>
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
                    <form method="post" id="createMultipleForm" enctype="multipart/form-data" action="{{route('vendor.products.bulk_upload')}}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select CSV File</label>
                                    <input type="file" name="product_csv" accept=".csv,.xlsx,.xls" class="form-control">                                    
                                </div>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Submit
                                    </button>                                    
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
    $('#createMultipleForm').validate({
        rules: {
            product_csv: {
                required: true
            }
        }
    });
</script>
@endsection