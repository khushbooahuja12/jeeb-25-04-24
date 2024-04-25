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
                            <h2 class=" pl-3">Wallet Money</h2>
                          </div>
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Mobile</th>
                                    <th>Wallet Money</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->mobile }}</td>
                                        <td>{{ $user->total_points }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-body">
                        <h5>History</h5>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Transaction Type</th>
                                    <th>Amount</th>
                                    <th>Payment Response</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($wallets)
                                    @foreach ($wallets as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->transaction_type }}</td>
                                            <td>{{ $row->amount }}</td>
                                            <td>#{{ $row->paymentResponse }}</td>
                                            <td>{{ $row->status }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $wallets->count() }} of {{ $wallets->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $wallets->links() }}
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