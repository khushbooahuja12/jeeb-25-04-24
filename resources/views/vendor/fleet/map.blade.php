<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Route Planner</title>
    <!-- Include Bootstrap CSS and Bootstrap Selectpicker CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css">
    <style>
        .marker-label {
            position: relative;
            bottom: 4px;
        }

        #map-container {
            height: 100vh;
        }

        #map {
            height: 100%;
        }

        #input-container {
            padding-right: 15px;
        }

        @media (max-width: 768px) {
            #input-container {
                padding-right: 0;
                margin-bottom: 20px;
            }
        }

        input[type="radio"] {
            transform: scale(1.5);
            margin-right: 10px;
        }

        .payment-methods {
            display: flex;
            flex-direction: column;
        }

        #map-container {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            left: 25%;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #input-container {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 25%;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- <form id="OrderSubmit" method="post"> -->
        <div class="row">
            <input type="hidden" value="{{$user->pass_token}}" id="pass_token">
            <div class="col-md-3" id="input-container">
                @if($user->user_role == 'BUSINESS')
                <div class="form-group mt-3">
                    <h4 id="business-pickup-toggle" style="cursor: pointer;">Where is your pickup?</h4>
                </div>
                <div class="form-group mt-3" id="business-pickup-select" style="display: none;">
                    <select id="pickup-select" class="form-control">
                        <option value="">Select Pickup</option>
                        <option value="25.2854473,51.5310397">Store 1</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div id="other-pickup-fields" style="display: none;">
                    <div class="form-group mt-3">
                        <label for="pickup">Pickup Destination</label>
                        <input type="text" class="form-control pickup-loc" id="pickup-input" placeholder="Enter Pickup Location">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Sender Name</label>
                        <input type="text" class="form-control sender-name" placeholder="Enter Sender Name">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Phone Number</label>
                        <input type="text" class="form-control pickup-phone-number" placeholder="+91 1234567890">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Pay</label>
                        <input type="number" class="form-control pickup-pay" placeholder="">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Collect</label>
                        <input type="number" class="form-control pickup-collect" placeholder="">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Description</label>
                        <textarea type="text" class="form-control pickup-description" placeholder="Enter your description"></textarea>
                    </div>
                </div>
                @else
                <div class="form-group mt-3">
                    <h4 id="pickup-toggle" style="cursor: pointer;">Where is your pickup?</h4>
                </div>

                <div id="pickup-details" style="display: none;">
                    <div class="form-group mt-3">
                        <label for="pickup">Pickup Destination</label>
                        <input type="text" class="form-control pickup-loc" id="pickup-input" placeholder="Enter Pickup Location">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Sender Name</label>
                        <input type="text" class="form-control sender-name" placeholder="Enter Sender Name">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Phone Number</label>
                        <input type="text" class="form-control pickup-phone-number" placeholder="+91 1234567890">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Pay</label>
                        <input type="number" class="form-control pickup-pay" placeholder="">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Collect</label>
                        <input type="number" class="form-control pickup-collect" placeholder="">
                    </div>
                    <div class="form-group mt-3">
                        <label for="pickup">Description</label>
                        <textarea type="text" class="form-control pickup-description" placeholder="Enter your description"></textarea>
                    </div>
                </div>
                @endif
                <div id="TextBoxContainer"></div>
                <button type="button" class="btn btn-primary" id="btnAdd">Add New DropOff</button>
                <!-- Vehicle selection section (initially hidden) -->
                @if($user->user_role == 'BUSINESS')
                <div id="vehicle-selection" class="form-group mt-3" style="display: none;">
                    <label>Select Vehicle</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="vehicle" value="1" id="vehicle-bicycle">
                        <label class="form-check-label" for="vehicle-bicycle">Bicycle</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="vehicle" value="2" id="vehicle-car">
                        <label class="form-check-label" for="vehicle-car">Car</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="vehicle" value="3" id="vehicle-van">
                        <label class="form-check-label" for="vehicle-van">Van</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="vehicle" value="4" id="vehicle-scooter">
                        <label class="form-check-label" for="vehicle-scooter">Scooter</label>
                    </div>
                </div>
                @endif
                <div class="form-group mt-3">
                    <div class="form-group mt-3">
                        <h4>Schedule :</h4>
                    </div>
                    <select id="scheduleSelect" class="form-control">
                        <option value="immediate">Immediate</option>
                        <option value="schedule">Add schedule</option>
                    </select>
                </div>
                <div id="scheduleInput" class="form-group mt-3" style="display: none;">
                    <label for="datetime">Date and Time:</label>
                    <div class="input-group">
                        <input type="datetime-local" class="form-control" id="datetime">
                        <!-- <div class="input-group-append">
                            <button class="btn btn-primary">Select</button>
                        </div> -->
                    </div>
                </div>
                <div class="form-group mt-3">
                    <h4 id="payment-toggle" style="cursor: pointer;">Payment :</h4>
                </div>
                <div id="payment-details" style="display: none;">
                    @if($user->user_role == 'BUSINESS')
                    <div class="form-group mt-3">
                        <label for="orderPriceNew">Price</label>
                        <input type="text" id="orderPriceNew" value="QAR 0.00" class="form-control" placeholder="Enter Price">
                    </div>
                    @else
                    <div class="form-group mt-3">
                        <label for="price">Price</label>
                        <input type="text" id="price" value="QAR 0.00" class="form-control" placeholder="Enter Price">
                    </div>
                    @endif
                    <div class="form-group mt-3">
                        <h4 id="promo-toggle" style="cursor: pointer;">+ Add Promo</h4>
                    </div>
                    <div id="promo-details" class="form-group mt-3" style="display: none;">
                        <label for="datetime">Remove Promo :</label>
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Enter your promo code">
                            <div class="input-group-append">
                                <button class="btn btn-primary">Confirm</button>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3 payment-methods">
                        <h4 id="promo-toggle" style="cursor: pointer;">Choose your payment method :</h4>
                        <label for="wallet">
                            <input type="radio" id="wallet" name="payment_method" value="Wallet Balance (QAR 15)">
                            Wallet Balance (QAR 15)
                        </label>
                        <label for="cod">
                            <input type="radio" id="cod" name="payment_method" value="cash" checked>
                            Cash on Delivery
                        </label>
                    </div>
                </div>
                <div class="text-center mt-3 d-none" id="confirm_btn">
                    <button type="button" id="btnRequestOrder" class="btn btn-primary">Request Order</button>
                </div>
            </div>
            <div class="col-md-9 p-0" id="map-container">
                <div id="map"></div>
            </div>
        </div>
        <!-- </form> -->
    </div>

    <script>
        // Function to show or hide the date-time input based on the selected option
        function toggleDateTimeInput() {
            var scheduleSelect = document.getElementById('scheduleSelect');
            var scheduleInput = document.getElementById('scheduleInput');

            if (scheduleSelect.value === 'schedule') {
                scheduleInput.style.display = 'block';
            } else {
                scheduleInput.style.display = 'none';
            }
        }

        // Function to validate the selected time
        function validateTime() {
            var datetimeInput = document.getElementById('datetime');
            var selectedTime = new Date(datetimeInput.value);
            var currentTime = new Date();

            // Calculate the difference in minutes between the selected time and current time
            var timeDiffMinutes = (selectedTime - currentTime) / (1000 * 60);

            if (timeDiffMinutes < 30) {
                alert('Choose a time that is 30 minutes or more from the current time.');
                datetimeInput.value = ''; // Clear the input value
            }
        }

        // Event listener to trigger the toggle function when the select option changes
        document.getElementById('scheduleSelect').addEventListener('change', toggleDateTimeInput);

        // Event listener to validate the selected time when the input changes
        document.getElementById('datetime').addEventListener('change', validateTime);
    </script>

    <!-- Include Bootstrap JS, Bootstrap Selectpicker JS, and Google Maps API -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC45nn0EABv-2VVhUmCn5BJFo7EZo7xDlU&libraries=places&callback=initMap" async defer></script>

    <script>
        let distance = null;
        let duration = null;
        // Initialize the map and autocomplete
        function initMap() {
            // Set the coordinates for Doha
            var doha = {
                lat: 25.276987,
                lng: 51.520067
            };

            // Set initial map options with Doha as the center
            var mapOptions = {
                center: doha, // Center the map at Doha
                zoom: 12, // Set the initial zoom level
            };

            // Create the map
            var map = new google.maps.Map(document.getElementById("map"), mapOptions);

            // Initialize autocomplete for pickup location
            var pickupAutocomplete = new google.maps.places.Autocomplete(
                document.getElementById('pickup-input'),
                autocompleteOptions
            );
        }

        // Autocomplete options
        var autocompleteOptions = {
            componentRestrictions: {
                country: 'qa'
            }, // Limit autocomplete to Qatar
            fields: ['place_id', 'name', 'formatted_address'] // Define fields to be returned
        };

        $("body").on("click", ".remove", function() {
            var dropoffInput = $(this).closest(".dropoff-input");
            // console.log("Parent:", dropoffInput);
            // console.log("Children Length:", dropoffInput.children().length);

            if ($("#TextBoxContainer").children().length > 1) {
                dropoffInput.remove();
                // Show the "Add Drop-off" button if less than three drop-off locations
                if ($("#TextBoxContainer").children().length >= 3) {
                    $('#btnAdd').show();
                }
                // Update markers on the map
                getDropOffLocations();
            } else {
                // console.log($("#TextBoxContainer").children().length);
                alert("At least one drop-off location is required.");
            }
        });


        // Initialize autocomplete for dynamically added drop-off locations
        $('body').on('click', '#btnAdd', function() {
            var div = $('<div/>');
            div.html(GetDynamicTextBox(''));
            $("#TextBoxContainer").append(div);

            // Hide the "Add Drop-off" button if the number of drop-off inputs is 3 or more
            if ($("#TextBoxContainer").children().length >= 3) {
                $('#btnAdd').hide();
            }

            // Initialize autocomplete for new drop-off location
            var dropoffAutocomplete = new google.maps.places.Autocomplete(
                div.find('.drop-off-loc')[0],
                autocompleteOptions
            );

            // Attach change event listener to dynamically added drop-off location input
            dropoffAutocomplete.addListener('place_changed', function() {
                // Run separate function to gather drop-off locations
                getDropOffLocations();
            });
        });

        var dropoffCount = 0;

        // Function to create dynamic text box for drop-off locations
        function GetDynamicTextBox(value) {
            dropoffCount++;

            // return '<div class="form-group">' +
            //     '<label for="dropoff">Drop-off Destination ' + dropoffCount + '</label>' +
            //     '<input type="text" class="form-control drop-off-loc" name="dropoffs[]" value="' + value + '" placeholder="Enter Drop-off Location">' +
            //     '<button type="button" onclick="reset_map()" class="btn btn-danger remove">Delete</button>' +
            //     '</div>';

            return '<div class="form-group dropoff-input">' +
                '<h4 id="dropoff-toggle-' + dropoffCount + '" class="dropoff-toggle" style="cursor: pointer;">Where is your Drop-offs?</h4>' +
                '<div id="dropoff-details-' + dropoffCount + '" >' +
                '<label for="dropoff">Drop-off Destination</label>' +
                '<div class="input-group">' +
                '<input type="text" class="form-control drop-off-loc" value="' + value + '" placeholder="Enter Drop-off Location">' +
                '<div class="input-group-append">' +
                '<button type="button" class="btn btn-danger remove">Delete</button>' +
                '</div>' +
                '</div>' +
                '<div class="form-group mt-3">' +
                '<label for="pickup">Sender Name</label>' +
                '<input type="text" class="form-control receiver-name" placeholder="Enter Receiver Name">' +
                '</div>' +
                '<div class="form-group mt-3">' +
                '<label for="pickup">Phone Number</label>' +
                '<input type="text" class="form-control dropoff-phone-number" placeholder="+91 1234567890">' +
                '</div>' +
                '<div class="form-group mt-3">' +
                '<label for="pickup">Pay</label>' +
                '<input type="number" class="form-control dropoff-pay" placeholder="">' +
                '</div>' +
                '<div class="form-group mt-3">' +
                '<label for="pickup">Collect</label>' +
                '<input type="number" class="form-control dropoff-collect" placeholder="">' +
                '</div>' +
                '<div class="form-group mt-3">' +
                '<label for="pickup">Description</label>' +
                '<textarea type="text" class="form-control dropoff-description" placeholder="Enter your description"></textarea>' +
                '</div>' +

                '<div class="text-center mt-3">' +
                '<button type="button" class="btn btn-primary confirm-dropoff">Confirm</button>' +
                '</div>' +
                '</div>' +
                '</div>';
        }

        $(document).on('click', '.dropoff-toggle', function() {
            var targetId = $(this).attr('id').replace('toggle', 'details');
            $('#' + targetId).toggle();
        });

        function reset_map() {
            getDropOffLocations()
        }

        var dropOffLocationsForm = [];

        $('#btnRequestOrder').click(function() {
            const datetimeValue = $('#datetime').val();
            const datetime = datetimeValue !== '' ? datetimeValue : null;

            const url = "{{ env('API_URL') }}";
            const token = "{{ env('BEARER_TOKEN') }}";
            const passToken = $('#pass_token').val();

            const OrderSubmit = {
                "city_id": 2,
                "vehicle_id": 2,
                "is_return": false,
                "discount_code": null,
                "endpoint": "web",
                "card_id": null,
                "payment_intent_id": null,
                "payment_type": "cash",
                "schedule": datetime,
                "addresses": dropOffLocationsForm
            };

            // Send the fetch request
            console.log('Sending data3:', OrderSubmit);
            fetch(url + "order-submit", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': token,
                        'Auth-Token': passToken
                    },
                    body: JSON.stringify(OrderSubmit),
                })
                .then(response => {

                    console.log('Response received3:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("order_id:", data.data.order_id);
                    if (data.status == 'success') {
                        if (confirm('Your order has been placed successfully. Would you like to cancel the order?')) {
                            // Code to cancel the order if the user clicks 'OK'
                            console.log('Order canceled by user');
                            const OrderCancel = {
                                "order_id": data.data.order_id,
                            };
                            fetch(api_url_v1 + "order-cancel", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Authorization': token,
                                    },
                                    body: JSON.stringify(OrderCancel),
                                })
                                .then(response => {

                                    console.log('Response received:', response.status);
                                    if (!response.ok) {
                                        throw new Error('Network response was not ok');
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('JSON Data:', data);
                                })
                        } else {
                            // Code to proceed with the order if the user clicks 'Cancel'
                            console.log('Order confirmed by user');
                        }
                    } else {
                        alert('Failed to place order. Please try again.');
                    }
                    console.log('JSON Data:', data);
                })

                .catch(error => {
                    console.error('Error:', error);
                });
        });

        // Function to gather pickup and drop-off locations
        function getDropOffLocations() {
            var pickupLocation = $('.pickup-loc').val(); // Get the pickup location

            // Define the URL for the POST request (LIVE)
            // const api_url_v1 = "{{ env('API_URL_v1') }}";
            // const token = "{{ env('BEARER_TOKEN') }}";

            // Define the URL for the POST request
            const url = "{{ env('API_URL') }}";
            const api_url_v1 = "{{ env('API_URL_v1') }}";
            const token = "{{ env('BEARER_TOKEN') }}";
            const passToken = $('#pass_token').val();


            var dropOffLocations = [];
            // Handle radio button change event
            $('input[type="radio"][name="vehicle"]').click(function() {
                // Get the selected vehicle value
                var selectedVehicle = $('input[type="radio"][name="vehicle"]:checked').val();

                var OrderPriceNew = {
                    pickup: {
                        lat: dropOffLocations[0].lat,
                        long: dropOffLocations[0].lng
                    },
                    "discount_code": null,
                    "vehicle_id": selectedVehicle,
                    "pickup_id": 2,
                    "isBusinessUser": true
                };

                // Loop through dropOffLocations starting from index 1 (index 0 is the pickup)
                for (let i = 1; i < dropOffLocations.length; i++) {
                    OrderPriceNew['dropoff_' + i] = {
                        lat: dropOffLocations[i].lat,
                        long: dropOffLocations[i].lng
                    };
                }

                // Send the fetch request
                console.log('Sending data5:', OrderPriceNew);
                fetch(api_url_v1 + "orderPriceNew", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Authorization': token,
                        },
                        body: JSON.stringify(OrderPriceNew),
                    })
                    .then(response => {
                        console.log('Response received:', response.status);
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        $('#orderPriceNew').val(data.data.results.symbole + ' ' + data.data.results.price);
                        console.log('JSON Data:', data);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });


            // Geocode the pickup location address
            geocodeAddress(pickupLocation, function(pickupLatlng) {
                if (pickupLatlng) {
                    var pickupDropOffLocation = {
                        building_no: null,
                        zone_no: null,
                        street_no: null,
                        type: 'pickup',
                        lat: pickupLatlng.lat(),
                        long: pickupLatlng.lng(),
                        lng: pickupLatlng.lng(),
                        name: $('.sender-name').val(),
                        phone: $('.pickup-phone-number').val(),
                        address: pickupLocation,
                        description: $('.pickup-description').val(),
                        collect: $('.pickup-collect').val(),
                        deposit: $('.pickup-pay').val(),
                        label: pickupLocation,
                        price: "10.00",
                        distance: distance,
                        duration: duration
                    };
                    dropOffLocations.push(pickupDropOffLocation);



                    // Click event handler for the "Confirm" button
                    $('.confirm-dropoff').click(function() {

                        // const getPickupsZones = {
                        //     "pickup_id": 2,
                        //     "coordinates": "[{\"drop\":1,\"lat\":25.276239841921317,\"long\":51.61400929780273},{\"drop\":2,\"lat\":25.282490513993032,\"long\":50.77358433164064}]"
                        // }




                        $("#confirm_btn").removeClass("d-none");
                        $("#vehicle-selection").show();
                        // Iterate over drop-off locations
                        // $('.drop-off-loc').each(function(index) {
                        //     var address = $(this).val();



                        $('.dropoff-input').each(function(index) {
                            let address = $(this).find('.drop-off-loc').val();
                            let receiverName = $(this).find('.receiver-name').val();
                            let phoneNumber = $(this).find('.dropoff-phone-number').val();
                            let pay = $(this).find('.dropoff-pay').val();
                            let collect = $(this).find('.dropoff-collect').val();
                            let description = $(this).find('.dropoff-description').val();

                            // Geocode the address to fetch latitude and longitude
                            geocodeAddress(address, function(latlng) {
                                if (latlng) {
                                    var dropOffLocation1 = {
                                        numbering: index + 1, // Assigning a unique ID to each drop-off location
                                        building_no: null,
                                        zone_no: null,
                                        street_no: null,
                                        type: 'dropoff',
                                        lat: latlng.lat(),
                                        long: latlng.lng(),
                                        lng: latlng.lng(),
                                        name: receiverName,
                                        phone: phoneNumber,
                                        address: address,
                                        description: description,
                                        collect: collect,
                                        deposit: pay,
                                        label: pickupLocation,
                                        price: "20.41",
                                        distance: distance,
                                        duration: duration
                                    };
                                    dropOffLocations.push(dropOffLocation1);



                                    // Check if all addresses have been processed
                                    if (dropOffLocations.length - 1 === $('.dropoff-input').length) {
                                        console.log('dropOffLocations new - ', dropOffLocations); // Output pickup and drop-off locations with latitude and longitude

                                        // Define the data to be sent in the request body
                                        const OrderDuration = {
                                            pointOne: {
                                                lat: pickupLatlng.lat(),
                                                long: pickupLatlng.lng()
                                            },
                                            pointTwo: {
                                                lat: latlng.lat(),
                                                long: latlng.lng()
                                            },
                                            vehicle_id: null
                                        };

                                        // // Define the URL for the POST request
                                        // const url = "{{ env('API_URL') }}";
                                        // const api_url_v1 = "{{ env('API_URL_v1') }}";
                                        // const token = "{{ env('BEARER_TOKEN') }}";
                                        // const passToken = $('#pass_token').val();

                                        // Send the fetch request
                                        console.log('Sending data1:', OrderDuration);
                                        fetch(url + "order-duration", {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Authorization': token,
                                                },
                                                body: JSON.stringify(OrderDuration),
                                            })
                                            .then(response => {
                                                console.log('Response received:', response.status);
                                                if (!response.ok) {
                                                    throw new Error('Network response was not ok');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                console.log('JSON Data:', data);
                                                distance = data.data.results.distance;
                                                duration = data.data.results.duration;
                                                show_routes(dropOffLocations)
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                            });

                                        var OrderPrice = {
                                            pickup: {
                                                lat: dropOffLocations[0].lat,
                                                long: dropOffLocations[0].lng
                                            },
                                            discount_code: null,
                                            vehicle_id: null
                                        };

                                        // Loop through dropOffLocations starting from index 1 (index 0 is the pickup)
                                        for (let i = 1; i < dropOffLocations.length; i++) {
                                            OrderPrice['dropoff_' + i] = {
                                                lat: dropOffLocations[i].lat,
                                                long: dropOffLocations[i].lng
                                            };
                                        }

                                        // Send the fetch request
                                        console.log('Sending data2:', OrderPrice);
                                        fetch(url + "order-price", {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Authorization': token,
                                                },
                                                body: JSON.stringify(OrderPrice),
                                            })
                                            .then(response => {
                                                console.log('Response received:', response.status);
                                                if (!response.ok) {
                                                    throw new Error('Network response was not ok');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                $('#price').val(data.data.results[0].symbole + ' ' + data.data.results[0].price);
                                                console.log('JSON Order Price Data:', data);
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                            });


                                        // Initialize an array to store drop-off coordinates
                                        const dropoffCoordinates = [];

                                        // Loop through dropOffLocations starting from index 1
                                        for (let i = 1; i < dropOffLocations.length; i++) {
                                            // Create an object for each drop-off location
                                            const dropoffObject = {
                                                drop: i, // Assuming drop is the sequence number starting from 1
                                                lat: dropOffLocations[i].lat,
                                                long: dropOffLocations[i].lng
                                            };
                                            // Push the drop-off object to the dropoffCoordinates array
                                            dropoffCoordinates.push(dropoffObject);
                                        }

                                        // Convert dropoffCoordinates array to JSON string
                                        const coordinatesJson = JSON.stringify(dropoffCoordinates);

                                        // Construct the request data object
                                        const requestData = {
                                            pickup_id: 2,
                                            coordinates: coordinatesJson
                                        };


                                        fetch(url + "getPickupsZones", {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Authorization': token,
                                                    'Auth-Token': passToken
                                                },
                                                body: JSON.stringify(requestData),
                                            })
                                            .then(response => {
                                                console.log('Zones Response received:', response.status);
                                                if (!response.ok) {
                                                    throw new Error('Network response was not ok');
                                                }
                                                return response.json();
                                            })
                                            .then(data => {
                                                console.log('JSON Data:', data);
                                                // Check if the 'zones' array exists in the data and is not empty
                                                if (data && data.data && data.data.zones && data.data.zones.length > 0) {
                                                    // Iterate through the zones array
                                                    data.data.zones.forEach(zone => {
                                                        // Check if status is false for any zone
                                                        if (zone.status === false) {
                                                            // Show alert for each zone with status false
                                                            alert(`Delivery is not possible for Drop-off ${zone.drop}`);
                                                        }
                                                    });
                                                } else {
                                                    console.error('Invalid or empty data received from server');
                                                }
                                            })
                                            .catch(error => {
                                                console.error('Error:', error);
                                                // Show an alert for the error message
                                                alert('Error fetching data from the server');
                                            });




                                    }
                                } else {
                                    console.error('Geocoding failed for address: ' + address);
                                }
                            });
                        });
                        dropOffLocationsForm = dropOffLocations;
                    });
                } else {
                    console.error('Geocoding failed for pickup location: ' + pickupLocation);
                }
            });
        }
    </script>
    <script>
        // Function to geocode an address and retrieve its latitude and longitude
        function geocodeAddress(address, callback) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({
                'address': address
            }, function(results, status) {
                if (status === google.maps.GeocoderStatus.OK && results[0]) {
                    var latlng = results[0].geometry.location;
                    callback(latlng);
                } else {
                    callback(null);
                }
            });
        }
    </script>
    <script>
        // Function to show routes from pickup to drop-off locations
        function show_routes(dropOffLocations) {
            // Create a new map
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: {
                    lat: dropOffLocations[0].lat,
                    lng: dropOffLocations[0].lng
                } // Center the map at the pickup location
            });

            // Initialize the DirectionsService and DirectionsRenderer
            var directionsService = new google.maps.DirectionsService();
            var directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                polylineOptions: {
                    strokeColor: 'red',
                    strokeOpacity: 0,
                    icons: [{
                        icon: {
                            path: google.maps.SymbolPath.FORWARD_CLOSED_ARROW,
                            scale: 2,
                            strokeOpacity: 1,
                            strokeWeight: 2,
                            strokeColor: 'red',
                            fillColor: 'red',
                            fillOpacity: 1,
                        },
                        offset: '0',
                        repeat: '20px'
                    }]
                },
                suppressMarkers: true // Suppress default markers
            });

            // Set the origin (pickup location)
            var pickupLatLng = new google.maps.LatLng(dropOffLocations[0].lat, dropOffLocations[0].lng);

            // Create marker for pickup location
            var pickupMarker = new google.maps.Marker({
                position: pickupLatLng,
                map: map,
                icon: "{{asset('_admin/assets/img/location_pickup.png')}}", // Custom marker for pickup
                label: {
                    text: 'P',
                    color: 'black',
                    fontWeight: 'bold',
                } // Label 'P' for pickup
            });

            // Style the marker label using CSS
            const labelClass = 'marker-label';
            pickupMarker.addListener('label_changed', function() {
                pickupMarker.getLabel().className = labelClass;
            });
            pickupMarker.getLabel().className = labelClass;

            // Add drop-off locations as markers with custom images and numbering
            for (var i = 1; i < dropOffLocations.length; i++) {
                var dropoffLatLng = new google.maps.LatLng(dropOffLocations[i].lat, dropOffLocations[i].lng);
                var dropoffMarker = new google.maps.Marker({
                    position: dropoffLatLng,
                    map: map,
                    icon: "{{asset('_admin/assets/img/dropoffs.png')}}", // Custom marker for drop-off
                    label: {
                        text: dropOffLocations[i].numbering.toString(),
                        color: 'black',
                        fontWeight: 'bold',
                    }, // Add numbering from array to drop-off markers
                    tooltipContent: 'Distance: ' + distance + '<br>Duration: ' + duration
                });

                // Style the marker label using CSS
                const labelClass = 'marker-label';
                dropoffMarker.addListener('label_changed', function() {
                    dropoffMarker.getLabel().className = labelClass;
                });
                dropoffMarker.getLabel().className = labelClass;

                // Add a tooltip to the marker
                var tooltip = new google.maps.InfoWindow({
                    content: dropoffMarker.tooltipContent
                });

                // Show tooltip on marker mouseover
                dropoffMarker.addListener('mouseover', function() {
                    tooltip.open(map, this);
                });

                // Hide tooltip on marker mouseout
                dropoffMarker.addListener('mouseout', function() {
                    tooltip.close();
                });
            }

            // Define the request object for DirectionsService
            var request = {
                origin: pickupLatLng,
                destination: dropoffLatLng, // Destination initially set to pickupLatLng, will be updated for each drop-off location
                waypoints: dropOffLocations.slice(1).map(function(location) {
                    return {
                        location: new google.maps.LatLng(location.lat, location.lng),
                        stopover: true
                    };
                }),
                optimizeWaypoints: true, // Optimize the order of waypoints
                travelMode: 'DRIVING'
            };

            // Send request to DirectionsService to get directions
            directionsService.route(request, function(response, status) {
                if (status === 'OK') {
                    // Display the route on the map
                    directionsRenderer.setDirections(response);
                } else {
                    window.alert('Directions request failed due to ' + status);
                }
            });
        }
    </script>

    <script>
        $(document).ready(function() {
            $("#pickup-toggle").click(function() {
                $("#pickup-details").toggle();
            });
            $("#payment-toggle").click(function() {
                $("#payment-details").toggle();
            });
            $("#promo-toggle").click(function() {
                $("#promo-details").toggle();
            });
            $("#business-pickup-toggle").click(function() {
                $("#business-pickup-select").toggle();
                // $("#other-pickup-fields").toggle();
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#pickup-select').change(function() {
                var selectedOption = $(this).val();
                if (selectedOption === 'other') {
                    $('#other-pickup-fields').show();
                    $('#pickup-input').val('');
                } else {
                    $('#other-pickup-fields').hide();
                    var latLng = selectedOption.split(',');
                    var lat = latLng[0];
                    var lng = latLng[1];
                    $('#pickup-input').val(lat + ', ' + lng);
                }
            });
        });
    </script>
    <!-- Include the Google Maps API script -->
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC45nn0EABv-2VVhUmCn5BJFo7EZo7xDlU&libraries=places&callback=initMap" async defer></script>
</body>

</html>