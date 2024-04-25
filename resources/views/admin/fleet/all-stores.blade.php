@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Stores List
                        <a href="<?= url('admin/stores/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/fleet/stores') ?>">Stores</a></li>
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
                            <a href="<?= url('admin/fleet/stores') ?>">
                                <button type="button" class="btn btn-warning">Clear</button>
                            </a>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>@sortablelink('name', 'Store Name')</th>
                                    <th>Company Name</th>
                                    <th>Address</th>
                                    <th>Latitude</th>
                                    <th>Longitute</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->getCompany ? $row->getCompany->name : '' }}</td>
                                            <td>{{ $row->address }}</td>
                                            <td>{{ $row->latitude }}</td>
                                            <td>{{ $row->longitude }}</td>
                                            <td><a href="http://maps.google.com/maps?&z=10&q=<?=$row->latitude?>+<?=$row->longitude?>" target="_blank"><i class="fa fa-eye"></i></a></td>
                                            {!! $row->status==1 ? '<td style="background: #00ff00;">active</td>' : '<td style="background: #ff0000;">inactive</td>' !!}
                                            <td><a href="<?=url('admin/fleet/stores/edit/'.base64url_encode($row->id))?>"><i class="fa fa-pen"></i></a></td>
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
