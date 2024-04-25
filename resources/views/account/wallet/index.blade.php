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
                    <div class="col-md-12">
                        <form method="get" action="">
                            <div class="row" style="padding: 10px">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" disabled value="+974" style="width:20px;"/>
                                        <input type="text" name="mobile" class="form-control mobile" style="flex-grow:3"
                                            autocomplete="off"
                                            value="<?= isset($_GET['mobile']) && $_GET['mobile'] != '' ? $_GET['mobile'] : '' ?>"
                                            placeholder="1234 5678">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= url('account/wallets') ?>"><button type="button"
                                            class="btn btn-danger">Clear</button></a>
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
                                    <th>Customer Name</th>
                                    <th>Mobile</th>
                                    <th>Wallet Money</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($wallets)
                                    @foreach ($wallets as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>{{ $row->mobile }}</td>
                                            <td>{{ $row->total_points }}</td>
                                            <td><a href="<?= url('account/wallets/'.$row->id) ?>"><button type="button"
                                                    class="btn btn-primary">View</button></a></td>
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