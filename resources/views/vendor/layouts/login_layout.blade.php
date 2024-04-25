<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>Jeeb Vendor Panel</title>
        <meta content="Responsive vendor theme build on top of Bootstrap 4" name="description" />
        <meta content="Themesdesign" name="author" />

        <link rel="shortcut icon" type="image/jpg" href="{{asset('home_assets/uploads/2019/10/jeeb_square_logo.png')}}" />

        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/metismenu.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css">

        <script src="{{asset('assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
    </head>
    <body>
        <!-- Begin page -->
        <div class="accountbg"></div>       
        <div class="wrapper-page">
            @yield('content')
        </div>
        <!-- END wrapper -->

        <!-- jQuery  -->

        <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/metismenu.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.slimscroll.js')}}"></script>
        <script src="{{asset('assets/js/waves.min.js')}}"></script>

        <!-- App js -->
        <script src="{{asset('assets/js/app.js')}}"></script>
        <script>
$("#vendorLoginForm").validate({
    rules: {
        email: {
            required: true,
            minlength: 5,
            maxlength: 50,
            valid_email: true
        },
        password: {
            required: true,
            minlength: 8,
            maxlength: 33,
        },
    }
});
$("#forgotPassForm").validate({
    rules: {
        email: {
            required: true,
            minlength: 5,
            maxlength: 50,
            valid_email: true
        },
    }
});
$("#resetPassForm").validate({
    rules: {
        email: {
            required: true,
            minlength: 5,
            maxlength: 50,
            valid_email: true
        },
        password: {
            required: true,
            minlength: 8,
            maxlength: 33,
        },
        cpassword: {
            required: true,
            minlength: 8,
            maxlength: 33,
            equalTo: "#exampleInputPassword1"
        }
    }
});
        </script>
    </body>

</html>