@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Add New Vehicle</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/vehicles'); ?>">Vehicles</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.drivers.store_vehicle')}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Vehicle Number</label>
                                    <input type="text" name="vehicle_number" class="form-control" maxlength="10" placeholder="Ex. XXXXX">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Vehicle Type</label>
                                    <select class="form-control" name="vehicle_type">
                                        <option value="1" selected>Car</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Order limit</label>
                                    <input type="text" name="vehicle_capacity" class="form-control numericOnly" maxlength="2" placeholder="Max order to carry">
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
                vehicle_number: {
                    required: true
                },
                vehicle_type: {
                    required: true
                },
                vehicle_capacity: {
                    required: true,
                }
            }
        });
    });
</script>
@endsection