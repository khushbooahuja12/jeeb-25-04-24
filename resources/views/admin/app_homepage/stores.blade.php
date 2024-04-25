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
                    <h4 class="page-title">Stores Home Static Upload</h4>
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
        {{-- Stores --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @if ($homepage_stores)
                            @foreach ($homepage_stores as $item)
                            <div class="row">
                                <div class="col-sm-12">
                                    <a href="javascript:void(0);" style="display:block; border-radius: 10px; padding: 10px; background: #e7e7e7; font-size: 22px;font-weight:bold;">
                                        {{ $item->store_name_en }}
                                        {{-- | {{ $item->store_name_ar}} --}}
                                        <br/>
                                        <span style="font-size: 14px;font-weight:normal;">Home Static Key: {{ $item->store_key}}</a><br/>
                                    </a>
                                    <span style="font-size: 14px;font-weight:normal;">API: 
                                        <a href="{{ env('APP_URL','https://jeeb.tech/') }}api/users/home_static_store?store={{ $item->store_key}}">
                                            {{ env('APP_URL','https://jeeb.tech/') }}api/users/home_static_store?store={{ $item->store_key}}
                                        </a> | Method: GET
                                    </span>
                                </div>
                            </div>
                            <br clear="all"/>
                            <div class="row" >
                                <div class="col-sm-6">
                                    <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                        action="{{ route('admin.app_homepage.stores_home_static_store') }}">
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
                                                    <input type="hidden" name="store_key" id="store_key" value="{{ $item->store_key}}"/>
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
                                                        <th>Last Processed At</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($item->getHomeStaticEn)
                                                        @foreach ($item->getHomeStaticEn as $key => $home_static)
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td style="word-wrap:break-word;white-space:normal;word-break:break-all;"><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
                                                                <td>{{$home_static->created_at}}</td>
                                                                <td>{{$home_static->last_processed_at}}</td>
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
                                        action="{{ route('admin.app_homepage.stores_home_static_store') }}">
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
                                                    <input type="hidden" name="store_key" id="store_key" value="{{ $item->store_key}}"/>
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
                                                    @if ($item->getHomeStaticAr)
                                                        @foreach ($item->getHomeStaticAr as $key => $home_static)
                                                            <tr>
                                                                <td>{{ $key + 1 }}</td>
                                                                <td style="word-wrap: break-word;white-space: normal;"><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
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
                            <br clear="all"/><br/>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    <script>
        $('#run-command-english').click(function () {
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Command has been queued for execution...');
            $(this).prop('disabled', true);
            var language = 'en';
            var model = 'home_static_1';

            $.ajax({
                url: '{{ route('proccess-home-static-json') }}',
                type: 'POST',
                data: JSON.stringify({ model:model, language: language }),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}'
                },
                success: function (data) {
                    console.log(data.message);
                    // Update UI or perform other actions on success
                },
                error: function (error) {
                    console.error(error);
                    // Handle errors or display messages on failure
                }
            });
        });

        $('#run-command-arabic').click(function () {
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Command has been queued for execution...');
            $(this).prop('disabled', true);
            var language = 'ar';
            var model = 'home_static_1';

            $.ajax({
                url: '{{ route('proccess-home-static-json') }}',
                type: 'POST',
                data: JSON.stringify({ model:model, language: language }),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-Token': '{{ csrf_token() }}'
                },
                success: function (data) {
                    console.log(data.message);
                    // Update UI or perform other actions on success
                },
                error: function (error) {
                    console.error(error);
                    // Handle errors or display messages on failure
                }
            });
        });
    </script>
@endsection
