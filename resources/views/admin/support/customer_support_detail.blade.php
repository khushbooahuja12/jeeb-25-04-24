@extends('admin.layouts.dashboard_layout')
@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/customer_support.css') }}">
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Ticket Detail ({{$detail->id}})</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/customer-support') ?>">Customer Support</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')        
        <div class="mesgs">
            @if ($messages->count())
                <div class="msg_history" id="msg_history" style="max-height: none">
                    @foreach ($messages as $key => $value)
                        <div class="<?= $value->message_type == 'sent' ? 'incoming_msg' : 'outgoing_msg' ?>">
                            <div class="<?= $value->message_type == 'sent' ? 'received_msg' : 'sent_msg' ?>">
                                <div class="<?= $value->message_type == 'sent' ? 'received_withd_msg' : '' ?>">
                                    <p>
                                        <?php if ($value->type == 'image'): ?>
                                        <img src="<?= $value->getFile && $value->getFile->file_path ? asset('/') . $value->getFile->file_path . $value->getFile->file_name : asset('assets/images/dummy_user.png') ?>"
                                            width="100" height="75" />
                                        <?php else: ?>
                                        {{ $value->message }}
                                        <?php endif; ?>
                                    </p>
                                    <span
                                        created_at="<?= strtotime($value->created_at) ?> class="changeUtcDateTime">{{ date('H:i A | d-m-Y', strtotime($value->created_at)) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="msg_history">
                    <center>No messages found !</center>
                </div>
            @endif           
        </div>
    </div>
@endsection
