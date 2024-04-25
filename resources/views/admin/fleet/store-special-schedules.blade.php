@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .text_mode {
        display: block;
    }
    .edit_mode {
        display: none;
    }
    .driver_edit {
        display: block;
    }
    .driver_saving {
        display: none;
    }
    .driver_save {
        display: none;
    }
    .product_btn {
        width: 70px;
        height: 35px;
    }
    .schedule_btn {
        background: #9CC0F9;
        padding: 5px 10px;
        display: inline-block;
        width: auto;
        margin: 5px 5px;
        border-radius: 5px;
        text-align: center;
        color: #000000;
        font-weight: bold;
    }
    .schedule_input {
        width: 100%;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">{{$store->name.', '.$store->company_name}}</h4>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        <div id="map" style="width: 100%; height: 250px;"></div>
                        <br clear="all"/>
                        @include('partials.errors')
                        @include('partials.success')
                        <div class="ajax_alert"></div>
                        <br clear="all"/>
                        <div>
                            <a href="{{ route('fleet-stores-schedules', $store->id) }}">Regular Schedules</a>
                             | 
                            <a href="{{ route('fleet-stores-special-days-schedules', $store->id) }}">Special Days Schedules</a>
                        </div>
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_special_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Add new special day</h2>
                            <br/>
                            <input type="date" name="schedule_date" id="schedule_date" value=""/>
                            <br clear="all"/><br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value=""/>
                            <br clear="all"/><br/>
                            <table class="table table-bordered dt-responsive nowrap schedule_slots" style="max-width: 800px;">
                                <tr>
                                    <th style="width: 30%"></th>
                                    <th style="width: 35%">Open Time</th>
                                    <th style="width: 35%">Close Time</th>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                <tr>
                                    <td style="width: 30%"></td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours"/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close"/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @for ($no = 1; $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
                        @if ($special_slots->count())
                            @foreach ($special_slots as $slot)
                            <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_special_schedules.update', $store->id) }}">
                                @csrf
                                <h2>{{ $slot->date }}</h2>
                                <br/>
                                <input type="hidden" name="schedule_date" id="schedule_date" value="{{ $slot->date }}"/>
                                <input type="hidden" name="schedule_day" id="schedule_day" value=""/>
                                <br clear="all"/><br/>
                                <table class="table table-bordered dt-responsive nowrap schedule_slots" style="max-width: 800px;">
                                    <tr>
                                        <th style="width: 30%"></th>
                                        <th style="width: 35%">Open Time</th>
                                        <th style="width: 35%">Close Time</th>
                                        {{-- <th style="width: 30%">Action</th> --}}
                                    </tr>
                                    <tr>
                                        <td style="width: 30%"></td>
                                        <td style="width: 35%">
                                            <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $slot->is24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                        </td>
                                        <td style="width: 35%">
                                            <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close" {{ $slot->is24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                        </td>
                                        {{-- <th style="width: 30%">Action</th> --}}
                                    </tr>
                                    @if ($slot->schedules)
                                        @foreach ($slot->schedules as $no1 => $slot1)
                                        <tr>
                                            <td>Slot {{$no1+1}}</td>
                                            <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot1->from }}"/></td>
                                            <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot1->to }}"/></td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    @for ($no = ($slot->schedules->count()+1); $no <= 5; $no++)
                                        <tr>
                                            <td>Slot {{$no}}</td>
                                            <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                            <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                        </tr>
                                    @endfor
                                </table>
                                <input type="submit" name="schedule_save" value="Save" class="btn btn-primary"/>
                            </form>
                            <hr/>
                            <br/>
                            @endforeach
                            <div class="row">
                                <div class="col-sm-12 col-md-5">
                                    <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                        Showing {{ $special_slots->count() }} of {{ $special_slots->total() }} entries.
                                    </div>
                                </div>
                                <div class="col-sm-12 col-md-7">
                                    <div class="dataTables_paginate" style="float:right">
                                        {{ $special_slots->links() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // jQuery(document).ready(function($){
    //     $('#schedule_24hours').on('change',showTimeSlots);
    //     function showTimeSlots() {
    //         var schedule_24hours = document.getElementById("schedule_24hours").checked;
    //         if (schedule_24hours) {
    //             $('.schedule_slots').hide();
    //         } else {
    //             $('.schedule_slots').show();
    //         }
    //     }
    //     showTimeSlots();
    // });
    // Initialize and add the map
    function initMap(){
        // Map options
        var options = {
            zoom:10,
            center:{lat:25.286106,lng:51.534817}
        }
        // New map
        var map = new google.maps.Map(document.getElementById('map'), options);
        // Listen for click on map
        google.maps.event.addListener(map, 'click', function(event){
            // Add marker
            addMarker({coords:event.latLng});
        });
        // Array of markers
        var markers = [
        @if (isset($store) && $store)
            {
                coords:{lat:{{$store->latitude}},lng:{{$store->longitude}}},
                content:'<h4>Store ID: {{$store->id}}</h4><p>{{$store->name}} - {{$store->company_name}}</p>'
            },
        @endif
        ];
        // Loop through markers
        for(var i = 0;i < markers.length;i++){
            // Add marker
            addMarker(markers[i]);
        }
        // Add Marker Function
        function addMarker(props){
        var marker = new google.maps.Marker({
            position:props.coords,
            map:map,
            // icon:props.iconImage
        });
        // Check for customicon
        if(props.iconImage){
            // Set icon image
            marker.setIcon(props.iconImage);
        }
        // Check content
        if(props.content){
            var infoWindow = new google.maps.InfoWindow({
            content:props.content
            });
            marker.addListener('click', function(){
            infoWindow.open(map, marker);
            });
        }
        }
    }
    window.initMap = initMap;
</script>
<script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBAQXKQdhn1KRWpRAF9wWp3fkKzjehGm6M&callback=initMap">
    </script>
    
@endsection
