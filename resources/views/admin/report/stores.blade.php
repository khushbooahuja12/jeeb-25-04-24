@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">All Stores
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/stores') ?>">Stores</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Store Name"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('name', 'Store Name')</th>
                                    <th>Company Name</th>
                                    {{-- <th>@sortablelink('email', 'Email')</th>
                                    <th>@sortablelink('mobile', 'Mobile')</th> --}}
                                    <th>Address</th>
                                    <th>Active Products</th>
                                    <th>All Products</th>
                                    <th>Status (Active)</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->getCompany ? $row->getCompany->name : '' }}</td>
                                            {{-- <td>{{ $row->email }}</td>
                                            <td>{{ $row->mobile }}</td> --}}
                                            <td>{{ $row->address }}</td>
                                            <td>{{ $row->getAllProducts() ? $row->getAllProducts()->where('store'.get_store_no($row->name),'>',0)->count() : '' }}</td>
                                            <td>{{ $row->getAllProducts() ? $row->getAllProducts()->count() : '' }}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <?= $row->status == 1 ? 'Active' : 'Inactive' ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a title="view details"
                                                    href="<?= url('admin/report/stores/' . $row->id . '/categories') ?>"><i
                                                        class="fa fa-eye"></i>
                                                </a>&ensp;                                                
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $stores->count() }} of {{ $stores->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $stores->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
