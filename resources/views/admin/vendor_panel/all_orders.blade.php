@extends('admin.layouts.dashboard_layout_for_vendor_panel')
@section('content')
<div class="page-body">
  <div class="container-fluid">
    <div class="page-title">
      <div class="row">
        <div class="col-sm-6">
          <h3>Orders</h3>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#"><i data-feather="home"></i></a></li>
            <li class="breadcrumb-item active"> Orders</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
  <!-- Container-fluid starts-->
  <div class="container-fluid">
    <div class="row">
      <div class="col-sm-12">
        <div class="card">
          <div class="card-body">
            <div class="order-history table-responsive">
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
                  @if ($orders)
                    @foreach ($orders as $key => $row)
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
                                <a href="<?= route('vendor-order-detail', ['store' => base64url_encode($store->id), 'id' => base64url_encode($row->id)]) ?>"><i
                                        class="icon-eye"></i></a>
                            </td>
                        </tr>
                    @endforeach
                  @endif
                </tbody>
              </table>
              <nav class="mb-30" aria-label="Page navigation example">
                <ul class="pagination pagination-primary">
                  {{ $orders->links() }}
                </ul>
              </nav>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Container-fluid Ends  -->
</div>
@endsection

