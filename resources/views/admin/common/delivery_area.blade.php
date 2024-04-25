@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .pac-container {
        z-index: 10000 !important;
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Delivery Area</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Delivery Area</li>                    
                </ol>
            </div>
        </div>
    </div>    
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="card m-b-30">
                <div class="add_new_btn" style="margin: 10px 20px 0px 0px;">                   
                    <a href="javascript:void(0)" class="add_edit_modal" type="add"><button type="button" class="btn btn-primary float-right"><i class="fa fa-plus"></i> Add New</button></a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Radius</th>
                                <th>Blocked Timeslots</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody class="alldata">
                            @if($delivery_area)
                            @foreach($delivery_area as $key=>$row)
                            <tr class="deliveryarea<?= $row->id; ?>">
                                <td class="location" style="width: 250px;">{{$row->location}}</td>
                                <td class="latitude">{{$row->latitude}}</td>
                                <td class="longitude">{{$row->longitude}}</td>
                                <td class="radius">{{$row->radius." km"}}</td>
                                <td class="blocked_timeslots">{{$row->blocked_timeslots}}</td>
                                <td>                                    
                                    <a title="edit" href="javascript:void(0)" class="add_edit_modal" type="edit" id="{{$row->id}}" location="{{$row->location}}" latitude="{{$row->latitude}}" longitude="{{$row->longitude}}" radius="{{$row->radius}}"><i class="icon-pencil"></i></a>&ensp;
                                    <a title="Delete" href="<?= url('admin/delivery-area/destroy/' . base64url_encode($row->id)); ?>"onclick="return confirm('Are you sure you want to delete this ?')"><i class="icon-trash-bin"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="deliveryArea" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Delivery Area</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="error_alert"></div>
                <form method="post" id="editDeliveryForm">
                    @csrf
                    <div class="row">                            
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="location" id="searchTextField" value="" id="pac-input" class="form-control">
                            </div>
                        </div>                            
                        <div class="col-md-6">
                            <div class="form-group">
                                <input name="latitude" value="" type="text" id="latitude" class="form-control" placeholder="latitude">
                            </div>
                        </div>                            
                        <div class="col-md-6">
                            <div class="form-group">
                                <input name="longitude" value="" type="text" id="longitude" class="form-control" placeholder="longitude">
                            </div>
                        </div>                            
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Radius</label>
                                <input type="text" name="radius" value="" class="form-control numericOnly" maxlength="4">
                            </div>
                        </div>                            
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Block Timeslots</label>
                                <input type="text" name="blocked_timeslots" value="" class="form-control numericOnly" maxlength="4">
                            </div>
                        </div>                            
                    </div>
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <div>
                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAQXKQdhn1KRWpRAF9wWp3fkKzjehGm6M&callback=initialize&libraries=places" type="text/javascript"></script>
<script>
    $(".add_edit_modal").on('click', function () {
        if ($(this).attr('type') == 'add') {
            $("#deliveryArea").modal('show');

            $("#deliveryArea").find('.modal-title').text('Add delivery area');
            $("#deliveryArea").find('.waves-effect').text('Submit');
        } else {
            $("#deliveryArea").modal('show');

            $("input[name=id]").val($(this).attr('id'));
            $("input[name=location]").val($(this).attr('location'));
            $("input[name=radius]").val($(this).attr('radius'));
            $("input[name=latitude]").val($(this).attr('latitude'));
            $("input[name=longitude]").val($(this).attr('longitude'));
            $("input[name=blocked_timeslots]").val($(this).attr('blocked_timeslots'));
        }
    });


    $('#editDeliveryForm').validate({
        rules: {
            location: {
                required: true
            },
            radius: {
                required: true
            }
        }
    });

    function initialize() {
        var input = document.getElementById('searchTextField');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            $("#latitude").val(place.geometry.location.lat());
            $("#longitude").val(place.geometry.location.lng());

        });
    }
    // google.maps.event.addDomListener(window, 'load', initialize);

    $(document.body).on('submit', '#editDeliveryForm', function (e) {
        e.preventDefault();

        $.ajax({
            url: '<?= url('admin/update_delivery_area'); ?>',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false
        }).done(function (response) {
            $("#deliveryArea").modal('hide');

            if (response.status_code == 200) {
                var success_str = '<div class="alert alert-success fade show alert-dismissible">'
                        + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                        + '<strong>' + response.message + '</strong>.'
                        + '</div>';
                $(".success_alert").html(success_str);

                // location.reload();

                if (response.result.type == 'add') {
                    var area_str = '<tr class="deliveryarea' + response.result.id + '">'
                            + '<td class="location">' + response.result.location + '</td>'
                            + '<td class="latitude">' + response.result.latitude + '</td>'
                            + '<td class="longitude">' + response.result.longitude + '</td>'
                            + '<td class="radius">' + response.result.radius + ' km' + '</td>'
                            + '<td class="blocked_timeslots">' + response.result.blocked_timeslots + '</td>'
                            + '<td>'
                            + '<a title="edit" href="javascript:void(0)" class="add_edit_modal" type="edit" id="' + response.result.id + '" location="' + response.result.location + '" latitude="' + response.result.latitude + '" longitude="' + response.result.longitude + '" radius="' + response.result.radius + '" blocked_timeslots="' + response.result.blocked_timeslots + '"><i class="icon-pencil"></i></a>'
                            + '<a title="Delete" href="javascript:void(0)"><i class="icon-trash-bin"></i></a>'
                            + '</td>'
                            + '</tr>';
                    $(".alldata").prepend(area_str);
                } else {
                    $(".deliveryarea" + response.result.id).find('.location').text(response.result.location);
                    $(".deliveryarea" + response.result.id).find('.latitude').text(response.result.latitude);
                    $(".deliveryarea" + response.result.id).find('.longitude').text(response.result.longitude);
                    $(".deliveryarea" + response.result.id).find('.radius').text(response.result.radius + ' km');
                    $(".deliveryarea" + response.result.id).find('.blocked_timeslots').text(response.result.blocked_timeslots);
                }

            } else {
                var error_str = '<div class="alert alert-danger fade show alert-dismissible">'
                        + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                        + '<strong>' + response.message + '</strong>.'
                        + '</div>';
                $(".error_alert").html(error_str);
            }
        });
    })
</script>
@endsection