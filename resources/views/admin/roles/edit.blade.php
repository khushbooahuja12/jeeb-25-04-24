@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Role</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/roles') ?>">Roles</a></li>
                        <li class="breadcrumb-item active">Add</li>
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
                        <form method="post" id="editForm" enctype="multipart/form-data"
                            action="{{ route('admin.roles.update',[base64url_encode($role->id)]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Role Name </label>
                                        <input type="text" name="name" id="role_name"
                                            value="{{ old('name',$role->name) }}" data-title="Role Name"
                                            class="form-control regInputs" placeholder="Role Name">
                                        <p class="errorPrint" id="role_nameError"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Role Description</label>
                                        <textarea name="description" id="description" data-title="Role Description"
                                            class="form-control regInputs" placeholder="Role Description">{{ old('description',$role->description) }}</textarea>
                                        <p class="errorPrint" id="descriptionError"></p>
                                    </div>
                                </div>
                            </div>
                            <label>Permissions</label>
                            <div class="row">
                                @foreach ($permissions as $permission )
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <input type="checkbox" name="permissions[]" value="{{ old('permissions[]',$permission->id) }}" {{ in_array($permission->id, $rol_permissions) ? 'checked': '' }} />&ensp;{{ $permission->name }}
                                        </div>
                                    </div>
                                @endforeach
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

            $('#editForm').validate({
                rules: {
                    name: {
                        required: true
                    }
                }
            });

        });
    </script>
@endsection
