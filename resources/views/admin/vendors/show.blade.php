@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Vendor Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/vendors'); ?>">Vendors</a></li>
                    <li class="breadcrumb-item active">Detail</li>
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
                    <div class="row">
                        <div class=" offset-11 col-md-1">
                            <a href="<?= url('admin/vendors/edit/' . base64url_encode($vendor->id)); ?>" class="btn btn-primary waves-effect waves-light text-white">
                                Edit
                            </a>
                        </div>
                    </div> 
                    @csrf                        
                    <div class="row">
                        <div class="offset-3 col-md-9 justify-center">
                            <table class="table table-borderless table-responsive">
                                <tr>
                                    <th>Vendor Name </th>
                                    <td> {{$vendor->name}}</td>
                                </tr>
                                <tr>
                                    <th>Email </th>
                                    <td> {{$vendor->email}}</td>
                                </tr>
                                <tr>
                                    <th>Mobile  </th>
                                    <td> {{$vendor->mobile}}</td>
                                </tr>
                                <tr>
                                    <th>Company/Store Name  </th>
                                    <td> {{$vendor->company_name}}</td>
                                </tr>
                                <tr>
                                    <th>Address  </th>
                                    <td> {{$vendor->address}}</td>
                                </tr>
                                <tr>
                                    <th>City  </th>
                                    <td> {{$vendor->city}}</td>
                                </tr>
                                <tr>
                                    <th>State  </th>
                                    <td> {{$vendor->state}}</td>
                                </tr>
                                <tr>
                                    <th>Country  </th>
                                    <td> {{$vendor->country}}</td>
                                </tr>
                                <tr>
                                    <th>Pin  </th>
                                    <td>{{$vendor->pin}}</td>
                                </tr>
                                <tr>
                                    <th>Status  </th>
                                    <td class="upd_status"> 
                                        @if($vendor->status==0)
                                        <p class="text-warning">Account not verified</p>
                                        @elseif($vendor->status == 1)
                                        <p class="text-primary">Not approved</p>
                                        @elseif($vendor->status == 2)
                                        <p class="text-success">Active</p>
                                        @elseif($vendor->status == 3)
                                        <p class="text-danger">Blocked</p>
                                        @elseif($vendor->status == 4)
                                        <p class="text-danger">Rejected</p>
                                        @endif
                                    </td>
                                </tr>
                                @if($vendor->status == 1)
                                <tr>
                                    <td></td>
                                    <td>
                                        <button class="btn btn-success" value="2" id="<?= $vendor->id; ?>" 
                                                onclick="approve_reject_entity(this)">Approve</button>
                                        @if($vendor->status == 1)  
                                        <button class="btn btn-danger" value="4" id="<?= $vendor->id; ?>" 
                                                onclick="approve_reject_entity(this)">Reject</button>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                @if($vendor->status == 4)
                                <tr class="approvetr">
                                    <td></td>
                                    <td>
                                        <button class="btn btn-success" value="2" id="<?= $vendor->id; ?>" 
                                                onclick="approve_reject_entity(this)">Approve</button>                                        
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>     
    </div>
</div>
<script>
    function approve_reject_entity(obj) {
        var id = $(obj).attr('id');
        var status = $(obj).attr('value');

        if (status == 2) {
            var statustext = 'approve';
        } else if (status == 4) {
            var statustext = 'reject';
        }
        if (confirm("Are you sure, you want to " + statustext + " this vendor ?")) {
            $.ajax({
                url: '<?= url('admin/approve_reject_entity'); ?>',
                type: 'post',
                dataType: 'json',
                data: {type: 'vendor', status: status, id: id},
                cache: false,
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Status updated successfully</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);

                    $(obj).parent().parent('tr').remove();
                    $(".upd_status").text(response.result.status);
                    $(".approvetr").show();
                } else {
                    var error_str = '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Some error found</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        } else {
            if (status == 2) {
                $(".set_status" + id).prop('checked', false);
            } else if (status == 4) {
                $(".set_status" + id).prop('checked', true);
            }
        }
    }
</script>
@endsection