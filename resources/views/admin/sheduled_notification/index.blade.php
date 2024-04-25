@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Sheduled Notifications 
                    <a href="<?= url('admin/sheduled_notification/create'); ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Create New
                    </a>
                </h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Sheduled Notifications</li>
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
                    <div class="form-group">
                        All Users - {{ $users_count->all_users }}<br/>
                        All English Users - {{ $users_count->english_users }}<br/>
                        All Arabic Users - {{ $users_count->arabic_users }}
                    </div>
                    <form class="form-inline searchForm" method="GET">
                        <div class="form-group mb-2">
                            <input type="text" class="form-control" name="filter" placeholder="Search Notifications" value="{{$filter}}">
                        </div>
                        <button type="submit" class="btn btn-dark">Filter</button>
                    </form>
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>@sortablelink('title', 'Title')</th>
                                <th>@sortablelink('body', 'Body')</th>
                                <th>Users</th>
                                <th>@sortablelink('shedule_time', 'Sheduled Time')</th>
                                <th class="nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($notifications)
                            @foreach($notifications as $key=>$row)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>{{$row->title}}</td>
                                <td>{{$row->body}}</td>
                                <td>
                                    <?php
                                    if ($row->userIds=="all") {
                                        echo "All users";
                                    } elseif ($row->userIds=="all_english_users") {
                                        echo "All English users";
                                    } elseif ($row->userIds=="all_arabic_users") {
                                        echo "All Arabic users";
                                    } elseif ($row->getUsers($row->userIds)) {
                                        echo (strlen($row->getUsers($row->userIds)->mobileNums) > 25 ? substr($row->getUsers($row->userIds)->mobileNums, 0, 25) . '...' : $row->getUsers($row->userIds)->mobileNums);
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </td>
                                <td>{{$row->shedule_time}}</td>
                                <td>
                                    @if($row->sent==0) 
                                        <a href="<?= url('admin/sheduled_notification/edit/' . base64url_encode($row->id)); ?>"><button class="btn btn-sm btn-danger">Edit</button></a>&ensp;
                                    @else
                                        <a href="<?= url('admin/sheduled_notification/resend/' . base64url_encode($row->id)); ?>"><button class="btn btn-sm btn-primary">Reshedule</button></a>&ensp;
                                    @endif
                                    <a href="<?= url('admin/sheduled_notification/detail/' . base64url_encode($row->id)); ?>"><i class="icon-eye"></i></a>&ensp;
                                    <a title="Delete" href="<?= url('admin/sheduled_notification/destroy/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure you want to delete this notification ?')"><i class="icon-trash-bin"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$notifications->count()}} of {{ $notifications->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $notifications->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection