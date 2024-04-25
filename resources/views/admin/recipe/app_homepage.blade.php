@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Recipe Home Static Upload</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Recipe</a></li>
                        <li class="breadcrumb-item">App Homepage</li>
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
                        <div class="row">
                            <div class="col-sm-6">
                                <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                    action="{{ route('admin.recipes.home_static_store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>JSON File (English Version): </label><br/>
                                                <input type="file" name="home_static_file" id="home_static_file"/>
                                                <input type="hidden" name="lang" id="lang" value="en"/>
                                                <p class="errorPrint" id="home_static_fileError"></p>
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
                                        <table class="table table-bordered" style="width:100%;max-width: 100%;">
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
                                                            <td style="word-break: break-all;"><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
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
                                    action="{{ route('admin.recipes.home_static_store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>JSON File (Arabic Version): </label><br/>
                                                <input type="file" name="home_static_file" id="home_static_file"/>
                                                <input type="hidden" name="lang" id="lang" value="ar"/>
                                                <p class="errorPrint" id="home_static_fileError"></p>
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
                                        <table class="table table-bordered" style="width:100%;max-width: 100%;">
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
                                                            <td style="word-break: break-all;"><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
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
    </div>
@endsection
