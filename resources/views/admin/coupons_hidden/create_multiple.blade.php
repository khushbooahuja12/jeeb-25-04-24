@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Bulk upload hidden coupons</h4>
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
                    <form method="post" id="createMultipleForm" enctype="multipart/form-data" action="{{route('admin.coupons_hidden.bulk_upload')}}">
                        @csrf
                        <div class="row">                                                       
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select CSV File</label>
                                    <input type="file" name="coupons_csv" accept=".csv" class="form-control">
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
        $('#createMultipleForm').validate({
            rules: {
                coupons_csv: {
                    required: true
                }
            }
        });
    });
</script>
@endsection