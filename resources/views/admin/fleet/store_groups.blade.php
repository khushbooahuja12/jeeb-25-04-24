@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    .list-group-item {
        background: #000000; color: rgb(255, 255, 255); padding: 0.2rem 1.2rem; margin: 0.2em;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Store Groups
                        <!-- Button trigger modal -->
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#storeGroupCreateModal">
                            Create Group <i class="fa fa-plus"></i>
                        </button>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item">Fleet Management</a></li>
                        <li class="breadcrumb-item active">Store Groups</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>Group</th>
                                    <th>Company Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (\App\Model\StoreGroup::where('deleted','=',0)->groupBy('group_id')->get() as $key => $value)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $value->group_id }}</td>
                                    <td>{{ $value->group_name }}</td>
                                    <td>
                                        <table>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>ID</th>
                                                <th>Company</th>
                                            </tr>
                                            @foreach (\App\Model\StoreGroup::join('stores','stores.id','=','store_groups.fk_store_id')->select('store_groups.id','stores.name','stores.company_name')->where(['store_groups.group_id' => $value->group_id, 'store_groups.deleted' => 0])->get() as $key => $value1)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <td>{{ $value1->id }}</td>
                                                <td>{{ $value1->name }}-{{ $value1->company_name }}</td>
                                            </tr>
                                            @endforeach
                                        </table>
                                    </td>
                                    <td><button type="button" class="btn btn-primary storeGroupEdit" data-toggle="modal" data-target="#storeGroupUpdateModal" data-id="{{ $value->group_id }}">
                                        Edit </i>
                                    </button>&nbsp;<a href="{{ route('delete_store_group',['id' => $value->group_id ]) }}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this group?')" data-id="{{ $value->group_id }}">
                                        Delete </i>
                                    </button></a>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade bd-example-modal-xl" id="storeGroupCreateModal" tabindex="-1" role="dialog" aria-labelledby="storeGroupCreateModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="storeGroupCreateModalLabel">Create Store Groups</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if(count($stores) > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="card m-b-30">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div id="listLeft" class="list-group col">
                                                    @foreach ($stores as $key => $value)
                                                        <div class="list-group-item" id="{{ $value->id }}">
                                                            <h6>{{ $value->name }}-{{ $value->company_name }}</h6>
                                                            <input type="hidden" name="stores[]" value="{{ $value->id }}">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <form action="{{ url('admin/fleet/store_groups/create') }}" method="POST">
                                                    @csrf
                                                    <p class="col-12">Drag and drop stores to this container to create a new group</p>
                                                    <div id="listRight" class="list-group col-md-12" style="border: 3px dotted rgb(151, 151, 151); padding: 5%"></div><br>
                                                    <input type="text" class="form-control" name="group_name" placeholder="Enter Group Name" required><br>
                                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure want to create this stores group ?')">Submit</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade bd-example-modal-xl" id="storeGroupUpdateModal" tabindex="-1" role="dialog" aria-labelledby="storeGroupUpdateLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="storeGroupUpdateLabel">Edit Group Stores</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @if(count($stores) > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="card m-b-30">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div id="listLeft1" class="list-group col">
                                                    @foreach ($stores as $key => $store)
                                                        <div class="list-group-item" id="{{ $store->id }}">
                                                            <h6>{{ $store->name }}-{{ $store->company_name }}</h6>
                                                            <input type="hidden" name="stores[]" value="{{ $store->id }}">
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <form action="{{ url('admin/fleet/store_groups/update') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="group_id" id="group_id" value="{{ $value->group_id }}">
                                                    <p class="col-12">Drag and drop stores to this container to edit the group</p>
                                                    <div id="listRight1" class="list-group col-md-12" style="border: 3px dotted rgb(151, 151, 151); padding: 5%"></div><br>
                                                    <input type="text" class="form-control" name="group_name" id="edit_group_name" placeholder="Enter Group Name" required><br>
                                                    <button type="submit" class="btn btn-primary" onclick="return confirm('Are you sure want to create this stores group ?')">Submit</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- @if(count(\App\Model\StoreGroup::where('deleted','=',0)->groupBy('group_id')->get()) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="row">
                            @foreach (\App\Model\StoreGroup::where('deleted','=',0)->groupBy('group_id')->get() as $key => $value)
                            <div class="col-sm-6">        
                                <table class="table table-bordered" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <td colspan="3"><b>{{ $value->group_name}}</b></td>
                                        </tr>
                                        <tr>
                                            <th>S.No.</th>
                                            <th>Stores</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    @foreach (\App\Model\StoreGroup::join('stores','stores.id','=','store_groups.fk_store_id')->select('store_groups.id','stores.name','stores.company_name')->where(['store_groups.group_id' => $value->group_id, 'store_groups.deleted' => 0])->get() as $key => $value)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $value->name }}-{{ $value->company_name }}</td>
                                            <td><a class="btn-sm btn-danger" href="<?= url('admin/fleet/store_groups/delete_store/'.$value->id) ?>" onclick="return confirm('Are you sure you want to remove this store from this group ?')">Remove</a></td>
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
        @endif --}}
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

            $( "#listLeft1" ).sortable({
               connectWith: "#listRight1",
            });

            $( "#listRight1" ).sortable({
               connectWith: "#listLeft1",
            });
    </script>
    <script>
        $('.storeGroupEdit').on('click',function(){

            var group = $(this).data("id");

            $.ajax({
                url: '<?= url('admin/fleet/store_groups/edit') ?>',
                type: 'GET',
                data: {group: group},
                dataType: 'JSON',
                cache: false,
                success:function(response){  console.log(response);
                    var html = '';
                    $.each(response.stores, function(i, v) {
                        html += '<div class="list-group-item" id="'+v.id+'" style="position: relative; left: 0px; top: 0px;"><h6>'+v.name+'-'+v.company_name+'</h6><input type="hidden" name="stores[]" value="'+v.id+'"></div>';
                    });
                    $('#edit_group_name').val(response.group.group_name);
                    $('#group_id').val(response.group.group_id)
                    $('#listRight1').html(html);
                }
            })
        });
    </script>
@endsection
