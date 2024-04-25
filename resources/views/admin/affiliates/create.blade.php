@extends('admin.layouts.dashboard_layout_for_affiliate_admin')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add New Affiliates</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/ads'); ?>">Affiliates</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    @if ($errors->any())
                      <div class="alert alert-danger">
                         <ul style="padding-bottom: 0px; margin-bottom: 0px;">
                            @foreach ($errors->all() as $error)
                               <li>{{ $error }}</li>
                            @endforeach
                         </ul>
                      </div>
                    @endif
                    @if(session()->has('message'))
                        <div class="alert alert-success">
                            {{ session()->get('message') }}
                        </div>
                    @endif
                    <br/>
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.affiliates.store')}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" name="affiliates_name" class="form-control" placeholder="Name">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" name="affiliates_email" class="form-control" placeholder="Email">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Mobile</label>
                                    <input type="text" name="affiliates_mobile" class="form-control" placeholder="Mobile">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Code</label>
                                    <input type="text" name="affiliates_code" class="form-control" placeholder="Code" readonly value="{{ $affiliate_code }}">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>QR Image</label>
                                    <input type="file" name="affiliates_image" class="form-control dropify" accept="image/*">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        Ref URL: 
                                        <input type="text" name="ref_url" class="form-control" placeholder="ref_url" readonly value="{{ $ref_url_base }}{{ $affiliate_code }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(1);">
                                            Cancel
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
    $(document).ready(function () {
        $('#addForm').validate({
            rules: {
                affiliates_code: {
                    required: true
                }
            }
        });
    });
</script>
@endsection