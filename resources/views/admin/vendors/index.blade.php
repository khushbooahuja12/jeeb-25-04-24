@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Vendor List</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/vendors'); ?>">Vendors</a></li>
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
                    <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Vendor Name</th>
                                <th>Email </th>
                                <th>Mobile</th>   
                                <th>Company Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($vendors)
                            @foreach($vendors as $key=>$row)
                            <tr class="vendor<?= $row->id; ?>">
                                <td>{{$key+1}}</td>
                                <td>{{$row->name}}</td>
                                <td>{{$row->email}}</td>
                                <td>{{$row->mobile}}</td>
                                <td>{{$row->company_name}}</td>
                                <td>
                                    @if($row->status==0)
                                    <p class="text-warning">Account not verified</p>
                                    @elseif($row->status == 1)
                                    <p class="text-primary">Not approved</p>
                                    @elseif($row->status == 2)
                                    <p class="text-success">Active</p>
                                    @elseif($row->status == 3)
                                    <p class="text-danger">Blocked</p>
                                    @elseif($row->status == 4)
                                    <p class="text-danger">Rejected</p>
                                    @endif

                                    @if($row->status > 1 && $row->status != 4)
                                    <div class="mytoggle">
                                        <label class="switch">
                                            <input class="switch-input set_status<?= $row->id; ?>" type="checkbox" value="{{$row->status}}" <?= $row->status == 2 ? 'checked' : ''; ?> 
                                                   id="<?= $row->id; ?>" 
                                                   onchange="checkStatus(this)">
                                            <span class="slider round"></span> 
                                        </label>
                                    </div>
                                    @endif
                                </td>
                                <td>
                                    <a title="edit" href="<?= url('admin/vendors/edit/' . base64url_encode($row->id)); ?>"><i class="icon-pencil"></i></a>
                                    <a title="view details" href="<?= url('admin/vendors/show/' . base64url_encode($row->id)); ?>"><i class="fa fa-eye"></i></a>
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
<script>
    function checkStatus(obj) {
        var id = $(obj).attr("id");

        var checked = $(obj).is(':checked');
        if (checked == true) {
            var status = 2;
            var statustext = 'activate';
        } else {
            var status = 3;
            var statustext = 'block';
        }

        if (confirm("Are you sure, you want to " + statustext + " this vendor ?")) {
            $.ajax({
                url: '<?= url('admin/change_status'); ?>',
                type: 'post',
                dataType: 'json',
                data: {method: 'changeVendorStatus', status: status, id: id},
                cache: false,
            }).done(function (response) {
                if (response.status_code == 200) {
                    var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                            + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                            + '<strong>Status updated successfully</strong>.'
                            + '</div>';
                    $(".ajax_alert").html(success_str);

                    $('.vendor' + id).find("p").text(response.result.status);
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
            } else if (status == 3) {
                $(".set_status" + id).prop('checked', true);
            }
        }
    }
</script>
@endsection