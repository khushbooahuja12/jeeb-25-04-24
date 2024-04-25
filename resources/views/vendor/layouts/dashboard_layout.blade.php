<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
        <title>Saudia Hypermarket</title>
        <meta content="Responsive vendor theme build on top of Bootstrap 4" name="description" />
        <meta content="Themesdesign" name="author" />
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel="shortcut icon" type="image/jpg" href="{{asset('home_assets/uploads/2019/10/jeeb_square_logo.png')}}" />

        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="{{asset('assets/plugins/morris/morris.css')}}">

        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/metismenu.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css">
        <link href="{{ asset('assets/plugins/dropify/dropify.min.css') }}" rel="stylesheet">
        <!--Developer added css-->
        <link href="{{asset('assets/css/mystyle.css')}}" rel="stylesheet" type="text/css">

        <link href="{{asset('assets/css/daterangepicker.css')}}" rel="stylesheet">

        <style>
            .errorPrint{
                color: red;
            }
            /* The switch - the box around the slider */
            .switch {
                position: relative;
                display: inline-block;
                width: 46px;
                height: 20px;
            }

            /* Hide default HTML checkbox */
            .switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            /* The slider */
            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                -webkit-transition: .4s;
                transition: .4s;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 12px;
                width: 12px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                -webkit-transition: .4s;
                transition: .4s;
            }

            input:checked + .slider {
                background-color: #30419b;
            }

            input:focus + .slider {
                box-shadow: 0 0 1px #2196F3;
            }

            input:checked + .slider:before {
                -webkit-transform: translateX(26px);
                -ms-transform: translateX(26px);
                transform: translateX(26px);
            }

            /* Rounded sliders */
            .slider.round {
                border-radius: 34px;
            }

            .slider.round:before {
                border-radius: 50%;
            }
        </style>

        <script src="{{asset('assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>

        <script src="{{asset('assets/js/daterangepicker.js')}}"></script>       
        <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
        <script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$.validator.addMethod("valid_email", function (value, element) {
    return this.optional(element) || /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i.test(value);
}, "Please enter a valid email address.");

$.validator.addMethod("invalid_text", function (value, element) {
    return this.optional(element) || /^[^-\s][a-zA-Z0-9.:/-_\s-]+$/.test(value);
}, "Initial characters should not be space");

$.validator.addMethod("space_not_allowed", function (value, element) {
    return this.optional(element) || /^[^-\s]+$/.test(value);
}, "Space not allowed");
        </script>        
        <script>
// Enable pusher logging - don't include this in production
            Pusher.logToConsole = true;

            var pusher = new Pusher('55299938e06d48d0468a', {
                cluster: 'ap2'
            });

            var channel = pusher.subscribe('my-channel');

            channel.bind('my-event', function (data) {

                var audio = new Audio("<?= asset('/H42VWCD-notification.ogg'); ?>");
                // audio.play();
                audio.addEventListener('ended', function () {
                    this.currentTime = 0;
                    this.play();
                }, false);
                audio.play();

                // alert(data.message);
                if (confirm(data.message)) {
                    audio.addEventListener('ended', function () {
                        audio.currentTime = 0;
                        audio.pause();
                    }, false);
                    audio.pause();

                    window.location.href = '<?= url('/vendor/orders'); ?>';
                } else {
                    setTimeout(() => {
                        audio.addEventListener('ended', function () {
                            audio.currentTime = 0;
                            audio.pause();
                        }, false);
                        audio.pause();
                    }, 15000);
                }
            });

        </script>
    </head>
    <body>
        <?php $vendor = get_vendor(Auth::guard('vendor')->user()->id); ?>
        <!-- Begin page -->
        <div id="wrapper">

            <!-- Top Bar Start -->
            <div class="topbar">

                <!-- LOGO -->
                <div class="topbar-left">
                    <a href="<?= url('vendor/dashboard'); ?>" class="logo">
                        <span class="logo-light">
                            Saudia Hypermarket
                        </span>
                        <span class="logo-sm">
                            <i class="mdi mdi-camera-control"></i>
                        </span>
                    </a>
                </div>
                <nav class="navbar-custom">
                    <ul class="navbar-right list-inline float-right mb-0">
                        <span>Hi 
                            @if(isset(Auth::guard('vendor')->user()->name)) 
                            {{Auth::guard('vendor')->user()->name}}
                            @else
                            Guest
                            @endif
                        </span>
                        <li class="dropdown notification-list list-inline-item">
                            <div class="dropdown notification-list nav-pro-img">
                                <a class="dropdown-toggle nav-link arrow-none nav-user" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                    <img src="{{asset('assets/images/dummy_user.png')}}" alt="user" class="rounded-circle">
                                </a>
                                <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                                    {{-- <a class="dropdown-item" href="<?= //url('vendor/changepassword'); ?>"><i class="mdi mdi-lock-open-outline"></i> Change Password</a> --}}
                                    {{-- <div class="dropdown-divider"></div> --}}
                                    <a class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to logout ?')" href="<?= url('vendor/logout'); ?>"><i class="mdi mdi-power text-danger"></i> Logout</a>
                                </div>
                            </div>
                        </li>
                    </ul>
                    <ul class="list-inline menu-left mb-0">
                        <li class="float-left">
                            <button class="button-menu-mobile open-left waves-effect">
                                <i class="mdi mdi-menu"></i>
                            </button>
                        </li>

                    </ul>
                </nav>
            </div>
            <!-- Top Bar End -->

            @include('vendor.layouts.sidebar_items')

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="content-page">
                <!-- Start content -->
                <div class="content">
                    @yield('content')
                    <!-- container-fluid -->

                </div>
                <!-- content -->

                <footer class="footer">
                    © 2019 - 2020 Jeeb <span class="d-none d-sm-inline-block"> - Crafted with <i class="mdi mdi-heart text-danger"></i></span>.
                </footer>

            </div>
            <!-- ============================================================== -->
            <!-- End Right content here -->
            <!-- ============================================================== -->

        </div>        
        <!--        <div class="modal fade" id="passAlertModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content" style='width:100%;'>
                            <div class="modal-header">
                                <h5 class="modal-title"><span style="color:red">Alert !</span> Please change your password first</h5>                        
                            </div>
                            <div class="modal-body">
                                <div class="" style="margin-left:140px"> 
                                    <a class="btn btn-success" href='<?= url('vendor/changepassword'); ?>'>Change Now</a>
                                    <a class="btn btn-danger" href='javascript:void(0)' onclick="UpdateLoginStatus(this)">Skip</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->

        <script src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{asset('assets/js/metismenu.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.slimscroll.js')}}"></script>
        <script src="{{asset('assets/js/waves.min.js')}}"></script>

        <!-- Required datatable js -->
        <script src="{{asset('assets/plugins/datatables/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('assets/plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>

        <!--Morris Chart-->
        <script src="{{asset('assets/plugins/morris/morris.min.js')}}"></script>
        <script src="{{asset('assets/plugins/raphael/raphael.min.js')}}"></script>

        <script src="{{asset('assets/pages/dashboard.init.js')}}"></script>

        <!-- App js -->
        <script src="{{asset('assets/js/app.js')}}"></script>        
        <script>
                                        $('input.create_date').daterangepicker({
                                            singleDatePicker: true,
                                            showDropdowns: true,
                                            autoUpdateInput: false
                                        }).on('apply.daterangepicker', function (ev, picker) {
                                            $(this).val(picker.startDate.format('MM/DD/YYYY'));
                                        });
                                        //ajax for checking first time logged in user
                                        $.ajax({
                                            url: "<?= url('vendor/check_first_time_login'); ?>",
                                            type: 'post',
                                            dataType: 'json',
                                            cache: false
                                        }).done(function (response) {
                                            if (response.error_code == 200 && response.vendor.login_status == 0) {
                                                $('#passAlertModal').modal('show');
                                            } else {
                                                $('#passAlertModal').modal('hide');
                                            }
                                        });

                                        function UpdateLoginStatus(obj) {
                                            $.ajax({
                                                url: "<?= url('vendor/update_login_status'); ?>",
                                                type: 'post',
                                                dataType: 'json',
                                                cache: false
                                            }).done(function (response) {
                                                if (response.error_code == 200) {
                                                    $('#passAlertModal').modal('hide');
                                                } else {
                                                    $('#passAlertModal').modal('show');
                                                }
                                            });
                                        }

                                        $('.characterOnly').keypress(function (e) {
                                            var regex = new RegExp(/^[a-zA-Z\s]+$/);
                                            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
                                            if (regex.test(str)) {
                                                return true;
                                            } else {
                                                e.preventDefault();
                                                return false;
                                            }
                                        });


                                        $(document).ready(function () {
                                            // Basic
                                            $("#datatable").DataTable();
                                        });


        </script>
    </body>

</html>