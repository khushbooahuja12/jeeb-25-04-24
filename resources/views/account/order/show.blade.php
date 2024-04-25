<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('recipe_assets/css/sidebar.css') }}">
    <link rel="stylesheet" href="{{ asset('recipe_assets/css/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('recipe_assets/css/card.css') }}">
    <link rel="stylesheet" href="{{ asset('recipe_assets/css/buyer/tender_table.css') }}">
    <link rel="stylesheet" href="{{ asset('recipe_assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/recipe_assets/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script type="text/javascript">
        function view_or_not(number, id) {
              if(number == 1){  // show
                  // alert('show');
                  // return;
                  document.getElementById('show-' + id).click();
              }
              else{ // edit
                // alert('edit');
                // return;
                document.getElementById('edit-' + id).click();
              }
        }

    </script>
    <style>
        body {
            font-size: 14px;
        }
        body .main-content * {
            font-size: 13px;
        }
        body .main-content h1,
        body .main-content h2 {
            font-size: 22px;
        }
        .order_product_status_colours {
            
        }
        .order_product_status_colours .colour {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 1px #333333 solid;
        }
        .product_out_of_stock {
            background: #f83838;
        }
        .product_replaced {
            background: #48f838;
        }
        .product_missed {
            background: #f5f838;
        }
        .table thead th {
            vertical-align: top;
        }
    </style>
</head>

<body>

  @include('account.partials.sidebar')


        <!------Main Content-->

        <div class="main-content">


            <main>

                <!-----Table Start-->
            <div class="py-5">
                <div class="row setScroll py-5">
                  <div class="col-lg-11 mx-auto mt-2">
                    <div class="card rounded shadow border-0">
                      <div class="card-body px-4 py-4 bg-white rounded">
                        @include('account.partials.alerts')
                          <div class="row setScroll mb-2 d-flex" style="justify-content: space-between;">
                            <h2 class=" pl-3">Order #{{ $order->orderId }}</h2>
                          </div>
                    <div class="card-body">
                        @php
                            $user = $order->getUser;
                        @endphp
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <tbody>
                                <tr>
                                    <th>Customer Name</th>
                                    <td>{{ $user ? $user->name : '' }}</td>
                                </tr>
                                <tr>
                                    <th>Mobile</th>
                                    <td>{{ $user ? $user->mobile : '' }}</td>
                                </tr>
                                <tr>
                                    <th>Sub Total</th>
                                    <td>{{ $order->total_amount }} QAR</td>
                                <tr>
                                    <th>Delivery Charge</th>
                                    <td>{{ $order->delivery_charge }} QAR</td>
                                </tr>
                                <tr>
                                    <th>Coupon Discount (if any)</th>
                                    <td>{{ $order->coupon_discount }} QAR</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body">

                        <h2>Ordered Products</h2>
                        <br/>
                        <div class="order_product_status_colours">
                            <div class="colour product_out_of_stock"></div> <strong>Out of stock products</strong><br/>
                            <div class="colour product_replaced"></div> <strong>Replaced products</strong><br/>
                            <div class="colour product_missed"></div> <strong>Forgot products by customer</strong><br/>
                        </div>
                        <br clear="all"/><br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total Amount</th>
                                    <th>Forgot Product</th>
                                    <th>Replaced Product</th>
                                    <th>Out Of Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($order_products)
                                    @foreach ($order_products as $key => $row)
                                        @php 
                                        if ($row->is_out_of_stock==1 || $row->status==2) {
                                            $product_class = 'product_out_of_stock';
                                        } elseif ($row->is_replaced_product==1) {
                                            $product_class = 'product_replaced';
                                        } elseif ($row->is_missed_product==1) {
                                            $product_class = 'product_missed';
                                        } else {
                                            $product_class = '';
                                        }
                                        @endphp
                                        <tr class="{{$product_class}}">
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $row->fk_product_id }}</td>
                                            <td>{{ $row->product_name_en }} ({{ $row->unit }})</td>
                                            <td>{{ $row->single_product_price }}</td>
                                            <td>{{ $row->product_quantity }}</td>
                                            <td>{{ $row->total_product_price }}</td>
                                            <td>{{ $row->is_missed_product }}</td>
                                            <td>{{ $row->is_replaced_product }}</td>
                                            <td>{{ $row->is_out_of_stock }}</td>
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

    <!-------Table End-->

    <script>
        const response = document.getElementById('viewResponse');
        response.addEventListener('click',()=>{
            document.getElementById('responseTable').classList.toggle('response-table');
            document.getElementById('responseTable').classList.add('addTransition');
        })
    </script>



</main>

</div>

<!---End of Main Content-->



<script src="{{ asset('recipe_assets/js/jquery-min.js') }}"></script>
<script src="{{ asset('recipe_assets/js/popper.min.js') }}"></script>
<script src="{{ asset('recipe_assets/js/bootstrap.min.js') }}"></script>

</body>

</html>