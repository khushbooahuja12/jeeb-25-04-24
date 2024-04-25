@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Upload Product Discount</h4>
            </div>
            <div class="col-sm-6">

            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="uploadBaseProductDiscount" enctype="multipart/form-data" action="{{route('admin.base_products.update_products_discount')}}">
                        @csrf
                        <div class="row">                                                       
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select CSV File</label>
                                    <input type="file" name="base_products_discounts_csv" accept=".csv" class="form-control">
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
    $(document).ready(function () {
        $('#uploadBaseProductDiscount').validate({
            rules: {
                base_products_discounts_csv: {
                    required: true
                }
            }
        });
    });
</script>
@endsection