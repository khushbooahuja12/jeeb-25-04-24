@extends('admin.layouts.dashboard_layout_for_product_admin')
@section('content')
@php
    $row_address = isset($_GET['tab']) && $_GET['tab']=='address' ? 'display:block;' : 'display:none;';
    $row_orders = isset($_GET['tab']) && $_GET['tab']=='orders' ? 'display:block;' : 'display:none;';
    $row_wallet_payments = isset($_GET['tab']) && $_GET['tab']=='wallet' ? 'display:block;' : 'display:none;';
    $row_rewards = isset($_GET['tab']) && $_GET['tab']=='rewards' ? 'display:block;' : 'display:none;';
    $row_tracking = isset($_GET['tab']) && $_GET['tab']=='tracking' ? 'display:block;' : 'display:none;';
    $row_cart_items = isset($_GET['tab']) && $_GET['tab']=='cart_items' ? 'display:block;' : 'display:none;';
    $row_ordered_items = isset($_GET['tab']) && $_GET['tab']=='ordered_items' ? 'display:block;' : 'display:none;';
    if (!isset($_GET['tab'])) {
        $row_address='display:block;';
    }
@endphp
<style>
    .row_sending_push {
        display: none;
    }
    .row_send_push {
        display: block;
    }
    .row_adding_wallet {
        display: none;
    }
    .row_add_wallet {
        display: block;
    }
    .row_adding_scratch_card {
        display: none;
    }
    .row_add_scratch_card {
        display: block;
    }
    .wrapper {
        padding: 50px;
    }

    .image--cover {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        margin: 20px;
        border: 5px double #8284b1;
        object-fit: cover;
        object-position: center right;
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">User Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/users'); ?>">Users</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    @csrf                        
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="wrapper">
                                <img src="{{$user->getUserImage? asset('images/user_images') . '/' . $user->getUserImage->file_name:asset('assets/images/dummy_user.png')}}" class="image--cover">
                            </div>
                        </div>
                        <div class="offset-3 col-md-9 justify-center">
                            <table class="table table-borderless">
                                <tr>
                                    <th>User ID </th>
                                    <td> {{$user->id?$user->id:'N/A'}}</td>
                                </tr>
                                <tr>
                                    <th>User Name </th>
                                    <td> {{$user->name?$user->name:'N/A'}}</td>
                                </tr>
                                <tr>
                                    <th>Email </th>
                                    <td> {{$user->email?$user->email:'N/A'}}</td>
                                </tr>
                                <tr>
                                    <th>Mobile </th>
                                    <td> {{'+'.$user->country_code.' '.$user->mobile}}</td>
                                </tr>
                                <tr>
                                    <th>Status </th>
                                    <td>
                                        @if($user->status == 1)
                                        <span class="text-success">Active</span> 
                                        @else 
                                        @if($user->status == 3)
                                        <span class="text-danger">Blocked</span>
                                        @else
                                        <span class="text-warning">Account not verified</span>
                                        @endif 
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Instant / Planned </th>
                                    <td> 
                                        {{$user->ivp=='i' ? "Instant Model" : ""}}
                                        {{$user->ivp=='p' ? "Plus Model" : ""}}
                                        {{$user->ivp=='m' ? "Mall Model" : ""}}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Expected time to deliver <br/>with preparation time</th>
                                    <td> {{round($user->expected_eta/60)}} minutes</td>
                                </tr>
                                <tr>
                                    <th>Preffered Language </th>
                                    <td> 
                                        @php
                                        if ($user->lang_preference=='ar') {
                                            echo 'Arabic';
                                        } elseif ($user->lang_preference=='en') {
                                            echo 'English';
                                        } else {
                                            echo 'Unknown';
                                        }
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At </th>
                                    <td> {{ $user->created_at }}</td>
                                </tr>
                                <tr>
                                    <th>Last Updated At </th>
                                    <td> {{ $user->updated_at }}</td>
                                </tr>
                                <tr>
                                    <th>Wallet </th>
                                    <td> {{ $wallet ? $wallet->total_points : 0 }}</td>
                                </tr>
                                <tr>
                                    <th>Features</th>
                                    <td>
                                        <div style="line-height: 1;">
                                            <form method="post" name="enable_user_features_form" class="enable_user_features_form enable_user_features_form_<?= $user->id ?>" id="enable_user_features_form_<?= $user->id ?>">
                                            <input type="hidden" name="id" value="<?= $user->id ?>"/>
                                            <input type="checkbox" name="features[]" value="ivp" {{ isset($user->features_enabled) && str_contains($user->features_enabled, 'ivp,') ? 'checked' : '' }}/> <label>IVP</label><br/>
                                            <input type="checkbox" name="features[]" value="scratch_card" {{ isset($user->features_enabled) && str_contains($user->features_enabled, 'scratch_card,') ? 'checked' : '' }}/> <label>Scratch Card</label><br/>
                                            <button name="enable_features_save" class="btn btn-danger btn-sm enable_features_save enable_features_save_<?= $user->id ?>" id="<?= $user->id ?>">Save</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <div class="alert enable_features_status enable_features_status_<?= $user->id ?>">&nbsp;</div>
                                    </td>
                                </tr>
                                <tr class="user_notify user_notify_<?= $user->id; ?>" style="background: #f1f5cd;">  
                                    <td colspan="2">
                                        <form method="post" name="form_add_wallet_<?= $user->id; ?>" id="form_add_wallet_<?= $user->id; ?>" enctype="multipart/form-data" action="">
                                            <table style="background: #f1f5cd;">
                                                <tr>
                                                    <td colspan="10">
                                                        <div class="row">  
                                                            <div class="col-lg-12">
                                                                <h5>Add Wallet</h5>
                                                                <hr/>
                                                            </div>                          
                                                            <div class="col-lg-4">
                                                                <div class="form-group">
                                                                    <input type="number" name="wallet_amount" value="" class="form-control" placeholder="Amount">
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <div class="form-group">
                                                                    <input type="password" name="secret_key" value="" class="form-control" placeholder="Secret Key" autocomplete="new-password">
                                                                </div>  
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <button title="Adding" href="javascript:void(0);" id="{{ $user->id }}" class="row_btn row_adding_wallet row_adding_wallet_{{ $user->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                                    <button title="Add" href="javascript:void(0);" id="{{ $user->id }}" class="row_btn row_add_wallet row_add_wallet_{{ $user->id }} btn btn-success waves-effect">Add</button>
                                                                    <br/>
                                                                    <input type="hidden" class="form-control" name="user_id" value="{{ $user->id }}">
                                                                </div>  
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <div class="edit_response_add_wallet edit_response_add_wallet_{{ $user->id }}"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                        {{-- <br/> --}}
                                        <form method="post" name="form_add_scratch_card_<?= $user->id; ?>" id="form_add_scratch_card_<?= $user->id; ?>" enctype="multipart/form-data" action="">
                                            <table style="background: #f1f5cd;">
                                                <tr>
                                                    <td colspan="10">
                                                        <div class="row">  
                                                            <div class="col-lg-12">
                                                                <h5>Add Scratch Card</h5>
                                                                <hr/>
                                                            </div>                          
                                                            <div class="col-lg-4">
                                                                <div class="form-group">
                                                                    <select name="scratch_card_id" class="form-control">
                                                                        <option value="0">Select a scratch card</option>
                                                                        @if ($scratch_cards)
                                                                            @foreach ($scratch_cards as $key => $item)
                                                                                <option value="{{ $item->id }}">{{ $item->title_en }} ({{ $item->scratch_card_type == 0 ? 'Onspot Reward' : 'Coupon' }})</option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-lg-4">
                                                                <div class="form-group">
                                                                    <input type="password" name="secret_key" value="" class="form-control" placeholder="Secret Key" autocomplete="new-password">
                                                                </div>  
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <div class="form-group">
                                                                    <button title="Sending" href="javascript:void(0);" id="{{ $user->id }}" class="row_btn row_adding_scratch_card row_adding_scratch_card_{{ $user->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                                    <button title="Send" href="javascript:void(0);" id="{{ $user->id }}" class="row_btn row_add_scratch_card row_add_scratch_card_{{ $user->id }} btn btn-success waves-effect">Send</button>
                                                                    <br/>
                                                                    <input type="hidden" class="form-control" name="user_id" value="{{ $user->id }}">
                                                                </div>  
                                                            </div>
                                                            <div class="col-lg-12">
                                                                <div class="edit_response_add_scratch_card edit_response_add_scratch_card_{{ $user->id }}"></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                        <br/>
                                        <form method="post" name="form_push_<?= $user->id; ?>" id="form_push_<?= $user->id; ?>" enctype="multipart/form-data" action="">
                                            <table style="background: #f1f5cd;">
                                                <tr>
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
                                                                    $device_detail = $user->getDeviceToken;
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
                                                                    if ($user->lang_preference=='ar') {
                                                                        echo 'Arabic';
                                                                    } elseif ($user->lang_preference=='en') {
                                                                        echo 'English';
                                                                    } else {
                                                                        echo 'Unknown';
                                                                    }
                                                                    @endphp
                                                                    (Last updated on {{ $user->updated_at }})
                                                                    <br/>
                                                                    <label>Phone Type:</label> {{ $device_type }}<br/>
                                                                    <label>App Version:</label> {{ $device_detail && $device_detail->app_version ? $device_detail->app_version : 'Old'; }}<br/>
                                                                    <label>Device Token:</label> {{ $device_detail && $device_detail->device_token ? $device_detail->device_token : ''; }}<br/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <button title="Sending" href="javascript:void(0);" id="{{ $user->id }}" class="row_btn row_sending_push row_sending_push_{{ $user->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                        <button title="Send" href="javascript:void(0);" id="{{ $user->id }}" class="row_btn row_send_push row_send_push_{{ $user->id }} btn btn-success waves-effect">Send</button>
                                                        <br/>
                                                        <input type="hidden" class="form-control" name="user_id" value="{{ $user->id }}">
                                                        <div class="edit_response_push edit_response_push_{{ $user->id }}"></div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </form>
                                    </td>
                                </tr> 
                            </table>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->  
            @if (isset($full_view) && $full_view==true)
            <table class="table table-bordered dt-responsive nowrap text-center">
                <tr>
                    <td>
                        <b class="btn btn-primary toggle_address">Address</b>
                    </td>
                    <td>
                        <b class="btn btn-primary toggle_wallet">Wallet</b>
                    </td>
                    <td>
                        <b class="btn btn-primary toggle_rewards">Rewards (User Specific)</b>
                    </td>
                    <td>
                        <b class="btn btn-primary toggle_orders">Orders</b>
                    </td>
                    <td>
                        <b class="btn btn-primary toggle_tracking">Tracking Data</b>
                    </td>
                    <td>
                        <b class="btn btn-primary toggle_cart_items">Shopping Cart</b>
                    </td>
                    <td>
                        <b class="btn btn-primary toggle_ordered_items">Ordered Products</b>
                    </td>
                </tr>
            </table>    
            @endif
        </div> <!-- end row -->      
    </div>
    {{-- --------------------------------- --}}
    {{--               Address             --}}
    {{-- --------------------------------- --}}
    <div class="row row_address" style="{{$row_address}}">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h5>User Address Locations</h5>
                    <div class="mb-2">
                        <table class="table table-bordered dt-responsive nowrap text-center">
                            <tr>
                                <td>
                                    <b><img src="http://maps.google.com/mapfiles/ms/icons/red-dot.png"> Address</b>
                                </td>
                                <td>
                                    <b><img src="http://maps.google.com/mapfiles/ms/icons/green-dot.png"> Default Address</b>
                                </td>
                                <td>
                                    <b><img src="{{ asset("assets_v4/images/map_icon/store.png")}}"> &nbsp; Hubs</b>
                                </td>
                                <td>
                                    <b><img src="{{ asset("assets_v4/images/map_icon/store-selected.png")}}"> &nbsp; Nearest Hub</b>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div id="map" style="width: 100%; height: 450px;"></div>
                    @if (count($user_address)) 
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Mobile</th>
                                    <th>Landmark</th>
                                    <th>Address Line1</th>
                                    <th>Address Line2</th>
                                    <th>Lat & Long</th>
                                    <th>Type</th>
                                    <th>Is Default</th>
                                    <th>Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user_address as $key => $row2)
                                    <tr {!! $row2->is_default==1 ? 'style="background:#00FF00;"' : '' !!}>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $row2->id }}</td>
                                        <td>{{ $row2->name }}</td>
                                        <td>{{ $row2->mobile }}</td>
                                        <td>{{ $row2->landmark }}</td>
                                        <td>{{ $row2->address_line1 }}</td>
                                        <td>{{ $row2->address_line2 }}</td>
                                        <td>{{ $row2->latitude }}, {{ $row2->longitude }}</td>
                                        <td>{{ $row2->address_type }}</td>
                                        <td>{{ $row2->is_default }}</td>
                                        <td>{{ $row2->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        No address yet..
                    @endif
                </div>
            </div> <!-- end col -->       
        </div> <!-- end row -->      
    </div>
    {{-- --------------------------------- --}}
    {{--               Wallet              --}}
    {{-- --------------------------------- --}}
    @if (isset($wallet_payments))
    <div class="row row_wallet_payments" style="{{$row_wallet_payments}}">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h5>Wallet ({{ $wallet ? $wallet->total_points : 0 }})</h5>
                    @if (count($wallet_payments)) 
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Detail</th>
                                    <th>Order Number</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($wallet_payments as $key => $row2)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            @if ($row2->wallet_type==1)
                                                Top up
                                            @elseif ($row2->wallet_type==2)
                                                Spent on Order ID:
                                            @elseif ($row2->wallet_type==3)
                                                Refunded from Order ID
                                            @elseif ($row2->wallet_type==4)
                                                Referral Bonus
                                            @elseif ($row2->wallet_type==5)
                                                Redeemed from Scratch Card
                                            @elseif ($row2->wallet_type==6)
                                                Added by Jeeb
                                            @endif
                                        </td>
                                        <td>{{ $row2->wallet_type==2 || $row2->wallet_type==3 ? '#' . $row2->paymentResponse : '' }}</td>
                                        <td>{{ $row2->amount }}</td>
                                        <td>{{ $row2->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        No wallet transactions..
                    @endif
                </div>
            </div> <!-- end col -->       
        </div> <!-- end row -->      
    </div>
    @endif
    {{-- --------------------------------- --}}
    {{--       Rewards (User Specific)     --}}
    {{-- --------------------------------- --}}
    @if (isset($scratch_card_users))
    <div class="row row_rewards" style="{{$row_rewards}}">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h5>Rewards (User Specific)</h5>
                    @if (count($scratch_card_users)) 
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('id', 'ID')</th>
                                    <th>Created At</th>
                                    <th>Scratch Card Type</th>
                                    <th>Coupon Code</th>
                                    <th>Status</th>
                                    <th>Received Order ID</th>
                                    <th>Scratched At</th>
                                    <th>Redeemed Amount</th>
                                    <th>Redeemed At</th>
                                    <th>Scratch Card ID</th>
                                    <th>@sortablelink('title_en', 'Title')</th>
                                    <th>Title (Ar)</th>
                                    <th>Discount</th>
                                    <th>Expiry Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($scratch_card_users)
                                    @foreach ($scratch_card_users as $key => $item)
                                        @php
                                            $item_status = 'Unknown';
                                            if ($item->status==0) {
                                                $item_status = 'Inactive';
                                            } elseif ($item->status==1) {
                                                $item_status = 'Active-Unscratched';
                                            } elseif ($item->status==2) {
                                                $item_status = 'Scratched';
                                            } elseif ($item->status==3) {
                                                $item_status = 'Redeemed';
                                            } elseif ($item->status==4) {
                                                $item_status = 'Cancelled';
                                            }
                                        @endphp
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $item->id }}</td>
                                            <td>{{ $item->created_at }}</td>
                                            <td>{{ $item->scratch_card_type == 0 ? 'Onspot Reward' : 'Coupon' }}</td>
                                            <td>{{ $item->coupon_code }}</td>
                                            <td> {{ $item_status }}</td>
                                            <td> {{ $item->fk_order_id }}</td>
                                            <td> {{ $item->scratched_at }}</td>
                                            <td> {{ $item->redeem_amount }}</td>
                                            <td> {{ $item->redeemed_at }}</td>
                                            <td> {{ $item->fk_scratch_card_id }}</td>
                                            <td>{{ $item->title_en }}</td>
                                            <td>{{ $item->title_ar }}</td>
                                            <td>{{ $item->discount . ($item->type == 1 ? ' %' : '') }}</td>
                                            <td>{{ $item->expiry_date }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    @else
                        No scratch cards received..
                    @endif
                </div>
            </div> <!-- end col -->       
        </div> <!-- end row -->      
    </div>
    @endif
    {{-- --------------------------------- --}}
    {{--               Orders              --}}
    {{-- --------------------------------- --}}
    @if (isset($orders))
    <div class="row row_orders" style="{{$row_orders}}" id="orders">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h5>Orders</h5>
                    @if (count($orders)) 
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
                                        <td>{{ '#' . $row2->orderId }} {!! $row2->parent_id!=0 ? '<br/>(Forgot something order of #'.(1000+$row2->parent_id).')' : '' !!}</td>
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
                    @else
                        No orders yet..
                    @endif
                </div>
            </div> <!-- end col -->       
        </div> <!-- end row -->      
    </div>
    @endif
    {{-- --------------------------------- --}}
    {{--               User Tracking              --}}
    {{-- --------------------------------- --}}
    @if (isset($tracking_data))
    <div class="row row_tracking" style="{{$row_tracking}}">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body" style="overflow-x: scroll;">
                    <h5>User Tracking</h5>
                    @if (count($tracking_data)) 
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>Details</th>
                                    <th>Key</th>
                                    <th>Request</th>
                                    <th>Response</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tracking_data as $key => $row2)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $row2->id }}</td>
                                        <td>
                                            <a title="View Detail" href="javascript:void(0);" data-id="{{$row2->id}}" class="show_detail"><i class="fa fa-eye"></i></a>
                                        </td>
                                        <td>{{ $row2->key }}</td>
                                        <td>{{ substr($row2->request, 0, 100); }}..</td>
                                        <td>{{ substr($row2->response, 0, 100); }}..</td>
                                        <td>{{ $row2->created_at }}</td>
                                        <td>{{ $row2->updated_at }}</td>
                                    </tr>
                                    <tr class="show_detail show_detail_<?= $row2->id; ?>" style="display: none; background: #f1f5cd;">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td colspan="4">
                                            <pre>{{ $row2->request }}</pre>
                                            <hr/>
                                            <pre>{{ $row2->response }}</pre>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        No orders yet..
                    @endif
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$tracking_data->count()}} of {{ $tracking_data->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $tracking_data->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->       
        </div> <!-- end row -->      
    </div>
    @endif
    {{-- --------------------------------- --}}
    {{--               Shopping Cart              --}}
    {{-- --------------------------------- --}}
    @if (isset($cart_items))
    <div class="row row_cart_items" style="{{$row_cart_items}}">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h5>Shopping Cart</h5>
                    @if (count($cart_items)) 
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Base Product ID</th>
                                    <th>Base Product Store ID</th>
                                    <th>Base Product Name</th>
                                    <th>Unit</th>
                                    <th>Quntity</th>
                                    <th>Product Price</th>
                                    <th>Amount</th>
                                    <th>Added Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cart_items as $key => $row2)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $row2->fk_product_id }}</td>
                                        <td>{{ $row2->fk_product_store_id }}</td>
                                        <td>{{ $row2->product_name_en }}</td>
                                        <td>{{ $row2->unit }}</td>
                                        <td>{{ $row2->quantity }}</td>
                                        <td>{{ $row2->product_store_price }}</td>
                                        <td>{{ $row2->product_store_price * $row2->quantity }}</td>
                                        <td>{{ $row2->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        No items in cart
                    @endif
                </div>
            </div> <!-- end col -->       
        </div> <!-- end row -->      
    </div>
    @endif
    {{-- --------------------------------- --}}
    {{--               Shopping Cart              --}}
    {{-- --------------------------------- --}}
    @if (isset($ordered_items))
    <div class="row row_ordered_items" style="{{$row_ordered_items}}">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <h5>Ordered Products</h5>
                    @if (count($ordered_items)) 
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Base Product ID</th>
                                    <th>Base Product Store ID</th>
                                    <th>Base Product Name</th>
                                    <th>Unit</th>
                                    <th>Quntity</th>
                                    <th>Product Price</th>
                                    <th>Amount</th>
                                    <th>Order No.</th>
                                    <th>Order Status</th>
                                    <th>Added Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ordered_items as $key => $row2)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $row2->fk_product_id }}</td>
                                        <td>{{ $row2->fk_product_store_id }}</td>
                                        <td>{{ $row2->product_name_en }}</td>
                                        <td>{{ $row2->product_unit }}</td>
                                        <td>{{ $row2->product_quantity }}</td>
                                        <td>{{ $row2->single_product_price }}</td>
                                        <td>{{ $row2->total_product_price }}</td>
                                        <td>#{{ $row2->orderId }}</td>
                                        <td>{{ $order_status[$row2->status] }}</td>
                                        <td>{{ $row2->created_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        No products ordered yet..
                    @endif
                </div>
            </div> <!-- end col -->       
        </div> <!-- end row -->      
    </div>
    @endif
</div>
<script>
    // Enable / Disable Features
    $('.enable_features_save').on('click',function(e){
        e.preventDefault();
        let row_id = $(this).attr('id');
        var formData = $(".enable_user_features_form_" + row_id).serialize();
        $.ajax({
            url: '<?= url('admin/users/enable_user_features/') ?>',
            type: 'POST',
            data: formData,
            dataType: 'JSON',
            cache: false,
            headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function() {
                $('.enable_features_status_'+row_id).html('&nbsp;');
                $('.enable_features_status_'+row_id).removeClass('alert-success');
                $('.enable_features_status_'+row_id).removeClass('alert-danger');
            },
            success: function(response) { console.log(response);
                $('.enable_features_status_'+row_id).html(response.message);
                if (response.status) {
                    $('.enable_features_status_'+row_id).addClass('alert-success');
                    $('.enable_features_status_'+row_id).removeClass('alert-danger');
                } else {
                    $('.enable_features_status_'+row_id).addClass('alert-danger');
                    $('.enable_features_status_'+row_id).removeClass('alert-success');
                }
            },
            complete: function(){
            }
        });
    });
    // Add wallet actions
    $('.row_add_wallet').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.row_adding_wallet_'+id).show();
        $('.row_add_wallet_'+id).hide();
        $('.edit_response_add_wallet_'+id).hide();
        if (id) {
            let form = $("#form_add_wallet_"+id);
            let token = "{{ csrf_token() }}";
            console.log(form);
            let formData = new FormData(form[0]);
            formData.append('id', id);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/users/add_wallet') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('.row_adding_wallet_'+id).hide();
                    $('.row_add_wallet_'+id).show();
                    $('.edit_response_add_wallet_'+id).html(data.message);
                    $('.edit_response_add_wallet_'+id).show();
                    // if (data.error_code == "200") {
                    //     alert(data.message);
                    // } else {
                    //     alert(data.message);
                    // }
                }
            });
        } else {
            alert("Something went wrong");
        }
    });
    // Add scratch card actions
    $('.row_add_scratch_card').on('click',function(e){
        e.preventDefault();
        let id = $(this).attr('id');
        $('.row_adding_scratch_card_'+id).show();
        $('.row_add_scratch_card_'+id).hide();
        $('.edit_response_add_scratch_card_'+id).hide();
        if (id) {
            let form = $("#form_add_scratch_card_"+id);
            let token = "{{ csrf_token() }}";
            console.log(form);
            let formData = new FormData(form[0]);
            formData.append('id', id);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/users/add_scratch_card') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    $('.row_adding_scratch_card_'+id).hide();
                    $('.row_add_scratch_card_'+id).show();
                    $('.edit_response_add_scratch_card_'+id).html(data.message);
                    $('.edit_response_add_scratch_card_'+id).show();
                    // if (data.error_code == "200") {
                    //     alert(data.message);
                    // } else {
                    //     alert(data.message);
                    // }
                }
            });
        } else {
            alert("Something went wrong");
        }
    });
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
                    $('.row_sending_push_'+id).hide();
                    $('.row_send_push_'+id).show();
                    $('.edit_response_push_'+id).html(data.message);
                    $('.edit_response_push_'+id).show();
                    // if (data.error_code == "200") {
                    //     alert(data.message);
                    // } else {
                    //     alert(data.message);
                    // }
                }
            });
        } else {
            alert("Something went wrong");
        }
    });
    // Initialize and add the map
    function initMap(){
        // Map options
        var options = {
            zoom:10,
            center:{lat:25.286106,lng:51.534817}
        }
        // New map
        var map = new google.maps.Map(document.getElementById('map'), options);
        // Listen for click on map
        google.maps.event.addListener(map, 'click', function(event){
            // Add marker
            addMarker({coords:event.latLng});
        });
        
        // Array of markers
        var markers = [
        @if (isset($user_address) && !empty($user_address))
            @foreach ($user_address as $key => $address)
            {
                @if($address->is_default == 1)
                    image: "http://maps.google.com/mapfiles/ms/icons/green-dot.png",
                @else
                    image: "http://maps.google.com/mapfiles/ms/icons/red-dot.png",
                @endif
                
                coords:{lat:{{$address->latitude}},lng:{{$address->longitude}}},
                content:'<h4>Address {{$address->id}}: </h4>{{$address->name}}<br>{{ $address->landmark }}<br>{{ $address->address_line1 }}<br>{{ $address->address_line2 }}<br>{{ $address->mobile }}'
            }@if ($key < count($user_address)-1 ),@endif
            @endforeach
        @endif
        ];

        var hub_markers = [
            @if (isset($store_group_hubs) && !empty($store_group_hubs))
                @foreach ($store_group_hubs as $key => $store_group_hub)
                {
                    image: '{{ asset("assets_v4/images/map_icon/store.png")}}',
                    coords:{lat:{{$store_group_hub->latitude}},lng:{{$store_group_hub->longitude}}},
                    content:'<h4>Hub {{$store_group_hub->id}}: </h4>{{$store_group_hub->name}}-{{$store_group_hub->company_name}}'
                }@if ($key < count($store_group_hubs)-1 ),@endif
                @endforeach
            @endif
        ];

        var nearest_hub_markers = [
            @if ($user->getStoreGroup && $user->getStoreGroup->getStore && $user->getStoreGroup->getStore->latitude)
            {
                image: '{{ asset("assets_v4/images/map_icon/store-selected.png")}}',
                coords:{lat:{{$user->getStoreGroup->getStore->latitude}},lng:{{$user->getStoreGroup->getStore->longitude}}},
                content:'<h4>Nearest Hub {{$user->getStoreGroup->getStore->id}}: </h4>{{$user->getStoreGroup->getStore->name}}-{{$user->getStoreGroup->getStore->company_name}}'
            }
            @endif
        ];
        // Loop through markers
        // User Address Locations
        for(var i = 0;i < markers.length;i++){
            // Add marker
            addMarker(markers[i]);
        }

        // Store Hub Locations
        for(var i = 0;i < hub_markers.length;i++){ console.log(hub_markers[i]);
            // Add marker
            addMarker(hub_markers[i]);
        }

        // Store Hub Locations
        for(var i = 0;i < nearest_hub_markers.length;i++){ console.log(nearest_hub_markers[i]);
            // Add marker
            addMarker(nearest_hub_markers[i]);
        }
        // Add Marker Function
        function addMarker(props){
        
        // if(props.default == 1){
        //     image = image;
        // }else{
        //     image = props.iconImage;
        // }

        var marker = new google.maps.Marker({
            position:props.coords,
            map:map,
            icon:props.image
        });
        // Check for customicon
        if(props.iconImage){
            // Set icon image
            marker.setIcon(props.iconImage);
        }
        // Check content
        if(props.content){
            var infoWindow = new google.maps.InfoWindow({
            content:props.content
            });
            marker.addListener('click', function(){
            infoWindow.open(map, marker);
            });
        }
        }
    }
    window.initMap = initMap;
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAQXKQdhn1KRWpRAF9wWp3fkKzjehGm6M&callback=initMap">
</script>
<script>
    // Toggle view
    $('.show_detail').on('click', function() {
        let id = $(this).data('id');
        $('.show_detail_'+id).toggle();
    })
    $('.toggle_address').on('click', function() {
        $('.row_orders').hide();
        $('.row_address').show();
        $('.row_wallet_payments').hide();
        $('.row_rewards').hide();
        $('.row_tracking').hide();
        $('.row_cart_items').hide();
        $('.row_ordered_items').hide();
    })
    $('.toggle_wallet').on('click', function() {
        $('.row_orders').hide();
        $('.row_address').hide();
        $('.row_wallet_payments').show();
        $('.row_rewards').hide();
        $('.row_tracking').hide();
        $('.row_cart_items').hide();
        $('.row_ordered_items').hide();
    })
    $('.toggle_rewards').on('click', function() {
        $('.row_orders').hide();
        $('.row_address').hide();
        $('.row_wallet_payments').hide();
        $('.row_rewards').show();
        $('.row_tracking').hide();
        $('.row_cart_items').hide();
        $('.row_ordered_items').hide();
    })
    $('.toggle_orders').on('click', function() {
        $('.row_orders').show();
        $('.row_address').hide();
        $('.row_wallet_payments').hide();
        $('.row_rewards').hide();
        $('.row_tracking').hide();
        $('.row_cart_items').hide();
        $('.row_ordered_items').hide();
    })
    $('.toggle_tracking').on('click', function() {
        $('.row_orders').hide();
        $('.row_address').hide();
        $('.row_wallet_payments').hide();
        $('.row_rewards').hide();
        $('.row_tracking').show();
        $('.row_cart_items').hide();
        $('.row_ordered_items').hide();
    })
    $('.toggle_cart_items').on('click', function() {
        $('.row_orders').hide();
        $('.row_address').hide();
        $('.row_wallet_payments').hide();
        $('.row_rewards').hide();
        $('.row_tracking').hide();
        $('.row_cart_items').show();
        $('.row_ordered_items').hide();
    })
    $('.toggle_ordered_items').on('click', function() {
        $('.row_orders').hide();
        $('.row_address').hide();
        $('.row_wallet_payments').hide();
        $('.row_rewards').hide();
        $('.row_tracking').hide();
        $('.row_cart_items').hide();
        $('.row_ordered_items').show();
    })
</script>
@endsection