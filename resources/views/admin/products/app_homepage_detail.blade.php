@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title"><?= $homepage->title ?> Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/app_homepage') ?>"><?= $homepage->title ?></a>
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
            <div class="col-md-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                @if ($ui_type == 1)
                                    <tr>
                                        <th>S.No.</th>
                                        <th>Title</th>
                                        <th>Image</th>
                                        <th>Image 2</th>
                                        <th>Redirection Type</th>
                                        <th>Keyword</th>
                                        <th>Action</th>
                                    </tr>
                                @else
                                    <th>S.No.</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Product Name</th>
                                    <th>Quantity </th>
                                    <th>Product Image </th>
                                    <th>Action</th>
                                @endif
                            </thead>
                            <tbody>
                                @if ($homepage_data)
                                    @if ($ui_type == 1)
                                        @foreach ($homepage_data as $key => $row)
                                            <tr class="product<?= $row->id ?>">
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $row->title }}</td>
                                                <td><img src="{{ $row->getImage ? asset('/') . $row->getImage->file_path . $row->getImage->file_name : asset('assets/images/dummy-product-image.jpg') }}"
                                                        width="75" height="50">
                                                </td>
                                                <td><img src="{{ $row->getImage2 ? asset('/') . $row->getImage2->file_path . $row->getImage2->file_name : asset('assets/images/dummy-product-image.jpg') }}"
                                                        width="75" height="50">
                                                </td>
                                                <td>{{ $row->redirection_type ? $redirection_type_arr[$row->redirection_type - 1] : 'N/A' }}
                                                </td>
                                                <td>{{ $row->keyword }}</td>
                                                <td>
                                                    <a title="Edit"
                                                        href="<?= url('admin/app_homepage_edit_data/' . base64url_encode($row->id)) ?>">
                                                        <i class="icon-pencil"></i>
                                                    </a>&ensp;
                                                    <a title="Remove" href="javascript:void(0)"
                                                        onClick="delete_product(this,<?= $row->id ?>)">
                                                        <i class="fa fa-trash"></i>
                                                    </a>&ensp;
                                                    @if ($row->redirection_type == 4)
                                                        <a title="Add items"
                                                            href="<?= url('admin/app_homepage_add_banner_products/' . base64url_encode($row->id)) ?>">
                                                            <button class="btn btn-small btn-success">Add Items</button>
                                                        </a>
                                                    @endif
                                                </td>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach ($homepage_data as $key => $row)
                                            <tr class="product<?= $row->id ?>">
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $row->getProduct->category_name }}</td>
                                                <td>{{ $row->getProduct->brand_name }}</td>
                                                <td><a
                                                        href="<?= url('admin/products/show/' . base64url_encode($row->getProduct->id)) ?>">{{ $row->getProduct->product_name_en }}
                                                    </a>
                                                </td>
                                                <td>{{ $row->getProduct->unit }}</td>
                                                <td><img src="{{ $row->getProduct->product_image_url }}" width="75"
                                                        height="50">
                                                </td>
                                                <td>
                                                    <a title="Remove" href="javascript:void(0)"
                                                        onClick="delete_product(this,<?= $row->id ?>)">
                                                        <i class="icon-trash-bin"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function delete_product(obj, id) {
            if (confirm("Are you sure you want to remove item from this section ?")) {
                $.ajax({
                    url: '<?= url('admin/app_homepage_remove_data') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: id
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
                        $(".product" + id).remove();
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
    </script>
@endsection
