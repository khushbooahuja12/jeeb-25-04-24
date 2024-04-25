@extends('admin.layouts.dashboard_layout_for_vendor_panel')
@section('content')
<style>
    .table-bordered{
        border-color:#000000
    }
</style>
<div class="page-body">
    <div class="container-fluid">
      <div class="page-title">
        <div class="row">
          <div class="col-sm-6">
            <h3>Order Detail</h3>
          </div>
          <div class="col-sm-6">
            <a class="btn btn-primary" href="{{ url()->previous() }}" type="button">Back</a>
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="index.html"><i data-feather="home"></i></a></li>
              <li class="breadcrumb-item"> Orders</li>
              <li class="breadcrumb-item active">Order Detail</li>
            </ol>
          </div>
        </div>
      </div>
    </div>
    <!-- Container-fluid starts                    -->
    <div class="container-fluid">
      <!-- Table Row Starts-->
      <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body" style="overflow-x: scroll;">
                    <table class="table table-bordered dt-responsive nowrap" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <td>{{ $order->orderId }}</td>
                            </tr>
                            <tr>
                                <th>Order Placed On</th>
                                <td>{{ date('d-m-Y h:i A', strtotime($order->created_at)) }}</td>
                            </tr>
                            <tr>
                                <th>Delivery Date</th>
                                <td><?= date('d-m-Y', strtotime($order->getOrderDeliverySlot->delivery_date)) ?></td>
                            </tr>
                            <tr>
                                <th>Delivery Time</th>
                                <td>
                                    @php
                                    $deliverySlot = $order->getOrderDeliverySlot;
                                    if ($deliverySlot) {
                                        if ($deliverySlot->delivery_time==1) {
                                            echo 'within ' . getDeliveryIn($deliverySlot->expected_eta);
                                        } else {
                                            echo $deliverySlot->later_time;
                                        }
                                    } 
                                    @endphp
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td> 
                                    @if($order->status == 1)
                                    @if((count($order->getOrderCollectedProducts) == count($order->getOrderProducts->where('is_out_of_stock', 0))))
                                    Collected
                                    @else
                                    {{ $order_status[$order->status] }}
                                    @endif
                                    @else
                                    {{ $order_status[$order->status] }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Invoice</th>
                                <td>
                                    <a class="btn btn-primary" href="{{ route('vendor-invoice-pdf', ['store' => base64url_encode($store->id), 'id' => base64url_encode($order->id)]) }}" >Download PDF</a>
                                </td>
                                <td>
                                    <a class="btn btn-primary" href="{{ route('vendor-invoice-print', ['store' => base64url_encode($store->id), 'id' => base64url_encode($order->id)]) }}" >Print</a>
                                </td>
                            </tr>
                        </thead>
                    </table>
                </div>
                
                @include('partials.errors')
                @include('partials.success')
                <div class="card-header">
                    <h6>Order Items</h6>
                </div>
                <div class="card-body" style="overflow-x: scroll;">
                    <table class="table table-bordered dt-responsive nowrap">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Product Name</th>
                                <th>Itemcode</th>
                                <th>Barcode</th>
                                <th>Product Image</th>
                                <th>No. of Items</th>
                                <th>Product Price</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($order_products)
                                @foreach ($order_products as $key => $value)
                                    @php
                                    if ($value->is_missed_product==1) {
                                        $product_class = 'product_missed';
                                    } else {
                                        $product_class = '';
                                    }
                                    @endphp
                                    <tr class="{{$product_class}}">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $value->product_name_en ?? 'N/A' }}
                                            <?= $value->product_weight != 0 ? '(' . $value->product_unit . ')' : '' ?>
                                            @if ($value->sub_products)
                                                <br/>
                                                @php
                                                    $sub_products = json_decode($value->sub_products);
                                                @endphp
                                                @foreach ($sub_products as $item)
                                                &nbsp;&nbsp;- {{ $item->product_name_en ?? "" }} ({{ $item->unit ?? "" }}) - {{ $item->price ?? "" }}  * {{ $item->product_quantity ?? "" }}<br/>
                                                @endforeach
                                            @endif
                                        </td>
                                        <td>{{ $value->itemcode ?? 'N/A' }}</td>
                                        <td>{{ $value->barcode ?? 'N/A' }}</td>
                                        <td><img src="{{ !empty($value->product_image_url) ? $value->product_image_url : asset('assets/images/dummy-product-image.jpg') }}"
                                                width="75" height="50"></td>
                                        <td>{{ $value->product_quantity }}</td>
                                        <td>{{ $value->distributor_price }}</td>
                                        <td style="width: 20%">
                                            @if($value->collected_status == 0 &&  $value->is_out_of_stock == 0 && $order->status < 3)
                                            <a class="btn-sm btn-primary mark_collected" id="{{ $value->id }}" href="javascript:void(0);"> Mark Collected </a><br><br>
                                            <a class="btn-sm btn-primary mark_out_of_stock" id="{{ $value->id }}" href="javascript:void(0);"> Mark Out of stock </a>
                                            @elseif($value->collected_status == 1)
                                            Marked Collected
                                            @else
                                            Marked Out Of Stock
                                            @endif
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
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  
  <script>
    //Mark item collected
    $('.mark_collected').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure to mark this item as collected ?');
        if (confirmed) {
            let id = $(this).attr('id');
            if (id) {
                $.ajax({
                    url: "<?= url('admin/store/order/product/collect') ?>",
                    type: 'post',
                    data: {id:id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
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
        }
    });

    //Mark item collected
    $('.mark_out_of_stock').on('click',function(e){
        e.preventDefault();
        var confirmed = confirm('Are you sure to mark this item as out of stock ?');
        if (confirmed) {
            let id = $(this).attr('id');
            if (id) {
                $.ajax({
                    url: "<?= url('admin/store/order/product/out_of_stock') ?>",
                    type: 'post',
                    data: {id:id},
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
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
        }
    });
  </script>
@endsection