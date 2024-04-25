@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Storekeeper Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="<?= url('store/' . $storeId . '/storekeepers') ?>">Storekeepers</a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @csrf
                        <div class="row">
                            <div class="offset-3 col-md-9 justify-center">
                                <table class="table table-borderless table-responsive">
                                    <tr>
                                        <th>Name </th>
                                        <td> {{ $storekeeper->name . ' (' . $storekeeper->id . ')' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email </th>
                                        <td> {{ $storekeeper->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile </th>
                                        <td> {{ '+' . $storekeeper->country_code . ' ' . $storekeeper->mobile }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address </th>
                                        <td> {{ $storekeeper->address }}</td>
                                    </tr>
                                    <tr>
                                        <th>Store </th>
                                        <td> {{ $storekeeper->getStore->name ?? 'N/A' }}</td>
                                    </tr>
                                    <form method="post" id="subcatform">
                                        <tr>
                                            <th>Sub Categories </th>
                                            <td width="100%">
                                                <select class="form-control select2" name="sub_category_ids[]" id="sub_category_ids" multiple>
                                                    @if ($sub_categories)
                                                        @foreach ($sub_categories as $key => $value)
                                                            <option value="{{ $value->id }}"
                                                                <?= in_array($value->id, $added_subcategories) ? 'selected' : '' ?>>
                                                                {{ $value->category_name_en }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <td>
                                                <input type="checkbox" {{ count($added_subcategories) >= 1 ? 'checked' : '' }} id="sub_category_id" > Select All
                                            </td>
                                        </tr>
                                        <input type="hidden" name="storekeeper_id" value="{{ $storekeeper->id }}">
                                        <tr>
                                            <th>
                                                <button class="btn btn-success btn-sm">Update</button>
                                            </th>
                                        </tr>
                                    </form>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Pending Items to be packed</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Category</th>
                                    <th>Product Name</th>
                                    <th>No. of items</th>
                                    <th>Product Image</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($storekeeper_products)
                                    @foreach ($storekeeper_products as $key => $value)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $value->getOrder->orderId ?? 'N/A' }}</td>
                                            <td>{{ $value->category_name ?? 'N/A' }}</td>
                                            <td>{{ $value->product_name_en }}</td>
                                            <td>{{ $value->getProductQuantity($value->fk_order_id, $value->id)->product_quantity ?? 'N/A' }}
                                            </td>
                                            <td><img src="{{ !empty($value->product_image_url) ? $value->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                    width="75" height="50"></td>
                                            <td>
                                                <button
                                                    class="btn btn-<?= $value->status == 1 ? 'success' : 'danger' ?> btn-sm"><?= $value->status == 1 ? 'Collected' : 'Waiting' ?></button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $("#subcatform").on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= url('store/' . $storeId . '/update_storekeeper_subcategories') ?>',
                type: 'post',
                dataType: 'json',
                data: $(this).serialize(),
                cache: false,
            }).done(function(response) {
                if (response.status_code == 200) {
                    var success_str =
                        '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(success_str);
                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        });
    </script>
    <script>
        $("#sub_category_id").click(function(){
            if($("#sub_category_id").is(':checked') ){
                $("#sub_category_ids > option").prop("selected", true);
                $("#sub_category_ids").trigger("change");
            }else{
                $("#sub_category_ids > option").prop("selected", false);
                $("#sub_category_ids").trigger("change");
            }
        });
    </script>
@endsection
