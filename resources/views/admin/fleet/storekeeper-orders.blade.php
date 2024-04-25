@extends('admin.layouts.dashboard_layout_for_fleet_panel')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">All Orders</h4>
                </div>
            </div>
        </div>
        <a class="btn btn-primary" href="{{route('storekeeper-driver-list')}}">Drivers (Collectors)</a>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
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
                                                <a href="{{ route('fleet-storekeeper-order-detail', $row->id) }}"><i
                                                        class="icon-eye"></i>
                                                </a>
                                            </td>
                                            <td>{{ $row->getSubOrder ? $row->total_amount + $row->getOrderSubTotal($row->id) : $row->total_amount }}</td>
                                            <td>{{ $order_status[$row->status] }}</td>
                                            <th>{{ $row->payment_type }}</th>
                                            <td>{{ floor($amount_from_cod + $amount_from_cod_suborders) }}</td>
                                            <td>{{ $amount_from_card + $amount_from_card_suborders }}</td>
                                            <td>{{ $amount_from_wallet + $amount_from_wallet_suborders }}</td>
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
@endsection
