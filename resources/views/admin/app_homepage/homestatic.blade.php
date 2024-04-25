@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
.store_key_container {
    display: none;
}
td {
    word-wrap:break-word;white-space:normal;word-break:break-all;
}
</style>
    <div class="container-fluid">
        @include('partials.errors')
        @include('partials.errors_array')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">{{ $homestatic_title }} - HomeStatic Upload</h4>
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
        {{-- Home static 1 --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <span style="font-size: 14px;font-weight:normal;display:block;padding: 10px;background:#f7f2c6">API: 
                                    <a href="{{ env('APP_URL','https://jeeb.tech/') }}api/users/home_static_{{$homestatic_key}}{{ $group_enabled && isset($first_instant_store_group_id) ? '?group='.$first_instant_store_group_id : '' }}">
                                        {{ env('APP_URL','https://jeeb.tech/') }}api/users/home_static_{{$homestatic_key}}{{ $group_enabled && isset($first_instant_store_group_id) ? '?group='.$first_instant_store_group_id : '' }}
                                    </a> | Method: GET
                                </span><br/>
                            </div>
                            <div class="col-sm-6">
                                <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                    action="{{ route('admin.app_homepage.stores_home_static_'.$homestatic_key) }}">
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
                                                    <th>Last Processed At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($homestatic_ens)
                                                    @foreach ($homestatic_ens as $key => $home_static)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
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
                                    action="{{ route('admin.app_homepage.stores_home_static_'.$homestatic_key) }}">
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
                                                    <th>Last Processed At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($homestatic_ars)
                                                    @foreach ($homestatic_ars as $key => $home_static)
                                                        <tr>
                                                            <td>{{ $key + 1 }}</td>
                                                            <td><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
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
                        </div>
                    </div>
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
                                    <a href="javascript:void(0);" class="store_key_selector" id="{{ $item->store_key }}" style="display:block; border-radius: 10px; padding: 10px; background: #e7e7e7; font-size: 22px;font-weight:bold;">
                                        {{ $item->store_name_en }}
                                        {{-- | {{ $item->store_name_ar}} --}}
                                        <br/>
                                        <span style="font-size: 14px;font-weight:normal;">Home Static Key: {{ $item->store_key}}</a><br/>
                                    </a>
                                </div>
                            </div>
                            <div class="store_key_container container_{{ $item->store_key }}" >
                                <div class="row" >
                                    <div class="col-sm-12">
                                        <span style="font-size: 14px;font-weight:normal;display:block;padding: 10px;background:#f7f2c6">API: 
                                            <a href="{{ env('APP_URL','https://jeeb.tech/') }}api/users/home_static_{{$homestatic_key}}?store={{ $item->store_key}}{{ $group_enabled && isset($first_instant_store_group_id) ? '&group='.$first_instant_store_group_id : '' }}">
                                                {{ env('APP_URL','https://jeeb.tech/') }}api/users/home_static_{{$homestatic_key}}?store={{ $item->store_key}}{{ $group_enabled && isset($first_instant_store_group_id) ? '&group='.$first_instant_store_group_id : '' }}
                                            </a> | Method: GET
                                        </span><br/>
                                    </div>
                                    <div class="col-sm-6">
                                        <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                            action="{{ route('admin.app_homepage.stores_home_static_'.$homestatic_key) }}">
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
                                                        @php
                                                            $homestatic_items = $item->getHomeStaticEn($homestatic_key);
                                                        @endphp
                                                        @if ($homestatic_items)
                                                            @foreach ($homestatic_items as $key => $home_static)
                                                                <tr>
                                                                    <td>{{ $key + 1 }}</td>
                                                                    <td><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
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
                                            action="{{ route('admin.app_homepage.stores_home_static_'.$homestatic_key) }}">
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
                                                            <th>Last Processed At</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $homestatic_items = $item->getHomeStaticAr($homestatic_key);
                                                        @endphp
                                                        @if ($homestatic_items)
                                                            @foreach ($homestatic_items as $key => $home_static)
                                                                <tr>
                                                                    <td>{{ $key + 1 }}</td>
                                                                    <td><a href="{{"/storage/".$home_static->file_name}}" target="_blank">{{$home_static->file_name}}</a></td>
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
                                </div>
                                <br clear="all"/><br/>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    <script>
        $('.store_key_selector').click(function () {
            let store_key = $(this).attr('id');
            $('.container_'+store_key).toggle();
        });

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
