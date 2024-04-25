@extends('admin.layouts.dashboard_layout')
@section('content')
    <style>
        .wrapper {
            padding: 50px;
        }

        .image--cover {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin: 20px;
            border: 5px double #8284b1;
            object-fit: cover;
            object-position: center right;
        }

        .doc-images img {
            width: 50%;
            height: 200px;
            border-radius: 10px;
        }

        label {
            color: #212529;
        }
    </style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Driver Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/drivers') ?>">Drivers</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        @csrf
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <div class="wrapper">
                                    <img src="{{ $driver->getDriverImage ? asset('images/driver_images') . '/' . $driver->getDriverImage->file_name : asset('assets/images/dummy_user.png') }}"
                                        class="image--cover">
                                </div>
                            </div>
                            <div class="offset-3 col-md-9 justify-center">
                                <table class="table table-borderless">
                                    <tr>
                                        <th>Driver Name </th>
                                        <td> {{ $driver->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email </th>
                                        <td> {{ $driver->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile </th>
                                        <td> {{ '+' . $driver->country_code . ' ' . $driver->mobile }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status </th>
                                        <td>
                                            @if ($driver->status == 0)
                                                <span class="text-danger">Blocked</span>
                                            @else
                                                <span class="text-success">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Vehicle </th>
                                        <td> {{ $driver->getVehicle ? $driver->getVehicle->vehicle_number : 'Not assigned' }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-12 text-center mt-3 mb-2">
                                <label>Attached Documents</label>
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="text-bold">National Identity Card</label>
                                @if ($driver->getNationalId)
                                    <div class="doc-images">
                                        <a href="{{ asset('images/driver_images') . '/' . $driver->getNationalId->file_name }}"
                                            title="view" target="_blank"><img
                                                src="{{ asset('images/driver_images') . '/' . $driver->getNationalId->file_name }}"
                                                class="doc-image"></a>
                                    </div>
                                @else
                                    <p>Not attached</p>
                                @endif
                            </div>
                            <div class="col-md-4 text-center">
                                <label class="text-bold">Driving License</label>
                                @if ($driver->getDrivingLicence)
                                    <div class="doc-images">
                                        <a href="{{ asset('images/driver_images') . '/' . $driver->getDrivingLicence->file_name }}"
                                            title="view" target="_blank"><img
                                                src="{{ asset('images/driver_images') . '/' . $driver->getDrivingLicence->file_name }}"
                                                class="doc-image"></a>
                                    </div>
                                @else
                                    <p>Not attached</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
