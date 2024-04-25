@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">View Recipe Variants
                        <a href="<?= url('admin/recipes/edit/'.$recipe->id.'/create_recipe_variant') ?>" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Create Variants
                        </a>
                        <a href="<?= url('admin/recipes/edit/'.$recipe->id) ?>" class="btn btn-primary">
                            <i class="icon-eye"></i> Go Back To Recipe
                        </a>
                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/recipes') ?>">Recipes</a></li>
                        <li class="breadcrumb-item active">Add</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <form method="post" id="addForm" enctype="multipart/form-data" action="{{ route('admin.recipe.store_recipe_variant') }}">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-b-30">
                        <div class="card-body">
                            <table class="table table-bordered dt-responsive"
                                style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name (En)</th>
                                        <th>Name (Ar)</th>
                                        <th>Image</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $recipe->id }}</td>
                                        <td>{{ $recipe->recipe_name_en }}</td>
                                        <td>{{ $recipe->recipe_name_ar }}</td>
                                        <td><img src="{{ !empty($recipe->getRecipeImage) ? asset('/') . $recipe->getRecipeImage->file_path . $recipe->getRecipeImage->file_name : asset('assets/images/no_img.png') }}" style="height: 60px; width: auto;"/></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Recipe Variants</label>
                                    <table class="table table-bordered dt-responsive"
                                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Product ID</th>
                                                <th>@sortablelink('serving', 'Serving (no. of persons)')</th>
                                                <th>Selling Price</th>
                                                <th>Ingredients</th>
                                                <th>Pantry Items</th>
                                                <th class="nosort">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        @if ($recipe_variants)
                                            @foreach ($recipe_variants as $key => $row)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $row->fk_product_id }}</td>
                                                    <td>{{ $row->serving }}</td>
                                                    <td>{{ $row->price }}</td>
                                                    <td>
                                                    @php
                                                        $ingredients = json_decode($row->ingredients);
                                                    @endphp
                                                    @if ($ingredients)
                                                        @foreach ($ingredients as $value)
                                                            {{ isset($value->desc_en) ? $value->desc_en : ''}}<br/>
                                                            {{ isset($value->desc_ar) ? $value->desc_ar : ''}}<br/>
                                                            @if (isset($value->image_url) && !empty($value->image_url))
                                                            <img src="{{ $value->image_url }}" style="width: auto; height:60px"/><br/>
                                                            @endif
                                                            {{ isset($value->quantity) ? $value->quantity : ''}}
                                                            <hr/>
                                                        @endforeach
                                                    @endif
                                                    {{-- {{ $row->ingredients }} --}}
                                                    </td>
                                                    <td>
                                                    @php
                                                        $pantry_items = json_decode($row->pantry_items);
                                                    @endphp
                                                    @if ($pantry_items)
                                                        @foreach ($pantry_items as $value)
                                                            {{ isset($value->desc_en) ? $value->desc_en : ''}}<br/>
                                                            {{ isset($value->desc_ar) ? $value->desc_ar : ''}}<br/>
                                                            @if (isset($value->image_url) && !empty($value->image_url))
                                                            <img src="{{ $value->image_url }}" style="width: auto; height:60px"/><br/>
                                                            @endif
                                                            {{ isset($value->quantity) ? $value->quantity : ''}}<br/>
                                                            Product ID: {{ isset($value->quantity) ? $value->product_id : ''}}<br/>
                                                            Product Price: {{ isset($value->quantity) ? $value->price : ''}}<br/>
                                                            Product Unit: {{ isset($value->quantity) ? $value->unit : ''}}<br/>
                                                            <hr/>
                                                        @endforeach
                                                    @endif
                                                    {{-- {{ $row->ingredients }} --}}
                                                    </td>
                                                    <td>
                                                        {{-- <a href="<?= url('admin/recipes/edit_recipe_variant' . $row->id) ?>"><i
                                                                class="icon-pencil"></i></a>&ensp; --}}
                                                        <a title="Remove Recipe Variant"
                                                            href="<?= url('admin/recipes/destroy_recipe_variant/' . $row->id) ?>"
                                                            onclick="return confirm('Are you sure you want to remove this recipe serving ?')"><i
                                                                class="icon-trash-bin"></i>
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
            </div>
        </form>
    </div>
    <script>
        var option_str = '';
        $(document).ready(function() {
            $('#addForm').validate({
                rules: {
                    name_en: {
                        required: true
                    },
                    name_ar: {
                        required: true
                    },
                    recipe_desc_en: {
                        required: true
                    },
                    recipe_desc_ar: {
                        required: true
                    },
                    recipe_img: {
                        required: true
                    }
                }
            });

            // var products_str = '';
            // $.ajax({
            //     url: '<?= url('admin/recipes/get_products') ?>',
            //     type: 'post',
            //     dataType: 'json',
            //     data: {},
            //     cache: false,
            // }).done(function(response) {
            //     if (response.result.products) {
            //         $.each(response.result.products, function(i, v) {
            //             products_str += '<option value="' + v.id + '">' + v.product_name_en +
            //                 '</option>';
            //         });
            //     }
            // });

            var return_first = function() {
                var tmp = '';
                $.ajax({
                    url: '<?= url('admin/recipes/get_products') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {},
                    cache: false,
                    'async': false,
                    success: function(response) {
                        if (response.result.products) {
                            $.each(response.result.products, function(i, v) {
                                tmp += '<option value="' + v.id + '">' + v.product_name_en +
                                    ' ' +
                                    '( ' + v.unit + ' )' +
                                    '</option>';
                            });
                        }
                    }
                });
                return tmp;
            }();
            option_str = return_first;
        });

    </script>
@endsection
