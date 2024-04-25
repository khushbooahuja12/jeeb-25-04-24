@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Tag Bundles
                        <a href="<?= url('admin/tag_bundles/create') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Tag Bundles</li>
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
                                    <th>Bundle Name (Eng)</th>
                                    <th>Bundle Name (Arb)</th>
                                    <th>Tags</th>
                                    <th>Bundle Image</th>
                                    <th>Banner Image</th>
                                    <th class="nosort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @if ($bundles)
                                    @foreach ($bundles as $key => $row)
                                        @php 
                                            $tags_arr = [];
                                        @endphp
                                        @foreach ($row->getBundleTags as $bundle_tags )
                                            @php  $tags_arr[] = $bundle_tags->getProductTags->title_en; @endphp
                                        @endforeach
                                        <tr>
                                            <td width="5%">{{ $key + 1 }}</td>
                                            <td>{{ $row->name_en }}</td>
                                            <td>{{ $row->name_ar }}</td>
                                            <td>{{ implode(",",$tags_arr) }}</td>
                                            <td>{!! $row->tag_image ? '<img src="'.$row->tag_image.'" style="height: 50px; width: auto; max-width: 100px;"/>' : 'N/A' !!}</td>
                                            <td>{!! $row->banner_image ? '<img src="'.$row->banner_image.'" style="height: 50px; width: auto; max-width: 100px;"/>' : 'N/A' !!}</td>
                                            <td>                                                
                                                <a href="<?= url('admin/tag_bundles/edit/' . base64url_encode($row->id)) ?>"><i
                                                        class="icon-pencil"></i></a>&ensp;
                                                <a title="Delete"
                                                    href="<?= url('admin/tag_bundles/destroy/' . base64url_encode($row->id)) ?>"
                                                    onclick="return confirm('Are you sure you want to delete this bundle ?')"><i
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
