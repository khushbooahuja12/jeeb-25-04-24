@extends('admin.layouts.dashboard_layout_for_fleet_panel')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">All Stores
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/stores') ?>">Stores</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body" style="overflow-x: scroll;">
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Store Name"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                      
                        <div id="map" style="width: 100%; height: 450px;"></div>

                        <br clear="all"/><br/>
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>@sortablelink('name', 'Company Name')</th>
                                    <th>@sortablelink('name', 'Store Name')</th>
                                    <th>Action</th>
                                    <th>@sortablelink('email', 'Email')</th>
                                    <th>@sortablelink('mobile', 'Mobile')</th>
                                    <th>Address</th>
                                    <th># Of Products</th>
                                    <th># Of Available Products</th>
                                    <th># Of Instock Products</th>
                                    <th># Of Recipes</th>
                                    <th># Of Available Recipes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="vendor0">
                                    <td>0</td>
                                    <td>All Vendors</td>
                                    <td>All Stores</td>
                                    <td>
                                        <a title="view details"
                                            href="{{route("fleet-active-orders",0)}}"><i
                                                class="fa fa-eye"></i>
                                        </a>&ensp;                                                
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                @if ($stores)
                                    @foreach ($stores as $key => $row)
                                        <tr class="vendor<?= $row->id ?>">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->company_name }}</td>
                                            <td>{{ $row->name }}</td>
                                            <td>
                                                {{-- <a title="view details"
                                                    href="{{route("fleet-active-orders",$row->id)}}"><i
                                                        class="fa fa-eye"></i>
                                                </a>&ensp; --}}
                                            </td>
                                            <td>{{ $row->email }}</td>
                                            <td>{{ $row->mobile }}</td>
                                            <td>{{ $row->address }}</td>
                                            <td>{{ $row->no_products }}</td>
                                            <td>{{ $row->no_available_products }}</td>
                                            <td>{{ $row->no_instock_products }}</td>
                                            <td>{{ $row->no_recipes }}</td>
                                            <td>{{ $row->no_available_recipes }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $stores->count() }} of {{ $stores->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $stores->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
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
        @if (isset($stores) && !empty($stores))
            @foreach ($stores as $key => $store)
            {
                coords:{lat:{{$store->latitude}},lng:{{$store->longitude}}},
                content:'<h4>Store ID: {{$store->id}}</h4><p>{{$store->name}}<br/>{{$store->company_name}}<br/><a href="{{route("fleet-orders",$store->id)}}">Orders</a></p>'
            }@if ($key < count($stores)-1 ),@endif
            @endforeach
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
            //icon:props.iconImage
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
