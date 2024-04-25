@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Create Bot Command</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/brands'); ?>">Brands</a></li>
                    <li class="breadcrumb-item active">List</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">               
                <div class="card-body">
                    <form method="post" id="commandForm" enctype="multipart/form-data"
                            action="{{ url('admin/bot_rotator/store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Identifier</label>
                                        <select name="identifier" id="identifier"
                                            data-title="Identifier" class="form-control regInputs select2">
                                            <option value="" disabled selected>--Select--</option>
                                            <option value="home_page">Home Page</option>
                                            <option value="order_page">Order Page</option>
                                            <option value="cart_page">Cart Page</option>
                                            <option value="saved_cart">Saved Carts</option>
                                            <option value="buy_it_for_me">Buy it for me</option>
                                            <option value="search_page">Search page</option>
                                            <option value="search_single_product">Search - Single product</option>
                                            <option value="search_multiple_product">Search - Multiple Product</option>
                                        </select>
                                        <p class="errorPrint" id="identifier_Error"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Command</label>
                                        <input type="text" name="command" id="command" data-title="Command"
                                            value="{{ old('command') }}" class="form-control regInputs">
                                        <p class="errorPrint" id="command_Error"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5"
                                            onclick="cancelForm(1);">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    
                </div>
            </div>
        </div>
    </div>
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Bulk Upload Bot Commands</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">               
                <div class="card-body">
                    <form method="post" id="commandFileUploadForm" enctype="multipart/form-data"
                            action="{{ url('admin/bot_rotator/upload_csv') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select CSV File</label>
                                        <input type="file" name="command_file" id="command_file" data-title="Select CSV File" class="form-control regInputs">
                                        <p class="errorPrint" id="command_file_Error"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#commandFileUploadForm').validate({
            rules: {
                command_file: {
                    required: true
                }
            }
        });
    });
</script>
@endsection