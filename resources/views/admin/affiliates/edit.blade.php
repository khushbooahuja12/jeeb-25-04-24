@extends('admin.layouts.dashboard_layout_for_affiliate_admin')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Affiliates</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/affiliates'); ?>">Affiliates</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.affiliates.update',[base64url_encode($affiliates->id)])}}">
                        @csrf
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Name</label>
                                    <input type="text" value="{{$affiliates->name}}" name="affiliates_name" class="form-control" placeholder="Name">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="text" value="{{$affiliates->email}}" name="affiliates_email" class="form-control" placeholder="Email">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Mobile</label>
                                    <input type="text" value="{{$affiliates->mobile}}" name="affiliates_mobile" class="form-control" placeholder="Mobile">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Code</label>
                                    <input type="text" value="{{$affiliates->code}}" name="affiliates_code" class="form-control" placeholder="Code" readonly>
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>QR Image</label>
                                    @if ($affiliates->qr_code_image_url) 
                                        <img src="{{ '/storage/'.$affiliates->qr_code_image_url }}" width="auto" height="30"/>
                                    @endif
                                    <input type="file" name="affiliates_image" class="form-control dropify" accept="image/*">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        Ref URL: 
                                        <input type="text" name="ref_url" class="form-control" placeholder="ref_url" readonly value="{{ $ref_url_base }}{{  $affiliates->code }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <input type="checkbox" value="1" name="status"
                                    <?= $affiliates->status == 1 ? 'checked' : '' ?> />
                                </div>  
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(2);">
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
                affiliates_code:{
                    required: true
                }
            }
        });
    });
</script>
@endsection