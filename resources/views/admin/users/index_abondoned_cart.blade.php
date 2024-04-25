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
                <h4 class="page-title">Abondoned Cart Users List  <a class="btn btn-primary" href="<?= url('admin/users') ?>">All Users</a></h4>
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
                    <form name="filter" action="" method="GET">
                        <div class="row pt-2 mb-2" style="{{ isset($_GET['filter']) ? 'background: #f7f2c6' : '' }}">
                            <div class="col-9">
                                <div class="row">
                                    <div class="col-3 mb-2">
                                        <label>Minimum No. Of Orders</label>
                                        <input type="number" class="form-control" name="minimum_no_of_orders" placeholder="" value="{{$minimum_no_of_orders}}">
                                    </div>
                                    <div class="col-3 mb-2">
                                        <label>Maximum No. Of Orders</label>
                                        <input type="number" class="form-control" name="maximum_no_of_orders" placeholder="" value="{{$maximum_no_of_orders}}">
                                    </div>
                                    <div class="col-3 mb-2">
                                        <label>Minimum Amount Of Orders</label>
                                        <input style="min-width: 230px;" type="number" step=".01" class="form-control" name="minimum_amount_of_orders" placeholder="" value="{{$minimum_amount_of_orders}}">
                                    </div>
                                    <div class="col-3 mb-2">
                                        <label>Miximum Amount Of Orders</label>
                                        <input style="min-width: 230px;" type="number" step=".01" class="form-control" name="maximum_amount_of_orders" placeholder="" value="{{$maximum_amount_of_orders}}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="row">
                                    <div class="col-4 mb-2">
                                        <label>User ID</label>
                                        <input type="text" class="form-control" name="user_id" placeholder="" value="{{$user_id}}">
                                    </div>
                                    <div class="col-5 mb-2">
                                        <label>Mobile/Name/Email</label>
                                        <input type="text" class="form-control" name="filter" placeholder="" value="{{$filter}}">
                                    </div>
                                    <div class="col-3 mb-2">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-dark">Filter</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>@sortablelink('name', 'User Name')</th>
                                <th>@sortablelink('email', 'User ID')</th>
                                <th>@sortablelink('email', 'Email')</th>
                                <th>@sortablelink('mobile', 'Mobile')</th>    
                                <th>Instant / Planned</th>    
                                <th>Date Joined</th>
                                <th>Last Tracked</th>
                                <th># Of Orders</th>
                                <th>Sum Of Orders</th>
                                <th>
                                    # Of Items In Cart
                                </th>
                                <th>Address Added</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($users)
                            @foreach($users as $key=>$row)
                            @php
                                $cus_name = 'N/A';
                                if ($row->name) {
                                    $cus_name = $row->name;
                                } elseif ($row->getAddress) {
                                    $cus_name = $row->getAddress->name;
                                }
                                $orders = $row->getDeliveredOrdersOnly;
                                $last_tracking = $row->lastUserTracking();
                                $no_of_orders = $row->no_of_orders ? $row->no_of_orders : 0;
                                $sum_of_orders = $row->sum_of_orders ? $row->sum_of_orders : 0;
                                $sum_of_sub_orders = $row->sum_of_sub_orders ? $row->sum_of_sub_orders : 0;
                                $no_of_tracking = $row->no_of_tracking ? $row->no_of_tracking : 0;
                                $cart_items = $row->cart_items ? $row->cart_items : 0;
                            @endphp
                            <tr class="user<?= $row->id; ?>">
                                <td>{{$key+1}}</td>
                                <td>{{$cus_name}}</td>
                                <td>{{$row->id}}</td>
                                <td>{{$row->email?$row->email:'N/A'}}</td>
                                <td>{{'+'.$row->country_code.' '.$row->mobile}}</td>
                                <td>{{$row->ivp=='i' ? "Instant Model" : "Planned Model"}}</td>
                                <td>{{$row->created_at?$row->created_at:'N/A'}}</td>
                                <td>{{$last_tracking?$last_tracking->created_at:'N/A'}}</td>
                                <td>
                                    {{$no_of_orders}} 
                                    {!! count($orders) ? '<a href="javascript:void(0);" data-id="'.$row->id.'" class="show_orders btn-sm btn-primary">View</a>' : '' !!}
                                </td>
                                <td>
                                    {{number_format($sum_of_orders+$sum_of_sub_orders,2)}}
                                </td>
                                <td>
                                    {{$cart_items}}
                                </td>
                                <td>{{$row->getAddress ? 'Yes' : 'No'}}</td>
                                <td>
                                    @if($row->status == 3)
                                    <p class="text-danger">Blocked</p>
                                    @else 
                                        @if($row->status == 1)
                                            <p class="text-success">Active</p>
                                        @else
                                            <p class="text-warning">OTP not verified</p>
                                        @endif 
                                    @endif
                                    {{-- @if($row->status > 0)
                                    <div class="mytoggle">
                                        <label class="switch">
                                            <input class="switch-input set_status<?= $row->id; ?>" type="checkbox" value="{{$row->status}}" <?= $row->status == 2 ? 'checked' : ''; ?> 
                                                   id="<?= $row->id; ?>" 
                                                   onchange="checkStatus(this)">
                                            <span class="slider round"></span> 
                                        </label>
                                    </div>
                                    @endif --}}
                                </td>
                                <td>
                                    <div style="width: 100px;">
                                        <a title="View detail" href="<?= url('admin/users/show/' . $row->id); ?>"><i class="fa fa-eye"></i></a>
                                        &nbsp;
                                        <a title="Notify User" href="javascript:void(0);" data-id="{{$row->id}}" class="show_notify"><i class="fa fa-bell"></i></a>
                                        &nbsp;
                                        <a title="Remove user" href="<?= url('admin/users/delete/' . base64url_encode($row->id)); ?>" onclick="return confirm('Are you sure, you want to completely delete this user?')"><i class="fa fa-trash"></i></a>
                                    </div>  
                                </td>
                            </tr>
                            <tr class="user_notify user_notify_<?= $row->id; ?>" style="display: none; background: #f1f5cd;">
                                <form method="post" name="form_push_<?= $row->id; ?>" id="form_push_<?= $row->id; ?>" enctype="multipart/form-data" action="">
                                    <td></td>
                                    <td colspan="10">
                                        <div class="row">  
                                            <div class="col-lg-12">
                                                <h5>Push Notifications</h5>
                                                <hr/>
                                            </div>                          
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>Title</label>
                                                    <input type="text" name="title" value="{{old('title')}}" class="form-control" placeholder="Title">
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="form-group">
                                                    <label>Body</label>
                                                    <textarea name="body" value="{{old('body')}}" class="form-control" placeholder="Body"></textarea>
                                                </div>  
                                            </div>
                                            <div class="col-lg-12">
                                                @php
                                                    $device_detail = $row->getDeviceToken;
                                                    if ($device_detail && $device_detail->device_type==1) {
                                                        $device_type =  'iOS';
                                                    } elseif ($device_detail && $device_detail->device_type==2) {
                                                        $device_type =  'Android';
                                                    } else {
                                                        $device_type = 'Not detected';
                                                    }
                                                @endphp
                                                <h5>Device Details</h5>
                                                <hr/>
                                                <div style="line-height: 1;">
                                                    <label>Preffered Language:</label> 
                                                    @php
                                                    if ($row->lang_preference=='ar') {
                                                        echo 'Arabic';
                                                    } elseif ($row->lang_preference=='en') {
                                                        echo 'English';
                                                    } else {
                                                        echo 'Unknown';
                                                    }
                                                    @endphp
                                                    (Last updated on {{ $row->updated_at }})
                                                    <br/>
                                                    <label>Phone Type:</label> {{ $device_type }}<br/>
                                                    <label>App Version:</label> {{ $device_detail && $device_detail->app_version ? $device_detail->app_version : 'Old'; }}<br/>
                                                    <label>Device Token:</label> {{ $device_detail && $device_detail->device_token ? $device_detail->device_token : ''; }}<br/>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="row">                            
                                            <div class="col-lg-12">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button title="Sending" href="javascript:void(0);" id="{{ $row->id }}" class="row_btn row_sending_push row_sending_push_{{ $row->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                    <button title="Send" href="javascript:void(0);" id="{{ $row->id }}" class="row_btn row_send_push row_send_push_{{ $row->id }} btn btn-success waves-effect">Send</button>
                                                    <br/>
                                                    <input type="hidden" class="form-control" name="user_id" value="{{ $row->id }}">
                                                    <div class="edit_response_push edit_response_push_{{ $row->id }}"></div>
                                                </div>  
                                            </div>
                                        </div>
                                    </td>
                                </form>
                            </tr>
                                @if (count($orders)) 
                                <tr class="user_order user_order_<?= $row->id; ?>" style="display: none; background: #cde6f5;">
                                    <td></td>
                                    <td colspan="11">
                                        <table class="table table-bordered dt-responsive nowrap"
                                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>Order Number</th>
                                                    <th>Total Amount</th>
                                                    <th>Status</th>
                                                    <th>Store</th>
                                                    <th>Ordered Date</th>
                                                    <th>Detail</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($orders as $key => $row2)
                                                    <tr>
                                                        <td>{{ $key + 1 }}</td>
                                                        <td>{{ '#' . $row2->orderId }}</td>
                                                        <td>{{ $row2->getSubOrder ? $row2->total_amount + $row2->getOrderSubTotal($row2->id) : $row2->total_amount }}</td>
                                                        <td>{{ $order_status[$row2->status] }}</td>
                                                        <td>{{ $row2->getStore && $row2->getStore->name ? $row2->getStore->company_name.' - '.$row2->getStore->name : '' }}</td>
                                                        <td>{{ $row2->created_at }}</td>
                                                        <td>
                                                            <a href="<?= url('admin/orders/detail/' . base64url_encode($row2->id)) ?>"><i class="icon-eye"></i></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if ($no_of_orders > count($orders))
                                            <a title="View More Orders" href="<?= url('admin/users/show/' . $row->id.'?tab=orders#orders'); ?>">View More Orders</a>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$users->count()}} of {{ $users->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $users->appends($_GET)->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    // Notification actions
    $('.show_notify').on('click', function() {
        let id = $(this).data('id');
        $('.user_notify_'+id).toggle();
    })
    $('.row_send_push').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.row_sending_push_'+id).show();
        $('.row_send_push_'+id).hide();
        $('.edit_response_push_'+id).hide();
        if (id) {
            let form = $("#form_push_"+id);
            let token = "{{ csrf_token() }}";
            console.log(form);
            let formData = new FormData(form[0]);
            formData.append('id', id);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/users/send_push_notification') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        $('.row_sending_push_'+id).hide();
                        $('.row_send_push_'+id).show();
                        if (data.data) {
                            let row = data.data;
                            $('.edit_response_push_'+id).html(data.message);
                            $('.edit_response_push_'+id).show();
                        }
                    } else {
                        alert(data.message);
                    }
                }
            });
        } else {
            alert("Something went wrong");
        }
    });
    // Order actions
    $('.show_orders').on('click', function() {
        let id = $(this).data('id');
        $('.user_order_'+id).toggle();
    })
    // User actions
    function checkStatus(obj) {
        var id = $(obj).attr("id");

        var checked = $(obj).is(':checked');
        if (checked == true) {
            var status = 2;
            var statustext = 'activate';
        } else {
            var status = 3;
            var statustext = 'block';
        }

        if (confirm("Are you sure, you want to " + statustext + " this user ?")) {
            $.ajax({
                url: '<?= url('admin/change_status'); ?>',
                type: 'post',
                dataType: 'json',
                data: {method: 'changeUserStatus', status: status, id: id},
                cache: false,
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Status updated successfully</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);

                    $('.user' + id).find("p").text(response.result.status);
                } else {
                    var error_str = '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Some error found</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        } else {
            if (status == 2) {
                $(".set_status" + id).prop('checked', false);
            } else if (status == 3) {
                $(".set_status" + id).prop('checked', true);
            }
        }
    }
</script>
@endsection