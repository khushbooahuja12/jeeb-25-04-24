@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Administrator</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/administrators') ?>"> Administrator</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                        <form method="post" id="addForm" enctype="multipart/form-data"
                            action="{{ route('admin.administrators.update',['id'=>base64url_encode($administrator->id)]) }}" autocomplete="off">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name </label>
                                        <input type="text" name="name" id="name"
                                            value="{{ old('name',$administrator->name) }}" data-title="Name"
                                            class="form-control regInputs" placeholder="Name">
                                        <p class="errorPrint" id="nameError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email </label>
                                        <input type="email" name="email" id="email"
                                            value="{{ old('email',$administrator->email) }}" data-title="Email"
                                            class="form-control regInputs" placeholder="Email" autocomplete="off">
                                        <p class="errorPrint" id="emailError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Role</label>
                                        <select name="fk_role_id" id="fk_role_id"
                                            data-title="Role" class="form-control regInputs">
                                            <option value="" selected disabled>Select Role</option>
                                            @foreach ($roles as $role )
                                                <option value="{{ $role->id }}" {{ $role->id == $administrator->fk_role_id ? 'selected':'' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Store</label>
                                        <select name="fk_store_id" id="fk_store_id"
                                            data-title="Role" class="form-control regInputs">
                                            <option value="" selected disabled>Select Store</option>
                                            @foreach ($stores as $store )
                                                <option value="{{ $store->id }}" {{ $administrator->fk_store_id == $store->id ? 'selected':'' }}>{{ $store->company_name }}-{{ $store->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Vendor</label>
                                        <select name="fk_company_id" id="fk_company_id"
                                            data-title="Role" class="form-control regInputs">
                                            <option value="" selected disabled>--Select--</option>
                                            @foreach ($companies as $company )
                                                <option value="{{ $company->id }}" {{ $administrator->fk_company_id == $company->id ? 'selected':'' }}>{{ $company->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password (Leave blank if no need to change)</label>
                                        <input type="password" name="password" id="password"
                                            value="{{ old('password') }}" data-title="Password"
                                            class="form-control regInputs" placeholder="Password" autocomplete="off">
                                        <p class="errorPrint" id="passwordError"></p>
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
                                            onclick="cancelForm(2);">
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
    </div>
    <script>
        
        $(document).ready(function() {

            $('#addForm').validate({
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true
                    },
                    fk_role_id: {
                        required: true
                    }
                }
            });

        });
    </script>
@endsection
