@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Admin Settings</h4>
            </div>
            <div class="col-sm-6">

            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.update_setting')}}">
                        @csrf                        
                        <div class="row">
                            <div class="col-lg-12">
                                <h3>Planned Model</h3>
                            </div>
                        </div>
                        <div class="row"> 
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Minimum purchase amount</label>
                                    <input type="text" name="minimum_purchase_amount" value="{{ isset($setting['minimum_purchase_amount']) ? $setting['minimum_purchase_amount'] : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Minimum purchase amount for free delivery</label>
                                    <input type="text" name="minimum_purchase_amount_for_free_delivery" value="{{ isset($setting['minimum_purchase_amount_for_free_delivery']) ? $setting['minimum_purchase_amount_for_free_delivery'] : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Delivery cost (QAR)</label>
                                    <input type="text" name="delivery_cost" value="{{ isset($setting['delivery_cost']) ? $setting['delivery_cost'] : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Order preparation time (In seconds)</label>
                                    <input type="text" name="packing_time" value="{{ isset($setting['packing_time']) ? $setting['packing_time'] : '' }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row"> 
                            <div class="col-lg-12">
                                <h3>Instant Model</h3>
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Minimum purchase amount</label>
                                    <input type="text" name="minimum_purchase_amount_instant" value="{{ isset($setting['minimum_purchase_amount_instant']) ? $setting['minimum_purchase_amount_instant'] : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Minimum purchase amount for free delivery</label>
                                    <input type="text" name="minimum_purchase_amount_instant_for_free_delivery" value="{{ isset($setting['minimum_purchase_amount_instant_for_free_delivery']) ? $setting['minimum_purchase_amount_instant_for_free_delivery'] : ''}}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Delivery cost (QAR)</label>
                                    <input type="text" name="delivery_cost_instant" value="{{ isset($setting['delivery_cost_instant']) ? $setting['delivery_cost_instant'] : '' }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <label>Order preparation time (In seconds)</label>
                                    <input type="text" name="packing_time_instant" value="{{ isset($setting['packing_time_instant']) ? $setting['packing_time_instant'] : '' }}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row"> 
                            <div class="col-lg-12">
                                <h3>Other Settings</h3>
                            </div>
                        </div>
                        <div class="row">   
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Warehouse Location</label>
                                    <input type="text" name="warehouse_location" value="{{$setting['warehouse_location']}}" id="pac-input" class="form-control">
                                    <input name="latitude" type="hidden" value="{{old('latitude')}}" id="latitude">
                                    <input name="longitude" type="hidden" value="{{old('longitude')}}"  id="longitude">
                                    <div id="map"></div>
                                    <p class="errorPrint" id="addressError"></p>
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Facebook</label>
                                    <input type="text" name="facebook" value="{{$setting['facebook']}}" class="form-control">
                                </div>
                            </div>                            
                        </div>
                        <div class="row">       
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Instagram</label>
                                    <input type="text" name="instagram" value="{{$setting['instagram']}}" class="form-control">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Youtube</label>
                                    <input type="text" name="youtube" value="{{$setting['youtube']}}" class="form-control">
                                </div>
                            </div>

                        </div>
                        <div class="row">  
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Linkedin</label>
                                    <input type="text" name="linkedin" value="{{$setting['linkedin']}}" class="form-control">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Snapchat</label>
                                    <input type="text" name="snapchat" value="{{$setting['snapchat']}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">  
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Referral Bonus (QAR)</label>
                                    <input type="text" name="referral_bonus" value="{{$setting['referral_bonus']}}" class="form-control numericOnly">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Max Order Amount for Referral (QAR)</label>
                                    <input type="text" name="max_order_amount_for_referral" value="{{$setting['max_order_amount_for_referral']}}" class="form-control numericOnly">
                                </div>  
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Under Maintenance Settings</h4>
            </div>
            <div class="col-sm-6">

            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.update_maintenance_setting')}}">
                        @csrf                        
                        <div class="row">                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Yes/No</label><br>
                                    <input type="radio" name="under_maintenance" value="true" <?= $setting['under_maintenance']=='true'?'checked':''; ?>> Yes
                                    <input type="radio" name="under_maintenance" value="false" <?= $setting['under_maintenance']=='false'?'checked':''; ?>> No
                                </div>
                            </div>                            
                        </div>                        
                        <div class="row">  
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Maintenance Title</label>
                                    <input type="text" name="maintenance_title" value="{{$setting['maintenance_title']}}" class="form-control">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Maintenance Desc</label>
                                    <textarea name="maintenance_desc" class="form-control">{{$setting['maintenance_desc']}}</textarea>
                                </div>  
                            </div>
                        </div>
                        <div class="row">                            
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Update
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div> --}}
</div>
<script type="text/javascript">
    function initMap() {
        var centerCoordinates = new google.maps.LatLng(28.6162944, 77.1457024);
        var map = new google.maps.Map(document.getElementById('map'), {
            center: centerCoordinates,
            zoom: 7
        });
        var card = document.getElementById('pac-card');
        var input = document.getElementById('pac-input');
        var infowindowContent = document.getElementById('infowindow-content');
        map.controls[google.maps.ControlPosition.TOP_RIGHT].push(card);
        var autocomplete = new google.maps.places.Autocomplete(input);
        var infowindow = new google.maps.InfoWindow();
        //SHOW ADDRESS ON MARKER
        var geocoder = new google.maps.Geocoder();
        infowindow.setContent(infowindowContent);


        google.maps.event.addDomListener(input, 'keydown', function (e) {
            if (e.keyCode == 13) {
                e.preventDefault();
            }
        });
        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            document.getElementById('latitude').value = place.geometry.location.lat();
            document.getElementById('longitude').value = place.geometry.location.lng();
            if (!place.geometry) {
                document.getElementById("addressError").style.display = 'inline-block';
                document.getElementById("addressError").innerHTML = "Cannot Locate '" + input.value + "' on map";
                return;
            }
            if (place.address_components) {
                document.getElementById('city').value = (place.address_components[(place.address_components.length - 4)]['long_name']);
                document.getElementById('state').value = (place.address_components[(place.address_components.length - 3)]['long_name']);
                document.getElementById('country').value = (place.address_components[(place.address_components.length - 2)]['long_name']);
                document.getElementById('pin').value = (place.address_components[(place.address_components.length - 1)]['long_name']);
            } else {
                alert('unable to fetch your location');
                document.getElementById('pac-input').value = "";
            }
            map.fitBounds(place.geometry.viewport);
            marker.setPosition(place.geometry.location);
            marker.setVisible(true);
            marker.setMap(map);
            infowindowContent.children['place-icon'].src = place.icon;
            infowindowContent.children['place-name'].textContent = place.name;
            infowindowContent.children['place-address'].textContent = input.value;
            console.log(input.value + '---' + place.name + '---' + place.icon);
            infowindow.open(map, marker);
        });
    }
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATVDQvqkwGh2NBBl9j4t9ohG6pGxdahL0&libraries=places&callback=initMap"
async defer></script>
<script>
    $(document).ready(function () {
        $('#addForm').validate({
            rules: {
                minimum_purchase_amount: {
                    required: true
                },
                warehouse_location: {
                    required: true
                },
//                facebook: {
//                    required: true
//                },
//                instagram: {
//                    required: true
//                },
//                youtube: {
//                    required: true
//                },
//                linkedin: {
//                    required: true
//                },
//                snapchat: {
//                    required: true
//                },
//                twitter: {
//                    required: true
//                }
            }
        });

    });
</script>
@endsection