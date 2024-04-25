@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add New Company</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/companies'); ?>">Companies</a></li>
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
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.companies.store')}}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Comany Name</label>
                                    <input type="text" name="name" id="name" value="{{old('name')}}" data-title="Name" class="form-control regInputs" placeholder="Name">
                                    <p class="errorPrint" id="nameError"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" autocomplete="email" id="email" data-title="Email" value="{{old('email')}}" class="form-control regInputs" placeholder="Email">
                                    <p class="errorPrint" id="emailError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" autocomplete="password" id="password" data-title="Password" value="" class="form-control regInputs" placeholder="Password">
                                    <p class="passwordPrint" id="passwordError"></p>
                                </div>  
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes</label>
                                    <textarea name="notes" id="notes" data-title="Notes" value="" class="form-control regInputs" placeholder="Notes"></textarea>
                                    <p class="errorPrint" id="notesError"></p>
                                </div>  
                            </div>
                        </div>
                        <div class="form-group">
                            <div>
                                <button type="submit" class="btn btn-primary waves-effect waves-light">
                                    Submit
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
    $(document).ready(function () {

        $('#addForm').validate({// initialize the plugin
            rules: {
                name: {
                    required: true
                },
                email: {
                    required: true,
                    email: true
                },
                password: {
                        required: true,
                        minlength: 8,
                        mypassword: true
                    },
            }
        });

    });
</script>
@endsection