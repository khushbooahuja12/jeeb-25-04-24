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
                            <h2 class=" pl-3">Orders</h2>
                          </div>
                    <div class="col-md-12">
                        <form method="get" action="">
                            <div class="row" style="padding: 10px">
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
                                    <a href="<?= url('account/orders') ?>"><button type="button"
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
                                    <th>Customer Number</th>
                                    <th>Total Amount</th>
                                    <th>Payment Type</th>
                                    <th>Paid By Card / COD</th>
                                    <th>Paid By Wallet</th>
                                    <th>Out Of Stock Amount</th>
                                    <th>Order Replaced Amount</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
                                    <th>Ordered Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($orders)
                                    @foreach ($orders as $key => $row)
                                        @php
                                        $outOfStockProductAmount = 0;
                                        $outOfStockProducts = $row->getOrderOutOfStockProducts;
                                        if ($outOfStockProducts) {
                                            foreach ($outOfStockProducts as $outOfStockProduct) {
                                                $outOfStockProductAmount += $outOfStockProduct->total_product_price;
                                            }
                                        }
                                        $replacedProductAmount = 0;
                                        $replacedProducts = $row->getOrderReplacedProducts;
                                        if ($replacedProducts) {
                                            foreach ($replacedProducts as $replacedProduct) {
                                                $replacedProductAmount += $replacedProduct->total_product_price;
                                            }
                                        }
                                        @endphp
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ '#' . $row->orderId }}</td>
                                            <td>{{ $row->getUser ? $row->getUser->mobile : '' }}</td>
                                            <td>{{ $row->total_amount }}</td>
                                            <td>{{ $row->payment_type }}</td>
                                            <td>{{ $row->amount_from_card }}</td>
                                            <td>{{ $row->amount_from_wallet }}</td>
                                            <td>{{ $outOfStockProductAmount }}</td>
                                            <td>{{ $replacedProductAmount }}</td>
                                            <td>{{ $row->payment_status }}</td>
                                            <td>{{ $order_status[$row->status] }}</td>
                                            <td>{{ $row->created_at }}</td>
                                            <td>
                                                @if ($row->getUser)
                                                <a href="<?= url('account/wallets/'.$row->getUser->id) ?>"><button type="button" class="btn-sm btn-primary" style="width: 100%;">View Wallet</button></a>
                                                <br/><br/>
                                                @endif
                                                <a href="<?= url('account/orders/show/'.$row->id) ?>"><button type="button" class="btn-sm btn-primary" style="width: 100%;">View Order</button></a>
                                            </td>
                                        </tr>
                                        @if ($row->getSubOrder)
                                            @foreach ($row->getSubOrder as $key2 => $row2)
                                            <tr>
                                                <td></td>
                                                <td colspan="10" style="background: #aaaaaa;">Added through forgot something</td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td style="background: #aaaaaa;">{{ '#' . $row2->orderId }}</td>
                                                <td style="background: #aaaaaa;"></td>
                                                <td style="background: #aaaaaa;">{{ $row2->total_amount }}</td>
                                                <td style="background: #aaaaaa;">{{ $row->payment_type }}</td>
                                                <td style="background: #aaaaaa;">{{ $row2->amount_from_card }}</td>
                                                <td style="background: #aaaaaa;">{{ $row2->amount_from_wallet }}</td>
                                                <td style="background: #aaaaaa;"></td>
                                                <td style="background: #aaaaaa;"></td>
                                                <td style="background: #aaaaaa;">{{ $row2->payment_status }}</td>
                                                <td style="background: #aaaaaa;"></td>
                                                <td style="background: #aaaaaa;">{{ $row2->created_at }}</td>
                                                <td></td>
                                            </tr>
                                            @endforeach
                                        @endif
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