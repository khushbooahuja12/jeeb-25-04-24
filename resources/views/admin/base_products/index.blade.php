@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    /* Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
input[type=number] {
  -moz-appearance: textfield;
}
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Base Product List
                        <a href="<?= url('admin/base_products/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/base_products') ?>">Products</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="product_id" placeholder="Search Product ID" value="{{ $product_id }}">
                                <input type="text" class="form-control" name="filter" placeholder="Search Product Name" value="{{ $filter }}">
                                <input type="text" class="form-control" name="unit" placeholder="Search Unit" value="{{ $unit }}">
                                <input type="text" class="form-control" name="tags" placeholder="Search Tag" value="{{ $tags }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered accordion">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>Product Store ID</th>
                                    <th>@sortablelink('product_name_en', 'Product Name')</th>
                                    <th>Quantity </th>
                                    <th>Category</th>
                                    <th>Base Price</th>
                                    <th>Selling Price</th>
                                    <th class="nosort">Product Image </th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                        <tr class="product<?= $row->id ?>" data-toggle="collapse" data-target="#collapse<?= $row->id ?>" class="accordion-toggle">
                                            <td><a class="accordion-button" data-toggle="collapse" data-target="#collapse<?= $row->id ?>" class="accordion-toggle" href="javascript:void(0)"><i class="fa fa-plus"></i></a></td>
                                            <td>{{ isset($_GET['page']) && $_GET['page']>=2?($_GET['page']-1)*50+($key+1):($key+1)}}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->fk_product_store_id }}</td>
                                            <td><a href="<?= url('admin/products/show/' . base64url_encode($row->id)) ?>">{{ $row->product_name_en }}
                                                    ({{ $row->id }})</a></td>
                                            <td>{{ $row->unit }}</td>
                                            <td>{{ !empty($row->getProductCategory) ? $row->getProductCategory->category_name_en : 'N/A' }}</td>
                                            <td id="base_price_td_{{ $row->id }}">{{ $row->base_price }}</td>
                                            <td id="product_store_price_td_{{ $row->id }}">{{ $row->product_store_price }}</td>
                                            <td>
                                                <a href="javascript:void(0)" fk_product_id="{{ $row->id }}"
                                                    onclick="openClassificationModal(this)">
                                                    <?php if ($row->getClassification->count()): ?>
                                                    <img src="{{ asset('assets/images/classified.png') }}" width="25"
                                                        height="25"
                                                        style="position:absolute;margin-top:15px;margin-left:-2px">
                                                    <?php endif; ?>
                                                    <img src="{{ $row->product_image_url ? $row->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                        width="75" height="50">
                                                </a>
                                            </td>
                                            <td>
                                                <a title="view details"
                                                    href="<?= url('admin/base_products/show/' . $row->id) ?>"><i
                                                        class="fa fa-eye"></i></a>
                                                <a title="edit"
                                                    href="<?= url('admin/base_products/edit/' . $row->id) ?>"><i
                                                        class="icon-pencil"></i></a>
                                                <a title="Delete" href="javascript:void(0)"
                                                    onclick="delete_product(this,<?= $row->id ?>)"><i
                                                        class="icon-trash-bin"></i></a>
                                            </td>
                                        </tr>
                                            <tr>
                                                <td colspan="12" class="pm-hidden-row">
                                                    <div class="accordian-body collapse" id="collapse<?= $row->id ?>">
                                                        <table class="table table-bordered add_product_store_table_<?= $row->id ?>" id="add_product_store_table_<?= $row->id ?>">
                                                            <thead>
                                                                <tr class="info">
                                                                    <th>ID</th>
                                                                    <th>Stores</th>
                                                                    <th>Itemcode</th>
                                                                    <th>Barcode</th>
                                                                    <th>Distributor Price Before Back Margin</th>
                                                                    <th>Distributor Price</th>
                                                                    <th>Base Price</th>
                                                                    <th>Selling Price</th>
                                                                    <th>Stock</th>
                                                                    <th>Other Names</th>
                                                                    <th>Allow Margin</th>
                                                                    <th>Is Active ?</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr id="add_row_<?= $row->id ?>">
                                                                    <form method="post" name="add_base_product_store_form" onsubmit="addBaseProductStoreForm({{ $row->id }}, event)"  id="add_base_product_store_form_{{ $row->id }}">
                                                                        @csrf
                                                                        <input type="hidden" name="fk_product_id" value="{{ $row->id }}">
                                                                        <td>#</td>
                                                                        <td class="col-md-2">
                                                                            <select name="fk_store_id" id="add_product_store_<?= $row->id ?>" class="form-control add_product_store_<?= $row->id ?> select2" style="width: 100%" required>
                                                                                <option value="" disabled selected>Select</option>
                                                                                @foreach ($stores as $store )
                                                                                    <option value="{{ $store->id }}">
                                                                                        {{ $store->company_name }} - {{ $store->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="text" name="itemcode" id="add_product_itemcode_<?= $row->id ?>" data-title="Itemcode" class="form-control" style="margin-top: 1px" required></td>
                                                                        <td><input type="text" name="barcode" id="add_product_barcode_<?= $row->id ?>" data-title="Barcode" class="form-control"style="margin-top: 1px" required></td>
                                                                        <td><input type="text" name="product_distributor_price_before_back_margin" id="add_product_distributor_price_before_back_margin_<?= $row->id ?>" data-title="Distributor Price Before Back Margin" class="form-control" style="margin-top: 1px" min="1.00" placeholder="0.00" required></td>
                                                                        <td><input type="text" name="product_distributor_price" id="add_product_distributor_price_<?= $row->id ?>" data-title="Distributor Price" class="form-control"style="margin-top: 1px" min="1.00" placeholder="0.00" readonly required></td>
                                                                        <td><input type="text" name="base_price" id="add_product_base_price_<?= $row->id ?>" data-title="Base Price" class="form-control"style="margin-top: 1px" placeholder="0.00" min="1.00" readonly></td>
                                                                        <td><input type="text" name="product_store_price" id="add_product_store_price_<?= $row->id ?>" data-title="Selling Price" class="form-control" style="margin-top: 1px" min="1.00" placeholder="0.00" readonly required></td>
                                                                        <td><input type="number" name="stock" id="add_product_stock_<?= $row->id ?>" min="0" data-title="Product Stock" class="form-control" style="margin-top: 1px" value="0" min="0" required></td>
                                                                        <td><input type="text" name="other_names" id="add_product_other_names_<?= $row->id ?>" data-title="Product Other Names" class="form-control" style="margin-top: 1px"></td>
                                                                        <td><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="allow_margin" id="add_product_allow_margin_<?= $row->id ?>" onchange="changeAllowMarginOnAdd(<?= $row->id ?>)" value="1" checked><span class="slider round"></span></label></div></td>
                                                                        <td><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="is_active" id="add_product_is_active" value="1" checked><span class="slider round"></span></label></div></td>
                                                                        <td><button type="submit" class="btn btn-primary btn-sm" id="add_add_base_product_store_btn_<?= $row->id ?>">Add</a></td>
                                                                    </form>
                                                                </tr>

                                                                @foreach ($row->stocks as $stocks)
                                                                <tr id="edit_row_<?= $stocks->id ?>">
                                                                    <form method="post" name="edit_base_product_store_form" id="edit_base_product_store_form_<?= $stocks->id ?>">
                                                                        @csrf
                                                                        <input type="hidden" name="base_product_store_id" value="{{ $stocks->id }}">
                                                                        <input type="hidden" name="base_product_store_product_id" value="{{ $stocks->fk_product_id }}" id="base_product_store_{{ $stocks->id }}">
                                                                        <td>{{ $stocks->id }}</td>
                                                                        <td class="col-md-2">
                                                                            <select name="fk_store_id" id="edit_product_store_<?= $stocks->id ?>" class="form-control select2" style="width: 100%" disabled required>
                                                                                @foreach ($stores as $store )
                                                                                    <option value="{{ $store->id }}" {{ $stocks->fk_store_id == $store->id ? 'selected' : '' }}>
                                                                                        {{ $store->company_name }} - {{ $store->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td class="col-md-2"><input type="text" name="itemcode" id="edit_product_itemcode_<?= $stocks->id ?>" data-title="Itemcode" class="form-control" style="margin-top: 1px" value="{{ $stocks->itemcode }}" required readonly></td>
                                                                        <td class="col-md-2"><input type="text" name="barcode" id="edit_product_barcode_<?= $stocks->id ?>" data-title="Barcode" class="form-control"style="margin-top: 1px" value="{{ $stocks->barcode }}" required readonly></td>
                                                                        <td class="col-md-1"><input type="text" name="product_distributor_price_before_back_margin" id="edit_product_distributor_price_before_back_margin_<?= $stocks->id ?>" data-title="Distributor Price Before Back Margin" class="form-control"style="margin-top: 1px" value="{{ $stocks->product_distributor_price_before_back_margin }}" required readonly></td>
                                                                        <td class="col-md-1"><input type="text" name="product_distributor_price" id="edit_product_distributor_price_<?= $stocks->id ?>" data-title="Distributor Price" class="form-control"style="margin-top: 1px" value="{{ $stocks->product_distributor_price }}" readonly></td>
                                                                        <td class="col-md-2"><input type="text" name="base_price" id="edit_product_base_price_<?= $stocks->id ?>" data-title="Base Price" class="form-control"style="margin-top: 1px" value="{{ $stocks->base_price }}" required readonly></td>
                                                                        <td class="col-md-1"><input type="text" name="product_store_price" id="edit_product_store_price_<?= $stocks->id ?>" data-title="Selling Price" class="form-control" style="margin-top: 1px" value="{{ $stocks->product_store_price }}" required readonly></td>
                                                                        <td class="col-md-1"><input type="number" name="stock" id="edit_product_stock_<?= $stocks->id ?>" min="0" data-title="Product Stock" class="form-control" style="margin-top: 1px" value="{{ $stocks->stock }}" required readonly></td>
                                                                        <td class="col-md-2"><input type="text" name="other_names" id="edit_product_other_names_<?= $stocks->id ?>" data-title="Product Other Names" class="form-control" style="margin-top: 1px" value="{{ $stocks->other_names }}" readonly></td>
                                                                        <td class="col-md-1"><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="allow_margin" id="edit_product_allow_margin_<?= $stocks->id ?>" onchange="changeAllowMarginOnUpdate(<?= $stocks->id ?>)"  value="{{ $stocks->allow_margin }}" {{ $stocks->allow_margin == 1 ? 'checked' : '' }} disabled><span class="slider round"></span></label></div></td>
                                                                        <td class="col-md-1"><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="is_active" id="edit_product_is_active_<?= $stocks->id ?>" value="{{ $stocks->is_active }}" {{ $stocks->is_active == 1 ? 'checked' : '' }} disabled><span class="slider round"></span></label></div></td>
                                                                        <td class="col-md-1"><a href="javascript:void(0)" onclick="editBaseProductStoreForm(<?= $stocks->id ?>)" id="edit_base_product_store_btn_<?= $stocks->id ?>" class="btn btn-primary btn-sm mb-1">Edit</a> <a href="javascript:void(0)" class="btn btn-danger btn-sm" onclick="removeProductFields(<?= $stocks->id ?>);" id="delete_base_product_store_btn_<?= $stocks->id ?>">Delete</a></td>
                                                                    </form>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <div id="product_store_fields<?= $row->id ?>"></div>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $products->count() }} of {{ $products->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $products->appends($_GET)->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade classificationModal" data-backdrop="static" tabindex="-1" role="dialog"
        aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0">Classification</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form method="post" id="classificationForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Name</label>
                                    <select class="form-control" name="fk_classification_id"
                                        onchange="getSubClassification(this)">
                                        <option value="" disabled selected>Select</option>
                                        @if ($classification->count())
                                            @foreach ($classification as $key => $value)
                                                <option value="{{ $value->id }}">{{ $value->name_en }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Sub Name</label>
                                    <select class="form-control" name="fk_sub_classification_id" id="sub_classification">
                                        <option value="" disabled selected>Select</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="fk_product_id">
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
    <script>
        function checkHomeType(obj) {
            var id = $(obj).attr("id");
            var value = $(obj).val();

            var checked = $(obj).is(':checked');
            if (checked == true) {
                var is_switched_on = 1;
                var statustext = 'move this to offers screen';
                var message = 'Product moved to offers screen';
            } else {
                var is_switched_on = 0;
                var statustext = 'remove from offers screen';
                var message = 'Product removed from offers screen';
            }

            // if (confirm("Are you sure you want to " + statustext + "?")) {
            $.ajax({
                url: '<?= url('admin/products/set_home_product') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    id: id,
                    is_switched_on: is_switched_on,
                    value: value
                },
                cache: false,
            }).done(function(response) {
                if (response.status_code === 200) {
                    // var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                    //         + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                    //         + '<strong>' + message + ' successfully</strong>.'
                    //         + '</div>';
                    // $(".ajax_alert").html(success_str);
                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>Some error found</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
            // } else {
            //     if (is_switched_on === 1) {
            //         $("." + id + value).prop('checked', false);
            //     } else if (is_switched_on === 0) {
            //         $("." + id + value).prop('checked', true);
            //     }
            // }
        }

        function delete_product(obj, product_id) {
            if (confirm("Are you sure you want to delete this product?")) {
                $.ajax({
                    url: '<?= url('admin/base_products/delete_base_product') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        product_id: product_id
                    },
                    cache: false
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);
                        $(".product" + product_id).remove();
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }

                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                });
            }
        }

        function changeProductStatus(obj, id) {
            var confirm_chk = confirm('Are you sure to change product status?');
            if (confirm_chk) {
                var checked = $(obj).is(':checked');
                if (checked == true) {
                    var status = 1;
                } else {
                    var status = 0;
                }
                if (id) {
                    $.ajax({
                        url: "<?= url('admin/products/change_product_status') ?>",
                        type: 'post',
                        data: 'id=' + id + '&action=' + status + '&_token=<?= csrf_token() ?>',
                        success: function(data) {
                            if (data.error_code == "200") {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert(data.message);
                            }
                        }
                    });
                } else {
                    alert("Something went wrong");
                }
            } else {
                return false;
            }
        }

        function openClassificationModal(obj) {
            var product_id = $(obj).attr('fk_product_id');
            $(".classificationModal").find("input[name=fk_product_id]").val(product_id);
            $(".classificationModal").modal('show');
        }

        function getSubClassification(obj) {
            var id = $(obj).val();
            $('#fk_sub_classification_id').empty();
            var html = "<option value='' disabled selected>Select Sub Classification</option>";
            if (id) {
                $.ajax({
                    url: '<?= url('admin/products/get_sub_classification') ?>',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'JSON',
                    cache: false
                }).done(function(response) {
                    if (response.error_code == 200) {
                        let options = '<option value="" disabled selected>Select</option>';
                        $.each(response.data, function(i, v) {
                            options += '<option value="' + v.id + '">' + v.name_en + '</option>';
                        });
                        $('#sub_classification').html(options);
                    } else {
                        let options = '<option value="" disabled selected>No Sub Name Exist</option>';
                        $('#sub_classification').html(options);
                    }

                })
            } else {
                alert("Something went wrong !");
            }
        }
        $('#classificationForm').validate({
            rules: {
                fk_classification_id: {
                    required: true
                },
                product_name_ar: {
                    required: true
                }
            }
        });

        $("#classificationForm").on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: '<?= url('admin/products/add_classified_product') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                if (response.error_code == 200) {
                    $(".classificationModal").modal('hide');
                } else {

                }
            });
        })
        
        function addBaseProductStoreForm(row_id, event){
            event.preventDefault();
            var formData = $("#add_base_product_store_form_" +row_id).serialize();

            $.ajax({
                url: '<?= url('admin/base_products_store/create') ?>',
                type: 'POST',
                data: formData,
                dataType: 'JSON',
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend:function(){
                    $("#add_base_product_store_btn_"+row_id+"").prop('disabled', false);
                    $("#add_row_"+row_id).addClass('pm-loading-shimmer');
                },
                success:function(response) { console.log(response);

                    if(response.status == true){
                        $(".add_product_store_"+row_id+" option[value='']").prop("selected", true);
                        $("#add_product_distributor_price_before_back_margin_"+row_id+"").val('0.00');
                        $("#add_product_distributor_price_"+row_id+"").val('0.00');
                        $("#add_base_price_"+row_id+"").val('0.00');
                        $("#add_product_store_price_"+row_id+"").val('0.00');
                        $("#add_product_stock_"+row_id+"").val(0);
                        $("#add_product_itemcode_"+row_id+"").val('');
                        $("#add_product_barcode_"+row_id+"").val('');
                        $("#add_product_other_names_"+row_id+"").val('');
                        $("#add_product_is_active_"+row_id+"").prop('checked',true);
                        $("td#base_price_td_"+response.data.fk_product_id+"").text(response.data.base_price);
                        $("td#product_store_price_td_"+response.data.fk_product_id+"").text(response.data.product_store_price);
                        
                        var allow_margin_checked;
                        if(response.data.allow_margin == 1){
                            allow_margin_checked = 'checked';
                        }else{
                            allow_margin_checked = '';
                        }

                        var is_active_checked;
                        if(response.data.is_active == 1){
                           is_active_checked = 'checked';
                        }else{
                           is_active_checked='';
                        }

                        var csrfVar = $('meta[name="csrf-token"]').attr('content');

                        $("#add_product_store_table_"+row_id+" tbody").append('<tr id="edit_row_'+response.data.id+'"><form id="edit_base_product_store_form_'+response.data.id+'" name="edit_base_product_store_form" method="post"><input name="_token" value="' + csrfVar + '" type="hidden">"<input type="hidden" name="base_product_store_id" value="'+response.data.id+'"><input type="hidden" name="base_product_store_product_id" value="'+response.data.fk_product_id+'" id="edit_base_product_store_'+response.data.id+'"><td class="col-md-2">'+response.data.id+'</td><td class="col-md-2"><select name="fk_store_id" id="edit_product_store_'+response.data.id+'" data-title="" class="form-control" required readonly><option value="'+response.data.get_store.id+'" class="company">'+response.data.get_store.company_name+'-'+response.data.get_store.name+'</option></select></td><td class="col-md-2"><input type="text" name="itemcode" id="edit_product_itemcode_'+response.data.id+'" data-title="Itemcode" class="form-control" style="margin-top: 1px" value="'+response.data.itemcode+'" required readonly></td><td class="col-md-2"><input type="text" name="barcode" id="edit_product_barcode_'+response.data.id+'" data-title="Barcode" class="form-control"style="margin-top: 1px" value="'+response.data.barcode+'" required readonly></td><td class="col-md-1"><input type="text" name="product_distributor_price_before_back_margin" id="edit_product_distributor_price_before_back_margin_'+response.data.id+'" data-title="Distributor Price Before Back Margin" class="form-control" style="margin-top: 1px" value="'+response.data.product_distributor_price_before_back_margin+'" readonly></td><td class="col-md-1"><input type="text" name="product_distributor_price" id="edit_product_distributor_price_'+response.data.id+'" data-title="Distributor Price" class="form-control" style="margin-top: 1px" value="'+response.data.product_distributor_price+'" required readonly></td><td class="col-md-1"><input type="text" name="base_price" id="edit_product_base_price_'+response.data.id+'" data-title="Base Price" class="form-control" style="margin-top: 1px" value="'+response.data.base_price+'" required readonly></td><td class="col-md-1"><input type="text" name="product_store_price" id="edit_product_store_price_'+response.data.id+'" data-title="Selling Price" class="form-control" style="margin-top: 1px" value="'+response.data.product_store_price+'" required readonly></td><td class="col-md-1"><input type="number" name="stock" id="edit_product_stock_'+response.data.id+'" min="0" data-title="Product Stock" class="form-control" style="margin-top: 1px" value="'+response.data.stock+'" required readonly></td><td class="col-md-3"><input type="text" name="other_names" id="edit_product_other_names_'+response.data.id+'" data-title="Product Other Names" class="form-control" style="margin-top: 1px" value="'+response.data.other_names+'" readonly></td><td class="col-md-1"><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="allow_margin" id="edit_product_allow_margin_'+response.data.id+'" onchange="changeAllowMarginOnUpdate('+response.data.id+')" value="'+response.data.allow_margin+'" '+allow_margin_checked+' disabled><span class="slider round"></span></label></div></td><td class="col-md-1"><div class="mytoggle"> <label class="switch"><input class="switch-input" type="checkbox" name="is_active" id="edit_product_is_active_'+response.data.id+'" value="'+response.data.is_active+'" '+is_active_checked+' disabled><span class="slider round"></span></label></div></td><td class="col-md-1"> <a href="javascript:void(0)" onclick="editBaseProductStoreForm('+response.data.id+')" id="edit_base_product_store_btn_'+response.data.id+'" class="btn btn-primary btn-sm mb-1">Edit</a> <a href="javascript:void(0)" class="btn btn-danger btn-sm" onclick="removeProductFields('+response.data.id+');">Delete</a></td></form></tr>');
                    
                    }else{

                        alert(response.message);
                    }
                },
                complete:function(){
                    $('#add_row_'+row_id).removeClass('pm-loading-shimmer');
                }
            });
            
        }

        function editBaseProductStoreForm(row_id) {

            $("#edit_product_store_"+row_id+"").prop('disabled', false);
            $("#edit_product_distributor_price_before_back_margin_"+row_id+"").prop('readonly', false);
            $("#edit_product_store_price_"+row_id+"").prop('readonly', false);
            $("#edit_product_stock_"+row_id+"").prop('readonly', false);
            $("#edit_product_itemcode_"+row_id+"").prop('readonly', false);
            $("#edit_product_barcode_"+row_id+"").prop('readonly', false);
            $("#edit_product_other_names_"+row_id+"").prop('readonly', false);
            $("#edit_product_allow_margin_"+row_id+"").prop('disabled', false);
            $("#edit_product_is_active_"+row_id+"").prop('disabled', false);
            $("#edit_base_product_store_btn_"+row_id+"").text('Update');
            $("#edit_base_product_store_btn_"+row_id+"").attr("onclick","updateBaseProductStoreForm("+row_id+")");
            
            if($("#edit_product_allow_margin_"+row_id+"").is(':checked')){
                $("#edit_product_store_price_"+row_id+"").prop('readonly',true);
                $("#edit_product_distributor_price_"+row_id+"").prop('readonly',true);
                $("#edit_product_base_price_"+row_id+"").prop('readonly',true);
            }else{
                $("#edit_product_store_price_"+row_id+"").prop('readonly',false);
                $("#edit_product_distributor_price_"+row_id+"").prop('readonly',false);
                $("#edit_product_base_price_"+row_id+"").prop('readonly',false);
            }
        }


        function updateBaseProductStoreForm(row_id){

            if($("#edit_product_allow_margin_"+row_id+"").is(':checked')){
                $("#edit_product_allow_margin_"+row_id+"").val(1)
            }else{
                $("#edit_product_allow_margin_"+row_id+"").val(0)
            }

            if($("#edit_product_is_active_"+row_id+"").is(':checked')){
                $("#edit_product_is_active_"+row_id+"").val(1)
            }else{
                $("#edit_product_is_active_"+row_id+"").val(0)
            }

            var formData = $("tr#edit_row_" + row_id).find("input,select,textarea").serialize();
            
           $.ajax({
                url: '<?= url('admin/base_products_update/update/') ?>',
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
                        $("#edit_product_store_"+row_id+"").prop('disabled', true);
                        $("#edit_product_distributor_price_before_back_margin_"+row_id+"").prop('readonly', true);
                        $("#edit_product_distributor_price_"+row_id+"").prop('readonly', true);
                        $("#edit_product_base_price_"+row_id+"").prop('readonly', true);
                        $("#edit_product_store_price_"+row_id+"").prop('readonly', true);
                        $("#edit_product_stock_"+row_id+"").prop('readonly', true);
                        $("#edit_product_itemcode_"+row_id+"").prop('readonly', true);
                        $("#edit_product_barcode_"+row_id+"").prop('readonly', true);
                        $("#edit_product_other_names_"+row_id+"").prop('readonly', true);
                        $("#edit_product_allow_margin_"+row_id+"").prop('disabled', true);
                        $("#edit_product_is_active_"+row_id+"").prop('disabled', true);
                        $("#edit_base_product_store_btn_"+row_id+"").text('Edit');
                        $("#edit_base_product_store_btn_"+row_id+"").attr("onclick","editBaseProductStoreForm("+row_id+")");
                        $("#edit_base_product_store_btn_"+row_id+"").prop('disabled', true);
                        $("#delete_base_product_store_btn_"+row_id+"").prop('disabled', true);

                        $("#edit_product_distributor_price_"+row_id+"").attr('value',response.data.product_distributor_price);
                        $("#edit_product_base_price_"+row_id+"").attr('value',response.data.base_price);
                        $("#edit_product_store_price_"+row_id+"").attr('value',response.data.product_store_price);

                        $("td#base_price_td_"+response.data.fk_product_id+"").text(response.data.base_price);
                        $("td#product_store_price_td_"+response.data.fk_product_id+"").text(response.data.product_store_price);
                        
                    }else{

                        alert(response.message);
                    }
                },
                complete: function(){
                    $('#edit_row_'+row_id).removeClass('pm-loading-shimmer');
                }
            });
        }

        function removeProductFields(rid) {
            var confirmation = confirm("are you sure you want to remove the item?");
            if (confirmation) {
                var base_product_id = $("#base_product_store_"+rid).val();
                $.ajax({
                    url: '<?= url('admin/base_products_store/delete_base_product_store') ?>',
                    type: 'POST',
                    data: {base_product_store_id:rid, base_product_id:base_product_id},
                    dataType: 'JSON',
                    cache: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend:function(){
                        $('#edit_row_'+rid).addClass('pm-loading-shimmer');
                    },
                    complete:function() {
                        $('#edit_row_'+rid).removeClass('pm-loading-shimmer');
                        $('#edit_row_'+rid).remove();
                    }
                });
            }
        }
        

        function changeAllowMarginOnAdd(id) {
            if($("#add_product_allow_margin_"+id+"").is(':checked')){
                $("#add_product_store_price_"+id+"").prop('readonly',true);
                $("#add_product_distributor_price_"+id+"").prop('readonly',true);
                $("#add_product_base_price_"+id+"").prop('readonly',true);
            }else{
                $("#add_product_store_price_"+id+"").prop('readonly',false);
                $("#add_product_distributor_price_"+id+"").prop('readonly',false);
                $("#add_product_base_price_"+id+"").prop('readonly',false);
            }
        }

        function changeAllowMarginOnUpdate(id){
            if($("#edit_product_allow_margin_"+id+"").is(':checked')){
                $("#edit_product_store_price_"+id+"").prop('readonly',true);
                $("#edit_product_distributor_price_"+id+"").prop('readonly',true);
                $("#edit_product_base_price_"+id+"").prop('readonly',true);
            }else{
                $("#edit_product_store_price_"+id+"").prop('readonly',false);
                $("#edit_product_distributor_price_"+id+"").prop('readonly',false);
                $("#edit_product_base_price_"+id+"").prop('readonly',false);
            }
        }


        $(document).on('keypress','.price',function(e){
            var char = String.fromCharCode(e.which);
            if(isNaN(char)){
            char = '';
            }
            var value = $(this).val() + char;
            value = value.replace('.','');
            $(this).val((value/100).toFixed(2));
            
            if(!isNaN(char))
                return false;

        }).on('keyup','.price',function(e){
            var value = $(this).val();
            value = value.replace('.','');
            $(this).val((value/100).toFixed(2));

        });

        $(document).on('keypress','.edit_price',function(e){
            var char = String.fromCharCode(e.which);
            if(isNaN(char)){
            char = '';
            }
            var value = $(this).val() + char;
            value = value.replace('.','');
            $(this).val((value/100).toFixed(2));
            
            if(!isNaN(char))
                return false;

        }).on('keyup','.edit_price',function(e){
            var value = $(this).val();
            value = value.replace('.','');
            $(this).val((value/100).toFixed(2));
        });
        
    </script>
@endsection