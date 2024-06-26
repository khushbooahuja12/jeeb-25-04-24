@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">New Push Notification</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/custom_notifications'); ?>">Notifications</a></li>
                    <li class="breadcrumb-item active">Create</li>
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
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.custom_notification.update',[base64url_encode($notification->id)])}}">
                        @csrf
                        <!--                        <div class="row">                            
                                                    <div class="col-lg-6">
                                                        <div class="form-group">                                    
                                                            <input type="radio" id="one_time" name="type" value="1" checked>
                                                            <label for="one_time">One Time</label>
                                                            <input type="radio" id="recurring" name="type" value="2">
                                                            <label for="recurring">Recurring</label>
                                                        </div>
                                                    </div>
                                                </div>-->
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="title" value="{{old('title')?old('title'):$notification->title}}" class="form-control" placeholder="Title">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Body</label>
                                    <textarea name="body" class="form-control" placeholder="Body">{{old('body')?old('body'):$notification->body}}</textarea>
                                </div>  
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Users</label>
                                    <select class="form-control select2" name="users[]" multiple>
                                        @php $all_user_ids = ""; @endphp
                                        @if($users->count())
                                            @foreach($users as $key=>$value)
                                                <option value="{{$value->id}}" <?= $notification->userIds ? (in_array($value->id, explode(',', $notification->userIds)) ? 'selected' : '') : ''; ?>>{{($value->name?$value->name.' | ':'').'+'.$value->country_code.$value->mobile}}</option>
                                                @php
                                                    $all_user_ids .= $value->device_token_id.',';
                                                @endphp
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <input type="checkbox" name="all_users" id="all_users" value="1" {{old('all_users')==1 || $notification->userIds=="all"? "checked":""}}/>
                                    <label>Send all users</label>
                                    <small>- {{ $users_count->all_users.' Users' }}</small>
                                    {{-- <input type="hidden" name="all_users_ids" id="all_users_ids" value="777,1255,768,"/> --}}
                                    <input type="hidden" name="all_users_ids" id="all_users_ids" value="{{$all_user_ids}}"/>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" name="all_english_users" id="all_english_users" value="1" {{old('all_english_users')==1 || $notification->userIds=="all_english_users"? "checked":""}}/>
                                    <label>Send all users (English)</label>
                                    <small>- {{ $users_count->english_users.' Users' }}</small>
                                </div>
                                <div class="form-group">
                                    <input type="checkbox" name="all_arabic_users" id="all_arabic_users" value="1" {{old('all_arabic_users')==1 || $notification->userIds=="all_arabic_users"? "checked":""}}/>
                                    <label>Send all users (Arabic)</label>
                                    <small>- {{ $users_count->arabic_users.' Users' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Send
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#addForm').validate({
        rules: {
            brand_name_en: {
                required: true
            },
            brand_name_ar: {
                required: true
            },
            brand_image: {
                required: true
            }
        }
    });
</script>
@endsection