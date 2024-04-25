@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Bulk upload brands</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/brands'); ?>">Brands</a></li>
                    <li class="breadcrumb-item active">Bulk Upload</li>
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
                    <form method="post" id="createMultipleForm" enctype="multipart/form-data" action="{{route('admin.brands.bulk_upload')}}">
                        @csrf
                        <div class="row">                                                      
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Select CSV File</label>
                                    <input type="file" name="brand_csv" accept=".csv,.xlsx,.xls" class="form-control">                                    
                                </div>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Submit
                                    </button>
                                    <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(1);">
                                        Cancel
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
                fk_category_id: {
                    required: true
                },
                product_csv: {
                    required: true
                }
            }
        });

    });
    function getBrand(obj) {
        var id = $(obj).val();
        $('#fk_brand_id').empty();
        var html = "<option value='0'>Select Brand</option>";
        if (id) {
            $.ajax({
                url: "<?= url('admin/products/get_category_brand') ?>",
                type: 'post',
                data: 'fk_category_id=' + id + '&_token=<?= csrf_token() ?>',
                success: function (data) {
                    if (data.error_code == "200") {
                        html += data.data;
                    } else {
                        alert(data.message);
                    }
                    $('#fk_brand_id').append(html);
                }
            });
        } else {
            alert("Something went wrong");
        }
    }
    function getSubCategory(obj) {
        var id = $(obj).val();
        $('#fk_sub_category_id').empty();
        var html = "<option value='' disabled selected>Select Sub Category</option>";
        if (id) {
            $.ajax({
                url: "<?= url('admin/products/get_sub_category') ?>",
                type: 'post',
                data: 'fk_category_id=' + id + '&_token=<?= csrf_token() ?>',
                success: function (data) {
                    if (data.error_code == "200") {
                        html += data.data;
                    } else {
                        alert(data.message);
                    }
                    $('#fk_sub_category_id').append(html);
                }
            });
        } else {
            alert("Something went wrong");
        }
    }
</script>
@endsection