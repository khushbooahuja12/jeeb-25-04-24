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

        <link rel="shortcut icon" type="image/jpg" href="{{asset('home_assets/uploads/2019/10/jeeb_square_logo.png')}}" />

        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="{{asset('assets/plugins/morris/morris.css')}}">

        <link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/metismenu.min.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/icons.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/css/style.css')}}" rel="stylesheet" type="text/css">
        <link href="{{asset('assets/plugins/dropify/dropify.min.css')}}" rel="stylesheet" >

        <!--Timepicker CSS -->
        <link href="{{asset('assets/css/timepicker.min.css')}}" rel="stylesheet" >

        <link href="{{asset('assets/plugins/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />

        <link href="{{asset('assets/css/daterangepicker.css')}}" rel="stylesheet">

        <script src="{{asset('assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('assets/js/jquery.validate.min.js')}}"></script>
        <script src="{{asset('assets/pages/form-advanced.js')}}"></script>        

        <!--Timepicker Js -->
        <script src="{{asset('assets/js/timepicker.min.js')}}"></script>                

        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATVDQvqkwGh2NBBl9j4t9ohG6pGxdahL0&libraries=places"></script>
        <script src="{{asset('assets/js/daterangepicker.js')}}"></script>

        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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

/*Js Validation for numeric characters*/
$(function () {
    $(".numericOnly").bind('keypress', function (e) {
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
    $(".numericOnly").bind("paste", function (e) {
        var pastedData = e.originalEvent.clipboardData.getData('text');
        if ($.isNumeric(pastedData)) {
            return true;
        }
        return false;
    });
    $(".numericOnly").bind('mouseenter', function (e) {
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
        <div id="wrapper">
            <div class="content-page" style="margin-left:0px">
                <div class="content" style="margin-top:0px">
                    <div class="container-fluid">
                        <div class="page-title-box">
                            <div class="row align-items-center">
                                <div class="col-sm-6">
                                    <h4 class="page-title">All Products</h4>
                                </div>
                                <div class="col-sm-6">

                                </div>
                            </div>
                        </div>
                        @include('partials.errors')
                        @include('partials.success')
                        @csrf
                        <div class="row">
                            @if($products)
                            @foreach($products as $key=>$value)
                            <div class="col-md-6 col-xl-3">
                                <div class="card m-b-30">
                                    <img class="card-img-top img-fluid" 
                                         src="{{$value['product_image']}}"
                                         style="height: 200px!important;width:100%!important"
                                         />
                                    <div class="card-body" style="padding:0.75rem">
                                        <h4 class="card-title font-12 mt-0">
                                            {{$value['product_name']}}
                                        </h4>
                                        <p class="card-text">
                                            {{$value['quantity']}}&ensp;{{'QAR '.$value['distributor_price']}}
                                        </p>
                                        <div>
                                            <b>Category:</b> {{$value['category_name']}}<br>
                                            <b>Sub Category:</b> {{$value['sub_category_name']}}<br>
                                            <b>Brand:</b> {{$value['brand_name']}}
                                        </div>                       
                                    </div>                   
                                </div>
                            </div>
                            @endforeach
                            @endif
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
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

        <script src="{{asset('assets/plugins/dropify/dropify.min.js')}}"></script>

        <script src="{{asset('assets/pages/dashboard.init.js')}}"></script>

        <!-- App js -->
        <script src="{{asset('assets/js/app.js')}}"></script>
        <script src="{{asset('assets/js/moment.min.js')}}"></script>      
        <script>
$('input.create_date').daterangepicker({
    singleDatePicker: true,
    showDropdowns: true,
    autoUpdateInput: false
}).on('apply.daterangepicker', function (ev, picker) {
    $(this).val(picker.startDate.format('MM/DD/YYYY'));
});

$(document).ready(function () {
    var element = $(".changeUtcDateTime");
    $.each(element, function (i, e) {
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

        </script>

    </body>

</html>