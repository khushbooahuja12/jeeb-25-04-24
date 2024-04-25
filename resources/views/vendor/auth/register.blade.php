@extends('vendor.layouts.login_layout')
@section('content')
    <style>
        .wrapper-page {
            width: 600px;
        }
    </style>
    <div class="card card-pages shadow-none">
        <div class="card-body">
            <div class="text-center m-t-0 m-b-15">
                <h2><a href="<?= url('vendor') ?>" class="logo logo-vendor">JEEB</a></h2>
            </div>
            <h5 class="font-18 text-center">Register to setup your business with Jeeb.</h5>
            @include('partials.errors')
            @include('partials.success')
            <form method="post" autocomplete="off" id="regForm" action="<?= url('vendor/register_post') ?>"
                class="form-horizontal m-t-30">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Vendor Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                data-title="Vendor Name" class="form-control characterOnly regInputs"
                                placeholder="Vendor Name">
                            <p class="errorPrint" id="nameError"></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="email" autocomplete="new-password"
                                data-title="Email" value="{{ old('email') }}" class="form-control regInputs"
                                placeholder="Email">
                            <p class="errorPrint" id="emailError"></p>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Store Name</label>
                            {{-- <input type="text" name="company_name" id="company_name" value="{{old('company_name')}}" data-title="Company Name" class="form-control regInputs" placeholder="Company Name"> --}}
                            <select name="store_name" id="store_name" style="width: 100%;">
                                @if(!empty($company))
                                @foreach ($company as $value)
                                    <optgroup label="{{ $value->name }}">
                                        @foreach ($stores as $val)
                                            @if ($val->company_id == $value->id)
                                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                                            @endif
                                        @endforeach
                                    </optgroup>
                                @endforeach
                                @endif
                            </select>
                            <p class="errorPrint" id="company_nameError"></p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" id="pac-input" data-title="Address"
                                value="{{ old('address') }}" class="form-control regInputs" placeholder="Address">
                            <input name="latitude" type="hidden" value="{{ old('latitude') }}" id="latitude">
                            <input name="longitude" type="hidden" value="{{ old('longitude') }}" id="longitude">
                            <div id="map"></div>
                            <p class="errorPrint" id="addressError"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>City</label>
                            <input type="text" name="city" id="city" data-title="City"value="{{ old('city') }}"
                                class="form-control characterOnly regInputs" placeholder="City">
                            <p class="errorPrint" id="cityError"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>State</label>
                            <input type="text" name="state" id="state" data-title="State"
                                value="{{ old('state') }}" class="form-control characterOnly regInputs"
                                placeholder="State">
                            <p class="errorPrint" id="stateError"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Country</label>
                            <input type="text" name="country" id="country" data-title="Country"
                                value="{{ old('country') }}" class="form-control characterOnly regInputs"
                                placeholder="Country">
                            <p class="errorPrint" id="countryError"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Pin</label>
                            <input type="text" name="pin" id="pin" data-title="Pin"
                                value="{{ old('pin') }}" class="form-control numberOnly regInputs" placeholder="Pin">
                            <p class="errorPrint" id="pinError"></p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mobile</label>
                            <input type="text" name="mobile" id="mobile" data-title="Mobile"
                                value="{{ old('mobile') }}" class="form-control numberOnly regInputs"
                                placeholder="Mobile">
                            <p class="errorPrint" id="mobileError"></p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" id="password" autocomplete="new-password"
                                data-title="Password" class="form-control regInputs" placeholder="Password">
                            <p class="errorPrint" id="passwordError"></p>
                        </div>
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-12">
                        <button class="btn btn-primary btn-block btn-lg waves-effect waves-light"
                            type="submit">Register</button>
                    </div>
                </div>
                <div class="form-group row m-t-30 m-b-0">
                    <div class="col-sm-7">
                        <a href="<?= url('vendor') ?>" class="text-muted"> Existing user? Login </a>
                    </div>
                </div>
            </form>
        </div>
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
            var marker = new google.maps.Marker({
                position: centerCoordinates,
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP,
            });

            google.maps.event.addListener(map, 'click', function(e) {
                updateMarkerPosition(marker, e);
            });

            marker.addListener('click', toggleBounce);
            marker.addListener('dragend', function() {
                var currentlatlng = marker.position;
                var lat = marker.position.lat();
                var lng = marker.position.lng();
                geocoder.geocode({
                    'location': currentlatlng
                }, function(results, status) {
                    if (status === 'OK') {
                        if (results[1]) {
                            $('#pac-input').val(results[1].formatted_address);
                            marker.setPosition(currentlatlng);
                            map.setCenter(marker.getPosition());
                            infowindow.setContent(results[1].formatted_address);
                            infowindow.open(map, marker);
                        } else {
                            window.alert('No results found');
                        }
                    } else {
                        window.alert('Geocoder failed due to: ' + status);
                    }
                });
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
                console.log("latlng is " + JSON.stringify(currentlatlng));
                map.setCenter(marker.getPosition());
            });
            google.maps.event.addDomListener(input, 'keydown', function(e) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                }
            });
            autocomplete.addListener('place_changed', function() {
                var place = autocomplete.getPlace();
                document.getElementById('latitude').value = place.geometry.location.lat();
                document.getElementById('longitude').value = place.geometry.location.lng();
                if (!place.geometry) {
                    document.getElementById("addressError").style.display = 'inline-block';
                    document.getElementById("addressError").innerHTML = "Cannot Locate '" + input.value +
                    "' on map";
                    return;
                }
                if (place.address_components) {
                    document.getElementById('city').value = (place.address_components[(place.address_components
                        .length - 4)]['long_name']);
                    document.getElementById('state').value = (place.address_components[(place.address_components
                        .length - 3)]['long_name']);
                    document.getElementById('country').value = (place.address_components[(place.address_components
                        .length - 2)]['long_name']);
                    document.getElementById('pin').value = (place.address_components[(place.address_components
                        .length - 1)]['long_name']);
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

        function updateMarkerPosition(marker, e) {
            marker.setPosition(e.latLng);
        }

        function toggleBounce() {
            if (marker.getAnimation() !== null) {
                marker.setAnimation(null);
            } else {
                marker.setAnimation(google.maps.Animation.BOUNCE);
            }
        }
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyATVDQvqkwGh2NBBl9j4t9ohG6pGxdahL0&libraries=places&callback=initMap"
        async defer></script>

    <script>
        $(document).ready(function() {

            $('#regForm').validate({ // initialize the plugin
                rules: {
                    name: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    mobile: {
                        required: true,
                        rangelength: [6, 15]
                    },
                    company_name: {
                        required: true
                    },
                    address: {
                        required: true
                    },
                    lat: {
                        required: true
                    },
                    city: {
                        required: true
                    },
                    state: {
                        required: true
                    },
                    country: {
                        required: true
                    },
                    pin: {
                        required: true,
                        digits: true
                    },
                    password: {
                        required: true,
                        minlength: 8
                    }
                }
            });

        });
    </script>
@endsection
