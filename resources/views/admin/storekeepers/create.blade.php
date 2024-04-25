@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Storekeeper</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/storekeepers') ?>">Storekeepers</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="addForm" enctype="multipart/form-data"
                            action="{{ route('admin.storekeepers.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="name" class="form-control" placeholder="Name">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mobile Number</label>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <input type="text" name="country_code" value="{{ old('country_code') }}"
                                                    class="form-control numericOnly" placeholder="Code">
                                            </div>
                                            <div class="col-md-9">
                                                <input type="text" name="mobile" value="{{ old('mobile') }}"
                                                    class="form-control numericOnly" placeholder="Mobile">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" name="address" class="form-control" placeholder="Address">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Image</label>
                                        <input type="file" name="image" class="form-control dropify" accept="image/*">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Store</label>
                                        <select class="form-control" name="fk_store_id">
                                            <option disabled selected>Select Store</option>
                                            @if ($stores)
                                                @foreach ($stores as $key => $value)
                                                    <option value="{{ $value->id }}">
                                                        {{ $value->company_name.' - '.$value->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div>
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                Submit
                                            </button>
                                        </div>
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
        $(document).ready(function() {
            $('#addForm').validate({
                rules: {
                    name: {
                        required: true
                    },
                    mobile: {
                        required: true
                    },
                    address: {
                        required: true
                    }
                }
            });
        });
    </script>
@endsection
