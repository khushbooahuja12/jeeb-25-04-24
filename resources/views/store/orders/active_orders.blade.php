@extends('store.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Orders </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('store/' . $storeId . '/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Active Orders</li>
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
                    <div class="col-md-12">
                        <form method="get" action="">
                            <div class="row" style="padding: 10px">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" name="orderId" class="form-control" autocomplete="off"
                                            value="<?= isset($_GET['orderId']) && $_GET['orderId'] != '' ? $_GET['orderId'] : '' ?>"
                                            placeholder="JEEB1000">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control">
                                        <option selected disabled>Select Status</option>
                                        @if ($order_status)
                                            @foreach ($order_status as $key => $value)
                                                @if ($key != 5)
                                                    <option
                                                        value="{{ $key }}"<?= isset($_GET['status']) && $_GET['status'] == $key ? 'selected' : '' ?>>
                                                        {{ $value }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= url('store/' . $storeId . '/active-orders') ?>">
                                        <button type="button" class="btn btn-danger">Clear</button>
                                    </a>
                                    <button type="submit" class="btn btn-success">Filter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Order Number</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($orders)
                                    @foreach ($orders as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ '#' . $row->orderId }}</td>
                                            <td>{{ $row->getSubOrder ? $row->total_amount + $row->getOrderSubTotal($row->id) : $row->total_amount }}
                                            </td>
                                            <td>{{ $order_status[$row->status] }}</td>
                                            <td>
                                                <a
                                                    href="<?= url('store/' . $storeId . '/orders/detail/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
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

        $('input.delivery_date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));

            $.ajax({
                url: '<?= url('admin/orders/get_slots') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    date: $(this).val()
                },
                cache: false
            }).done(function(response) {
                if (response.status_code == 200) {
                    var html = '<option disabled selected>Select Slot</option>'
                    $.each(response.slots, function(i, v) {
                        html += '<option value=' + v.slot + '>' + v.slot + '</option>';
                    });
                } else {
                    var html = '<option disabled selected>No slot found</option>'
                }
                $("#slots").html(html);
            });
        });
    </script>
@endsection
