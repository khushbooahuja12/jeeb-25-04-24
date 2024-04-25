@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Recipe
                        <a href="<?= url('admin/recipes/edit/'.$recipe->id.'/view_recipe_variant') ?>" class="btn btn-primary">
                            <i class="icon-eye"></i> View Variants
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
                            <input type="hidden" class="form-control" name="id" value="{{ $recipe->id }}">
                            <br/>
                            <div class="row">
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Serving (no. of persons)</label>
                                        <input type="text" name="serving" class="form-control numericOnly" placeholder="Serving">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Distributor Price (QAR)</label>
                                        <input type="text" name="distributor_price" class="form-control" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label>Selling Price (QAR)</label>
                                        <input type="text" name="price" class="form-control" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            <br clear="all"/>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Recipe Ingredients</label>
                                    <table class="table table-bordered dt-responsive"
                                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <tbody>
                                        @php
                                        $ingredient_tag_no = 1;
                                        @endphp
                                        @if ($ingredient_tags)
                                            @foreach ($ingredient_tags as $key => $value)
                                                @if ($value->pantry_item==0)
                                                    <tr>
                                                        <td>
                                                            @if (!empty($value->image_url))
                                                            <img src="{{ $value->image_url }}" style="width: auto; height:60px"/><br/><br/>
                                                            @endif
                                                            {{ $value->desc_en }}<br/>
                                                            {{ $value->desc_ar }}<br/>
                                                            <input type="hidden" class="form-control" name="ingredient_id[]" value="{{ $value->id }}">
                                                            <input type="hidden" class="form-control" name="ingredient_en[]" value="{{ $value->desc_en }}">
                                                            <input type="hidden" class="form-control" name="ingredient_ar[]" value="{{ $value->desc_ar }}">
                                                            <input type="hidden" class="form-control" name="ingredient_tag[]" value="{{ $value->tag }}">
                                                            <input type="hidden" class="form-control" name="ingredient_image_url[]" value="{{ $value->image_url }}">
                                                            <input type="hidden" class="form-control" name="ingredient_pantry_item[]" value="{{ $value->pantry_item }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="ingredient_quantity[]" placeholder="Quantity" value="">
                                                        </td>
                                                    </tr>
                                                    @php
                                                    $ingredient_tag_no++;
                                                    @endphp
                                                @endif
                                            @endforeach
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <br clear="all"/>
                            <div class="row">
                                <div class="col-lg-12">
                                    <label>Recipe Pantry Items</label>
                                    <table class="table table-bordered dt-responsive"
                                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <tbody>
                                        @php
                                        $pantry_item_tag_no = 1;
                                        @endphp
                                        @if ($ingredient_tags)
                                            @foreach ($ingredient_tags as $key => $value)
                                                @if ($value->pantry_item==1)
                                                    <tr>
                                                        <td>
                                                            @if (!empty($value->image_url))
                                                            <img src="{{ $value->image_url }}" style="width: auto; height:60px"/><br/><br/>
                                                            @endif
                                                            {{ $value->desc_en }}<br/>
                                                            {{ $value->desc_ar }}<br/>
                                                            <input type="hidden" class="form-control" name="pantry_item_id[]" value="{{ $value->id }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_en[]" value="{{ $value->desc_en }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_ar[]" value="{{ $value->desc_ar }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_tag[]" value="{{ $value->tag }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_image_url[]" value="{{ $value->image_url }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_pantry_item[]" value="{{ $value->pantry_item }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_price[]" value="{{ $value->price }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_unit[]" value="{{ $value->unit }}">
                                                            <input type="hidden" class="form-control" name="pantry_item_product_id[]" value="{{ $value->fk_product_id }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="pantry_item_quantity[]" placeholder="Quantity" value="">
                                                        </td>
                                                    </tr>
                                                    @php
                                                    $pantry_item_tag_no++;
                                                    @endphp
                                                @endif
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
            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-b-30">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div>
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                Submit
                                            </button>
                                        </div>
                                    </div>
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
