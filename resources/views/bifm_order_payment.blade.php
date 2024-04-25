<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="robots" content="noindex,nofollow">
    <title>Order Payment</title>
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('home_assets/uploads/2019/10/jeeb_square_logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Condensed&family=Rubik+Moonrocks&family=Ubuntu:ital,wght@0,400;1,700&display=swap"
        rel="stylesheet">
    <style type="text/css">
    .form-row {
        width: 70%;
        float: left;
        background-color: #aaaaaa;
    }
    #card-element {
    background-color: transparent;
    height: 40px;
    border-radius: 4px;
    border: 1px solid transparent;
    box-shadow: 0 1px 3px 0 #e6ebf1;
    -webkit-transition: box-shadow 150ms ease;
    transition: box-shadow 150ms ease;
    }

    #card-element--focus {
    box-shadow: 0 1px 3px 0 #cfd7df;
    }

    #card-element--invalid {
    border-color: #fa755a;
    }

    #card-element--webkit-autofill {
    background-color: #fefde5 !important;
    }

    #submitbutton,#tap-btn{
    align-items:flex-start;
    background-attachment:scroll;background-clip:border-box;
    background-color:rgb(50, 50, 93);background-image:none;
    background-origin:padding-box;
    background-position-x:0%;
    background-position-y:0%;
    background-size:auto;
    border-bottom-color:rgb(255, 255, 255);
    border-bottom-left-radius:4px;
    border-bottom-right-radius:4px;border-bottom-style:none;
    border-bottom-width:0px;border-image-outset:0px;
    border-image-repeat:stretch;border-image-slice:100%;
    border-image-source:none;border-image-width:1;
    border-left-color:rgb(255, 255, 255);
    border-left-style:none;
    border-left-width:0px;
    border-right-color:rgb(255, 255, 255);
    border-right-style:none;
    border-right-width:0px;
    border-top-color:rgb(255, 255, 255);
    border-top-left-radius:4px;
    border-top-right-radius:4px;
    border-top-style:none;
    border-top-width:0px;
    box-shadow:rgba(50, 50, 93, 0.11) 0px 4px 6px 0px, rgba(0, 0, 0, 0.08) 0px 1px 3px 0px;
    box-sizing:border-box;color:rgb(255, 255, 255);
    cursor:pointer;
    display:block;
    float:left;
    font-family:"Helvetica Neue", Helvetica, sans-serif;
    font-size:15px;
    font-stretch:100%;
    font-style:normal;
    font-variant-caps:normal;
    font-variant-east-asian:normal;
    font-variant-ligatures:normal;
    font-variant-numeric:normal;
    font-weight:600;
    height:35px;
    letter-spacing:0.375px;
    line-height:35px;
    margin-bottom:0px;
    margin-left:12px;
    margin-right:0px;
    margin-top:28px;
    outline-color:rgb(255, 255, 255);
    outline-style:none;
    outline-width:0px;
    overflow-x:visible;
    overflow-y:visible;
    padding-bottom:0px;
    padding-left:14px;
    padding-right:14px;
    padding-top:0px;
    text-align:center;
    text-decoration-color:rgb(255, 255, 255);
    text-decoration-line:none;
    text-decoration-style:solid;
    text-indent:0px;
    text-rendering:auto;
    text-shadow:none;
    text-size-adjust:100%;
    text-transform:none;
    transition-delay:0s;
    transition-duration:0.15s;
    transition-property:all;
    transition-timing-function:ease;
    white-space:nowrap;
    width:150.781px;
    word-spacing:0px;
    writing-mode:horizontal-tb;
    -webkit-appearance:none;
    -webkit-font-smoothing:antialiased;
    -webkit-tap-highlight-color:rgba(0, 0, 0, 0);
    -webkit-border-image:none;

    }
    .alert-success {
      color: #ffffff;
      background-color: #02c58d;
      padding: 0.75rem 1.25rem;
      border: 1px solid transparent;
      border-radius: 0.25rem;
      margin-left: 12px;
    }
    .alert-danger {
      color: #ffffff;
      background-color: #fc5923;
      padding: 0.75rem 1.25rem;
      border: 1px solid transparent;
      border-radius: 0.25rem;
      margin-left: 12px;
    }
    #error-handler {
      color: #fc5923;
      padding: 0.75rem 0rem 0rem;
      border: 1px solid transparent;
      border-radius: 0.25rem;
      margin-left: 12px;
    }
    </style>
</head>
<body style=" background: #fff;">
<div style="margin: 20px auto; max-width: 600px; width: 80%; height: auto; padding-bottom: 70px; position: relative; top: 10%; border-radius: 10px; border: 1px #aaa solid;">
    <h2 style="background: #0056b3; color: #fff; padding: 5px 10px; text-align: center;">Pay for the order - #{{$order->orderId}}</h2>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bluebird/3.3.4/bluebird.min.js"></script>
    <script src="https://secure.gosell.io/js/sdk/tap.min.js"></script>
    <form id="form-container" class="charge" method="post" action="/charge">
        @csrf
        <!-- Tap element will be here -->
        <div id="element-container"></div>
        <div id="error-handler" role="alert"></div>
        <div id="success" style=" display: none;;position: relative;float: left;">
                Success! Your token is <span id="token" class="token"></span>
        </div>
        <!-- Tap pay button -->
        <br/>
        <input type="hidden" name="token" class="token_input" value=""/>
        <input type="hidden" name="order_id" class="order_id" value="{{ $order->id }}"/>
        <input type="hidden" name="amount" class="amount" value="{{ $order->amount_from_card }}"/>
        <input type="hidden" name="user_id" class="user_id" value="{{ $user->id }}"/>
        <div class="success_alert" role="alert"></div>
        <div class="error_alert" role="alert"></div>
        <button id="tap-btn">Submit</button>
    </form>
</div>
<script src="/assets/js/jquery.min.js"></script>
<script>
//pass your public key from tap's dashboard
var tap = Tapjsli("{{env('TAP_PAYMENT_PUB_KEY')}}");

var elements = tap.elements({});
var style = {
  base: {
    color: '#535353',
    lineHeight: '18px',
    fontFamily: 'sans-serif',
    fontSmoothing: 'antialiased',
    fontSize: '16px',
    '::placeholder': {
      color: 'rgba(0, 0, 0, 0.26)',
      fontSize:'15px'
    }
  },
  invalid: {
    color: 'red'
  }
};
// input labels/placeholders
var labels = {
    cardNumber:"Card Number",
    expirationDate:"MM/YY",
    cvv:"CVV",
    cardHolder:"Card Holder Name"
  };
//payment options
var paymentOptions = {
  currencyCode:["QAR","SAR"],
  labels : labels,
  TextDirection:'ltr'
}
//create element, pass style and payment options
var card = elements.create('card', {style: style},paymentOptions);
//mount element
card.mount('#element-container');
//card change event listener
card.addEventListener('change', function(event) {
  if(event.BIN){
    console.log(event.BIN)
  }
  if(event.loaded){
    console.log("UI loaded :"+event.loaded);
    console.log("current currency is :"+card.getCurrency())
  }
  var displayError = document.getElementById('error-handler');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

// Handle form submission
var form = document.getElementById('form-container');
form.addEventListener('submit', function(event) {
  event.preventDefault();

  tap.createToken(card).then(function(result) {
    console.log(result);
    if (result.error) {
      // Inform the user if there was an error
      var errorElement = document.getElementById('error-handler');
      errorElement.textContent = result.error.message;
    } else {
      // Send the token to your server
      // var errorElement = document.getElementById('success');
      // errorElement.style.display = "block";
      var tokenElement = document.getElementById('token');
      tokenElement.textContent = result.id;
      console.log(result.id);

      $('.token_input').val(result.id);
      $.ajax({
          url: '<?= url('/bifm_order/payment/') ?>',
          type: 'POST',
          data: $('.charge').serialize(),
          dataType: 'JSON',
          cache: false
      }).done(function(response) {
          if (response.status_code === 200) {
              var success_str =
                  '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                  '' +
                  '<strong>' + response.message + '</strong>.' +
                  '</div>';
              $(".success_alert").html(success_str);
              if (response.message=='Pending') {
                console.log(response.result);
                console.log(response.result.transaction.url);
                window.location.href = response.result.transaction.url;
              }
          } else {
              var message = response.message;
              if (response.result && response.result.message) {
                message = response.result.message;
              }
              var error_str =
                  '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                  '' +
                  '<strong>' + message + '</strong>.' +
                  '</div>';
              $(".error_alert").html(error_str);
          }
      });
    }
  });
});


</script>
</body>
</html>