@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Recent searched keywords</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/products'); ?>">Products</a></li>
                    <li class="breadcrumb-item active">Recent keywords</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="ajax_alert"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="card m-b-30">                
                <div class="card-body">
                    <table id="datatable" class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>                               
                                <th>Keyword</th>
                                <th>By User</th>                               
                            </tr>
                        </thead>
                        <tbody>
                            @if($recent_searches)
                            @foreach($recent_searches as $key=>$row)
                            <tr class="product<?= $row->id; ?>">
                                <td>{{$key+1}}</td>
                                <td>{{$row->search_text}}</td>                               
                                <td>
                                    <a href="<?= url('admin/users/show') . '/' . base64url_encode($row->fk_user_id); ?>" target="_blank">
                                        {{$row->getUser?$row->getUser->name:'N/A'}}
                                    </a>
                                </td>                                                               
                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection