@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Product List: Company 1, Fruites and Vegetables
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
                    <div class="card-body" style="overflow-x: scroll">
                        <div class="">
                            Total products found: {{ $products_count; }}<br/>
                            Total completed updating: <span id="price_updated">0</span><br/>
                            <br/>
                            <button type="submit" class="btn btn-success waves-effect waves-light update_all_prices">
                                Update all prices
                            </button>
                            <br clear="all"/><br/>
                            <div id="success_message"></div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Company / Supplier</th>
                                    <th>Category</th>
                                    <th>@sortablelink('product_name_en', 'Product Name')</th>
                                    <th>Store 1 Distributor Price</th>
                                    <th>Store 2 Distributor Price</th>
                                    <th>Store 3 Distributor Price</th>
                                    <th>Store 4 Distributor Price</th>
                                    <th>Store 5 Distributor Price</th>
                                    <th>Store 6 Distributor Price</th>
                                    <th>Store 7 Distributor Price</th>
                                    <th>Store 8 Distributor Price</th>
                                    <th>Store 9 Distributor Price</th>
                                    <th>Store 10 Distributor Price</th>
                                    <th>Store 1 Price</th>
                                    <th>Store 2 Price</th>
                                    <th>Store 3 Price</th>
                                    <th>Store 4 Price</th>
                                    <th>Store 5 Price</th>
                                    <th>Store 6 Price</th>
                                    <th>Store 7 Price</th>
                                    <th>Store 8 Price</th>
                                    <th>Store 9 Price</th>
                                    <th>Store 10 Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $products_array="";
                                @endphp
                                @if ($products)
                                    @foreach ($products as $key => $row)
                                        @php
                                        if ($row->checked == 0) {
                                            $products_array .= $row->id.",";
                                        }
                                        @endphp
                                        <tr class="product<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ !empty($row->getCompany) ? $row->getCompany->name : 'N/A' }}
                                            </td>
                                            <td>{{ !empty($row->getProductCategory) ? $row->getProductCategory->category_name_en : 'N/A' }}
                                            </td>
                                            <td><a href="<?= url('admin/products/show/' . base64url_encode($row->id)) ?>">{{ $row->product_name_en }}
                                                    ({{ $row->id }})</a></td>
                                            <td>{{ $row->store1_distributor_price }}</td>
                                            <td>{{ $row->store2_distributor_price }}</td>
                                            <td>{{ $row->store3_distributor_price }}</td>
                                            <td>{{ $row->store4_distributor_price }}</td>
                                            <td>{{ $row->store5_distributor_price }}</td>
                                            <td>{{ $row->store6_distributor_price }}</td>
                                            <td>{{ $row->store7_distributor_price }}</td>
                                            <td>{{ $row->store8_distributor_price }}</td>
                                            <td>{{ $row->store9_distributor_price }}</td>
                                            <td>{{ $row->store10_distributor_price }}</td>
                                            <td>{{ $row->store1_price }}</td>
                                            <td>{{ $row->store2_price }}</td>
                                            <td>{{ $row->store3_price }}</td>
                                            <td>{{ $row->store4_price }}</td>
                                            <td>{{ $row->store5_price }}</td>
                                            <td>{{ $row->store6_price }}</td>
                                            <td>{{ $row->store7_price }}</td>
                                            <td>{{ $row->store8_price }}</td>
                                            <td>{{ $row->store9_price }}</td>
                                            <td>{{ $row->store10_price }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $products->count() }} entries.
                                </div>
                            </div>
                        </div>
                        <input type="hidden" style="display: none;" id="products_array" value="{{$products_array}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Update al stocks
        var price_updated = $("#price_updated").html();
        $('.update_all_prices').on('click',function(e){
            e.preventDefault();
            $("#success_message").html('<div class="loader"></div>');
            let products = $("#products_array").val();
            if (products!="") {
                let products_array = new Array();
                products_array = products.split(',');
                update_price(products_array);
            }
        });
        const update_price = (products_array, i=0) => {
            let form_id = products_array[i];
            let total_products = products_array.length;
            let form = $("#"+form_id);
        
            let token = "{{ csrf_token() }}";
            let formData = new FormData();
            formData.append("_token",token);
            formData.append("id",form_id);
            $.ajax({
            url: "/admin/products/update_price",
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function(response){
                console.log(response);
                price_updated++;
                $("#price_updated").html(price_updated);
                if (i<total_products) {
                    update_price(products_array, (i+1));
                } else {
                    $("#success_message").html('<div class="alert alert-primary text-center">Completed all!</div>');
                }
            },
            error: function (error) {
                console.log(error);
                $("#success_message").html('<div class="alert alert-danger text-center">Error occured while performing this action</div>');
            }
            });
        }
    </script>
@endsection
