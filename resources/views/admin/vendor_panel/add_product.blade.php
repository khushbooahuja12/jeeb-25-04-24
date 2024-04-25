@extends('admin.layouts.dashboard_layout_for_vendor_panel')
@section('content')
<div class="page-body">
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
          <div class="col-sm-6">
            <h3>Add Product</h3>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#"><i data-feather="home"></i></a></li>
              <li class="breadcrumb-item active">Add Product</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <!-- Container-fluid starts-->
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
          <div class="card">
            <form class="form theme-form" method="post" id="regForm" enctype="multipart/form-data" action="{{ route('vendor-product-store') }}">
              @csrf
              <input type="hidden" name="fk_store_id" value="{{ base64url_encode($store->id) }}" >
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Base Product</label>
                      <select name="fk_product_id" id="fk_product_id" class="form-control regInputs select2">
                        <option value="">Select</option>
                        @if ($base_products)
                            @foreach ($base_products as $row)
                                <option value="{{ $row->id }}" class="base_product{{ $row->id }}"
                                    <?= old('fk_category_id') ? (old('fk_category_id') == $row->id ? 'selected' : '') : '' ?>>
                                    {{ $row->product_name_en }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="errorPrint" id="fk_category_idError"></p>
                    </div>
                  </div><div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Product Name (En)</label>
                      <input class="form-control" type="text" name="product_name_en" id="product_name_en" placeholder="Product Name (En)">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Product Name (Ar)</label>
                      <input class="form-control" type="text" name="product_name_ar" id="product_name_ar" placeholder="Product Name (Ar)">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Product Category</label>
                      <select name="fk_category_id" id="fk_category_id" onchange="getSubCategory(this);"data-title="Product Category" class="form-control regInputs">
                        <option value="">Select Category</option>
                        @if ($categories)
                            @foreach ($categories as $row)
                                <option value="{{ $row->id }}" class="category{{ $row->id }}"
                                    <?= old('fk_category_id') ? (old('fk_category_id') == $row->id ? 'selected' : '') : '' ?>>
                                    {{ $row->category_name_en }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="errorPrint" id="fk_category_idError"></p>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Sub Category</label>
                      <select name="fk_sub_category_id" id="fk_sub_category_id" data-title="Product Sub Category" class="form-control regInputs">
                          <option value="">Select Sub Category</option>
                        </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Brand Name</label>
                      <select class="form-select digits" name="fk_brand_id" id="fk_brand_id">
                        <option value="">Select Brand</option>
                        @if ($brands)
                            @foreach ($brands as $row)
                                <option value="{{ $row->id }}"
                                    <?= old('fk_brand_id') ? (old('fk_brand_id') == $row->id ? 'selected' : '') : '' ?>>
                                    {{ $row->brand_name_en }}
                                </option>
                            @endforeach
                        @endif
                      </select>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Itemcode </label>
                      <input class="form-control" type="text" name="itemcode" id="itemcode" placeholder="Itemcode">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Barcode </label>
                      <input class="form-control" type="text" name="barcode" id="barcode" placeholder="Barcode">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Unit </label>
                      <input class="form-control" type="text" name="unit" id="unit" placeholder="Unit">
                    </div>
                  </div>
                  
                  {{-- <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Tags </label>
                      <input class="form-control" type="text" name="_tags" id="_tags" placeholder="Tags">
                    </div>
                  </div> --}}
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Price (QAR) </label>
                      <input class="form-control" type="text" name="base_price" id="base_price" placeholder="Price">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Stock</label>
                      <input class="form-control" type="number" name="stock" id="stock" placeholder="Stock" min="0" value="0">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label" >Country Code </label>
                      <input class="form-control" type="text" name="country_code" id="country_code" placeholder="Country Code">
                    </div>
                  </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                      <label>Product Main Image</label>
                      <div class="form-group">
                          <input type="file" accept="image/*" class="dropify regInputs"
                              data-title="Image" id="image" name="image" />
                          <p class="errorPrint" id="imageError"></p>
                      </div>
                  </div>
                  <div class="col-md-4">
                    <label>Country Icon</label>
                    <div class="form-group">
                        <input type="file" accept="image/*" class="dropify regInputs"
                            id="country_icon" name="country_icon" />
                    </div>
                  </div>
                </div>
              </div>
              <div class="card-footer text-end">
                <button class="btn btn-primary" type="submit">Submit</button>
                <a class="btn btn-light" href="{{ route('vendor-product-add',['id' => base64url_encode($store->id)]) }}">Clear</a>
              </div>
            </form>
          </div>
          
        </div>
        
      </div>
    </div>
    <!-- Container-fluid Ends-->
  </div>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script>

$(document).ready(function() {

$('#regForm').validate({
    rules: {
        fk_company_id: {
            required: true
        },
        fk_category_id: {
            required: true
        },
        fk_sub_category_id: {
            required: true
        },
        itemcode: {
            required: true
        },
        barcode: {
            required: true
        },
        product_name_en: {
            required: true
        },
        product_name_ar: {
            required: true
        },
        base_price: {
            required: true,
            number: true,
            min: 0
        },
        unit: {
          required: true,
        },
        image: {
            required: true
        }
    }
});

});

    function getSubCategory(obj) {
            var id = $(obj).val();
            $('#fk_sub_category_id').empty();
            var html = "<option value='' disabled selected>Select Sub Category</option>";
            if (id) {
                if (id == 16) {
                    $("input[name=min_scale]").parent().parent().css('display', 'block');
                    $("input[name=max_scale]").parent().parent().css('display', 'block');
                }
                $.ajax({
                    url: "<?= url('admin/products/get_sub_category') ?>",
                    type: 'post',
                    data: 'fk_category_id=' + id + '&_token=<?= csrf_token() ?>',
                    success: function(data) {
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
  <script>
    $(document).ready(function() {
            $('.select2').select2();
        });
  </script>
  <script>
    $(document).ready(function() {
            $('#fk_product_id').change(function(){
                if($(this).val()){
                  $('#product_name_en').prop('disabled',true);
                  $('#product_name_ar').prop('disabled',true);
                  $('#fk_category_id').prop('disabled',true);
                  $('#fk_sub_category_id').prop('disabled',true);
                  $('#fk_brand_id').prop('disabled',true);
                  $('#_tags').prop('disabled',true);
                  $('#country_code').prop('disabled',true);
                  $('#image').prop('disabled',true);
                  $('#country_icon').prop('disabled',true);
                }else{
                  $('#product_name_en').prop('disabled',false);
                  $('#product_name_ar').prop('disabled',false);
                  $('#fk_category_id').prop('disabled',false);
                  $('#fk_sub_category_id').prop('disabled',false);
                  $('#fk_brand_id').prop('disabled',false);
                  $('#_tags').prop('disabled',false);
                  $('#country_code').prop('disabled',false);
                  $('#image').prop('disabled',false);
                  $('#country_icon').prop('disabled',false);
                }
            });
        });
  </script>
@endsection