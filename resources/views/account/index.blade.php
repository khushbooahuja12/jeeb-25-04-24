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
                            <h2 class=" pl-3">Invoices</h2>
                          </div>
                        <div class="table-responsive">
                          <table id="example" style="width:100%" class="table table-bordered text-center">
                            <thead>
                              <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Order Id</th>
                                <th>Selling Price</th>
                                <th>Purchasing Price</th>
                                <th>Actions</th>
                              </tr>
                            </thead>
                            <tbody>
                                @foreach ($invoices as $invoice)
                                <a href="{{ url('account/invoice/show/'.$invoice->id) }}" id="show-{{ $invoice->id }}" hidden></a>
                                {{-- @if($invoice->purchase_price != null)
                                <tr onclick="view_or_not(1,'{{ $invoice->id }}');" style="cursor: pointer;">
                                @else --}}
                                <tr>
                                {{-- @endif --}}
                                    <td>{{ $invoice->date }}</td>
                                    <td> {{ $invoice->time }} </td>
                                    <td> {{ $invoice->order_id }} </td>
                                    <td>{{ $invoice->selling_price }}</td>
                                    <td>
                                      @if ($invoice->purchase_price != null)
                                        {{ $invoice->purchase_price }}
                                      @else
                                        <a href="{{ url('account/invoice/'.$invoice->id.'/edit') }}"  id="edit-{{ $invoice->id }}" hidden></a>
                                        <a href="" onclick="event.preventDefault(); view_or_not(2,'{{ $invoice->id }}');" type="button" class="btn btn-info btn-sm">Edit</a>
                                      @endif  
                                    </td>
                                    <td>
                                    @if($invoice->purchase_price != null)
                                    <a href="{{ url('account/invoice/'.$invoice->id.'/edit') }}" class="checkBtn btn btn-primary" role="button"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="{{ url('account/invoice/'.$invoice->id) }}" class="checkBtn btn btn-secondary" role="button"><i class="fas fa-eye"></i> View</a>
                                    @else
                                    @endif
                                    </td>
                                  </tr>
                                @endforeach
                                         
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