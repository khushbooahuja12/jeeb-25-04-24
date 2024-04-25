@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Scratch Card Bulk Upload To Users</h4>
                </div>
                <div class="col-sm-6">

                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="createMultipleForm" enctype="multipart/form-data"
                            action="{{ route('admin.scratch_cards.bulk_upload_to_users_post') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="page-title">Upload User IDs List with Scratch Card IDs</h5>
                                    <hr/>
                                    <div class="form-group">
                                        <label>Select CSV File</label>
                                        <input type="file" name="file" accept=".csv" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="page-title">Sample CSV</h5>
                                    <hr/>
                                    <table class="table table-bordered dt-responsive"
                                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>User ID</th>
                                                <th>ScratchCard ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>123</td>
                                                <td>XX</td>
                                            </tr>
                                            <tr>
                                                <td>124</td>
                                                <td>XX</td>
                                            </tr>
                                            <tr>
                                                <td>125</td>
                                                <td>XX</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#createMultipleForm').validate({
                rules: {
                    file: {
                        required: true
                    }
                }
            });
        });
    </script>
@endsection
