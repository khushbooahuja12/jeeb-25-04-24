<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="robots" content="noindex,nofollow">
    <title>Order</title>
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('home_assets/uploads/2019/10/jeeb_square_logo.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto+Condensed&family=Rubik+Moonrocks&family=Ubuntu:ital,wght@0,400;1,700&display=swap"
        rel="stylesheet">
    <style type="text/css">
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        hr {
            background-color: black;
            width: 200px;
            height: 2px;
            float: left;
        }

        .hr2 {
            background-color: black;
            width: 200px;
            height: 2px;
            float: right;
        }

        .hg {
            font-size: 14px !important;
        }

        td,
        th {
            text-align: left;
            padding: 25px;
        }

        .account-d {
            font-size: 13px !important;
        }

        .account-d .span2 {
            margin-left: 30px;
        }

        .account-d .span1 {
            width: 300px !important;
        }

        .account-y {
            font-size: 23px !important;
            font-weight: bold;
        }

        .account-y .span2 {
            margin-left: 50px;
        }

        .account-y .span1 {
            width: 300px !important;
        }

        tr:nth-child(even) {
            background-color: #f6f8f8;
            border-bottom: 2px solid #b9b9b8;
        }

        tr:nth-child(6) {
            background-color: #f6f8f8;
            border-bottom: none !important;
        }

        tr:nth-child(odd) {
            border-bottom: 2px solid #b9b9b8;
        }

        tr:nth-child(1) {
            border-bottom: none !important;
        }

        .content {
            margin-top: -120px;
        }

        .t-width {
            width: 800px;
            margin: auto;
        }

        .t-width2 {
            width: 800px;
            margin: auto;
        }

        .row {
            display: flex;
        }

        .col-6 {
            width: 50%;
        }

        tr,
        th,
        td {
            font-size: 20px !important;
            font-family: 'Roboto Condensed', sans-serif !important;
            letter-spacing: 2px;
        }

        tr th:nth-child(1) {
            width: 105px;
            padding-right: 0px !important;
        }

        .t-width tr th:nth-child(1) {
            width: 20px;
            padding-right: 0px !important;
        }

        .t-width tr td:nth-child(1) {
            width: 20px;
            padding-right: 0px !important;
        }

        .t-width2 tr th:nth-child(2) {
            padding-left: 0px !important;
            padding-top: 80px !important;
        }

        .t-width2 tr th:nth-child(4) {
            padding-top: 55px !important;
        }

        .t-width2 tr th:nth-child(5) {
            padding-top: 55px !important;
            letter-spacing: 4px;
            font-weight: 600;
            font-size: 15px !important;
        }

        .t-width2 tr th {
            line-height: 25px;
        }

        .spnu {
            font-size: 14px;
            font-weight: 100;
            line-height: 18px !important;
        }

    .center {
        margin: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        -ms-transform: translate(-50%, -50%);
        transform: translate(-50%, -50%);
    }

    @page {
        size: auto;
        /* auto is the initial value */
        /* this affects the margin in the printer settings */
        margin: 25mm 25mm 25mm 25mm;
    }

    body {
        margin: 0px;
    }

    /* Shopping cart */
    .shoppingcart {
        margin: 0px;
        padding: 0px;
        list-style: none;
    }
    .shoppingcart li {
        margin: 0px;
        padding: 0px;
        list-style: none;
    }
    .shoppingcart-item {
        overflow: hidden;
        height: auto;
    }
    .shoppingcart-item-image {
        text-align: center;
        background: #aaa;
        width: 40%;
        /* border: 2px #aaa solid;  */
        border-radius: 10px;
    }
    .shoppingcart-item-image img {
        max-width: 100%;
        width: auto;
        height: 150px;
        float: left;
    }
    .shoppingcart-item-detail {
        width: 60%;
        float: left;
    }
    .payment-btn {
        margin: 10px auto;
        border-radius: 10px;
        color: #fff;
        font-size: 18px;
        background-color: #30419b;
        border: 1px solid #30419b;
        width: 100%;
        height: 40px;
    }
    </style>
</head>

<body>
    
<div style="margin: 20px auto; max-width: 800px; width: 80%; height: 100%;">
    <!-- Address -->
    <div style="border: 2px #aaa solid; border-radius: 10px;">
        <div id="map" style="width:100%; height:200px; border:none; margin:0; padding:0; overflow:hidden; z-index:99999;"></div>
        <div style="padding: 10px 20px 20px;">
            @php
                if ($addresses[0]->address_type==1) {
                    $address_type = 'Home';
                } elseif ($addresses[0]->address_type==2) {
                    $address_type = 'Office';
                } else {
                    $address_type = 'Other';
                }
            @endphp
            <h4>{{$addresses[0]->name}} ({{$address_type}})</h4>
            <p>
                {{$addresses[0]->address_line1 ? $addresses[0]->address_line1.'<br/>' : ''}}
                {{$addresses[0]->address_line2 ? $addresses[0]->address_line2.'<br/>' : ''}}
                {{$addresses[0]->landmark ? $addresses[0]->landmark.'<br/>' : ''}}
                {{$addresses[0]->mobile ? $addresses[0]->mobile.'<br/>' : ''}}
            </p>
        </div>
    </div>
    <!-- Cart -->
    <div class="shoppingcart">
        <ul>
            @if($order && isset($order['result']) && isset($order['result']['cart_items']))
                @foreach ($order['result']['cart_items'] as $item)
                    <li>
                        <div class="shoppingcart-item">
                            <div class="shoppingcart-item-image">
                                <img src="{{$item['product_image']}}"/>
                            </div>
                            <div class="shoppingcart-item-detail">
                                <h4>{{$item['product_name']}}</h4>
                                <p>{{$item['product_total_price']}}</p>
                                <p>{{$item['cart_quantity']}}</p>
                            </div>
                        </div>
                    </li>
                @endforeach
            @endif
        </ul>
    </div>
    <!-- Cart -->
    <div class="payment">
        <button class="btn btn-primary payment-btn">Make Online Payment</button><br/>
        <button class="btn btn-primary payment-btn">Cash on Delivery</button>
    </div>
</div>
    
<script>
    // Initialize and add the map
    function initMap(){
        // Map options
        var options = {
            zoom:15,
            center:{lat:{{$addresses[0]->latitude}},lng:{{$addresses[0]->longitude}}}
        }
        // New map
        var map = new google.maps.Map(document.getElementById('map'), options);
        // Listen for click on map
        google.maps.event.addListener(map, 'click', function(event){
            // Add marker
            addMarker({coords:event.latLng});
        });
        // Array of markers
        var markers = [
        @if (isset($driver) && $driver && isset($driver_location) && $driver_location)
            {
                coords:{lat:{{$driver_location->latitude}},lng:{{$driver_location->longitude}}},
                content:"<h4>{{$driver_title}}</h4>",
                iconImage:'https://jeeb.tech/images/icons/driver-signal.gif'
            },
        @endif
        @if (isset($addresses[0]) && $addresses[0])
            {
                coords:{lat:{{$addresses[0]->latitude}},lng:{{$addresses[0]->longitude}}},
                content:"<h4></h4>"
            }
        @endif
        ];
        // Loop through markers
        for(var i = 0;i < markers.length;i++){
            // Add marker
            addMarker(markers[i]);
        }
        // Add Marker Function
        function addMarker(props){
        var marker = new google.maps.Marker({
            position:props.coords,
            map:map,
            // icon:props.iconImage
        });
        // Check for customicon
        if(props.iconImage){
            // Set icon image
            marker.setIcon(props.iconImage);
        }
        // Check content
        if(props.content){
            var infoWindow = new google.maps.InfoWindow({
            content:props.content
            });
            marker.addListener('click', function(){
            infoWindow.open(map, marker);
            });
        }
        }
    }
    window.initMap = initMap;
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAQXKQdhn1KRWpRAF9wWp3fkKzjehGm6M&callback=initMap">
    </script>
    
</body>

</html>