@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">User Specific Coupons / Onspot Rewards</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/scratch_cards') ?>">Scratch Cards</a></li>
                        <li class="breadcrumb-item active">Users List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search scratch card"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('id', 'ID')</th>
                                    <th>Created At</th>
                                    <th>User ID</th>
                                    <th>User Mobile</th>
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
                                @if ($scratch_cards)
                                    @foreach ($scratch_cards as $key => $item)
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
                                            <td> <a href="<?= url('admin/users/show/' . $item->fk_user_id) ?>">{{ $item->fk_user_id }}</a></td>
                                            <td> {{ $item->user ? $item->user->mobile : 'N/A' }}</td>
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
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $scratch_cards->count() }} of {{ $scratch_cards->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $scratch_cards->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function changescratch_cardstatus(obj, id) {
            var confirm_chk = confirm('Are you sure to change scratch_cards status?');
            if (confirm_chk) {
                var checked = $(obj).is(':checked');
                if (checked == true) {
                    var status = 1;
                } else {
                    var status = 0;
                }
                if (id) {
                    $.ajax({
                        url: "<?= url('admin/scratch_cards/change_scratch_cards_status') ?>",
                        type: 'post',
                        data: 'id=' + id + '&action=' + status + '&_token=<?= csrf_token() ?>',
                        success: function(data) {
                            if (data.error_code == "200") {
                                alert(data.message);
                                location.reload();
                            } else {
                                alert(data.message);
                            }
                        }
                    });
                } else {
                    alert("Something went wrong");
                }
            } else {
                return false;
            }
        }
    </script>
@endsection
