@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .text_mode {
        display: block;
    }
    .edit_mode {
        display: none;
    }
    .driver_edit {
        display: block;
    }
    .driver_saving {
        display: none;
    }
    .driver_save {
        display: none;
    }
    .product_btn {
        width: 70px;
        height: 35px;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">All Orders</h4>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('fleet-store-orders') }}">
                            <div class="row" style="padding: 10px">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" name="orderId" class="form-control" autocomplete="off"
                                            value="<?= isset($_GET['orderId']) && $_GET['orderId'] != '' ? $_GET['orderId'] : '' ?>"
                                            placeholder="JEEB1000">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" name="delivery_date" class="form-control delivery_date"
                                            autocomplete="off"
                                            value="<?= isset($_GET['delivery_date']) && $_GET['delivery_date'] != '' ? date('m/d/Y', strtotime($_GET['delivery_date'])) : '' ?>"
                                            placeholder="mm/dd/yyyy">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option selected value="">--Select Status--</option>
                                        @if ($order_status)
                                            @foreach ($order_status as $key => $value)
                                                @if ($key != 5)
                                                    <option
                                                        value="{{ $key == 0 ? $key = 'placed': $key }}"<?= isset($_GET['status']) && $_GET['status'] == $key ? 'selected' : '' ?>>
                                                        {{ $value }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="store" class="form-control">
                                        <option selected disabled value="">--Select Store--</option>
                                        <option selected value="0">All Store</option>
                                        @if ($stores)
                                            @foreach ($stores as $key => $value)
                                                <option value="{{ $value->id }}" <?= isset($_GET['store']) && $_GET['store'] == $value->id ? 'selected' : '' ?>>{{ $value->company_name }}-{{ $value->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= url('admin/fleet/store-orders') ?>">
                                        <button type="button" class="btn btn-warning">Clear</button>
                                    </a>
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                       
                        <br clear="all"/><br/>

                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Detail</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Payment Type</th>
                                    <th>COD</th>
                                    <th>Cash from Card</th>
                                    <th>By Wallet</th>
                                    @if (isset($_GET['store']) && $_GET['store'] !=0)
                                        <th>Order Store</th>
                                    @endif
                                    {{-- <th>Nearby Store</th> --}}
                                    <th>Delivery Date</th>
                                    <th>Time Slot</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($orders)
                                    @foreach ($orders as $key => $row)
                                        <tr>
                                            @php
                                                $amount_from_cod = $row->payment_type=='cod' ? $row->amount_from_card : 0;
                                                $amount_from_cod_suborders= $row->getSubOrder ? $row->getOrderPaidByCOD($row->id) : 0;
                                                $amount_from_card = $row->payment_type=='online' ? $row->amount_from_card : 0;
                                                $amount_from_card_suborders= $row->getSubOrder ? $row->getOrderPaidByCart($row->id) : 0;
                                                $amount_from_wallet = $row->amount_from_wallet;
                                                $amount_from_wallet_suborders = $row->getSubOrder ? $row->getOrderPaidByWallet($row->id) : 0;
                                            @endphp
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ '#' . $row->orderId }} {{ isset($row->test_order) && $row->test_order==1 ? '(Test order)' : '' }}</td>
                                            <td>
                                                @if (isset($_GET['store']) && $_GET['store'] !=0)
                                                    <a href="{{ route('fleet-store-order-detail', ['id' => base64url_encode($row->id), 'store' => base64url_encode($_GET['store'])]) }}"><i class="fa fa-eye"></i>
                                                    </a>
                                                @else
                                                    <a href="{{ route('fleet-store-order-detail', ['id' => base64url_encode($row->id), 'store' => base64url_encode(0)]) }}"><i class="fa fa-eye"></i>
                                                    </a>
                                                @endif
                                                
                                            </td>
                                            <td>{{ $row->getSubOrder ? $row->total_amount + $row->getOrderSubTotal($row->id) : $row->total_amount }}</td>
                                            <td>{{ $order_status[$row->status] }}</td>
                                            <th>{{ $row->payment_type }}</th>
                                            <td>{{ floor($amount_from_cod + $amount_from_cod_suborders) }}</td>
                                            <td>{{ $amount_from_card + $amount_from_card_suborders }}</td>
                                            <td>{{ $amount_from_wallet + $amount_from_wallet_suborders }}</td>
                                            @if (isset($_GET['store']) && $_GET['store'] !=0)
                                            <td>{{ $row->store_name ? $row->store_name.' - '.$row->store_company_name : '' }}</td>
                                            @endif
                                            {{-- <td>{{ $row->getStore->name ? $row->getStore->company_name.' - '.$row->getStore->name : '' }}</td> --}}
                                            <td>{{ $row->delivery_date }}</td>
                                            <td>{{ $row->later_time }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $orders->count() }} of {{ $orders->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $orders->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $('input.delivery_date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });
    </script>
@endsection
