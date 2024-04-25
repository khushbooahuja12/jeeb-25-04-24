@extends('admin.layouts.dashboard_layout_for_fleet_panel')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Groups </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item">Fleet Management</a></li>
                        <li class="breadcrumb-item">Instant Model</li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                    <br clear="all"/>
                    <a class="btn btn-primary float-right" href="{{route('fleet-instant-model')}}">Go back to all Groups</a>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        @if(count($stores) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div id="listLeft" class="list-group col">
                                    @foreach ($stores as $key => $value)
                                        <div class="list-group-item" id="{{ $value->id }}"><h6>{{ $value->name }}-{{ $value->company_name }}</h6><input type="hidden" name="stores[]" value="{{ $value->id }}"></div>
                                    @endforeach
                                </div>	
                            </div>
                            
                            <div class="col-sm-6">
                                
                                <form action="{{ route('fleet-create-instant-model-store-group') }}" method="POST">
                                    @csrf
                                    <label>Group Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Name" required>
                                    <br>
                                    <label>Group Hub</label>
                                    <select class="form-control" name="fk_hub_id" required>
                                        <option value="" selected>--Select--</option>
                                        @foreach ($stores as $key => $value)
                                            <option value="{{ $value->id }}">{{ $value->name }}-{{ $value->company_name }}</option>
                                        @endforeach
                                    </select>
                                    <br>
                                    <p class="col-12">Drag and drop stroes to this container to create a new group</p>
                                    <div id="listRight" class="list-group col" style="border: 3px dotted rgb(0, 0, 0); padding: 50px;"></div><br>
                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure want to create this stores group ?')">Create</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @php
            $instant_store_groups = \App\Model\InstantStoreGroup::join('stores','instant_store_groups.fk_hub_id','=','stores.id')
                ->select('instant_store_groups.*','stores.name as store_name','stores.company_name')
                ->where('instant_store_groups.deleted','=',0)->get();
        @endphp
        @if (count($instant_store_groups) > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card m-b-30">
                        <div class="card-body">
                            <div class="row">
                                <h5 class="col-12"><b>Groups</b></h5>
                                @foreach ($instant_store_groups as $key => $value)
                                <div class="col-sm-6">        
                                    <table class="table table-bordered" style="width:100%;">
                                        <thead>
                                            <tr>
                                                <td colspan="3"><b>Name : </b> {{ $value->name}}</td>
                                                <td colspan="3"><b>Hub : </b> {{ $value->store_name}}-{{ $value->company_name }}</h5></td>
                                                <td colspan="3"><a class="btn-sm btn-danger" href="<?= url('admin/fleet/instant_model/store_group/delete/'.$value->id) ?>" onclick="return confirm('Are you sure you want to remove this this group ?')">Delete</a></td>
                                                
                                            </tr>
                                            <tr>
                                                <th colspan="1">S.No.</th>
                                                <th colspan="6">Store</th>
                                            </tr>
                                        </thead>
                                        @php
                                            $instant_store_group_stores = \App\Model\InstantStoreGroupStore::join('instant_store_groups','instant_store_group_stores.fk_group_id', '=', 'instant_store_groups.id')
                                                ->leftJoin('stores','stores.id','=','instant_store_group_stores.fk_store_id')
                                                ->select('instant_store_groups.*','stores.name','stores.company_name')
                                                ->where('instant_store_group_stores.fk_group_id',$value->id)
                                                ->get();
                                        @endphp
                                        @foreach ($instant_store_group_stores as $key => $value)
                                            <tr>
                                                <td colspan="1">{{ $key+1 }}</td>
                                                <td colspan="6">{{ $value->name }}-{{ $value->company_name }}</td>
                                            </tr>
                                        @endforeach
                                        <tbody>
                                        </tbody>
                                    </table>
                                    <br>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <script src = "https://code.jquery.com/jquery-1.10.2.js"></script>
    <script src = "https://code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
    <script>
            $( "#listLeft" ).sortable({
               connectWith: "#listRight",
            });

            $( "#listRight" ).sortable({
               connectWith: "#listLeft",
            });
    </script>
@endsection
