<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <title>Jeeb Admin Panel</title>
    <meta content="Responsive admin theme build on top of Bootstrap 4" name="description" />
    <meta content="Themesdesign" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" rel="icon">
    <link href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
    <link rel="shortcut icon" type="image/png" href="{{ asset('assets_v3/img/apple-touch-icon.png') }}" />
 
    <!--Morris Chart CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/morris/morris.css') }}">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/metismenu.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/plugins/dropify/dropify.min.css') }}" rel="stylesheet">

    <!--Timepicker CSS -->
    <link href="{{ asset('assets/css/timepicker.min.css') }}" rel="stylesheet">

    <link href="{{ asset('assets/plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />

    <link href="{{ asset('assets/css/daterangepicker.css') }}" rel="stylesheet">

    <!--My CSS -->
    <link href="{{ asset('assets/css/mystyle.css') }}" rel="stylesheet">

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/pages/form-advanced.js') }}"></script>

    <!--Timepicker Js -->
    <script src="{{ asset('assets/js/timepicker.min.js') }}"></script>

    <script type="text/javascript"
        {{-- src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATVDQvqkwGh2NBBl9j4t9ohG6pGxdahL0&libraries=places"></script> --}}
    <script src="{{ asset('assets/js/daterangepicker.js') }}"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <link href="{{ asset('assets/css/coloris.css') }}" rel="stylesheet">
    <script src="{{ asset('assets/js/coloris.js') }}"></script>

    <script src="{{ asset('assets/js/vanillaEmojiPicker.js') }}"></script>

    <link href="{{ asset('assets_v3/css/custom.css') }}" rel="stylesheet">
    <style>
        .errorPrint {
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

        input:checked+.slider {
            background-color: #30419b;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
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
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.validator.addMethod("valid_email", function(value, element) {
            return this.optional(element) ||
                /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i
                .test(value);
        }, "Please enter a valid email address.");

        $.validator.addMethod("invalid_text", function(value, element) {
            return this.optional(element) || /^[^-\s][a-zA-Z0-9.:/-_\s-]+$/.test(value);
        }, "Initial characters should not be space");

        $.validator.addMethod("space_not_allowed", function(value, element) {
            return this.optional(element) || /^[^-\s]+$/.test(value);
        }, "Space not allowed");

        /*Js Validation for numeric characters*/
        $(function() {
            $(".numericOnly").bind('keypress', function(e) {
                if (e.keyCode == '9' || e.keyCode == '16') {
                    return;
                }
                var code;
                if (e.keyCode)
                    code = e.keyCode;
                else if (e.which)
                    code = e.which;
                if (e.which == 46)
                    return false;
                if (code == 8 || code == 46)
                    return true;
                if (code < 48 || code > 57)
                    return false;
            });
            $(".numericOnly").bind("paste", function(e) {
                var pastedData = e.originalEvent.clipboardData.getData('text');
                if ($.isNumeric(pastedData)) {
                    return true;
                }
                return false;
            });
            $(".numericOnly").bind('mouseenter', function(e) {
                var val = $(this).val();
                if (val != '0') {
                    val = val.replace(/[^0-9]+/g, "")
                    $(this).val(val);
                }
            });
        });
    </script>
</head>

<body>
    <?php
    $stores = get_all_stores();
    ?>
    <!-- Begin page -->
    <div id="wrapper">
        <!-- Top Bar Start -->
        <div class="topbar">
            <!-- LOGO -->
            <div class="topbar-left">
                <a href="<?= url('admin/dashboard') ?>" class="logo">
                    <span class="logo-light">
                        <img src="{{ asset('assets_v3/img/logo-white-2.webp') }}" width="100px" alt="">
                    </span>
                    <span class="logo-sm">
                        <i class="mdi mdi-camera-control"></i>
                    </span>
                </a>
            </div>
            <nav class="navbar-custom">

                <ul class="navbar-right list-inline float-right mb-0">
                    <li class="dropdown notification-list list-inline-item d-none d-md-inline-block">
                        <a class="nav-link dropdown-toggle arrow-none waves-effect" data-toggle="dropdown"
                            href="#" role="button" aria-haspopup="false" aria-expanded="false">
                            <img src="assets/images/flags/us_flag.jpg" class="mr-2" height="12" alt="" />
                            Stores <span class="mdi mdi-chevron-down"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated language-switch">
                            <a class="dropdown-item" href="<?= url('/admin/stores') ?>">
                                <span>All Stores</span>
                            </a>
                            @if ($stores)
                                @foreach ($stores as $key => $value)
                                    <a class="dropdown-item" href="<?= url('/admin/store/' . base64_encode($value->id) ) ?>" >
                                        <span>&ensp;<i class="fa fa-store"></i>{{ $value->getCompany->name }}&ensp;{{ $value->name }}</span>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </li>
                    <li class="dropdown notification-list list-inline-item">
                        <div class="dropdown notification-list nav-pro-img">
                            <a class="dropdown-toggle nav-link arrow-none nav-user" data-toggle="dropdown"
                                href="#" role="button" aria-haspopup="false" aria-expanded="false">
                                <img src="{{ asset('assets/images/users/user-4.jpg') }}" alt="user"
                                    class="rounded-circle">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right profile-dropdown ">
                                <!-- item-->
                                <a class="dropdown-item" href="<?= url('admin/changepassword') ?>"><i
                                        class="mdi mdi-lock-open-outline"></i> Change Password</a>
                                <a class="dropdown-item" href="<?= url('admin/settings') ?>"><i
                                        class="mdi mdi-settings"></i> Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger"
                                    onclick="return confirm('Are you sure you want to logout ?')"
                                    href="<?= url('admin/logout') ?>"><i class="mdi mdi-power text-danger"></i>
                                    Logout</a>
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

        @include('admin.layouts.sidebar_items')

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

            <!--                <footer class="footer">
                                    © 2019 - 2020 Jeeb <span class="d-none d-sm-inline-block"> - Crafted with <i class="mdi mdi-heart text-danger"></i></span>.
                                </footer>-->

        </div>
        <!-- ============================================================== -->
        <!-- End Right content here -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- jQuery  -->

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/metismenu.min.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.slimscroll.js') }}"></script>
    <script src="{{ asset('assets/js/waves.min.js') }}"></script>

    <!-- Required datatable js -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>

    <!--Morris Chart-->
    <script src="{{ asset('assets/plugins/morris/morris.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/raphael/raphael.min.js') }}"></script>

    <script src="{{ asset('assets/plugins/dropify/dropify.min.js') }}"></script>

    <script src="{{ asset('assets/pages/dashboard.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>

    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/1rbnbutlmuwxll0leh6topn916x0aocyl0bc985beu4xy9jw/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        $('input.create_date').daterangepicker({
            singleDatePicker: true,
            showDropdowns: true,
            autoUpdateInput: false
        }).on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });

        $(document).ready(function() {
            var element = $(".changeUtcDateTime");
            $.each(element, function(i, e) {
                var time = $(e).attr("created_at");
                var my_time = getDateTime(parseInt(time));
                $(e).html(my_time);
            });
        });

        //js function to convert utc date time according to current time zone
        function getDateTime(timestamp, type = '', format = '', separator = '') {
            var date = new Date(timestamp * 1000);
            var month = date.getMonth();
            var year = date.getFullYear();
            var daten = date.getDate();
            var hours = date.getHours();
            if (hours < 10) {
                hours = "0" + hours;
            }

            var minutes = date.getMinutes();
            if (minutes < 10) {
                minutes = "0" + minutes;
            }
            var seconds = date.getSeconds();
            if (seconds < 10) {
                seconds = "0" + date.getSeconds();
            }
            var months = Array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

            var sep;
            if (separator == '/') {
                sep = '/';
            } else {
                sep = '-';
            }
            if (format = 'mmddyyyy') {
                var only_date = [daten, months[month], year].join(sep);
            } else {
                var only_date = [year, months[month], daten].join(sep);
            }
            var only_time = [hours, minutes].join(":");

            var full_date_time = only_date + ' ' + only_time;
            if (type == 'date') {
                return only_date;
            } else if (type == 'time') {
                return only_time;
            } else {
                return full_date_time;
            }
        }


        $('.characterOnly').keypress(function(e) {
            var regex = new RegExp(/^[a-zA-Z\s]+$/);
            var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
            if (regex.test(str)) {
                return true;
            } else {
                e.preventDefault();
                return false;
            }
        });
    </script>
    <script>
        $(document).ready(function() {
            // Basic
            $('.dropify').dropify();
            $("#datatable").DataTable({
                'aoColumnDefs': [{
                    'bSortable': false,
                    'aTargets': ['nosort']
                }]
            });
        });
    </script>
    <script>
        function cancelForm(type) {
            var ask = confirm('Are you sure to leave this page?');
            if (ask) {
                var url = window.location.href;
                var path = "";
                if (type == 1) {
                    path = url.slice(0, url.lastIndexOf('/'));
                } else {
                    path = url.slice(0, url.lastIndexOf('/'));
                    path = path.slice(0, path.lastIndexOf('/'));
                }
                window.location.href = path;
            } else {
                return false;
            }
        }

        $(document).ready(function() {
            $('.select2').select2();
        });

        $(document).ready(function() {
            $(".search_base_products_ajax").select2({
                placeholder: 'Base Products...',
                // width: '350px',
                allowClear: true,
                ajax: {
                    url: '<?= url('admin/base_products/get_base_products_ajax') ?>',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                }
            });
            $(".search_base_products_ajax_with_price").select2({
                placeholder: 'Base Products...',
                // width: '350px',
                allowClear: true,
                ajax: {
                    url: '<?= url('admin/base_products/get_base_products_ajax?with_price=1') ?>',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                }
            });
        });

        $(document).ready(function() {
            $(".new_tag").select2({
                placeholder: 'Add new tags...',
                // width: '350px',
                allowClear: true,
                ajax: {
                    url: '<?= url('admin/base_products/get_tags') ?>',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1
                        }
                    },
                    cache: true
                }
            })
            .on("select2:select", function (e) {
                console.log("select2:select");
            });
        });

        $(".form-prevent-multiple-submits").on('submit', function() {
            $('.button-prevent-multiple-submits').attr('disabled', true);
        });
    </script>

    <!-- Tinymce Text Editor -->
    <script>
        $(document).ready(function(){
            tinymce.init({
                selector: 'textarea#desc_en',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            });
            tinymce.init({
                selector: 'textarea#desc_ar',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                directionality : "rtl"
            });
            tinymce.init({
                selector: 'textarea#characteristics_en',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            });
            tinymce.init({
                selector: 'textarea#characteristics_ar',
                plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
                directionality : "rtl"
            });
        });
    </script>
</body>

</html>
