@extends('store.layouts.dashboard_layout')
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
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/drivers') ?>">Drivers</a>
                        </li>
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
                                        <td> {{ $driver->name . ' (' . $driver->id . ')' }}</td>
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
                                            @if ($driver->status == 4)
                                                <span class="text-danger">Rejected</span>
                                            @else
                                                @if ($driver->status == 3)
                                                    <span class="text-danger">Blocked</span>
                                                @else
                                                    @if ($driver->status == 1)
                                                        <span class="text-primary">Not approved</span>
                                                    @else
                                                        @if ($driver->status == 2)
                                                            <span class="text-success">Active</span>
                                                        @else
                                                            <span class="text-warning">Account not verified</span>
                                                        @endif
                                                    @endif
                                                @endif
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
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Assigned Orders</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($orders)
                                    @foreach ($orders as $key => $value)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ '#' . $value->orderId }}</td>
                                            <td>{{ $value->getSubOrder ? $value->total_amount + $value->getOrderSubTotal($value->id) : $value->total_amount }}
                                            </td>
                                            <td>{{ $order_status[$value->status] }}</td>
                                            <td>
                                                <a
                                                    href="<?= url('store/' . $storeId . '/orders/detail/' . base64url_encode($value->id)) ?>"><i
                                                        class="icon-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script>
            function changeDriverStatus(obj, id, status) {
                if (status == 2) {
                    var confirm_chk = confirm('Are you sure to approve this driver?');
                } else {
                    var confirm_chk = confirm('Are you sure to reject this driver?');
                }
                if (confirm_chk) {
                    if (id) {
                        $.ajax({
                            url: "<?= url('admin/drivers/change_driver_status') ?>",
                            type: 'post',
                            data: 'id=' + id + '&action=' + status + '&_token=<?= csrf_token() ?>',
                            success: function(data) {
                                if (data.error_code == "200") {
                                    alert(data.message);
                                    location.reload();
                                } else {
                                    alert(data.message);
                                }
                            }
                        });
                    } else {
                        alert("Something went wrong");
                    }
                } else {
                    return false;
                }
            }
        </script>

    @endsection
