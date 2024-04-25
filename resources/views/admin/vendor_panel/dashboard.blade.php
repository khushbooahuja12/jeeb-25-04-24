@extends('admin.layouts.dashboard_layout_for_vendor_panel')
@section('content')
<div class="page-body-wrapper">
<div class="page-body">
  <!-- Container-fluid starts-->
  <div class="container-fluid default-dash2">
    <div class="row">
      <div class="col-xl-3 col-lg-4 col-sm-4 des-xsm-50 box-col-33">
        <div class="card investment-sec">
          <div class="animated-bg"><i></i><i></i><i></i></div>
          <a href="{{ route('fleet-stock-update-base-products-panel',['id' => base64url_encode($store->id), 'category' => 0]) }}">
            <div class="card-body">
              <div class="icon"><i data-feather="database"></i></div>
              <p>Products</p>
              <h3>{{ $all_products }}</h3>
            </div>
          </a>
        </div>
      </div>
      <div class="col-xl-3 col-lg-4 col-sm-4 des-xsm-50 box-col-33">
          <div class="card investment-sec">
            <div class="animated-bg"><i></i><i></i><i></i></div>
              <a href="{{ route('vendor_instock_product',['id' => base64url_encode($store->id), 'category' => 0]) }}">
                <div class="card-body">
                  <div class="icon"><i data-feather="database"></i></div>
                  <p>In stock Products</p>
                  <h3>{{ $instock_products }}</h3>
                </div>
              </a>
          </div>
      </div>
      <div class="col-xl-3 col-lg-4 col-sm-4 des-xsm-50 box-col-33">
        <div class="card investment-sec">
          <div class="animated-bg"><i></i><i></i><i></i></div>
          <a href="{{ route('vendor_out_of_stock_product',['id' => base64url_encode($store->id), 'category' => 0]) }}">
            <div class="card-body">
              <div class="icon"><i data-feather="database"></i></div>
              <p>Out of stock Products</p>
              <h3>{{ $outofstock_products }}</h3>
            </div>
          </a>
        </div>
      </div>
      <div class="col-xl-3 col-lg-4 col-sm-4 des-xsm-50 box-col-33">
        <div class="card investment-sec">
          <div class="animated-bg"><i></i><i></i><i></i></div>
          <a href="{{ route('vendor-active-orders',['id' => base64url_encode($store->id)]) }}">
            <div class="card-body">
              <div class="icon"><i data-feather="database"></i></div>
              <p>Active Orders</p>
              <h3>{{ $total_active_orders }}</h3>
            </div>
          </a>
        </div>
      </div>
    </div>
    {{-- <div class="row">      
      <div class="col-xl-12 box-col-6 best-selling">
        <div class="card bestselling-sec">
          <div class="card-header p-b-0">
            <h5>Active Orders</h5>
            <div class="center-content">
              <p>Latest confirmed orders list</p>
            </div>
          </div>
          <div class="card-body pt-0">
            <div class="bestselling-table table-responsive">
              <table class="table table-border display" >
                <thead>
                  <tr>
                    <th>S.No.</th>
                    <th>Order Number</th>
                    <th>Status</th>
                    <th>Delivery Date</th>
                    <th>Time Slot</th>
                    <th>Detail</th>
                  </tr>
                </thead>
                <tbody>
                  @if ($ative_orders)
                    @foreach ($ative_orders as $key => $row) 
                      @if($row->delivery_time == 1)
                          <tr>
                              <td style="color: #c10707;">{{ $key + 1 }}</td>
                              <td style="color: #c10707;">{{ '#' . $row->orderId }} {{ isset($row->test_order) && $row->test_order==1 ? '(Test order)' : '' }}</td>
                              <td style="color: #c10707;">
                                @if($row->status == 1)
                                  @if((count($row->getOrderCollectedProducts) == count($row->getOrderProducts->where('is_out_of_stock', 0))))
                                    Collected
                                  @else
                                    {{ $order_status[$row->status] }}
                                  @endif
                                @else
                                  {{ $order_status[$row->status] }}
                                @endif
                              </td>
                              <td style="color: #c10707;">{{ $row->delivery_date }}</td>
                              <td style="color: #c10707;">{{ $row->later_time }}</td>
                              <td style="color: #c10707;">
                                  <a href="<?= route('vendor-order-detail',['store' => base64url_encode($store->id), 'id' => base64url_encode($row->id)]) ?>"><i
                                    class="icon-eye" style="color: #c10707;"></i></a>
                              </td>
                          </tr>
                      @else
                        <tr>
                          <td>{{ $key + 1 }}</td>
                          <td>{{ '#' . $row->orderId }} {{ isset($row->test_order) && $row->test_order==1 ? '(Test order)' : '' }}</td>
                          <td>
                            @if($row->status == 1)
                              @if((count($row->getOrderCollectedProducts) == count($row->getOrderProducts->where('is_out_of_stock', 0))))
                                Collected
                              @else
                                {{ $order_status[$row->status] }}
                              @endif
                            @else
                              {{ $order_status[$row->status] }}
                            @endif
                          </td>
                          <td>{{ $row->delivery_date }}</td>
                          <td>{{ $row->later_time }}</td>
                          <td>
                              <a href="<?= route('vendor-order-detail',['store' => base64url_encode($store->id), 'id' => base64url_encode($row->id)]) ?>"><i
                                class="icon-eye"></i></a>
                          </td>
                      </tr>
                      @endif
                    @endforeach
                  @endif
                </tbody>
              </table>
            </div>
            <div class="code-box-copy">
              <button class="code-box-copy__btn btn-clipboard" data-clipboard-target="#bestselling" title="" data-bs-original-title="Copy" aria-label="Copy"><i class="icofont icofont-copy-alt"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div> --}}
    <div class="row">      
      <div class="col-xl-12 box-col-6">
        <div class="card ">
          <div class="card-header p-b-0">
            <h5>Active Orders</h5>
            <div class="center-content">
              <p>Latest confirmed orders list</p>
            </div>
          </div>
          <div class="card-body pt-0">
            <div class="bestselling-table table-responsive">
              <ul class="nav nav-tabs border-tab nav-primary" id="info-tab" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="info-home-tab" data-bs-toggle="tab" href="#info-home" role="tab" aria-controls="info-home" aria-selected="true">Preparing ({{ $preparing_orders->count() }})</a></li>
                <li class="nav-item"><a class="nav-link" id="profile-info-tab" data-bs-toggle="tab" href="#info-profile" role="tab" aria-controls="info-profile" aria-selected="false">Ready For Pickup ({{ $ready_for_pickup_orders->count() }})</a></li>
              </ul>
              <div class="tab-content" id="info-tabContent">
                <div class="tab-pane fade show active" id="info-home" role="tabpanel" aria-labelledby="info-home-tab">
                  <table class="table table-bordered display" id="preparing_orders">
                    <thead class="table-dark">
                      <tr>
                        <th>S.No.</th>
                        <th>Order Number</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                        <th>Time Slot</th>
                        <th>Detail</th>
                      </tr>
                    </thead>
                  </table>
                </div>
                <div class="tab-pane fade" id="info-profile" role="tabpanel" aria-labelledby="ready_for_pickup_orders-tab">
                  <table class="table table-border display" id="ready_for_pickup_orders">
                    <thead class="table-dark">
                      <tr>
                        <th>S.No.</th>
                        <th>Order Number</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Delivery Date</th>
                        <th>Time Slot</th>
                        <th>Detail</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
            <div class="code-box-copy">
              <button class="code-box-copy__btn btn-clipboard" data-clipboard-target="#bestselling" title="" data-bs-original-title="Copy" aria-label="Copy"><i class="icofont icofont-copy-alt"></i></button>
            </div>
          </div>
        </div>
      </div>
</div>
  </div>
  <!-- Container-fluid Ends-->
</div>
@endsection