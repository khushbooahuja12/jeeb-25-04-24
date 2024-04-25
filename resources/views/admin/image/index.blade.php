@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Image Upload</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item">Images</li>
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
                            <div class="col-sm-12">
                                @if ($errors->any())
                                  <div class="alert alert-danger">
                                    <ul style="padding-bottom: 0px; margin-bottom: 0px;">
                                        @foreach ($errors->all() as $error)
                                          <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                  </div>
                                @endif
                                <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                    action="{{ route('admin.upload_image_store') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Image File: </label><br/>
                                                <input type="file" name="image_file" id="image_file"/><br/><br/>
                                                <small>* Image must be webp format and  file size should not be more than 2MB.</small>
                                                <input type="hidden" name="lang" id="lang" value="en"/>
                                                <p class="errorPrint" id="image_fileError"></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <input class="btn btn-primary" type="submit" name="image_file_upload" id="image_file_upload" value="Upload"/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col-md-12">
                                        <table class="table table-bordered" style="width:100%;word-break: break-all;">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>File</th>
                                                    <th>URL</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($files)
                                                    @foreach ($files as $key => $file)
                                                        @if ($file!='..' && $file!='.')
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td><img src="{{$image_url_base.'/'.$file}}" style="height: 80px; width: auto; max-width: 500px;"/></td>
                                                            <td><a href="{{$image_url_base.'/'.$file}}" target="_blank">{{$image_url_base.'/'.$file}}</a></td>
                                                        </tr>
                                                        @endif
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
