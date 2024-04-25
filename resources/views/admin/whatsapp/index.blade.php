@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .row_sending_push {
        display: none;
    }
    .row_send_push {
        display: block;
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">WhatsApp API</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/users'); ?>">Users</a></li>
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
                <div class="card-body" style="overflow-x: scroll">
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Access Token</th>
                                <th>Expire at</th>
                                <th>Created Date </th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="user<?= $user->user_id; ?>">
                                <td>1</td>
                                <td>{{$user->token}}</td>
                                <td>{{$user->expires_at}}</td>
                                <td>{{$user->joined?$user->joined:'N/A'}}</td>
                                <td>
                                    @if($user->status == 1)
                                        <p class="text-success">Active</p>
                                    @else
                                         <p class="text-warning">Inactive</p>
                                    @endif 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection