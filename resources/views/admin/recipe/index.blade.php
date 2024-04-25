@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Recipes
                        <a href="<?= url('admin/recipes/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/recipes') ?>">Recipes</a></li>
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
                        <form class="form-inline searchForm" method="GET">
                            <div class="form-group mb-2">
                                <input type="text" class="form-control" name="filter" placeholder="Search Recipe"
                                    value="{{ $filter }}">
                            </div>
                            <button type="submit" class="btn btn-dark">Filter</button>
                        </form>
                        <table class="table table-bordered dt-responsive"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>@sortablelink('recipe_name_en', 'Name (En)')</th>
                                    <th>@sortablelink('recipe_name_ar', 'Name (Ar)')</th>
                                    {{-- <th>Tags</th> --}}
                                    <th>Variants Created</th>
                                    <th class="nosort">Image</th>
                                    <th>Is recommended ?</th>
                                    <th>Is featured ?</th>
                                    <th>Is home ?</th>
                                    <th>Is veg ?</th>
                                    <th>Is active ?</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($recipes)
                                    @foreach ($recipes as $key => $row)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $row->id }}</td>
                                            <td>{{ $row->recipe_name_en }}</td>
                                            <td>{{ $row->recipe_name_ar }}</td>
                                            {{-- <td>{{ $row->_tags }}</td> --}}
                                            <td>{{ $row->variants ? $row->variants->count() : 0 }}</td>
                                            <td>{{ $row->recipe_img }}</td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'recommended' ?>"
                                                            type="checkbox" value="recommended"
                                                            <?= $row->recommended == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'featured' ?>"
                                                            type="checkbox" value="featured"
                                                            <?= $row->is_featured == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'home' ?>" type="checkbox"
                                                            value="home" <?= $row->is_home == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'veg' ?>" type="checkbox"
                                                            value="veg" <?= $row->veg == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mytoggle">
                                                    <label class="switch">
                                                        <input class="switch-input <?= $row->id . 'active' ?>" type="checkbox"
                                                            value="active" <?= $row->active == 1 ? 'checked' : '' ?>
                                                            id="<?= $row->id ?>" onchange="checkHomeType(this)">
                                                        <span class="slider round"></span>
                                                    </label>
                                                </div>
                                            </td>
                                            {{-- <td><img src="{{ !empty($row->getBannerImage) ? asset('images/banner_images') . '/' . $row->getBannerImage->file_name : asset('assets/images/no_img.png') }}"
                                                    width="75" height="50"></td> --}}
                                            <td>
                                                <a href="<?= url('admin/recipes/edit/' . $row->id) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                <a title="Remove Recipe"
                                                    href="<?= url('admin/recipes/destroy/' . $row->id) ?>"
                                                    onclick="return confirm('Are you sure you want to remove this recipe ?')"><i
                                                        class="icon-trash-bin"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                    Showing {{ $recipes->count() }} of {{ $recipes->total() }} entries.
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate" style="float:right">
                                    {{ $recipes->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function checkHomeType(obj) {
            var id = $(obj).attr("id");
            var value = $(obj).val();

            var checked = $(obj).is(':checked');
            if (checked == true) {
                var is_switched_on = 1;
            } else {
                var is_switched_on = 0;
            }

            $.ajax({
                url: '<?= url('admin/recipes/set_featured_recipe') ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    id: id,
                    is_switched_on: is_switched_on,
                    value: value
                },
                cache: false,
            }).done(function(response) {
                if (response.status_code === 200) {

                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".ajax_alert").html(error_str);
                }
            });
        }
    </script>
@endsection
