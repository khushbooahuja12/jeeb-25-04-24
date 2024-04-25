@extends('admin.layouts.dashboard_layout_for_vendor_panel')
@section('content')
<style>
  .product_saving {
      display: none;
  }

  .center {
    display: block;
    margin-left: auto;
    margin-right: auto;
    
  }

</style>
<div class="page-body">
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
          <div class="col-sm-6">
            <h3>Products</h3>
          </div>
          
        </div>
      </div>
    </div>
    <!-- Container-fluid starts-->
    <div class="container-fluid product-wrapper">
      <div class="product-grid">
        <div class="feature-products">
          <div class="row m-b-10">
            
          </div>
          
        </div>

        <ul class="nav nav-tabs border-tab" id="myTab" role="tablist">
          <li class="nav-item">
            <a href="{{route('fleet-stock-update-base-products-panel',['id'=>$store->id,'category' => 0])}}"  class="nav-link" >Update Stock All Products</a>
          </li>
          <li class="nav-item">
            <a href="{{route('fleet-stock-update-store-update',['id'=>$store->id])}}" class="nav-link">Update Stock</a>
          </li>
          {{-- <li class="nav-item">
            <a href="{{route('fleet-stock-update-sale-update',['id'=>$store->id])}}" class="nav-link active" >Update Sale</a>
          </li> --}}
        </ul>

        <div class="product-wrapper-grid">
          <div class="row">
            <div class="row">
            <div class="col-sm-12 col-md-6">
              <div class="card">
                <div class="card-body">
                  <div class="clipboaard-container">
                    <form class="form-inline" id="findProduct" method="GET" action="">
                    <input class="form-control" id="barcode" type="text" name="barcode" placeholder="Barcode" value="{{ $barcode }}">
                    <div class="mt-3 text-end">
                      <button class="btn btn-primary btn-clipboard" type="submit" data-clipboard-action="copy" data-clipboard-target="#clipboardExample1"><i class="fa fa-copy"></i> Go</button>
                    </div>
                  </form> 
                  </div>
                </div>
              </div>
            </div>
            </div>
            @if ($product)
                <div class="col-xl-6 col-lg-4 col-sm-6 xl-25">
                  <div class="card">
                    <div class="product-box">
                      <div class="product-img"><img class="center" src="{{ $product->product_image_url }}" alt="" height="100">
                        <div class="product-hover">
                          <ul>
                            <li><a data-bs-toggle="modal" data-bs-target="#exampleModalCenter"><i class="icon-eye"></i></a></li>
                          </ul>
                        </div>
                      </div>
                      <div class="modal fade" id="exampleModalCenter">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                          <div class="modal-content">
                            <div class="modal-header">
                              <div class="product-box row">
                                <div class="product-img col-lg-6"><img class="center" src="{{ $product->product_image_url }}" alt="" height="100"></div>
                                <div class="product-details col-lg-6 text-start">
                                    <h4>{{ $product->product_name_en }}</h4>
                                    <h4>{{ $product->product_name_ar }}</h4><br>
                                  <h6>ID : {{ $product->id }}</h4>
                                  <h6>Itemcode : {{ $product->itemcode }}</h4>
                                  <h6>Barcode : {{ $product->barcode }}</h4>
                                  @php
                                  $product_subcategory = $product->getProductSubCategory; 
                                  $product_subcategory_id = $product->category_id ? $product->category_id : false;
                                  $product_subcategory_name = $product->category_name_en ? $product->category_name_en : 'N/A';
                                  @endphp
                                  <h6>Category : {{ $product_subcategory_name }}</h6>
                                  <h6>Price : {{ $product->product_store_distributor_price }}</h6>
                                  <h6>Stock :   {{ $product->product_store_product_stock }}</h6>
                                  <h6>Unit: {{ $product->product_store_unit }}</h6>
                                </div>
                              </div>
                              <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="product-details">
                          <h6>{{ $product->product_name_en }} ({{ $product->product_store_unit }})</h6>
                        
                        <form name="form_<?= $product->product_store_id ?>" id="form_<?= $product->product_store_id ?>" enctype="multipart/form-data">
                          <input type="hidden" name="fk_store_id" value="{{ $row->fk_store_id }}">
                          <div class="mb-3 m-form__group">
                            <div class="input-group"><span class="input-group-text">Selling Quantity</span>
                              <input class="form-control" type="number" name="product_store_product_stock" min="0" value="1">
                            </div>
                          </div>
                          <button class="btn btn-square btn-primary btn-sm product_saving product_saving_{{ $product->product_store_id }}" type="button" disabled>
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                            Saving...
                          </button>
                          
                          <button href="javascript:void(0);" id="{{ $product->product_store_id }}" class="btn btn-square btn-primary btn-sm product_save product_save_{{ $product->product_store_id }}" >Save</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
            @endif
          </div>
        </div>
      </div>
    </div>
    <!-- Container-fluid Ends-->
  </div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(function() {
        $("#barcode").select();
    });
    $(document).on('click', '#barcode', function() {
        $(this).select();
    });
    $(document).on('change', '#barcode', function() {
        $("#findProduct").submit();
    });
    $('.product_edit').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.edit_mode_'+id).toggle();
    });
    $('.product_save').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.product_saving_'+id).show();
        $('.product_save_'+id).hide();
        if (id) {
            let form = $("#form_"+id);
            let token = "{{ csrf_token() }}";
            // let formData = form.serialize();
            // formData += formData+"&_token="+token;
            // formData += formData+"&id="+id;
            console.log(form);
            let formData = new FormData(form[0]);
            // var form_data = new FormData(document.getElementById("form_"+id));
            formData.append('id', id);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/stock_update/store/'.$store->id.'/base_products/update_sale_save') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        // alert(data.message);
                        // location.reload();
                        $('.product_saving_'+id).hide();
                        $('.product_save_'+id).show();
                        if (data.data) {
                            let product = data.data;
                            $('.product_name_en_text_'+id).html(product.product_name_en);
                            $('.product_name_ar_text_'+id).html(product.product_name_ar);
                            $('.unit_text_'+id).html(product.unit);
                            $('.category_text_'+id).html(product.sub_category_name+' ('+product.fk_sub_category_id+')');
                            $('.unit_text_'+id).html(product.unit);
                            $('.store1_distributor_price_text_'+id).html(product.product_store_distributor_price);
                            $('.store1_price_text_'+id).html(product.product_store_product_price);
                            $('.store1_text_'+id).html(product.product_store_product_stock);
                            $('.product_image_change_'+id).prop('src', product.product_image_url);
                        }
                    } else {
                        alert(data.message);
                    }
                }
            });
        } else {
            alert("Something went wrong");
        }
    });
</script>
  @endsection