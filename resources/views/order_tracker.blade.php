<html>

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="robots" content="noindex,nofollow">
    <title>Customer Order Tracking</title>
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
    </style>
</head>

<body style="overflow:hidden; width: 100%; height: 100%;">
    <div style="background: #000000; color: #ffffff; text-align: center; position: fixed; top: 0px; left: 0px; right: 0px; bottom: auto; width: 100%; height: auto; overflow:hidden; z-index:999999; padding: 0px;"><h4 style="margin: 0px; padding: 15px 10px;">{{$title}}</h4></div>
    <div style="position: fixed; overflow:hidden; width: 100%; height: 100%;">
    <div id="map" style="position:fixed; top:-20%; left:-20%; bottom:0; right:0; width:140%; height:140%; border:none; margin:0; padding:0; overflow:hidden; z-index:99999;"></div>
    </div>
    {{-- <table>
        <tbody>
            <tr>
                <th>Driver</th>
                <td>
                    @if ($driver)        
                    <table class="table table-bordered dt-responsive nowrap">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <td><a
                                        href="<?= url('admin/drivers/show/' . base64url_encode($order->getOrderDriver->getDriver->id)) ?>">{{ $order->getOrderDriver->getDriver->name ?? 'N/A' }}</a>
                                </td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $order->getOrderDriver->getDriver->email ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Mobile</th>
                                <td>{{ $order->getOrderDriver->getDriver->mobile ? '+' . $order->getOrderDriver->getDriver->country_code . '-' . $order->getOrderDriver->getDriver->mobile : 'N/A' }}
                                </td>
                            </tr>
                            <tr>
                                <th>Image</th>
                                <td><img src="{{ !empty($order->getOrderDriver->getDriver->getDriverImage) ? asset('images/common_images') . '/' . $order->getOrderDriver->getDriver->getDriverImage->file_name : asset('assets/images/dummy_user.png') }}"
                                        width="75" height="50"></td>
                            </tr>
                            <tr>
                                <th>Latitude</th>
                                <td>{{ $driver_location ? $driver_location->latitude : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Longitude</th>
                                <td>{{ $driver_location ? $driver_location->longitude : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Last Tracked At</th>
                                <td>{{ $driver_location ? $driver_location->updated_at : 'N/A' }}</td>
                            </tr>
                        </thead>
                    </table>
                @else
                    <p>Driver not assigned yet</p>
                @endif
                </td>
            </tr>
        </tbody>
    </table> --}}

    
<script>
    // Initialize and add the map
    function initMap(){
        // Map options
        var options = {
            zoom:15,
            center:{lat:{{$center_location['lat']}},lng:{{$center_location['lng']}}}
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
        @if (isset($order) && $order)
            {
                coords:{lat:{{$order->latitude}},lng:{{$order->longitude}}},
                content:"<h4>{{$title}}</h4>",
                iconImage:'https://jeeb.tech/images/icons/delivery-location-icon.gif',
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