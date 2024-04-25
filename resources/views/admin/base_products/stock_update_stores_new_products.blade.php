@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
.select2-container {
    min-width: 100%;
}
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Existing products with different barcodes</h4>
                    <p>As per the daily stock updates</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/base_products/stock_update_stores') ?>">Stock Update</a></li>
                        <li class="breadcrumb-item active">New Products</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <select name="store_id" class="form-control">
                                    <option value="0">All Stores</option>
                                    @if ($stores)
                                        @foreach ($stores as $item)
                                            <option value="{{ $item->id }}" {{ ($item->id==$store_id) ? 'selected' : '' }}>{{ $item->name.' - '.$item->company_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th class="nosort">Store No</th>
                                    <th>@sortablelink('name', 'Store Name')</th>
                                    <th class="nosort">Matched Product IDs</th>
                                    <th>@sortablelink('name', 'Base Product')</th>
                                    <th class="nosort">Itemcode</th>
                                    <th class="nosort">Barcode</th>
                                    <th class="nosort">Product Name</th>
                                    <th class="nosort">Product Unit</th>
                                    <th class="nosort">Store Price</th>
                                    <th class="nosort">Stock</th>
                                    <th class="nosort">Stock Batch ID</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($new_products)
                                    @foreach ($new_products as $key => $row)
                                        <tr class="new_product_<?= $row->id ?>" id="edit_row_<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->fk_store_id }}</td>
                                            <td>{{ !empty($row->getStore) ? $row->getStore->name.' - '.$row->getStore->company_name : 'N/A' }}</td>
                                            <td>{{ $row->matched_record_ids }}</td>
                                            <td>
                                                @php 
                                                    $store_base_products = $row->storeBaseProducts();
                                                    if ($store_base_products) {
                                                        $same_store_text = 'checked';
                                                        $other_store_text = '';
                                                        $same_store_text2 = '';
                                                        $other_store_text2 = 'display:none;';
                                                    } else {
                                                        $same_store_text = '';
                                                        $other_store_text = 'checked';
                                                        $same_store_text2 = 'display:none;';
                                                        $other_store_text2 = '';
                                                    }
                                                @endphp
                                                <input type="radio" class="store_base_product store_base_product_{{ $row->id }}" onclick="selctStoreBaseProduct(<?= $row->id ?>)" name="store_base_product_{{ $row->id }}" value="0" {{ $same_store_text }}/> From same store base products<br/>
                                                <input type="radio" class="store_base_product store_base_product_{{ $row->id }}" onclick="selctStoreBaseProduct(<?= $row->id ?>)" name="store_base_product_{{ $row->id }}" value="1" {{ $other_store_text }}/> From other store base products
                                                <hr/>
                                                <div id="fk_product_id_container_{{ $row->id }}" style="{{ $same_store_text2 }}">
                                                    <select class="form-control" name="fk_product_id" id="fk_product_id_{{ $row->id }}">
                                                        @if ($store_base_products)
                                                            @foreach ($store_base_products as $item)
                                                                @if (isset($item->id))
                                                                    <option value="{{ $item->id }}" selected>{{ $item->product_name_en }} ({{ $item->id }}) - {{ $item->unit }} - {{ $item->product_store_price }}</option>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                                <div id="other_fk_product_id_container_{{ $row->id }}" style="{{ $other_store_text2 }}">
                                                    <select class="form-control search_base_products_ajax_with_price" data-with_price="1" name="other_fk_product_id" id="other_fk_product_id_{{ $row->id }}">
                                                        <option value="" selected>--Select--</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td>{{ $row->itemcode }}</td>
                                            <td>{{ $row->barcode }}</td>
                                            <td>{{ $row->product_name_en }}</td>
                                            <td>{{ $row->unit }}</td>
                                            <td>{{ $row->distributor_price }}</td>
                                            <td>{{ $row->stock }}</td>
                                            <td>{{ $row->batch_id }}</td>
                                            <td>
                                                <input type="hidden" name="id" value="{{ $row->id }}"/>
                                                <a href="javascript:void(0);" class="btn btn-primary btn-sm" onclick="addNewBarcodeStoreProduct(<?= $row->id ?>)">Add New Barcode Stock</a>    
                                                {{-- <br/><br/>
                                                <a href="javascript:void(0);" class="btn btn-danger btn-sm" onclick="addNewBarcodeBaseProduct(<?= $row->id ?>)">Create New Base Product</a>                                          --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $new_products->count() }} of {{ $new_products->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $new_products->appends($_GET)->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        const selctStoreBaseProduct = (row_id) => {
            var checkedId = $(".store_base_product_" + row_id + ":checked").val();
            if (checkedId==1) {
                $('#fk_product_id_container_'+row_id).hide();
                $('#other_fk_product_id_container_'+row_id).show();
            } else {
                $('#fk_product_id_container_'+row_id).show();
                $('#other_fk_product_id_container_'+row_id).hide();
            }
        }
        const addNewBarcodeStoreProduct = (row_id) => {
            var formData = $("tr#edit_row_" + row_id).find("input,select,textarea").serialize();
            var checkedId = $(".store_base_product_" + row_id + ":checked").val();
            if (checkedId==1) {
                var fk_product_id = $('#other_fk_product_id_'+row_id+" option:selected").val();
            } else {
                var fk_product_id = $('#fk_product_id_'+row_id+" option:selected").val();
            }
            if (fk_product_id==0 || fk_product_id==null) {
                alert('Please select the base product');
            } else {
                formData += '&fk_product_id='+fk_product_id;
                $.ajax({
                    url: '<?= url('admin/base_products/stock_update_stores/add_new_products_store') ?>',
                    type: 'POST',
                    data: formData,
                    dataType: 'JSON',
                    cache: false,
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {
                        $('#edit_row_'+row_id).addClass('pm-loading-shimmer');
                    },
                    success: function(response) { console.log(response);
                        if(response.status == true){
                            location.reload();
                        }else{
                            alert(response.message);
                        }
                    },
                    complete: function(){
                        $('#edit_row_'+row_id).removeClass('pm-loading-shimmer');
                    }
                });
            }
        }
    </script>
@endsection
