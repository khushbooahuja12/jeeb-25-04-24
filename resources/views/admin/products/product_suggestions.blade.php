@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Suggested Products
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/product_suggestions') ?>">Suggested Products</a>
                        </li>
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
                                    <th>User</th>
                                    <th>@sortablelink('product_company', 'Product Company')</th>
                                    <th>@sortablelink('product_name', 'Product Name')</th>
                                    <th width="45%">@sortablelink('product_description', 'Product Description')</th>
                                    <th class="nosort">Product Image</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($suggestions)
                                    @foreach ($suggestions as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                @if ($row->getUser && $row->getUser->name) 
                                                    {{ $row->getUser->name }}
                                                @elseif ($row->getUser && $row->getUser->country_code && $row->getUser->mobile)
                                                    {{ '+' . $row->getUser->country_code . $row->getUser->mobile }}
                                                @else
                                                    User not found!
                                                @endif
                                            </td>
                                            <td>{{ $row->product_company }}</td>
                                            <td>{{ $row->product_name }}</td>
                                            <td>{{ $row->product_description }}</td>
                                            <td>
                                                <a href="{{ !empty($row->getProductSuggestionImage) ? asset('/') . $row->getProductSuggestionImage->file_path . $row->getProductSuggestionImage->file_name : asset('assets/images/no_img.png') }}"
                                                    target="_blank">
                                                    <img src="{{ !empty($row->getProductSuggestionImage) ? asset('/') . $row->getProductSuggestionImage->file_path . $row->getProductSuggestionImage->file_name : asset('assets/images/no_img.png') }}"
                                                        width="75" height="50">
                                                </a>
                                            </td>
                                            <td>
                                                <a href="">
                                                    <button class="btn btn-primary btn-sm">Add</button>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $suggestions->count() }} of {{ $suggestions->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $suggestions->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
