@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Storekeepers [{{ $store->company_name}} ]</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                    <li class="breadcrumb-item"><a
                            href="#">Storekeepers</a>
                    </li>
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
                    
                    <table class="table table-bordered dt-responsive"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>@sortablelink('name', 'Name')</th>
                                <th>@sortablelink('email', 'Email')</th>
                                <th>@sortablelink('mobile', 'Mobile')</th>
                                <th>@sortablelink('address', 'Address')</th>
                                <th>Default</th>
                                <th class="nosort">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($storekeepers)
                                @foreach ($storekeepers as $key => $row)
                                    <tr class="hide<?= $row->id ?>">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $row->name . ' (' . $row->id . ')' }}</td>
                                        <td>{{ $row->email }}</td>
                                        <td>{{ '+' . $row->country_code . ' ' . $row->mobile }}</td>
                                        <td>{{ $row->address }}</td>
                                        <td>
                                            <div class="mytoggle">
                                                <label class="switch">
                                                    <input class="switch-input set_default<?= $row->id ?>"
                                                        type="checkbox" value="{{ $row->default }}"
                                                        <?= $row->default == 1 ? 'checked' : '' ?>
                                                        id="<?= $row->id ?>" onchange="makeDefault(this)">
                                                    <span class="slider round"></span>
                                                </label>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="<?= url('admin/store/' . $store->id . '/storekeepers/detail/' . base64url_encode($row->id)) ?>"><i class="fa fa-eye"></i>
                                            </a>
                                            &ensp;
                                            <a>
                                                <button
                                                    class="btn btn-<?= $row->is_available ? 'success' : 'danger' ?> btn-sm"><?= $row->is_available ? 'Available' : 'Occupied' ?>
                                                </button>
                                            </a>
                                            <a href="javascript:void" onclick="UnassignStorekeeper(<?= $row->id ?>)">
                                                <button class="btn btn-danger btn-sm"
                                                    <?= $row->is_available ? '' : 'disabled' ?>
                                                    style="cursor:<?= $row->is_available ? 'pointer' : 'not-allowed' ?>">Unassign
                                                </button>
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
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Subcategory Storekeeper Assigning</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body">
                    
                    <table class="table table-bordered dt-responsive"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>@sortablelink('name', 'Parent Category')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($categories)
                                @foreach ($categories as $key => $row)
                                    <tr class="hide<?= $row->id ?>">
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $row->category_name_en . ' (' . $row->id . ')' }}</td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>@sortablelink('name', 'Sub categroy')</th>
                                                        <th>Storekeeper</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($row->getSubCategory as $key => $sub_category)
                                                    @php
                                                        $added_storekeepers = \App\Model\StorekeeperSubcategory::where('fk_sub_category_id', '=', $sub_category->id)->pluck('fk_storekeeper_id')->toArray();
                                                    @endphp
                                                        <tr>
                                                            <form class="subcategories_storekeeper" method="post">
                                                                @csrf
                                                                <input type="hidden" name="fk_sub_category_id" id="fk_sub_category_id" value="{{ $sub_category->id }}">
                                                                <td>{{ $key+1 }} </td>
                                                                <td style="width:30%">{{ $sub_category->category_name_en }} ({{ $sub_category->id }}) </td>
                                                                <td style="width:70%">
                                                                    <select class="form-control select2" name="storekeepers[]" multiple>
                                                                        @if ($storekeepers)
                                                                            @foreach ( $storekeepers as $storekeeper)
                                                                                <option value="{{ $storekeeper->id }}" {{ in_array($storekeeper->id,$added_storekeepers) ? 'selected' : '' }}>{{ $storekeeper->name }}</option>
                                                                            @endforeach
                                                                        @endif
                                                                    </select>
                                                                </td>
                                                                <td><button type="submit" class="btn-sm btn-primary">Update</button></td>
                                                            </form>
                                                            
                                                        </tr>
                                                    
                                                </tbody>
                                                @endforeach
                                            </table>
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
    function makeDefault(obj) {
        var id = $(obj).attr("id");

        var checked = $(obj).is(':checked');
        if (checked == true) {
            var status = 1;
            var statustext = 'default';
        } else {
            var status = 0;
            var statustext = 'non-default';
        }

        if (confirm("Are you sure, you want to " + statustext + " this storekeeper ?")) {
            $.ajax({
                url: '<?= url('admin/change_status') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    method: 'makeStorekeeperDefault',
                    status: status,
                    id: id
                },
                cache: false,
            }).done(function(response) {
                if (response.status_code == 200) {
                    var success_str =
                        '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>Storekeeper default updated successfully</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(success_str);

                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                }else if (response.status_code == 201) {
                    var success_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>Store must have a default storekeeper</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(success_str);

                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>Some error found</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        } else {
            if (status == 0) {
                $(".set_status" + id).prop('checked', true);
            } else {
                $(".set_status" + id).prop('checked', false);
            }
        }
    }

    function UnassignStorekeeper(storekeeper_id) {
            if (confirm("Are you sure you want to un-assign this storekeeper?")) {
                $.ajax({
                    url: '<?= url('store/' . $store->id . '/unassign_storekeeper') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        storekeeper_id: storekeeper_id
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code == 200) {
                        var success_str =
                            '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>' + response.message + '</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(success_str);

                        $(".hide" + storekeeper_id).remove();
                    } else {
                        alert(response.message);
                    }
                });
            }
        }
 
    $(document).ready(function() {
        $('.select2').select2();
    });

    $(".subcategories_storekeeper").on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '<?= url('admin/storekeepers/update_subcategories_storekeeper') ?>',
            type: 'post',
            dataType: 'json',
            data: $(this).serialize(),
            cache: false,
        }).done(function(response) { console.log(response);
            if (response.status_code == 200) {
                var success_str =
                    '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                    '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                    '<strong>' + response.message + '</strong>.' +
                    '</div>';
                $(".ajax_alert").html(success_str);
                setTimeout(function() {
                        window.location.reload();
                    }, 1000);
            } else {
                var error_str =
                    '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                    '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                    '<strong>' + response.message + '</strong>.' +
                    '</div>';
                $(".ajax_alert").html(error_str);
            }
        });
    });
    
</script>

@endsection
