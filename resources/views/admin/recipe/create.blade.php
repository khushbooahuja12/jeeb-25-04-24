@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Add New Recipe</h4>
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
        <form method="post" id="addForm" enctype="multipart/form-data" action="{{ route('admin.recipe.store') }}">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-b-30">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Name (En)</label>
                                        <input type="text" name="recipe_name_en" class="form-control"
                                            placeholder="Name (Eng)">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Name (Ar)</label>
                                        <input type="text" name="recipe_name_ar" class="form-control"
                                            placeholder="Name (Arb)">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Duration (mins)</label>
                                        <input type="text" name="duration" class="form-control numericOnly" placeholder="Duration">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Tags</label>
                                        <input type="text" name="tags" class="form-control" placeholder="Tags">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Home Tag (En)</label>
                                        <input type="text" name="homepage_tag_en" class="form-control" placeholder="Home Tag (En)">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Home Tag (Ar)</label>
                                        <input type="text" name="homepage_tag_ar" class="form-control" placeholder="Home Tag (Ar)">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Nutrition (in kcal)</label>
                                        <input type="text" name="nutrition" class="form-control numericOnly" placeholder="Nutrition">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Categories</label>
                                        <select class="form-control select2" name="categories[]" multiple>
                                            @if ($categories)
                                                @foreach ($categories as $key => $value)
                                                    <option name="{{ $value->id }}" value="{{ $value->id }}">{{ $value->name_en }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Description (En)</label>
                                        <textarea type="text" name="recipe_desc_en" rows="5" class="form-control" placeholder="Description (En)"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Description (Ar)</label>
                                        <textarea type="text" name="recipe_desc_ar" rows="5" class="form-control" placeholder="Description (Ar)"></textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Recipe Image</label>
                                        <input type="file" name="recipe_img" class="form-control dropify"
                                            accept="image/*">
                                    </div>
                                </div>
                            </div>
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
