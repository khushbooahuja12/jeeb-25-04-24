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
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Monday</h2>
                            <br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value="monday"/>
                            <input type="hidden" name="schedule_date" id="schedule_date" value=""/>
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
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $monday_slots_24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close" {{ $monday_slots_24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @if ($monday_slots)
                                    @foreach ($monday_slots as $no => $slot)
                                    <tr>
                                        <td>Slot {{$no+1}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot->from }}"/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot->to }}"/></td>
                                    </tr>
                                    @endforeach
                                @endif
                                @for ($no = ($monday_slots->count()+1); $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" onclick="return validateTimeSlots()" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Tuesday</h2>
                            <br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value="tuesday"/>
                            <input type="hidden" name="schedule_date" id="schedule_date" value=""/>
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
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $tuesday_slots_24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close" {{ $tuesday_slots_24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @if ($tuesday_slots)
                                    @foreach ($tuesday_slots as $no => $slot)
                                    <tr>
                                        <td>Slot {{$no+1}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot->from }}"/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot->to }}"/></td>
                                    </tr>
                                    @endforeach
                                @endif
                                @for ($no = ($tuesday_slots->count()+1); $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" onclick="return validateTimeSlots()" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Wednesday</h2>
                            <br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value="wednesday"/>
                            <input type="hidden" name="schedule_date" id="schedule_date" value=""/>
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
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $wednesday_slots_24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close"  {{ $wednesday_slots_24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @if ($wednesday_slots)
                                    @foreach ($wednesday_slots as $no => $slot)
                                    <tr>
                                        <td>Slot {{$no+1}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot->from }}"/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot->to }}"/></td>
                                    </tr>
                                    @endforeach
                                @endif
                                @for ($no = ($wednesday_slots->count()+1); $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" onclick="return validateTimeSlots()" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Thursday</h2>
                            <br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value="thursday"/>
                            <input type="hidden" name="schedule_date" id="schedule_date" value=""/>
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
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $thursday_slots_24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close" {{ $thursday_slots_24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @if ($thursday_slots)
                                    @foreach ($thursday_slots as $no => $slot)
                                    <tr>
                                        <td>Slot {{$no+1}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot->from }}"/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot->to }}"/></td>
                                    </tr>
                                    @endforeach
                                @endif
                                @for ($no = ($thursday_slots->count()+1); $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" onclick="return validateTimeSlots()" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Friday</h2>
                            <br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value="friday"/>
                            <input type="hidden" name="schedule_date" id="schedule_date" value=""/>
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
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $friday_slots_24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close" {{ $friday_slots_24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @if ($friday_slots)
                                    @foreach ($friday_slots as $no => $slot)
                                    <tr>
                                        <td>Slot {{$no+1}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot->from }}"/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot->to }}"/></td>
                                    </tr>
                                    @endforeach
                                @endif
                                @for ($no = ($friday_slots->count()+1); $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" onclick="return validateTimeSlots()" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Saturday</h2>
                            <br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value="saturday"/>
                            <input type="hidden" name="schedule_date" id="schedule_date" value=""/>
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
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $saturday_slots_24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close" {{ $saturday_slots_24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @if ($saturday_slots)
                                    @foreach ($saturday_slots as $no => $slot)
                                    <tr>
                                        <td>Slot {{$no+1}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot->from }}"/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot->to }}"/></td>
                                    </tr>
                                    @endforeach
                                @endif
                                @for ($no = ($saturday_slots->count()+1); $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" onclick="return validateTimeSlots()" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
                        <form method="post" id="storeScheduleForm" enctype="multipart/form-data" action="{{ route('admin.fleet.store_schedules.update', $store->id) }}">
                            @csrf
                            <h2>Sunday</h2>
                            <br/>
                            <input type="hidden" name="schedule_day" id="schedule_day" value="sunday"/>
                            <input type="hidden" name="schedule_date" id="schedule_date" value=""/>
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
                                        <input type="checkbox" name="schedule_24hours" id="schedule_24hours" {{ $sunday_slots_24hours_open ? 'checked' : '' }}/> 24 Hours Open
                                    </td>
                                    <td style="width: 35%">
                                        <input type="checkbox" name="schedule_24hours_close" id="schedule_24hours_close" {{ $sunday_slots_24hours_close ? 'checked' : '' }}/> 24 Hours Close
                                    </td>
                                    {{-- <th style="width: 30%">Action</th> --}}
                                </tr>
                                @if ($sunday_slots)
                                    @foreach ($sunday_slots as $no => $slot)
                                    <tr>
                                        <td>Slot {{$no+1}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value="{{ $slot->from }}"/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value="{{ $slot->to }}"/></td>
                                    </tr>
                                    @endforeach
                                @endif
                                @for ($no = ($sunday_slots->count()+1); $no <= 5; $no++)
                                    <tr>
                                        <td>Slot {{$no}}</td>
                                        <td><input class="schedule_input" type="time" name="slot_open[]" value=""/></td>
                                        <td><input class="schedule_input" type="time" name="slot_close[]" value=""/></td>
                                    </tr>
                                @endfor
                            </table>
                            <input type="submit" onclick="return validateTimeSlots()" name="schedule_save" value="Save" class="btn btn-primary"/>
                        </form>
                        <hr/>
                        <br/>
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
