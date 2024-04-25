@extends('vendor.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Payment Card</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/profile') ?>">Payment Card</a></li>
                        
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
                            action="{{ route('vendor.settings.payment_store') }}">
                            @csrf
                            <div class="row">
                               
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Store Name</label>
                                        <input type="text" name="store" id="store"
                                            value="{{ $stores_data->name??'' }}" class="form-control"
                                            placeholder="Store Name">
                                    </div>
                                </div>
                               
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label id="discount_type_id">Company Name</label>
                                        <input type="text" name="company" id="company"
                                            value="{{ $stores_data->company_name??'' }}" class="form-control"
                                            placeholder="Company Name">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" id="email"
                                            value="{{ $user->email??'' }}" class="form-control"
                                            placeholder="Email" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Expiration</label>
                                        <input type="text" name="expiration" id="expiration"
                                            value="{{ old('expiration', ) }}" class="form-control"
                                            placeholder="Expiration">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Owner</label>
                                        <input type="text" name="name" id="name"
                                            value="{{ $user->name??'' }}" class="form-control"
                                            placeholder="Owner Name" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Number</label>
                                        <input type="number" name="number" id="number"
                                            value="{{ $user->mobile??'' }}" class="form-control"
                                            placeholder="Number">
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
        $(document).ready(function() {
            $('#regForm').validate({ // initialize the plugin
                rules: {
                    offer_type: {
                        required: true
                    },
                    type: {
                        required: true
                    },
                    coupon_code: {
                        required: true
                    },
                    title_en: {
                        required: true
                    },
                    title_ar: {
                        required: true
                    },
                    description_en: {
                        required: true
                    },
                    description_ar: {
                        required: true
                    },
                    discount: {
                        required: true,
                        number: true
                    },
                    min_amount: {
                        required: true,
                        number: true
                    },
                    uses_limit: {
                        required: true,
                        digits: true
                    },
                    expiry_date: {
                        required: true
                    }
                }
            });
        });


    </script>
@endsection
