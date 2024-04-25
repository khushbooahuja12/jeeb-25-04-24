@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Recipe Diets
                        <a href="<?= url('admin/recipe_diets/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Recipe Diets</li>
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
                        <table class="table table-bordered dt-responsive nowrap"
                            style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                            <thead>
                                <tr>
                                    <th width="5%">S.No.</th>
                                    <th>Diet Title (Eng)</th>
                                    <th>Diet Title (Arb)</th>
                                    <th>Tag</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($diets)
                                    @foreach ($diets as $key => $row)
                                        <tr>
                                            <td width="5%">{{ $key + 1 }}</td>
                                            <td>{{ $row->title_en }}</td>
                                            <td>{{ $row->title_ar }}</td>
                                            <td>{{ $row->tag }}</td>
                                            <td>                                                
                                                <a href="<?= url('admin/recipe_diets/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                <a title="Delete"
                                                    href="<?= url('admin/recipe_diets/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to delete this diet ?')"><i
                                                        class="icon-trash-bin"></i></a>
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
