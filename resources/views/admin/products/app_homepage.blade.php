@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        @include('partials.errors')
        @include('partials.errors_array')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Home Static Upload</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item">App Homepage</li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                    action="{{ route('admin.products.home_static_store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>JSON File (English Version): </label><br/>
                                                <input type="file" name="home_static_file" id="home_static_file"/>
                                                <input type="hidden" name="lang" id="lang" value="en"/>
                                                <p class="errorPrint" id="home_static_idError"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input class="btn btn-primary" type="submit" name="home_static_upload" id="home_static_upload" value="Upload"/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-bordered" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>FIle</th>
                                                    <th>Uploaded On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($home_static_ens)
                                                    @foreach ($home_static_ens as $key => $home_static)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
                                                            <td>{{$home_static->created_at}}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                    action="{{ route('admin.products.home_static_store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>JSON File (Arabic Version): </label><br/>
                                                <input type="file" name="home_static_file" id="home_static_file"/>
                                                <input type="hidden" name="lang" id="lang" value="ar"/>
                                                <p class="errorPrint" id="home_static_idError"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input class="btn btn-primary" type="submit" name="home_static_upload" id="home_static_upload" value="Upload"/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-bordered" style="width:100%;">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>FIle</th>
                                                    <th>Uploaded On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($home_static_ars)
                                                    @foreach ($home_static_ars as $key => $home_static)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
                                                            <td>{{$home_static->created_at}}</td>
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
                </div>
            </div>
        </div>
        {{-- <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Sections
                        <a href="<?= url('admin/app_homepage_create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
            </div>
        </div> --}}
        {{-- <div class="row">
            <div class="col-md-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Title</th>
                                    <th>UI type</th>
                                    <th>Index</th>
                                    <th>Background Color</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($homepage)
                                    @foreach ($homepage as $key => $row)
                                        <tr class="homepage<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->title }}</td>
                                            <td>
                                                <?php
                                                if ($row->ui_type == 1) {
                                                    echo 'Full width banner';
                                                } elseif ($row->ui_type == 2) {
                                                    echo 'Product listing layout';
                                                } elseif ($row->ui_type == 3) {
                                                    echo 'Categories';
                                                } elseif ($row->ui_type == 4) {
                                                    echo 'Featured brands';
                                                } elseif ($row->ui_type == 5) {
                                                    echo 'Extra goodies';
                                                }elseif ($row->ui_type == 6) {
                                                    echo 'Recent Orders';
                                                }
                                                ?>
                                            </td>
                                            <td>{{ $row->index }}</td>
                                            <td>{{ $row->background_color }}</td>
                                            <td>
                                                @if($row->ui_type==1 || $row->ui_type==2)
                                                <a title="view"
                                                    href="<?= url('admin/app_homepage_detail/' . base64url_encode($row->id)) ?>">
                                                    <i class="fa fa-eye"></i>
                                                </a>&ensp;
                                                @endif
                                                <a title="edit"
                                                    href="<?= url('admin/app_homepage_edit/' . base64url_encode($row->id)) ?>">
                                                    <i class="icon-pencil"></i>
                                                </a>&ensp;
                                                <a title="remove" href="javascript:void(0)"
                                                    onclick="delete_homepage(this,<?= $row->id ?>)">
                                                    <i class="fa fa-trash"></i>
                                                </a>&ensp;
                                                @if($row->ui_type==1 || $row->ui_type==2)
                                                <a title="remove"
                                                    href="<?= url('admin/app_homepage_add_data/' . base64url_encode($row->id)) ?>">
                                                    <button class="btn btn-small btn-success">Add Items</button>
                                                </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    <script>
        // function delete_homepage(obj, id) {
        //     if (confirm("Are you sure you want to remove this section ?")) {
        //         $.ajax({
        //             url: '<?= url('admin/app_homepage_remove') ?>',
        //             type: 'post',
        //             dataType: 'json',
        //             data: {
        //                 id: id
        //             },
        //             cache: false
        //         }).done(function(response) {
        //             if (response.status_code == 200) {
        //                 var success_str =
        //                     '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
        //                     '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
        //                     '<strong>' + response.message + '</strong>.' +
        //                     '</div>';
        //                 $(".ajax_alert").html(success_str);
        //                 $(".homepage" + id).remove();
        //             } else {
        //                 var error_str =
        //                     '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
        //                     '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
        //                     '<strong>' + response.message + '</strong>.' +
        //                     '</div>';
        //                 $(".ajax_alert").html(error_str);
        //             }
        //         });
        //     }
        // }
    </script>
@endsection
