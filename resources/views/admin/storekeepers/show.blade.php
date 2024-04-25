@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Storekeeper Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/storekeepers') ?>">Storekeepers</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="row">
                            <div class=" offset-11 col-md-1">
                                <a href="<?= url('admin/storekeepers/edit/' . base64url_encode($storekeeper->id)) ?>"
                                    class="btn btn-primary waves-effect waves-light text-white">
                                    Edit
                                </a>
                            </div>
                        </div>
                        @csrf
                        <div class="row">
                            <div class="offset-3 col-md-9 justify-center">
                                <table class="table table-borderless table-responsive">
                                    <tr>
                                        <th>Name </th>
                                        <td> {{ $storekeeper->name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email </th>
                                        <td> {{ $storekeeper->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Mobile </th>
                                        <td> {{ '+' . $storekeeper->country_code . ' ' . $storekeeper->mobile }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address </th>
                                        <td> {{ $storekeeper->address }}</td>
                                    </tr>
                                    <tr>
                                        <th>Store </th>
                                        <td> {{ $storekeeper->getStore->name ? $storekeeper->getStore->company_name.' - '.$storekeeper->getStore->name : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status </th>
                                        <td>
                                            @if ($storekeeper->status == 0)
                                                <span class="text-danger">Blocked</span>
                                            @else
                                                <span class="text-success">Active</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Sub Categories </th>
                                        <td>
                                            <?php
                                            if ($storekeeper->getSubCategories) {
                                                $store_arr = [];
                                                foreach ($storekeeper->getSubCategories as $key => $value) {
                                                    if($value->getSubCategory && $value->getSubCategory->category_name_en) {
                                                        array_push($store_arr, '<span>' . $value->getSubCategory->category_name_en . '</span><br>');
                                                    }
                                                }
                                                echo implode('', $store_arr);
                                            } else {
                                                echo 'N/A';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
