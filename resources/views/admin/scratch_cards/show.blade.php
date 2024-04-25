@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Scratch Card Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/scratch_cards') ?>">Scratch Cards</a></li>
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
                            <div class="offset-3 col-md-9 justify-center">
                                <table class="table table-borderless table-responsive">
                                    <tr>
                                        <th>Scratch Card Type</th>
                                        <td> {{ $scratch_card->scratch_card_type == 0 ? 'Onspot Reward' : 'Coupon' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card Title </th>
                                        <td> {{ $scratch_card->title_en }}</td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card Title (Ar) </th>
                                        <td> {{ $scratch_card->title_ar }}</td>
                                    </tr>
                                    <tr>
                                        <th>Offer By</th>
                                        <td> {{ $scratch_card->type == 1 ? 'Offer By Percentage' : 'Offer By Amount' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Discount </th>
                                        <td> {{ $scratch_card->discount . ($scratch_card->type == 1 ? ' %' : '') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Apply On Minimum Amount </th>
                                        <td> {{ $scratch_card->min_amount }}</td>
                                    </tr>
                                    <tr>
                                        <th>Expiry In </th>
                                        <td> {{ $scratch_card->expiry_in }} days</td>
                                    </tr>
                                    <tr>
                                        <th>Use Limit </th>
                                        <td> {{ $scratch_card->uses_limit }} times only</td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card Description </th>
                                        <td> {{ $scratch_card->description_en }}</td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card Description (Ar) </th>
                                        <td> {{ $scratch_card->description_ar }}</td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card Image</th>
                                        <td><img src="{{ $scratch_card->getScratchCardImage ? asset('/') . $scratch_card->getScratchCardImage->file_path . $scratch_card->getScratchCardImage->file_name : asset('assets/images/no_img.png') }}"
                                                width="150" height="100">
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Scratch Card Image (Ar)</th>
                                        <td><img src="{{ $scratch_card->getScratchCardImageAr ? asset('/') . $scratch_card->getScratchCardImageAr->file_path . $scratch_card->getScratchCardImageAr->file_name : asset('assets/images/no_img.png') }}"
                                                width="150" height="100">
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 justify-center">
                                <h3>Users received this Scratch Card</h3>
                                <table class="table table-responsive" style="width: 100%; border: 1px #aaa solid;">
                                    <tbody style="width: 100%;">
                                        @if ($scratch_card_users)
                                            <tr>
                                                <th>User ID</th>
                                                <th style="width: 15%;">User Mobile</th>
                                                <th style="width: 15%;">Coupon Code</th>
                                                <th style="width: 15%;">Status</th>
                                                <th>Received Order ID</th>
                                                <th>Scratched At</th>
                                                <th>Redeemed Amount</th>
                                                <th>Redeemed At</th>
                                                <th>Expiry Date</th>
                                            </tr>
                                            @foreach ($scratch_card_users as $item)
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
                                                    <td> <a href="<?= url('admin/users/show/' . $item->fk_user_id) ?>">{{ $item->fk_user_id }}</a></td>
                                                    <td> {{ $item->user ? $item->user->mobile : 'N/A' }}</td>
                                                    <td>{{ $item->coupon_code }}</td>
                                                    <td> {{ $item_status }}</td>
                                                    <td> {{ $item->fk_order_id }}</td>
                                                    <td> {{ $item->scratched_at }}</td>
                                                    <td> {{ $item->redeem_amount }}</td>
                                                    <td> {{ $item->redeemed_at }}</td>
                                                    <td>{{ $item->expiry_date }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr><td>No users receied this scratch card yet!</td>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->
        </div> <!-- end row -->
    </div>
@endsection
