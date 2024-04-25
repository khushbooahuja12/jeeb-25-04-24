@extends('vendor.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Settings</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/profile') ?>">pass key</a></li>

                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="regForm" enctype="multipart/form-data"
                            action="{{ route('vendor.settings.pass_key_generate') }}">
                            @csrf
                            <h6>Generate Pass Key</h6>
                            <div class="row">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                               

                            </div><br>
                            <div class="form-group">
                                <div>
                                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                                        Generate
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // $(document).ready(function() {
        //     // Generate a new CSRF token
        //     // var newToken = '{{ Session::token() }}';
        //     var newToken = '{{ csrf_token() }}';
        //     console.log(newToken);
        //     // Update the value of the hidden input field with the new CSRF token
        //     document.querySelector('input[name="_token"]').value = newToken;

        // })
    </script>

    </script>
@endsection
