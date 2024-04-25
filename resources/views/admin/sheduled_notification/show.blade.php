@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Push Notification Shedule Detail</h4>
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
                                @php
                                    $shedule_time_str = $notification->shedule_time;
                                    $shedule_time_arr = explode(" ",$shedule_time_str);
                                    $shedule_date = is_array($shedule_time_arr) && isset($shedule_time_arr[0]) ? $shedule_time_arr[0] : '';
                                    $shedule_time = is_array($shedule_time_arr) && isset($shedule_time_arr[1]) ? $shedule_time_arr[1] : '';
                                @endphp
                                <tr>
                                    <th>Sheduled Date</th>
                                    <td>{{ $shedule_date }}</td>
                                </tr>
                                <tr>
                                    <th>Sheduled Time</th>
                                    <td>{{ $shedule_time }}</td>
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