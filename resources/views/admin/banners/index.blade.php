@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Banners
                        <a href="<?= url('admin/banners/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/banners') ?>">Banners</a></li>
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
                                <input type="text" class="form-control" name="filter" placeholder="Search Banner"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>                                    
                                    <th>@sortablelink('heading_en', 'Heading (En)')</th>
                                    <th>Heading (Ar)</th>
                                    <th>@sortablelink('banner_name', 'Banner Name')</th>
                                    <th>Redirect Type</th>
                                    <th class="nosort">Image</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($banners)
                                    @foreach ($banners as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->heading_en }}</td>
                                            <td>{{ $row->heading_ar }}</td>
                                            <td>{{ $row->banner_name }}</td>
                                            <td>{{ $row->redirect_type == '1' ? 'Offer' : 'Product' }}</td>
                                            <td><img src="{{ !empty($row->getBannerImage) ? asset('images/banner_images') . '/' . $row->getBannerImage->file_name : asset('assets/images/no_img.png') }}"
                                                    width="75" height="50"></td>
                                            <td>
                                                <a href="<?= url('admin/banners/detail/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-eye"></i></a>&ensp;
                                                <a href="<?= url('admin/banners/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                <a title="Remove Banner"
                                                    href="<?= url('admin/banners/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to remove this banner ?')"><i
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
                                    Showing {{ $banners->count() }} of {{ $banners->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $banners->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
