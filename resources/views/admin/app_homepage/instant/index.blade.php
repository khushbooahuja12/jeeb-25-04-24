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
                    <h4 class="page-title">Home Static Instant Upload</h4>
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
                            <div class="col-sm-6">
                                <div class="col-sm-12 mb-3">
                                    <h4>Home Static Instant (English)</h4>
                                    @php
                                        $home_static_json_english_file = \App\Model\HomeStatic::where('lang', 'en')->where(['home_static_type' => 'home_static_instant'])->orderBy('id', 'desc')->first();

                                        if ($home_static_json_english_file && $home_static_json_english_file->last_processed_at != null) {
                                            $english_file_time_difference_in_minutes = \Carbon\Carbon::now()->diffInMinutes($home_static_json_english_file->last_processed_at);
                                        } else {
                                            $english_file_time_difference_in_minutes = 6;
                                        }
                                    @endphp

                                    @if ($home_static_json_english_file)
                                        @if ($home_static_json_english_file->last_processed_at != null && $english_file_time_difference_in_minutes <= 5)
                                            <button class="btn btn-primary" type="button" disabled>
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                Command has been queued for execution...
                                            </button>
                                        @else
                                            <button class="btn btn-primary" id="run-command-english">Run Command</button>
                                        @endif
                                    @endif
                                </div>
                                <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                    action="{{ route('admin.app_homepage.home_static_instant_store') }}">
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
                                                @if ($home_static_1_ens)
                                                    @foreach ($home_static_1_ens as $key => $home_static)
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
                                <div class="col-sm-12 mb-3">
                                    <h4>Home Static Instant (Arabic)</h4>
                                    @php
                                        $home_static_json_english_file = \App\Model\HomeStatic::where('lang', 'ar')->where(['home_static_type' => 'home_static_instant'])->orderBy('id', 'desc')->first();

                                        if ($home_static_json_english_file && $home_static_json_english_file->last_processed_at != null) {
                                            $arabic_file_time_difference_in_minutes = \Carbon\Carbon::now()->diffInMinutes($home_static_json_english_file->last_processed_at);
                                        } else {
                                            $arabic_file_time_difference_in_minutes = 6;
                                        }
                                    @endphp

                                    @if ($home_static_json_english_file)
                                        @if ($home_static_json_english_file->last_processed_at != null && $arabic_file_time_difference_in_minutes <= 5)
                                            <button class="btn btn-primary" type="button" disabled>
                                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                Command has been queued for execution...
                                            </button>
                                        @else
                                            <button class="btn btn-primary" id="run-command-arabic">Run Command</button>
                                        @endif
                                    @endif
                                </div>
                                <form method="post" id="regForm" enctype="multipart/form-data" style="border: 1px #aaaaaa solid; padding: 15px 15px 5px; margin: 0px;"
                                    action="{{ route('admin.app_homepage.home_static_instant_store') }}">
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
                                                @if ($home_static_1_ars)
                                                    @foreach ($home_static_1_ars as $key => $home_static)
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
    <script>
        $('#run-command-english').click(function () {
            $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>Command has been queued for execution...');
            $(this).prop('disabled', true);
            var language = 'en';
            var model = 'home_static_instant';

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
            var model = 'home_static_instant';

            $.ajax({
                url: '{{ route('proccess-home-static-json') }}',
                type: 'POST',
                data: JSON.stringify({ model: model, language: language }),
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
