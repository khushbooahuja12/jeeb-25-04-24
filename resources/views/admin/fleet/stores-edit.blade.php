@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Store</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/stores') ?>">Stores</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="regForm" enctype="multipart/form-data"
                            action="{{ route('admin.fleet.stores.update', [base64url_encode($store->id)]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Select Company</label>
                                        <select class="form-control select2" name="company_id" disabled>
                                            @if ($companies->count())
                                                @foreach ($companies as $key => $value)
                                                    <option value="{{ $value->id }}" {{ $store->company_id == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <label id="company_id-error" class="error" for="company_id"></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Store Name</label>
                                        <input type="text" name="name" value="{{ old('name', $store->email) }}"
                                            id="name" data-title="Store Name" class="form-control regInputs"
                                            placeholder="Name" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" name="email" data-title="Email"
                                            value="{{ old('email', $store->email) }}" class="form-control regInputs"
                                            placeholder="Email">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mobile</label>
                                        <input type="text" name="mobile" value="{{ old('mobile', $store->mobile) }}"
                                            id="mobile" data-title="Mobile" class="form-control numberOnly regInputs"
                                            placeholder="Mobile">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" name="address" value="{{ old('address', $store->address) }}"
                                            id="pac-input" data-title="Address" class="form-control regInputs"
                                            placeholder="Address">
                                        <input name="latitude" type="hidden"
                                            value="{{ old('latitude', $store->latitude) }}" id="latitude">
                                        <input name="longitude" type="hidden"
                                            value="{{ old('longitude', $store->longitude) }}" id="longitude">
                                        <div id="map"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>City</label>
                                        <input type="text" name="city" value="{{ old('city', $store->city) }}"
                                            id="city" data-title="City" class="form-control characterOnly regInputs"
                                            placeholder="City">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State</label>
                                        <input type="text" name="state" value="{{ old('state', $store->state) }}"
                                            id="state" data-title="State" class="form-control characterOnly regInputs"
                                            placeholder="State">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Country</label>
                                        <input type="text" name="country" value="{{ old('country', $store->country) }}"
                                            id="country" data-title="Country"
                                            class="form-control characterOnly regInputs"placeholder="Country">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Pin</label>
                                        <input type="text" name="pin" id="pin" data-title="Pin"
                                            value="{{ old('pin', $store->pin) }}" class="form-control numberOnly regInputs"
                                            placeholder="Pin">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Latitude</label>
                                        <input type="text" name="latitude" id="latitude" data-title="Latitude" value="{{old('latitude',$store->latitude)}}" class="form-control numberOnly regInputs" placeholder="Latitude">
                                        <p class="errorPrint" id="pinError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Longitude</label>
                                        <input type="text" name="longitude" id="longitude" data-title="Longitude" value="{{old('longitude',$store->longitude)}}" class="form-control numberOnly regInputs" placeholder="Longitude">
                                        <p class="errorPrint" id="pinError"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" name="password" id="password" data-title="Password" class="form-control regInputs" placeholder="Password">
                                        <p class="errorPrint" id="pinError"></p>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Api</label>
                                        <textarea name="api_url" id="api_url" data-title="Password" class="form-control regInputs" placeholder="URL">{{ old('api_url',$store->api_url) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Assign Storekeepers</label>
                                        <select class="form-control select2" name="storekeeper_ids[]" multiple>
                                            @if ($storekeepers->count())
                                                @foreach ($storekeepers as $key => $value)
                                                    <option value="{{ $value->id }}"
                                                        <?= in_array($value->id, $added_storekeepers) ? 'selected' : '' ?>>
                                                        {{ $value->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <label id="storekeeper_ids[]-error" class="error"
                                            for="storekeeper_ids[]"></label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Assign Drivers</label>
                                        <select class="form-control select2" name="driver_ids[]" multiple>
                                            @if ($drivers->count())
                                                @foreach ($drivers as $key => $value)
                                                    <option value="{{ $value->id }}"
                                                        <?= in_array($value->id, $added_drivers) ? 'selected' : '' ?>>
                                                        {{ $value->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <label id="driver_ids[]-error" class="error"
                                            for="driver_ids[]"></label>
                                    </div>
                                </div>
                            </div>
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

            $.validator.addMethod('mypassword', function(value, element) {
                return this.optional(element) || (value.match(/[a-zA-Z]/) && value.match(/[0-9]/));
            },
            'Password must contain at least one numeric and one alphabetic character.');

            $('#regForm').validate({ // initialize the plugin
                rules: {
                    name: {
                        required: false
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    mobile: {
                        required: false,
                        rangelength: [6, 15]
                    },
                    address: {
                        required: false
                    },
                    lat: {
                        required: true
                    },
                    city: {
                        required: false
                    },
                    state: {
                        required: false
                    },
                    country: {
                        required: false
                    },
                    pin: {
                        required: false,
                        digits: true
                    },
                    password: {
                        required: false,
                        minlength: 8,
                        mypassword: true
                    },
                }
            });

        });
    </script>
@endsection
