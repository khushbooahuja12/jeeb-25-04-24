<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Add Bill</title>
        <link rel="stylesheet" href="{{ asset('recipe_assets/css/sidebar.css') }}">
        <link rel="stylesheet" href="{{ asset('recipe_assets/css/navbar.css') }}">
        <link rel="stylesheet" href="{{ asset('recipe_assets/css/card.css') }}">
        <link rel="stylesheet" href="{{ asset('recipe_assets/css/create-edit-recipe.css') }}">
        <link rel="stylesheet" href="{{ asset('recipe_assets/css/bootstrap.min.css') }}">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/recipe_assets/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script type="text/javascript">
            function openImg(id) {
                var largeImage = document.getElementById('image-' + id);
                var url=largeImage.getAttribute('src');
                window.open(url,'Image','width=largeImage.stylewidth,height=largeImage.style.height,resizable=1');
            }

          var serialNo = -1;
          var bills = {!! json_encode($bills) !!};
          function addBill(){
              bills++;
              // Our Main Div
              var addBillDiv = document.getElementById('bills-div');
              var text = document.createElement('div');
              text.innerHTML = "<div class='row mx-auto' id='bills-row1-"+bills+"'><div class='form-group col-md-6'><input type='text' placeholder='Purchase Party' id='purchase-party-"+bills+"'name='purchase_parties[]' class='form-control' required></div><div class='form-group col-md-6'><select  id='purchase-by-" + bills + "' name='purchases_by[]' class='form-control' required><option value='credit'>Credit</option><option value='sale'>Sale</option></select></div></div><div class='row mx-auto' id='bills-row2-"+bills+"'><div class='form-group col-md-6'><input type='file' id='bill-image-" + bills +"' name='bill_images[]' class='form-control'required ></div><div class='form-group col-md-6'><input type='number' placeholder='Purchase Amount' id='purchase-amount-" + bills + "' name='purchase_amounts[]' class='form-control' required></div></div>";
              addBillDiv.appendChild(text);
          }
          function removeBill() {
              if(bills < 0){
                  return;
              }
              var div1 = document.getElementById('bills-row1-'+bills);
              var div2 = document.getElementById('bills-row2-'+bills);
              div1.remove();
              div2.remove();
              bills--;
          }
      
      </script>

        <style>
            .campaign-wrapper{
                width: 100%;
                height: 100%;
                padding-top: 40px;
            }

            .campaign-wrapper .c-container{
                padding-top: 20px;
                width: 90%;
                height: 100%;
                margin-left: auto;
                margin-right: auto;
                border-radius: 1rem;
                padding: 1rem 2rem 1rem 2rem;
                background-color: #fff;
                /* background-color: #600EDF; */
            }

            @media screen and (max-width:768px) {
                .campaign-wrapper .c-container{
                    width: 100%;
                    padding: 3rem 0rem 2.5rem 0rem;
                }
                .campaign-wrapper .c-container h1{
                    font-size: 27px;
                }
            }
        </style>

</head>

<body>

    @include('account.partials.sidebar')

    <!------Main Content-->
    <div class="main-content">
        @include('account.partials.header')
        <main>
            <!-- campaign Wrapper start  -->
              <div class="campaign-wrapper">
                      <div class="c-container shadow-md border">
                            <div class="row mx-auto justify-content-center">
                                <h1 class="text-center mb-4" style="font-weight: 600;">View Details</h1>
                            </div>
                            <div class="col-md-12 px-2 px-md-0">
                               
                                       
                                  {{-- <div class="row mx-auto d-flex justify-content-between input-details">
                                              <div class="form-group pl-md-4 pl-3">
                                                      <label>Attached Bills</label>
                                              </div>
                                              <div class="form-group pr-md-5 pr-3">
                                                      <div class="add-sub-buttons">
                                                          <button class="btn btn-info shadow-sm" onclick=" event.preventDefault(); addBill();">+</button>
                                                          <button class="btn btn-danger shadow-sm" onclick="event.preventDefault(); removeBill();">-</button>
                                                      </div>
                                              </div>
                                  </div> --}}
                                  <div class="result-inputs border pt-4 pb-2 rounded mb-md-4 mb-3" id="bills-div">
                                        @php($i=0)
                                        @foreach ($attached_bills as $singleBill)
                                        <div class='row mx-auto' id='bills-row1-{{ $i }}'>
                                          <div class='form-group col-md-6'>
                                            <label for=""class="font-weight-bold" >Purchase Party</label>
                                            <input type='text' placeholder='Purchase Party' id='purchase-party-{{ $i }}'name='purchase_parties[]' class='form-control' value="{{ $singleBill->purchase_party }}" readonly>
                                          </div>
                                          <div class='form-group col-md-6'>
                                            <label for=""class="font-weight-bold" >Purchase By</label>
                                            <select  id='purchase-by-{{ $i }}' name='purchases_by[]' class='form-control' readonly>
                                              @if($singleBill->purchase_by == "Credit")
                                                <option value='Credit' selected>Credit</option>
                                                <option value='Sale'>Sale</option>
                                              @else
                                                <option value='Sale' selected>Sale</option>
                                                <option value='Credit'>Credit</option>
                                              @endif
                                            </select>
                                          </div>
                                        </div>
                                        <div class='row mx-auto' id='bills-row2-{{ $i }}'>
                                          <div class='form-group col-md-6'>
                                            <label for=""class="font-weight-bold" >Bill Image </label>
                                            <div class="form-group col-md-6" style="border:1px solid rgb(196, 188, 188);  border-radius:10px; cursor: pointer;" onclick="openImg('{{ $singleBill->id }}');">
                                                <img id="image-{{ $singleBill->id }}" class="img-fluid" src="{{ asset('images/bill-images/'.$singleBill->bill_image) }}" alt="bill-img" style="height: 100%; width:100%; ">
                                            </div>
                                            <form action="{{ url('account/invoice/downloadImage') }}" method="POST">
                                                @csrf
                                                <input type="text" value="{{ $singleBill->bill_image }}" name="image" hidden>
                                                <button class="btn btn-sm btn-warning text-white">
                                                    download <i class="fa fa-download"></i>
                                                </button>
                                            </form>
                                          </div>
                                          <div class='form-group col-md-6'>
                                            <label class="font-weight-bold" for="" >Purchase Amount</label>
                                            <input type='number' placeholder='Purchase Amount' id='purchase-amount-{{ $i }}' name='purchase_amounts[]' class='form-control' value="{{ $singleBill->purchase_amount }}" readonly>
                                          </div>
                                        </div>
                                        <hr  >
                                        @php($i++)
                                        @endforeach
                                  </div>

                                    
                                    

                            </div>
                      </div>
              </div>

        </main>

    </div>

  <!---End of Main Content-->


    <script src="{{ asset('recipe_assets/js/jquery-min.js') }}"></script>
    <script src="{{ asset('recipe_assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('recipe_assets/js/bootstrap.min.js') }}"></script>

</body>

</html>