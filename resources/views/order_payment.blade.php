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

<body style="overflow:hidden; width: 100%; height: 100%; background: #ffffff; color: #000000; text-align: center;">
    <div style="display: -webkit-box;
    display: -webkit-flex;
    display: -moz-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-flex-align: center;
    -ms-flex-align: center;
    -webkit-align-items: center;
    align-items: center; 
    vertical-align: middle; 
    height: 100vh; 
    width: 100%; 
    align-items: center; 
    justify-content: center;">
        <img src="{{ asset('assets_v3/img/loading.gif') }}" style="max-width: 90%; height: auto;" alt="Loading"/>
        {{-- {{ $order_id }} --}}
    </div>
</body>

</html>