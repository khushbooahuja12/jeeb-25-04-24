@extends('admin.layouts.dashboard_layout_for_vendor_master_panel')
@section('content')
<div class="page-body-wrapper">
<div class="page-body">
  <!-- Container-fluid starts-->
  <div class="container-fluid default-dash2">
    <div class="row">
      @foreach ($stores as $store)
        <div class="col-sm-6">
          <div class="card social-widget-card">
            <div class="card-body">
              <div class="d-flex">
                <div class="social-font flex-shrink-0"><img src="../assets/images/general-widget/svg-icon/2.svg" alt=""></div>
                <div class="flex-grow-1">
                  <h4>{{ $store->name }} ( {{ $store->city }} ) </h4>
                </div>
                <a class="btn btn-primary" href="{{ route('vendor-dashboard',['id' => base64url_encode($store->id)]) }}">Check</a>
              </div>
            </div>
            @php
            $allProducts = \App\Model\BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
              ->join('categories','base_products.fk_sub_category_id','=','categories.id')
              ->where('base_products.parent_id', '=', 0)
              ->where('base_products.product_type', '=', 'product')
              ->where('base_products_store.fk_store_id', '=', $store->id)
              ->count();

            $inStockProducts = \App\Model\BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
              ->join('categories','base_products.fk_sub_category_id','=','categories.id')
              ->where('base_products.parent_id', '=', 0)
              ->where('base_products.product_type', '=', 'product')
              ->where('base_products_store.fk_store_id', '=', $store->id)
              ->where('base_products_store.stock', '>', 0)
              ->count();

            $outOfStockProducts = \App\Model\BaseProductStore::join('base_products','base_products_store.fk_product_id','=','base_products.id')
              ->join('categories','base_products.fk_sub_category_id','=','categories.id')
              ->where('base_products.parent_id', '=', 0)
              ->where('base_products.product_type', '=', 'product')
              ->where('base_products_store.fk_store_id', '=', $store->id)
              ->where('base_products_store.stock', '=', 0)
              ->count();

            $activeOrders = \App\Model\Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
              ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
              ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
              ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
              ->where('order_products.fk_store_id', '=', $store->id)
              ->whereNotIn('orders.status',[0,4,7])
              ->where('orders.parent_id', '=', 0)
              ->where(function($query) {
                  $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
              })
              ->groupBy('orders.id')
              ->orderBy('order_delivery_slots.delivery_date', 'desc')
              ->orderBy('order_delivery_slots.later_time', 'desc')
              ->get()
              ->count();

            $deliveredOrders = \App\Model\Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
              ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
              ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
              ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
              ->where('order_products.fk_store_id', '=', $store->id)
              ->where('orders.status','=',7)
              ->where('orders.parent_id', '=', 0)
              ->where(function($query) {
                  $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
              })
              ->groupBy('orders.id')
              ->orderBy('order_delivery_slots.delivery_date', 'desc')
              ->orderBy('order_delivery_slots.later_time', 'desc')
              ->get()
              ->count();

            $totalOrders = \App\Model\Order::leftJoin('order_delivery_slots', 'order_delivery_slots.fk_order_id', '=', 'orders.id')
              ->leftJoin('order_payments', 'orders.id', '=', 'order_payments.fk_order_id')
              ->leftJoin('order_products', 'orders.id', '=', 'order_products.fk_order_id')
              ->select("orders.*", 'order_payments.status as payment_status', 'order_delivery_slots.delivery_time', 'order_delivery_slots.delivery_date', 'order_delivery_slots.later_time')
              ->where('order_products.fk_store_id', '=', $store->id)
              ->where('orders.parent_id', '=', 0)
              ->where(function($query) {
                  $query->where('orders.test_order', '!=', 1)->orWhereNull('orders.test_order');
              })
              ->groupBy('orders.id')
              ->orderBy('order_delivery_slots.delivery_date', 'desc')
              ->orderBy('order_delivery_slots.later_time', 'desc')
              ->get()
              ->count();
            @endphp
            <div class="card-footer">
              <div class="row">
                <div class="col text-center">
                  <h6 class="font-roboto">Products</h6>
                  <h5 class="counter mb-0 font-roboto font-primary mt-1">{{ $allProducts }}</h5>
                </div>
                <div class="col text-center">
                  <h6 class="font-roboto">In Stock Products</h6>
                  <h5 class="counter mb-0 font-roboto font-primary mt-1">{{ $inStockProducts }}</h5>
                </div>
                <div class="col text-center">
                  <h6 class="font-roboto">Out Of Stock Products</h6>
                  <h5 class="counter mb-0 font-roboto font-primary mt-1">{{ $outOfStockProducts }}</h5>
                </div>
              </div>
              <div class="row">
                <div class="col text-center">
                  <h6 class="font-roboto">Active Orders</h6>
                  <h5 class="counter mb-0 font-roboto font-primary mt-1">{{ $activeOrders }}</h5>
                </div>
                <div class="col text-center">
                  <h6 class="font-roboto">Delivered Orders</h6>
                  <h5 class="counter mb-0 font-roboto font-primary mt-1">{{ $deliveredOrders }}</h5>
                </div>
                <div class="col text-center">
                  <h6 class="font-roboto">Total Orders</h6>
                  <h5 class="counter mb-0 font-roboto font-primary mt-1">{{ $totalOrders }}</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
  <!-- Container-fluid Ends-->
</div>
@endsection