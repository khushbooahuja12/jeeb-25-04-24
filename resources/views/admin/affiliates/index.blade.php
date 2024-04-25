@extends('admin.layouts.dashboard_layout_for_affiliate_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Affiliates
                        <a href="<?= url('admin/affiliates/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/affiliates') ?>">Affiliates</a></li>
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
                    <div class="card-body" style="overflow: scroll;">
                        @if ($errors->any())
                          <div class="alert alert-danger">
                             <ul>
                                @foreach ($errors->all() as $error)
                                   <li>{{ $error }}</li>
                                @endforeach
                             </ul>
                          </div>
                        @endif
                        @if(session()->has('message'))
                            <div class="alert alert-success">
                                {{ session()->get('message') }}
                            </div>
                        @endif
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('name', 'Name')</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th># Of Registered Users</th>
                                    <th>Code</th>
                                    <th>Ref URL</th>
                                    <th class="nosort">Image</th>
                                    <th>Status</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($affiliates)
                                    @foreach ($affiliates as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->email }}</td>
                                            <td>{{ $row->mobile }}</td>
                                            <td>{{ $row->userRegisteredMobiles->count() }}</td>
                                            <td>{{ $row->code }}</td>
                                            <td>{{ $ref_url_base }}{{ $row->code}}</td>
                                            <td>
                                                @if ($row->qr_code_image_url) 
                                                    <img src="{{ '/storage/'.$row->qr_code_image_url }}" width="auto" height="50"/>
                                                @endif
                                            </td>
                                            <td>{{ $row->status == '1' ? 'Active' : 'Inactive' }}</td>
                                            <td>
                                                <a href="<?= url('admin/affiliates/detail/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-eye"></i></a>&ensp;
                                                <a href="<?= url('admin/affiliates/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                {{-- <a title="Remove affiliate"
                                                    href="<?= url('admin/affiliates/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to remove this affiliate?')"><i
                                                        class="icon-trash-bin"></i></a> --}}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $affiliates->count() }} of {{ $affiliates->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $affiliates->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
