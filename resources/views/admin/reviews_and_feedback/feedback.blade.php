@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    html { 
        overflow-y: scroll; 
        overflow-x:hidden; 
    }
    body { 
        position: absolute; 
    }
    .rating-stars i {
        color: #FFD700; /* Set the star color to gold*/
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <ul class="nav nav-pills nav-justified" role="tablist" style="border:1px solid #30419b">
                    <li class="nav-item waves-effect waves-light">
                        <a href="<?= url('admin/order-reviews'); ?>" class="nav-link" aria-selected="true">
                            <span class="d-none d-md-block">Reviews</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span> 
                        </a>
                    </li>
                    <li class="nav-item waves-effect waves-light">
                        <a href="<?= url('admin/user-feedback'); ?>" class="nav-link active" aria-selected="false">
                            <span class="d-none d-md-block">Feedback</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                        </a>
                    </li>                   
                </ul>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">User Feedback</li>
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
                            <input type="text" class="form-control" name="filter" placeholder="Search..." value="{{$filter}}">
                        </div>
                        <button type="submit" class="btn btn-dark">Filter</button>
                    </form>
                    <table class="table table-bordered dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Description</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($feedbacks)
                            @foreach($feedbacks as $key=>$value)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>
                                    <a href="<?= url('admin/users/show') . '/' . base64url_encode($value->fk_user_id); ?>" target="_blank">
                                        {{$value->getUser?($value->getUser->name?$value->getUser->name:'+'.$value->getUser->country_code.'-'.$value->getUser->mobile):"N/A"}}
                                    </a>
                                </td>
                                <td>
                                    <div class="rating-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $value->experience)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </td>
                                <td>{{$value->description}}</td>
                                <td>{{date('d-m-Y h:i A',strtotime($value->created_at))}}</td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$feedbacks->count()}} of {{ $feedbacks->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $feedbacks->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection