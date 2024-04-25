@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Product List
                        <a href="<?= url('admin/products/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/products') ?>">Products</a></li>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Brand Name"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Company / Supplier</th>
                                    <th>Category</th>
                                    <th>@sortablelink('product_name_en', 'Product Name')</th>
                                    <th>Quantity </th>
                                    <th class="nosort">Product Image </th>
                                    <th class="nosort">Action</th>
                                    <th class="nosort">Offered ?</th>
                                    <th class="nosort">Frequently Bought ?</th>
                                    {{-- <th class="nosort">Handpicked</th> --}}
                                </tr>
                            </thead>
                            <tbody>
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                        <tr class="product<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ !empty($row->getCompany) ? $row->getCompany->name : 'N/A' }}
                                            </td>
                                            <td>{{ !empty($row->getProductCategory) ? $row->getProductCategory->category_name_en : 'N/A' }}
                                            </td>
                                            <td><a href="<?= url('admin/products/show/' . base64url_encode($row->id)) ?>">{{ $row->product_name_en }}
                                                    ({{ $row->id }})</a></td>
                                            <td>{{ $row->unit }}</td>
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
                                                    href="<?= url('admin/products/show/' . base64url_encode($row->id)) ?>"><i
                                                        class="fa fa-eye"></i></a>
                                                <a title="edit"
                                                    href="<?= url('admin/products/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>
                                                <a title="Delete" href="javascript:void(0)"
                                                    onclick="delete_product(this,<?= $row->id ?>)"><i
                                                        class="icon-trash-bin"></i></a>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'offered' ?>"
                                                            type="checkbox" value="offered"
                                                            <?= $row->is_home_screen == 1 && $row->offered == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'frequently_bought' ?>"
                                                            type="checkbox" value="frequently_bought"
                                                            <?= $row->frequently_bought_together == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            {{-- <td>                                    
                                    <div class="mytoggle">
                                        <label class="switch">
                                            <input class="switch-input <?= $row->id . 'handpicked' ?>" type="checkbox" value="handpicked" <?= $row->is_home_screen == 1 && $row->handpicked == 1 ? 'checked' : '' ?> 
                                                   id="<?= $row->id ?>" 
                                                   onchange="checkHomeType(this)">
                                            <span class="slider round"></span> 
                                        </label>
                                    </div>
                                </td> --}}
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
                                    {{ $products->links() }}
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
                    url: '<?= url('admin/products/delete_product') ?>',
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
    </script>
@endsection
