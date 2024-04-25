@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Scratch Cards List
                        <a href="<?= url('admin/scratch_cards/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/scratch_cards') ?>">Scratch Cards</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search scratch card"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        @if ($scratch_cards)
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%; background: #e7e7e7;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Apply On</th>
                                    <th style="background: #f7f2c6;">Apply On Minimum<br/> Order Amount</th>
                                    <th>@sortablelink('id', 'ID')</th>
                                    <th>Scratch Card Type</th>
                                    <th>@sortablelink('title_en', 'Title')</th>
                                    <th>Title (Ar)</th>
                                    <th>Discount</th>
                                    <th>Expiry In</th>
                                    <th>Redeemed Amount</th>
                                    <th># of Users<br/> Received</th>
                                    <th># of Users<br/> Redeemed</th>
                                    <th># of Users<br/> Not Redeemed</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scratch_cards as $key => $row)
                                    @php
                                        $redeemed_amount = $row->redeemed_amount();
                                        $number_of_coupons = $row->number_of_coupons();
                                        $number_of_coupons_used = $row->number_of_coupons_used();
                                    @endphp
                                    <tr>
                                        <td>{{ isset($_GET['page']) && $_GET['page']>=2?($_GET['page']-1)*10+($key+1):($key+1) }}</td>
                                        <td>{{ $row->apply_on }}</td>
                                        <td style="background: #f7f2c6;"> > {{ $row->apply_on_min_amount }}</td>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->scratch_card_type == 0 ? 'Onspot Reward' : 'Coupon' }}</td>
                                        <td>{{ $row->title_en }}</td>
                                        <td>{{ $row->title_ar }}</td>
                                        <td>{{ $row->discount . ($row->type == 1 ? ' %' : '') }}</td>
                                        <td>{{ $row->expiry_in }} Days</td>
                                        <td>{{ $redeemed_amount }}</td>
                                        <td>{{ $number_of_coupons }}</td>
                                        <td>{{ $number_of_coupons_used }}</td>
                                        <td>{{ $number_of_coupons - $number_of_coupons_used }}</td>
                                        <td>
                                            <div class="mytoggle">
                                                <span
                                                    style="color:<?= $row->status == 1 ? 'green' : 'red' ?>"><?= $row->status == 1 ? 'Active' : 'Inactive' ?>
                                                </span>
                                                <label class="switch">
                                                    <input type="checkbox" <?= $row->status == 1 ? 'checked' : '' ?>
                                                        onchange="changescratch_cardstatus(this,<?= $row->id ?>);">
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a title="view details"
                                                href="<?= url('admin/scratch_cards/show/' . $row->id) ?>"><i
                                                    class="fa fa-eye"></i></a>&ensp;
                                            <a title="view details"
                                                href="<?= url('admin/scratch_cards/edit/' . $row->id) ?>"><i
                                                    class="fa fa-edit"></i></a>&ensp;
                                        </td>
                                    </tr>
                                @endforeach
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
                                    {{ $scratch_cards->appends($_GET)->links() }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <h3>On register</h3>
                        @if ($scratch_cards_on_register)
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('id', 'ID')</th>
                                    <th>Scratch Card Type</th>
                                    <th>@sortablelink('title_en', 'Title')</th>
                                    <th>Title (Ar)</th>
                                    <th>Discount</th>
                                    <th>Expiry In</th>
                                    <th>Redeemed Amount</th>
                                    <th># of Users<br/> Received</th>
                                    <th># of Users<br/> Redeemed</th>
                                    <th># of Users<br/> Not Redeemed</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scratch_cards_on_register as $key => $row)
                                    @php
                                        $redeemed_amount = $row->redeemed_amount();
                                        $number_of_coupons = $row->number_of_coupons();
                                        $number_of_coupons_used = $row->number_of_coupons_used();
                                    @endphp
                                    <tr>
                                        <td>{{ isset($_GET['register']) && $_GET['register']>=2?($_GET['register']-1)*10+($key+1):($key+1) }}</td>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->scratch_card_type == 0 ? 'Onspot Reward' : 'Coupon' }}</td>
                                        <td>{{ $row->title_en }}</td>
                                        <td>{{ $row->title_ar }}</td>
                                        <td>{{ $row->discount . ($row->type == 1 ? ' %' : '') }}</td>
                                        <td>{{ $row->expiry_in }} Days</td>
                                        <td>{{ $redeemed_amount }}</td>
                                        <td>{{ $number_of_coupons }}</td>
                                        <td>{{ $number_of_coupons_used }}</td>
                                        <td>{{ $number_of_coupons - $number_of_coupons_used }}</td>
                                        <td>
                                            <div class="mytoggle">
                                                <span
                                                    style="color:<?= $row->status == 1 ? 'green' : 'red' ?>"><?= $row->status == 1 ? 'Active' : 'Inactive' ?>
                                                </span>
                                                <label class="switch">
                                                    <input type="checkbox" <?= $row->status == 1 ? 'checked' : '' ?>
                                                        onchange="changescratch_cardstatus(this,<?= $row->id ?>);">
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a title="view details"
                                                href="<?= url('admin/scratch_cards/show/' . $row->id) ?>"><i
                                                    class="fa fa-eye"></i></a>&ensp;
                                            <a title="view details"
                                                href="<?= url('admin/scratch_cards/edit/' . $row->id) ?>"><i
                                                    class="fa fa-edit"></i></a>&ensp;
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $scratch_cards_on_register->count() }} of {{ $scratch_cards_on_register->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $scratch_cards_on_register->appends($_GET)->links() }}
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <h3>On New Order</h3>
                        @if ($scratch_cards_on_order)
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th style="background: #f7f2c6;">Apply On Minimum<br/> Order Amount</th>
                                    <th>@sortablelink('id', 'ID')</th>
                                    <th>Scratch Card Type</th>
                                    <th>@sortablelink('title_en', 'Title')</th>
                                    <th>Title (Ar)</th>
                                    <th>Discount</th>
                                    <th>Expiry In</th>
                                    <th>Redeemed Amount</th>
                                    <th># of Users<br/> Received</th>
                                    <th># of Users<br/> Redeemed</th>
                                    <th># of Users<br/> Not Redeemed</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($scratch_cards_on_order as $key => $row)
                                    @php
                                        $redeemed_amount = $row->redeemed_amount();
                                        $number_of_coupons = $row->number_of_coupons();
                                        $number_of_coupons_used = $row->number_of_coupons_used();
                                    @endphp
                                    <tr>
                                        <td>{{ isset($_GET['order']) && $_GET['order']>=2?($_GET['order']-1)*10+($key+1):($key+1) }}</td>
                                        <td style="background: #f7f2c6;"> > {{ $row->apply_on_min_amount }} QAR</td>
                                        <td>{{ $row->id }}</td>
                                        <td>{{ $row->scratch_card_type == 0 ? 'Onspot Reward' : 'Coupon' }}</td>
                                        <td>{{ $row->title_en }}</td>
                                        <td>{{ $row->title_ar }}</td>
                                        <td>{{ $row->discount . ($row->type == 1 ? ' %' : '') }}</td>
                                        <td>{{ $row->expiry_in }} Days</td>
                                        <td>{{ $redeemed_amount }}</td>
                                        <td>{{ $number_of_coupons }}</td>
                                        <td>{{ $number_of_coupons_used }}</td>
                                        <td>{{ $number_of_coupons - $number_of_coupons_used }}</td>
                                        <td>
                                            <div class="mytoggle">
                                                <span
                                                    style="color:<?= $row->status == 1 ? 'green' : 'red' ?>"><?= $row->status == 1 ? 'Active' : 'Inactive' ?>
                                                </span>
                                                <label class="switch">
                                                    <input type="checkbox" <?= $row->status == 1 ? 'checked' : '' ?>
                                                        onchange="changescratch_cardstatus(this,<?= $row->id ?>);">
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a title="view details"
                                                href="<?= url('admin/scratch_cards/show/' . $row->id) ?>"><i
                                                    class="fa fa-eye"></i></a>&ensp;
                                            <a title="view details"
                                                href="<?= url('admin/scratch_cards/edit/' . $row->id) ?>"><i
                                                    class="fa fa-edit"></i></a>&ensp;
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $scratch_cards_on_order->count() }} of {{ $scratch_cards_on_order->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $scratch_cards_on_order->appends($_GET)->links() }}
                                </div>
                            </div>
                        </div>
                        @endif
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
