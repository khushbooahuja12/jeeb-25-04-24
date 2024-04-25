@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Ads
                        <a href="<?= url('admin/ads/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/ads') ?>">Ads</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Ad Name"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('name', 'Name')</th>
                                    <th>Redirect Type</th>
                                    <th class="nosort">Image</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($ads)
                                    @foreach ($ads as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->redirect_type == '1' ? 'Offer' : 'Product' }}</td>
                                            <td><img src="{{ !empty($row->getAdsImage) ? asset('images/ads_images') . '/' . $row->getAdsImage->file_name : asset('assets/images/no_img.png') }}"
                                                    width="75" height="50"></td>
                                            <td>
                                                <a href="<?= url('admin/ads/detail/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-eye"></i></a>&ensp;
                                                <a href="<?= url('admin/ads/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                <a title="Remove Ads"
                                                    href="<?= url('admin/ads/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to remove this ads ?')"><i
                                                        class="icon-trash-bin"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $ads->count() }} of {{ $ads->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $ads->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
