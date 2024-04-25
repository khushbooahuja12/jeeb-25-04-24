@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Notification Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/custom_notifications'); ?>">Notifications</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                    
                    <div class="row">
                        <div class="col-md-6 mt-3">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Title</th>
                                    <td>{{$notification->title}}</td>
                                </tr>
                                <tr>
                                    <th>Body</th>
                                    <td>{{$notification->body}}</td>
                                </tr>                                
                                <tr>
                                    <th>Users</th>
                                    <td>
                                        <?php
                                        if ($notification->userIds=="all") {
                                            echo "All users";
                                        } elseif ($notification->userIds=="all_english_users") {
                                            echo "All English users";
                                        } elseif ($notification->userIds=="all_arabic_users") {
                                            echo "All Arabic users";
                                        } else if ($notification->users) {
                                            echo $notification->users->mobileNums;
                                        } else {
                                            echo "N/A";
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