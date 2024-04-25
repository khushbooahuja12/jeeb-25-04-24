@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    @if (isset($active_orders) && $active_orders===true)
                        <h4 class="page-title">Active Orders</h4>
                    @else
                        <h4 class="page-title">All Orders</h4>
                    @endif
                    {{-- <form method="POST" action="{{ route('admin.orders.apply_all_order_generate_jsons') }}" name="all_order_generate_json_form">
                        @csrf
                        <button type="submit" class="btn btn-success waves-effect waves-light">Generate json for all delivered and cancelled orders</button>
                    </form> --}}
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Orders</li>
                    </ol>
                    <br clear="all"/>
                    <form method="get" action="{{ route('admin.orders.export_orders') }}" class="float-right">
                        @csrf
                        <input type="hidden" name="delivery_from" class="form-control delivery_from"
                            autocomplete="off"
                            value="<?= isset($_GET['delivery_from']) && $_GET['delivery_from'] != '' ? date('m/d/Y', strtotime($_GET['delivery_from'])) : '' ?>"
                            placeholder="From">
                        <input type="hidden" name="delivery_to" class="form-control delivery_to"
                            autocomplete="off"
                            value="<?= isset($_GET['delivery_to']) && $_GET['delivery_to'] != '' ? date('m/d/Y', strtotime($_GET['delivery_to'])) : '' ?>"
                            placeholder="To">
                        {{-- <input type="hidden" name="delivery_date" class="form-control delivery_date"
                            autocomplete="off"
                            value="<?= isset($_GET['delivery_date']) && $_GET['delivery_date'] != '' ? date('m/d/Y', strtotime($_GET['delivery_date'])) : '' ?>"
                            placeholder="MM/DD/YYYY"> --}}
                        <input type="hidden" name="orderId" class="form-control" autocomplete="off"
                            value="<?= isset($_GET['orderId']) && $_GET['orderId'] != '' ? $_GET['orderId'] : '' ?>"
                            placeholder="JEEB1000">
                        <input type="hidden" name="status" class="form-control" autocomplete="off"
                            value="<?= isset($_GET['status']) && $_GET['status'] != '' ? $_GET['status'] : '' ?>"
                            placeholder="Status">
                        <button type="submit" class="btn btn-secondary">Export CSV</button>
                    </form>
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
                        <br/>
                        <form method="get" action="">
                            <div class="row" style="padding: 10px">
                                <div class="col-md-2">
                                    <select name="order_type" class="form-control">
                                        <option value="0" <?= isset($order_type) && $order_type == '0' ? 'selected' : '' ?>>All Orders</option>
                                        <option value="1" <?= isset($order_type) && $order_type == '1' ? 'selected' : '' ?>>Instant Orders</option>
                                        <option value="2" <?= isset($order_type) && $order_type == '2' ? 'selected' : '' ?>>Plus Orders</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <input type="text" name="delivery_from" class="form-control delivery_from"
                                            autocomplete="off"
                                            value="<?= isset($_GET['delivery_from']) && $_GET['delivery_from'] != '' ? date('m/d/Y', strtotime($_GET['delivery_from'])) : '' ?>"
                                            placeholder="From">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <input type="text" name="delivery_to" class="form-control delivery_to"
                                            autocomplete="off"
                                            value="<?= isset($_GET['delivery_to']) && $_GET['delivery_to'] != '' ? date('m/d/Y', strtotime($_GET['delivery_to'])) : '' ?>"
                                            placeholder="To">
                                    </div>
                                </div>
                                {{-- <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" name="delivery_date" class="form-control delivery_date"
                                            autocomplete="off"
                                            value="<?= isset($_GET['delivery_date']) && $_GET['delivery_date'] != '' ? date('m/d/Y', strtotime($_GET['delivery_date'])) : '' ?>"
                                            placeholder="From">
                                    </div>
                                </div> --}}
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <input type="text" name="orderId" class="form-control" autocomplete="off"
                                            value="<?= isset($_GET['orderId']) && $_GET['orderId'] != '' ? $_GET['orderId'] : '' ?>"
                                            placeholder="JEEB1000">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">Select Status</option>
                                        @if (isset($active_orders) && $active_orders===true)
                                            @if ($order_status)
                                                @foreach ($order_status as $key => $value)
                                                    @if ($key == 0 || $key == 4 || $key == 7)
                                                    @elseif ($key != 5)
                                                        <option
                                                            value="{{ $key }}" <?= isset($od_status) && $od_status == $key && $od_status != 'all' ? 'selected' : '' ?>>
                                                            {{ $value }}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @else
                                            <option value="all" <?= isset($_GET['status']) && $_GET['status'] == 'all' ? 'selected' : '' ?>>All Status</option>
                                            @if ($order_status)
                                                @foreach ($order_status as $key => $value)
                                                    @if ($key == 0)
                                                        <option
                                                            value="placed" <?= isset($od_status) && $od_status == 'placed' && $od_status != 'all' ? 'selected' : '' ?>>
                                                            {{ $value }}</option>
                                                    @elseif ($key != 5)
                                                        <option
                                                            value="{{ $key }}" <?= isset($od_status) && $od_status == $key && $od_status != 'all' ? 'selected' : '' ?>>
                                                            {{ $value }}</option>
                                                    @endif
                                                @endforeach
                                            @endif
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <a href="<?= url('admin/all-orders') ?>"><button type="button"
                                            class="btn btn-danger">Clear</button></a>
                                    <button type="submit" class="btn btn-success">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body" style="overflow-x: scroll;">
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Order Type</th>
                                    <th>Product Types</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Payment Type</th>
                                    <th>COD</th>
                                    <th>Cash from Card</th>
                                    <th>By Wallet</th>
                                    {{-- <th>Store</th> --}}
                                    <th>Delivery Date</th>
                                    <th>Time Slot /<br/> Delivery In</th>
                                    <th>Detail</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($orders)
                                    @foreach ($orders as $key => $row)
                                        @php
                                            $amount_from_cod = $row->payment_type=='cod' ? $row->amount_from_card : 0;
                                            $amount_from_cod_suborders= $row->getSubOrder ? $row->getOrderPaidByCOD($row->id) : 0;
                                            $amount_from_card = $row->payment_type=='online' ? $row->amount_from_card : 0;
                                            $amount_from_card_suborders= $row->getSubOrder ? $row->getOrderPaidByCart($row->id) : 0;
                                            $amount_from_wallet = $row->amount_from_wallet;
                                            $amount_from_wallet_suborders = $row->getSubOrder ? $row->getOrderPaidByWallet($row->id) : 0;
                                        @endphp
                                        <tr id="row{{$row->id}}">
                                            <td>{{ isset($_GET['page']) && $_GET['page']>=2?($_GET['page']-1)*50+($key+1):($key+1)}}</td>
                                            <td>{{ '#' . $row->orderId }} {{ isset($row->test_order) && $row->test_order==1 ? '(Test order)' : '' }}</td>
                                            <td>{{ $row->order_type }}</td>
                                            <td>{{ $row->products_type }}</td>
                                            <td>{{ $row->getSubOrder ? $row->total_amount + $row->getOrderSubTotal($row->id) : $row->total_amount }}</td>
                                            <td>{{ $order_status[$row->status] }}</td>
                                            <td>{{ $row->payment_status }}</td>
                                            <th>{{ $row->payment_type }}</th>
                                            <td>{{ floor($amount_from_cod + $amount_from_cod_suborders) }}</td>
                                            <td>{{ $amount_from_card + $amount_from_card_suborders }}</td>
                                            <td>{{ $amount_from_wallet + $amount_from_wallet_suborders }}</td>
                                            {{-- <td>{{ ($row->getStore && $row->getStore->name) ? $row->getStore->company_name.' - '.$row->getStore->name : '' }}</td> --}}
                                            <td>{{ $row->delivery_date }}</td>
                                            <td>{{ $row->delivery_time==1 ? getDeliveryIn($row->expected_eta) : $row->later_time }}</td>
                                            <td>
                                                <a href="<?= url('admin/orders/detail/' . base64url_encode($row->id)) ?>"><i class="icon-eye"></i></a>
                                            </td>
                                            <td>
                                                <form name="order_generate_json_form" id="{{ $row->id }}" class="order_generate_json_form">
                                                    <input type="hidden" name="order_id" value="{{ $row->id }}"/>
                                                    <button type="submit" id="order_generate_json_btn_{{ $row->id }}" class="btn btn-success waves-effect waves-light order_generate_json_btn order_generate_json_btn_{{$row->id}}">Generate json</button>
                                                </form>
                                            </td>
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
                                    {{ $orders->appends($_GET)->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // generate order json
        $(".order_generate_json_form").submit(function(e){ 
            e.preventDefault();
            var $form = $(this);
            var id = $form.attr('id');

            $.ajax({
                url: '<?= url('admin/orders/order_generate_json') ?>',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'JSON',
                cache: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend:function(){
                    // $("#order_generate_json_btn_"+id+"").prop('disabled', true);
                    $('#row'+id).addClass('pm-loading-shimmer');
                },
                success:function(response) {

                    if(response.status == true){
                        
                        console.log(response.message);

                    }else{

                        console.log(response.message);

                    }
                },
                complete:function(){
                    $('#row'+id).removeClass('pm-loading-shimmer');
                }
            });

        });

        function allocate_order(obj, order_id) {
            if (confirm("Are you sure you want to allocate this order?")) {
                $.ajax({
                    url: '<?= url('admin/orders/allocate_order') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        order_id: order_id
                    },
                    cache: false
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);

                        $(obj).find('button').attr('disabled', 'disabled');
                        $(obj).find('button').text('Allocated');
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }
                });
            }
        }

        // $('input.delivery_date').daterangepicker({
        //     singleDatePicker: true,
        //     showDropdowns: true,
        //     autoUpdateInput: false
        // }).on('apply.daterangepicker', function(ev, picker) {
        //     $(this).val(picker.startDate.format('MM/DD/YYYY'));

        //     $.ajax({
        //         url: '<?= url('admin/orders/get_slots') ?>',
        //         type: 'post',
        //         dataType: 'json',
        //         data: {
        //             date: $(this).val()
        //         },
        //         cache: false
        //     }).done(function(response) {
        //         if (response.status_code == 200) {
        //             var html = '<option disabled selected>Select Slot</option>'
        //             $.each(response.slots, function(i, v) {
        //                 html += '<option value=' + v.slot + '>' + v.slot + '</option>';
        //             });
        //         } else {
        //             var html = '<option disabled selected>No slot found</option>'
        //         }
        //         $("#slots").html(html);
        //     });
        // });

        
        $('input.delivery_from').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });

        $('input.delivery_to').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });
    </script>
@endsection
