@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Bot Rotator</h4>
                <a href="{{ url('admin/bot_rotator/create') }}" class="btn btn-primary"><i class="fa fa-plus"></i> Add New</a>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/brands'); ?>">Brands</a></li>
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
                <div class="card-body">
                    <form method="POST" action="{{ url('admin/bot_rotator/rotate') }}">
                        @csrf
                        <select class="col-md-3 select2" name="identifier" id="identifier" required>
                            <option value="" disabled selected>--Select Identifier--</option>
                            @foreach ($identifiers as $key => $value)
                                <option value="{{ $value->identifier }}" {{ $value->identifier == $identifier ? 'selected': ''}}>{{ $value->identifier }}</option>
                            @endforeach
                        </select>
                        @isset($identifier)
                            <button type="submit" class="btn btn-primary"><i class="fa fa-robot"></i> Rotate</button>
                        @endisset
                    </form>
                    <form class="form-inline searchForm" method="GET">
                        <div class="form-group mb-2">
                            <input type="text" class="form-control" name="filter" placeholder="Search Command Name" value="">
                        </div>
                        <button type="submit" class="btn btn-dark">Filter</button>
                    </form>
                    <table class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Command</th>
                                <th>Identifier </th>
                                <th class="nosort">1st Time</th>
                                <th class="nosort">2nd Time</th>
                                <th class="nosort">3rd Time</th>
                                <th class="nosort">4th Time</th>
                                <th class="nosort">5th Time</th>
                                <th class="nosort">6th Time</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($commands as $key => $command)
                            <tr>
                                <td>{{ $key+1 }}</td>
                                <td>{{ $command->command }}</td>
                                <td>{{ $command->identifier }}</td>
                                <td>{{ $command->status_1 }}</td>
                                <td>{{ $command->status_2 }}</td>
                                <td>{{ $command->status_3 }}</td>
                                <td>{{ $command->status_4 }}</td>
                                <td>{{ $command->status_5 }}</td>
                                <td>{{ $command->status_6 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$commands->count()}} of {{ $commands->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $commands->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $("#identifier").change(function(){
        var identifier = $(this).val();
        window.location.href = '/admin/bot_rotator?identifier='+identifier; 
    });
</script>
@endsection