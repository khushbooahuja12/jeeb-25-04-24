@extends('admin.layouts.dashboard_layout_for_recipe_admin')
@section('content')
<style>
    /* .row_hide {
        display: none;
    } */
    .text_mode {
        display: block;
    }
    .edit_mode {
        display: none;
    }
    .row_edit {
        display: none;
    }
    .row_saving {
        display: none;
    }
    .row_save {
        display: block;
    }
    .row_deleting {
        display: none;
    }
    .row_delete {
        display: block;
    }
    .edit_response {
        display: none;
    }
    .row_add_saving {
        display: none;
    }
    .row_add_save {
        display: none;
    }
    .row_add {
        display: block;
    }
    .edit_response {
        display: none;
    }
    .row_btn {
        width: 70px;
        height: 55px;
    }
    /* Steps */
    .row_saving_step {
        display: none;
    }
    .row_save_step {
        display: block;
    }
    .row_deleting_step {
        display: none;
    }
    .row_delete_step {
        display: block;
    }
    .edit_response_step {
        display: none;
    }
    .row_add_saving_step {
        display: none;
    }
    .row_add_save_step {
        display: none;
    }
    .row_add_step {
        display: block;
    }
    /* Variants */
    .row_saving_variant {
        display: none;
    }
    .row_save_variant {
        display: block;
    }
    .row_deleting_variant {
        display: none;
    }
    .row_delete_variant {
        display: block;
    }
    .edit_response_variant {
        display: none;
    }
    .row_add_saving_variant {
        display: none;
    }
    .row_add_save_variant {
        display: none;
    }
    .row_add_variant {
        display: block;
    }
</style>
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Edit Recipe</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/recipes') ?>">Recipes</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="ajax_alert"></div>
        <form method="post" id="addForm" enctype="multipart/form-data"
            action="{{ route('admin.recipe.update', [$recipe->id]) }}">
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
                                            placeholder="Name (Eng)" value="{{ $recipe->recipe_name_en }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Name (Ar)</label>
                                        <input type="text" name="recipe_name_ar" class="form-control"
                                            placeholder="Name (Arb)" value="{{ $recipe->recipe_name_ar }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Duration (mins)</label>
                                        <input type="text" name="duration" class="form-control numericOnly" placeholder="Duration"
                                            value="{{ $recipe->duration }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Tags</label>
                                        <input type="text" name="tags" class="form-control" placeholder="Tags"
                                            value="{{ $recipe->_tags }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Home Tag (En)</label>
                                        <input type="text" name="homepage_tag_en" class="form-control" placeholder="Home Tag (En)"
                                        value="{{ $recipe->homepage_tag_en }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Home Tag (Ar)</label>
                                        <input type="text" name="homepage_tag_ar" class="form-control" placeholder="Home Tag (Ar)"
                                        value="{{ $recipe->homepage_tag_ar }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Nutrition (in kcal)</label>
                                        <input type="text" name="nutrition" class="form-control numericOnly" placeholder="Nutrition"
                                            value="{{ $recipe->nutrition }}">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Categories</label>
                                        <select class="form-control select2" name="categories[]" multiple>
                                            @if ($categories)
                                                @foreach ($categories as $key => $value)
                                                    <option value="{{ $value->id }}"
                                                        <?= in_array($value->id, $added_categories) ? 'selected' : '' ?>>
                                                        {{ $value->name_en }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Description (En)</label>
                                        <textarea type="text" name="recipe_desc_en" rows="5" class="form-control" placeholder="Description (En)">{{ $recipe->recipe_desc_en }}</textarea>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label>Description (Ar)</label>
                                        <textarea type="text" name="recipe_desc_ar" rows="5" class="form-control" placeholder="Description (Ar)">{{ $recipe->recipe_desc_ar }}</textarea>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label>Recipe Image</label>
                                        <input type="file" name="recipe_img" data-show-remove="false"
                                            data-default-file="{{ !empty($recipe->getRecipeImage) ? asset('/') . $recipe->getRecipeImage->file_path . $recipe->getRecipeImage->file_name : asset('assets/images/no_img.png') }}"
                                            class="form-control dropify" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <div>
                                            <button type="submit" class="btn btn-primary waves-effect waves-light">
                                                Update
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
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-12">
                    <div class="alert alert-danger" role="alert">
                        Please note if you update any ingredient or pantry items, you should save the steps and variants again to reflect in the Apps.
                    </div>
                </div>
            </div>
        </div>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Recipe Ingredients 
                        {{-- <button title="Show" href="javascript:void(0);" data-target="RecipeIngredients" class="row_show btn btn-success waves-effect">Show</button> --}}
                    </h4>
                </div>
            </div>
        </div>
        <div class="row row_hide" id="RecipeIngredients">
            <div class="col-lg-12">
                <div class="card m-b-30 ingredient_tag_rows" style="border: 1px solid black;padding:5px; overflow-x: scroll;">
                    @php
                    $ingredient_tag_no = 1;
                    @endphp
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>ID</th>
                                <th>Ingredient (En)</th>
                                <th>Ingredient (Ar)</th>
                                <th>Tag</th>
                                <th>Image</th>
                                <th class="nosort">Save</th>
                                <th class="nosort">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($ingredient_tags)
                                @foreach ($ingredient_tags as $key => $value)
                                    @if ($value->pantry_item==0)
                                    <tr>
                                        <td>{{ $ingredient_tag_no }}</td>
                                        <td>{{ $value->id }}</td>
                                        <form name="form_<?= $value->id ?>" id="form_<?= $value->id ?>" enctype="multipart/form-data">
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_en"
                                                    placeholder="Ingredient (En)" value="{{ $value->desc_en }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_ar"
                                                    placeholder="Ingredient (Ar)" value="{{ $value->desc_ar }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_tag"
                                                    placeholder="Tag" value="{{ $value->tag }}">
                                            </td>
                                            <td>
                                                <input type="hidden" class="form-control" name="ingredient_img_url" value="{{ $value->image_url}}">
                                                @if (!empty($value->image_url))
                                                <img src="{{ $value->image_url }}" class="form-control form-control-imageshow"/>
                                                @endif
                                                <input type="file" name="ingredient_img" accept="image/*">
                                            </td>
                                            <td>
                                                <input type="hidden" class="form-control" name="pantry_item" value="{{ $value->pantry_item }}">
                                                <input type="hidden" class="form-control" name="ingredient_id" value="{{ $value->id }}">
                                                <input type="hidden" class="form-control" name="recipe_id" value="{{ $value->recipe_id }}">
                                                <input type="hidden" class="form-control" name="product_id" value="{{ $value->product_id }}">
                                                <button title="Saving" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_saving row_saving_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                <button title="Save" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_save row_save_{{ $value->id }} btn btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                                <br/>
                                                <div class="edit_response edit_response_{{ $value->id }}"></div>
                                            </td>
                                            <td>
                                                <button title="Deleting" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_deleting row_deleting_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                <button title="Delete" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_delete row_delete_{{ $value->id }} btn btn-danger waves-effect"><i class="icon-check"></i> Delete</button>
                                                <br/>
                                                <div class="delete_response delete_response_{{ $value->id }}"></div>
                                            </td>
                                        </form>
                                    </tr>
                                    @php
                                    $ingredient_tag_no++;
                                    @endphp
                                    @endif
                                @endforeach
                            @endif
                            <tr>
                                <td>{{ $ingredient_tag_no }}</td>
                                <td></td>
                                <form name="form_add" id="form_add" enctype="multipart/form-data">
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_en"
                                            placeholder="Ingredient (En)" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_ar"
                                            placeholder="Ingredient (Ar)" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_tag"
                                            placeholder="Tag" value="">
                                    </td>
                                    <td>
                                        <input type="file" name="ingredient_img" accept="image/*">
                                    </td>
                                    <td>
                                        <input type="hidden" class="form-control" name="pantry_item" value="0">
                                        <input type="hidden" class="form-control" name="recipe_id" value="{{ $recipe->id }}">
                                        <button title="Add" href="javascript:void(0);" id="" class="row_btn row_add btn btn-warning waves-effect"><i class="icon-pencil"></i> Add</button>
                                        <button title="Saving" href="javascript:void(0);" id="" class="row_btn row_add_saving row_saving btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                        <br/>
                                        <div class="add_response "></div>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Recipe Pantry Items
                        {{-- <button title="Show" href="javascript:void(0);" data-target="RecipePantryItems" class="row_show btn btn-success waves-effect">Show</button> --}}
                    </h4>
                </div>
            </div>
        </div>
        <div class="row row_hide" id="RecipePantryItems">
            <div class="col-lg-12">
                <div class="card m-b-30 ingredient_tag_rows" style="border: 1px solid black;padding:5px; overflow-x: scroll;">
                    @php
                    $ingredient_tag_no = 1;
                    @endphp
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Product ID</th>
                                <th>Base Product ID</th>
                                <th>Base Product Store ID</th>
                                <th>Ingredient (En)</th>
                                <th>Ingredient (Ar)</th>
                                <th>Tag</th>
                                <th>Image</th>
                                <th>Unit</th>
                                <th>Price</th>
                                <th class="nosort">Save</th>
                                <th class="nosort">Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($ingredient_tags)
                                @foreach ($ingredient_tags as $key => $value)
                                    @if ($value->pantry_item==1)
                                    <tr>
                                        <td>{{ $ingredient_tag_no }}</td>
                                        <td>{{ $value->fk_product_id }}</td>
                                        <td>{{ $value->base_product_id }}</td>
                                        <td>{{ $value->base_product_store_id }}</td>
                                        <form name="form_<?= $value->id ?>" id="form_<?= $value->id ?>" enctype="multipart/form-data">
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_en"
                                                    placeholder="Ingredient (En)" value="{{ $value->desc_en }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_ar"
                                                    placeholder="Ingredient (Ar)" value="{{ $value->desc_ar }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_tag"
                                                    placeholder="Tag" value="{{ $value->tag }}">
                                            </td>
                                            <td>
                                                <input type="hidden" class="form-control" name="ingredient_img_url" value="{{ $value->image_url}}">
                                                @if (!empty($value->image_url))
                                                <img src="{{ $value->image_url }}" class="form-control form-control-imageshow"/>
                                                @endif
                                                <input type="file" name="ingredient_img" accept="image/*">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_unit"
                                                    placeholder="Unit" value="{{ $value->unit }}">
                                            </td>
                                            <td>
                                                <input type="text" class="form-control" name="ingredient_price"
                                                    placeholder="Price" value="{{ $value->price }}">
                                            </td>
                                            <td>
                                                <input type="hidden" class="form-control" name="pantry_item" value="{{ $value->pantry_item }}">
                                                <input type="hidden" class="form-control" name="ingredient_id" value="{{ $value->id }}">
                                                <input type="hidden" class="form-control" name="recipe_id" value="{{ $value->recipe_id }}">
                                                <input type="hidden" class="form-control" name="product_id" value="{{ $value->fk_product_id }}">
                                                <button title="Saving" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_saving row_saving_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                <button title="Save" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_save row_save_{{ $value->id }} btn btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                                <br/>
                                                <div class="edit_response edit_response_{{ $value->id }}"></div>
                                            </td>
                                            <td>
                                                <button title="Deleting" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_deleting row_deleting_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                                <button title="Delete" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_delete row_delete_{{ $value->id }} btn btn-danger waves-effect"><i class="icon-check"></i> Delete</button>
                                                <br/>
                                                <div class="delete_response delete_response_{{ $value->id }}"></div>
                                            </td>
                                        </form>
                                    </tr>
                                    @php
                                    $ingredient_tag_no++;
                                    @endphp
                                    @endif
                                @endforeach
                            @endif
                            <tr>
                                <td>{{ $ingredient_tag_no }}</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <form name="form_add_pantry_item" id="form_add_pantry_item" enctype="multipart/form-data">
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_en" placeholder="Ingredient (En)" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_ar" placeholder="Ingredient (Ar)" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_tag" placeholder="Tag" value="">
                                    </td>
                                    <td>
                                        <input type="file" name="ingredient_img" accept="image/*">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_unit" placeholder="Unit" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="ingredient_price" placeholder="Price" value="">
                                    </td>
                                    <td>
                                        <input type="hidden" class="form-control" name="pantry_item" value="1">
                                        <input type="hidden" class="form-control" name="recipe_id" value="{{ $recipe->id }}">
                                        <button title="Add" href="javascript:void(0);" id="" class="row_btn row_add_pantry btn btn-warning waves-effect"><i class="icon-pencil"></i> Add</button>
                                        <button title="Saving" href="javascript:void(0);" id="" class="row_btn row_add_pantry_saving row_saving btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                        <br/>
                                        <div class="add_response_pantry_item "></div>
                                    </td>
                                </form>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="page-title">Recipe Steps
                            {{-- <button title="Show" href="javascript:void(0);" data-target="RecipeSteps" class="row_show btn btn-success waves-effect">Show</button> --}}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="row row_hide" id="RecipeSteps">
                <div class="col-lg-12">
                    <div class="card m-b-30 step_rows" style="border: 1px solid black;padding:5px; overflow-x: scroll;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>ID</th>
                                    <th>Step (En)</th>
                                    <th>Step (Ar)</th>
                                    <th>Time</th>
                                    <th class="nosort">Save</th>
                                    <th class="nosort">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $step_no = 1;
                                @endphp
                                @if ($steps)
                                    @foreach ($steps as $key => $value)
                                    <form name="form_step_<?= $value->id ?>" id="form_step_<?= $value->id ?>" enctype="multipart/form-data">
                                    <tr>
                                        <td>{{ $step_no }}</td>
                                        <td>{{ $value->id }}</td>
                                        <td>
                                            <textarea class="form-control" name="step_en" placeholder="Step (En)">{{ $value->step_en }}</textarea>
                                        </td>
                                        <td>
                                            <textarea class="form-control" name="step_ar" placeholder="Step (Ar)">{{ $value->step_ar }}</textarea>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control numericOnly" name="time"
                                                placeholder="Time" value="{{ $value->time }}">
                                        </td>
                                        <td>
                                            <input type="hidden" class="form-control" name="recipe_id" value="{{ $value->recipe_id }}">
                                            <button title="Saving" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_saving_step row_saving_step_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                            <button title="Save" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_save_step row_save_step_{{ $value->id }} btn btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                            <br/>
                                            <div class="edit_response_step edit_response_step_{{ $value->id }}"></div>
                                        </td>
                                        <td>
                                            <button title="Deleting" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_deleting_step row_deleting_step_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                            <button title="Delete" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_delete_step row_delete_step_{{ $value->id }} btn btn-danger waves-effect"><i class="icon-check"></i> Delete</button>
                                            <br/>
                                            <div class="delete_response_step delete_response_step_{{ $value->id }}"></div>
                                        </td>
                                        @php
                                        $step_no++;
                                        @endphp
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td colspan="3">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Ingredient</th>
                                                        <th>Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($ingredient_tags)
                                                        @foreach ($ingredient_tags as $key2 => $value2)
                                                            @if ($value2->pantry_item==0)
                                                                @php
                                                                    $step_ingredient_checked = false;
                                                                    $step_ingredient_quantity = "";
                                                                    $step_ingredients = $value->ingredients ? json_decode($value->ingredients) : [];
                                                                    if ($step_ingredients) {
                                                                        foreach ($step_ingredients as $key3 => $value3) {
                                                                            if ($value3->id==$value2->id) {
                                                                                $step_ingredient_checked = true;
                                                                                $step_ingredient_quantity = $value3->quantity;
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="steps_ingredient[]" value="{{ $value2->id }}" {{ $step_ingredient_checked ? "checked" : "" }}> &ensp;<label>{{ $value2->desc_en }}</label> 
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_en_{{ $value2->id }}" value="{{ $value2->desc_en }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_ar_{{ $value2->id }}" value="{{ $value2->desc_ar }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_tag_{{ $value2->id }}" value="{{ $value2->tag }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_image_url_{{ $value2->id }}" value="{{ $value2->image_url }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_pantry_item_{{ $value2->id }}" value="{{ $value2->pantry_item }}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="form-control" name="steps_ingredient_quantity_{{ $value2->id }}" placeholder="Quantity" value="{{ $step_ingredient_quantity }}">
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                                <thead>
                                                    <tr>
                                                        <th>Pantry Items</th>
                                                        <th>Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($ingredient_tags)
                                                        @foreach ($ingredient_tags as $key2 => $value2)
                                                            @if ($value2->pantry_item==1)
                                                                @php
                                                                    $step_ingredient_checked = false;
                                                                    $step_ingredient_quantity = "";
                                                                    $step_ingredients = $value->ingredients ? json_decode($value->ingredients) : [];
                                                                    if ($step_ingredients) {
                                                                        foreach ($step_ingredients as $key3 => $value3) {
                                                                            if ($value3->id==$value2->id) {
                                                                                $step_ingredient_checked = true;
                                                                                $step_ingredient_quantity = $value3->quantity;
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="steps_ingredient[]" value="{{ $value2->id }}" {{ $step_ingredient_checked ? "checked" : "" }}> &ensp;<label>{{ $value2->desc_en }}</label> 
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_en_{{ $value2->id }}" value="{{ $value2->desc_en }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_ar_{{ $value2->id }}" value="{{ $value2->desc_ar }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_tag_{{ $value2->id }}" value="{{ $value2->tag }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_image_url_{{ $value2->id }}" value="{{ $value2->image_url }}">
                                                                        <input type="hidden" class="form-control" name="steps_ingredient_pantry_item_{{ $value2->id }}" value="{{ $value2->pantry_item }}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="form-control" name="steps_ingredient_quantity_{{ $value2->id }}" placeholder="Quantity" value="{{ $step_ingredient_quantity }}">
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    </form>
                                    @endforeach
                                @endif
                                <form name="form_add_step" id="form_add_step" enctype="multipart/form-data">
                                <tr>
                                    <td>{{ $step_no }}</td>
                                    <td></td>
                                    <td>
                                        <textarea class="form-control" name="step_en" placeholder="Step (En)"></textarea>
                                    </td>
                                    <td>
                                        <textarea class="form-control" name="step_ar" placeholder="Step (Ar)"></textarea>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="time"
                                            placeholder="Time" value="">
                                    </td>
                                    <td>
                                        <input type="hidden" class="form-control" name="recipe_id" value="{{ $recipe->id }}">
                                        <button title="Add" href="javascript:void(0);" id="" class="row_btn row_add_step btn btn-warning waves-effect"><i class="icon-pencil"></i> Add</button>
                                        <button title="Saving" href="javascript:void(0);" id="" class="row_btn row_add_saving_step row_saving btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                        <br/>
                                        <div class="add_response_step "></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td colspan="3">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Ingredient</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($ingredient_tags)
                                                    @foreach ($ingredient_tags as $key => $value)
                                                        @if ($value->pantry_item==0)
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox" name="steps_ingredient[]" value="{{ $value->id }}"> &ensp;<label>{{ $value->desc_en }}</label> 
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_en_{{ $value->id }}" value="{{ $value->desc_en }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_ar_{{ $value->id }}" value="{{ $value->desc_ar }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_tag_{{ $value->id }}" value="{{ $value->tag }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_image_url_{{ $value->id }}" value="{{ $value->image_url }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_pantry_item_{{ $value->id }}" value="{{ $value->pantry_item }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" name="steps_ingredient_quantity_{{ $value->id }}" placeholder="Quantity" value="">
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tbody>
                                            <thead>
                                                <tr>
                                                    <th>Pantry Items</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($ingredient_tags)
                                                    @foreach ($ingredient_tags as $key => $value)
                                                        @if ($value->pantry_item==1)
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox" name="steps_ingredient[]" value="{{ $value->id }}"> &ensp;<label>{{ $value->desc_en }}</label> 
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_en_{{ $value->id }}" value="{{ $value->desc_en }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_ar_{{ $value->id }}" value="{{ $value->desc_ar }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_tag_{{ $value->id }}" value="{{ $value->tag }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_image_url_{{ $value->id }}" value="{{ $value->image_url }}">
                                                                    <input type="hidden" class="form-control" name="steps_ingredient_pantry_item_{{ $value->id }}" value="{{ $value->pantry_item }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" name="steps_ingredient_quantity_{{ $value->id }}" placeholder="Quantity" value="">
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </form>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="page-title-box">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h4 class="page-title">Recipe Variants
                            {{-- <button title="Show" href="javascript:void(0);" data-target="RecipeVariants" class="row_show btn btn-success waves-effect">Show</button> --}}
                        </h4>
                    </div>
                </div>
            </div>
            <div class="row row_hide" id="RecipeVariants">
                <div class="col-lg-12">
                    <div class="card m-b-30 variant_rows" style="border: 1px solid black;padding:5px; overflow-x: scroll;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S.No.</th>
                                    <th>Product ID</th>
                                    <th>Base Product ID</th>
                                    <th>Base Product Store ID</th>
                                    <th>@sortablelink('serving', 'Serving (no. of persons)')</th>
                                    <th>Selling Price</th>
                                    <th class="nosort">Save</th>
                                    <th class="nosort">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $recipe_variant_no = 1;
                                @endphp
                                @if ($recipe_variants)
                                    @foreach ($recipe_variants as $key => $value)
                                    <form name="form_variant_<?= $value->id ?>" id="form_variant_<?= $value->id ?>" enctype="multipart/form-data">
                                    <tr>
                                        <td>{{ $recipe_variant_no }}</td>
                                        <td>{{ $value->fk_product_id }}</td>
                                        <td>{{ $value->base_product_id }}</td>
                                        <td>{{ $value->base_product_store_id }}</td>
                                        <td>
                                            <input type="text" class="form-control" name="serving"
                                                placeholder="Serving" value="{{ $value->serving }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="price"
                                                placeholder="Price" value="{{ $value->price }}">
                                        </td>
                                        <td>
                                            <input type="hidden" class="form-control" name="recipe_id" value="{{ $value->fk_recipe_id }}">
                                            <button title="Saving" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_saving_variant row_saving_variant_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                            <button title="Save" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_save_variant row_save_variant_{{ $value->id }} btn btn-success waves-effect"><i class="icon-check"></i> Save</button>
                                            <br/>
                                            <div class="edit_response_variant edit_response_variant_{{ $value->id }}"></div>
                                        </td>
                                        <td>
                                            <button title="Deleting" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_deleting_variant row_deleting_variant_{{ $value->id }} btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                            <button title="Delete" href="javascript:void(0);" id="{{ $value->id }}" class="row_btn row_delete_variant row_delete_variant_{{ $value->id }} btn btn-danger waves-effect"><i class="icon-check"></i> Delete</button>
                                            <br/>
                                            <div class="delete_response_variant delete_response_variant_{{ $value->id }}"></div>
                                        </td>
                                        @php
                                        $recipe_variant_no++;
                                        @endphp
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td colspan="2">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Ingredient</th>
                                                        <th>Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($ingredient_tags)
                                                        @foreach ($ingredient_tags as $key2 => $value2)
                                                            @if ($value2->pantry_item==0)
                                                                @php
                                                                    $variant_ingredient_tag_checked = false;
                                                                    $variant_ingredient_tag_quantity = "";
                                                                    $variant_ingredient_tags = $value->ingredients ? json_decode($value->ingredients) : [];
                                                                    if ($variant_ingredient_tags) {
                                                                        foreach ($variant_ingredient_tags as $key3 => $value3) {
                                                                            if ($value3->id==$value2->id) {
                                                                                $variant_ingredient_tag_checked = true;
                                                                                $variant_ingredient_tag_quantity = $value3->quantity;
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="variant_ingredients[]" value="{{ $value2->id }}" {{ $variant_ingredient_tag_checked ? "checked" : "" }}> &ensp;<label>{{ $value2->desc_en }}</label> 
                                                                        <input type="hidden" class="form-control" name="variant_ingredient_en_{{ $value2->id }}" value="{{ $value2->desc_en }}">
                                                                        <input type="hidden" class="form-control" name="variant_ingredient_ar_{{ $value2->id }}" value="{{ $value2->desc_ar }}">
                                                                        <input type="hidden" class="form-control" name="variant_ingredient_tag_{{ $value2->id }}" value="{{ $value2->tag }}">
                                                                        <input type="hidden" class="form-control" name="variant_ingredient_image_url_{{ $value2->id }}" value="{{ $value2->image_url }}">
                                                                        <input type="hidden" class="form-control" name="variant_ingredient_pantry_item_{{ $value2->id }}" value="{{ $value2->pantry_item }}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="form-control" name="variant_ingredient_quantity_{{ $value2->id }}" placeholder="Quantity" value="{{ $variant_ingredient_tag_quantity }}">
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                                <thead>
                                                    <tr>
                                                        <th>Pantry Items</th>
                                                        <th>Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if ($ingredient_tags)
                                                        @foreach ($ingredient_tags as $key2 => $value2)
                                                            @if ($value2->pantry_item==1)
                                                                @php
                                                                    $variant_pantry_item_checked = false;
                                                                    $variant_pantry_item_quantity = "";
                                                                    $variant_pantry_items = $value->pantry_items ? json_decode($value->pantry_items) : [];
                                                                    if ($variant_pantry_items) {
                                                                        foreach ($variant_pantry_items as $key3 => $value3) {
                                                                            if ($value3->id==$value2->id) {
                                                                                $variant_pantry_item_checked = true;
                                                                                $variant_pantry_item_quantity = $value3->quantity;
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td>
                                                                        <input type="checkbox" name="variant_pantry_items[]" value="{{ $value2->id }}" {{ $variant_pantry_item_checked ? "checked" : "" }}> &ensp;<label>{{ $value2->desc_en }}</label> 
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_en_{{ $value2->id }}" value="{{ $value2->desc_en }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_ar_{{ $value2->id }}" value="{{ $value2->desc_ar }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_tag_{{ $value2->id }}" value="{{ $value2->tag }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_image_url_{{ $value2->id }}" value="{{ $value2->image_url }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_pantry_item_{{ $value2->id }}" value="{{ $value2->pantry_item }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_price_{{ $value2->id }}" value="{{ $value2->price }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_unit_{{ $value2->id }}" value="{{ $value2->unit }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_product_id_{{ $value2->id }}" value="{{ $value2->fk_product_id }}">
                                                                        <input type="hidden" class="form-control" name="variant_pantry_item_base_product_id_{{ $value2->id }}" value="{{ $value2->base_product_id }}">
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="form-control" name="variant_pantry_item_quantity_{{ $value2->id }}" placeholder="Quantity" value="{{ $variant_pantry_item_quantity }}">
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    </form>
                                    @endforeach
                                @endif
                                <form name="form_add_variant" id="form_add_variant" enctype="multipart/form-data">
                                <tr>
                                    <td>{{ $recipe_variant_no }}</td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td>
                                        <input type="text" class="form-control" name="serving"
                                            placeholder="Serving" value="">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="price"
                                            placeholder="Price" value="">
                                    </td>
                                    <td>
                                        <input type="hidden" class="form-control" name="recipe_id" value="{{ $recipe->id }}">
                                        <button title="Add" href="javascript:void(0);" id="" class="row_btn row_add_variant btn btn-warning waves-effect"><i class="icon-pencil"></i> Add</button>
                                        <button title="Saving" href="javascript:void(0);" id="" class="row_btn row_add_saving_variant row_saving btn btn-success waves-effect"><img src="{{ asset("assets_v3/img/Pulse-1s-200px.gif") }}" style="max-width: 50px; height: auto;"/></button>
                                        <br/>
                                        <div class="add_response_variant "></div>
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td colspan="2">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Ingredient</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($ingredient_tags)
                                                    @foreach ($ingredient_tags as $key => $value)
                                                        @if ($value->pantry_item==0)
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox" name="variant_ingredients[]" value="{{ $value->id }}" checked="checked"> &ensp;<label>{{ $value->desc_en }}</label> 
                                                                    <input type="hidden" class="form-control" name="variant_ingredient_en_{{ $value->id }}" value="{{ $value->desc_en }}">
                                                                    <input type="hidden" class="form-control" name="variant_ingredient_ar_{{ $value->id }}" value="{{ $value->desc_ar }}">
                                                                    <input type="hidden" class="form-control" name="variant_ingredient_tag_{{ $value->id }}" value="{{ $value->tag }}">
                                                                    <input type="hidden" class="form-control" name="variant_ingredient_image_url_{{ $value->id }}" value="{{ $value->image_url }}">
                                                                    <input type="hidden" class="form-control" name="variant_ingredient_item_{{ $value->id }}" value="{{ $value->pantry_item }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" name="variant_ingredient_quantity_{{ $value->id }}" placeholder="Quantity" value="">
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tbody>
                                            <thead>
                                                <tr>
                                                    <th>Pantry Items</th>
                                                    <th>Quantity</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($ingredient_tags)
                                                    @foreach ($ingredient_tags as $key => $value)
                                                        @if ($value->pantry_item==1)
                                                            <tr>
                                                                <td>
                                                                    <input type="checkbox" name="variant_pantry_items[]" value="{{ $value->id }}" checked="checked"> &ensp;<label>{{ $value->desc_en }}</label> 
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_en_{{ $value->id }}" value="{{ $value->desc_en }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_ar_{{ $value->id }}" value="{{ $value->desc_ar }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_tag_{{ $value->id }}" value="{{ $value->tag }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_image_url_{{ $value->id }}" value="{{ $value->image_url }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_item_{{ $value->id }}" value="{{ $value->pantry_item }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_price_{{ $value->id }}" value="{{ $value->price }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_unit_{{ $value->id }}" value="{{ $value->unit }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_product_id_{{ $value->id }}" value="{{ $value->fk_product_id }}">
                                                                    <input type="hidden" class="form-control" name="variant_pantry_item_base_product_id_{{ $value->id }}" value="{{ $value->base_product_id }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control" name="variant_pantry_item_quantity_{{ $value->id }}" placeholder="Quantity" value="">
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </form>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>
    <script>
        // Show / hide recipe sections
        // $('.row_show').on('click',function(e){
        //     e.preventDefault();
        //     let id = $(this).data('target');
        //     $('#'+id).toggle();
        // });
        // Delete recipe ingredient
        $('.row_delete').on('click',function(e){
            e.preventDefault();
            let confirmed = confirm('Are you sure you want to remove this ?')
            if (confirmed) {
                let id = $(this).attr('id');
                $('.row_delete_'+id).hide();
                $('.row_deleting_'+id).show();
                $('.delete_response_'+id).hide();
                if (id) {
                    let form = $("#form_"+id);
                    let token = "{{ csrf_token() }}";
                    console.log(form);
                    let formData = new FormData(form[0]);
                    formData.append('id', id);
                    formData.append('_token', token);
                    $.ajax({
                        url: "<?= url('admin/recipes/delete_ingredient_save') ?>",
                        type: 'post',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.error_code == "200") {
                                $('.row_deleting_'+id).hide();
                                $('.row_delete_'+id).show();
                                if (data.data) {
                                    let row = data.data;
                                    $('.delete_response_'+id).html(data.message);
                                    $('.delete_response_'+id).show();
                                    location.reload();
                                }
                            } else {
                                alert(data.message);
                            }
                        }
                    });
                } else {
                    alert("Something went wrong");
                }
            }
        });

        // Add recipe ingredient
        $('.row_add').on('click',function(e){
            e.preventDefault();
            $('.row_add').hide();
            $('.row_add_saving').show();
            $('.row_add_save').hide();
            $('.add_response').hide();
            let form = $("#form_add");
            let token = "{{ csrf_token() }}";
            console.log(form);
            let formData = new FormData(form[0]);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/recipes/add_ingredient_save') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        // alert(data.message);
                        $('.row_add').hide();
                        $('.row_add_saving').hide();
                        $('.row_add_save').show();
                        if (data.data) {
                            let row = data.data;
                            $('.add_response').html(data.message);
                            $('.add_response').show();
                            location.reload();
                        }
                    } else {
                        $('.row_add').show();
                        $('.row_add_saving').hide();
                        $('.add_response').html(data.message);
                        $('.add_response').show();
                    }
                }
            });
        });

        // Add recipe ingredient pantry item
        $('.row_add_pantry').on('click',function(e){
            e.preventDefault();
            $('.row_add_pantry').hide();
            $('.row_add_pantry_saving').show();
            $('.row_add_pantry_save').hide();
            $('.add_response_pantry_item').hide();
            let form = $("#form_add_pantry_item");
            let token = "{{ csrf_token() }}";
            console.log(form);
            let formData = new FormData(form[0]);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/recipes/add_ingredient_save') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        // alert(data.message);
                        $('.row_add_pantry').hide();
                        $('.row_add_pantry_saving').hide();
                        $('.row_add_pantry_save').show();
                        if (data.data) {
                            let row = data.data;
                            $('.add_response_pantry_item').html(data.message);
                            $('.add_response_pantry_item').show();
                            location.reload();
                        }
                    } else {
                        $('.row_add_pantry').show();
                        $('.row_add_pantry_saving').hide();
                        $('.add_response_pantry_item').html(data.message);
                        $('.add_response_pantry_item').show();
                    }
                }
            });
        });

        // Edit recipe ingredient
        $('.row_save').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            $('.row_edit_'+id).hide();
            $('.row_saving_'+id).show();
            $('.row_save_'+id).hide();
            $('.edit_response_'+id).hide();
            if (id) {
                let form = $("#form_"+id);
                let token = "{{ csrf_token() }}";
                // let formData = form.serialize();
                // formData += formData+"&_token="+token;
                // formData += formData+"&id="+id;
                console.log(form);
                let formData = new FormData(form[0]);
                // var form_data = new FormData(document.getElementById("form_"+id));
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/recipes/edit_ingredient_save') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            // alert(data.message);
                            // location.reload();
                            $('.row_edit_'+id).hide();
                            $('.row_saving_'+id).hide();
                            $('.row_save_'+id).show();
                            if (data.data) {
                                let row = data.data;
                                $('.edit_response_'+id).html(data.message);
                                $('.edit_response_'+id).show();
                                location.reload();
                            }
                        } else {
                            $('.row_edit_'+id).hide();
                            $('.row_saving_'+id).hide();
                            $('.row_save_'+id).show();
                            $('.edit_response_'+id).html(data.message);
                            $('.edit_response_'+id).show();
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
        });

        // Delete recipe step
        $('.row_delete_step').on('click',function(e){
            e.preventDefault();
            let confirmed = confirm('Are you sure you want to remove this ?')
            if (confirmed) {
                let id = $(this).attr('id');
                $('.row_delete_step_'+id).hide();
                $('.row_deleting_step_'+id).show();
                $('.delete_response_step_'+id).hide();
                if (id) {
                    let form = $("#form_step_"+id);
                    let token = "{{ csrf_token() }}";
                    console.log(form);
                    let formData = new FormData(form[0]);
                    formData.append('id', id);
                    formData.append('_token', token);
                    $.ajax({
                        url: "<?= url('admin/recipes/delete_step_save') ?>",
                        type: 'post',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.error_code == "200") {
                                $('.row_deleting_step_'+id).hide();
                                $('.row_delete_step_'+id).show();
                                if (data.data) {
                                    let row = data.data;
                                    $('.delete_response_step_'+id).html(data.message);
                                    $('.delete_response_step_'+id).show();
                                    location.reload();
                                }
                            } else {
                                $('.row_deleting_step_'+id).hide();
                                $('.row_delete_step_'+id).show();
                                $('.delete_response_step_'+id).html(data.message);
                                $('.delete_response_step_'+id).show();
                            }
                        }
                    });
                } else {
                    alert("Something went wrong");
                }
            }
        });

        // Add recipe step
        $('.row_add_step').on('click',function(e){
            e.preventDefault();
            $('.row_add_step').hide();
            $('.row_add_saving_step').show();
            $('.row_add_save_step').hide();
            $('.add_response_step').hide();
            let form = $("#form_add_step");
            let token = "{{ csrf_token() }}";
            console.log(form);
            let formData = new FormData(form[0]);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/recipes/add_step_save') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        // alert(data.message);
                        $('.row_add_step').hide();
                        $('.row_add_saving_step').hide();
                        $('.row_add_save_step').show();
                        if (data.data) {
                            let row = data.data;
                            $('.add_response_step').html(data.message);
                            $('.add_response_step').show();
                            location.reload();
                        }
                    } else {
                        $('.row_add_step').show();
                        $('.row_add_saving_step').hide();
                        $('.row_add_save_step').show();
                        $('.add_response_step').html(data.message);
                        $('.add_response_step').show();
                    }
                }
            });
        });

        // Edit step
        $('.row_save_step').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            $('.row_saving_step_'+id).show();
            $('.row_save_step_'+id).hide();
            $('.edit_response_step_'+id).hide();
            if (id) {
                let form = $("#form_step_"+id);
                let token = "{{ csrf_token() }}";
                console.log(form);
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/recipes/edit_step_save') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            $('.row_saving_step_'+id).hide();
                            $('.row_save_step_'+id).show();
                            if (data.data) {
                                let row = data.data;
                                $('.edit_response_step_'+id).html(data.message);
                                $('.edit_response_step_'+id).show();
                            }
                        } else {
                            $('.row_saving_step_'+id).hide();
                            $('.row_save_step_'+id).show();
                            $('.edit_response_step_'+id).html(data.message);
                            $('.edit_response_step_'+id).show();
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
        });

        // Delete recipe variant
        $('.row_delete_variant').on('click',function(e){
            e.preventDefault();
            let confirmed = confirm('Are you sure you want to remove this ?')
            if (confirmed) {
                let id = $(this).attr('id');
                $('.row_delete_variant_'+id).hide();
                $('.row_deleting_variant_'+id).show();
                $('.delete_response_variant_'+id).hide();
                if (id) {
                    let form = $("#form_variant_"+id);
                    let token = "{{ csrf_token() }}";
                    console.log(form);
                    let formData = new FormData(form[0]);
                    formData.append('id', id);
                    formData.append('_token', token);
                    $.ajax({
                        url: "<?= url('admin/recipes/delete_variant_save') ?>",
                        type: 'post',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(data) {
                            if (data.error_code == "200") {
                                $('.row_deleting_variant_'+id).hide();
                                $('.row_delete_variant_'+id).show();
                                if (data.data) {
                                    let row = data.data;
                                    $('.delete_response_variant_'+id).html(data.message);
                                    $('.delete_response_variant_'+id).show();
                                    location.reload();
                                }
                            } else {
                                $('.row_deleting_variant_'+id).hide();
                                $('.row_delete_variant_'+id).show();
                                $('.delete_response_variant_'+id).html(data.message);
                                $('.delete_response_variant_'+id).show();
                            }
                        }
                    });
                } else {
                    alert("Something went wrong");
                }
            }
        });

        // Add recipe variant
        $('.row_add_variant').on('click',function(e){
            e.preventDefault();
            $('.row_add_variant').hide();
            $('.row_add_saving_variant').show();
            $('.row_add_save_variant').hide();
            $('.add_response_variant').hide();
            let form = $("#form_add_variant");
            let token = "{{ csrf_token() }}";
            console.log(form);
            let formData = new FormData(form[0]);
            formData.append('_token', token);
            $.ajax({
                url: "<?= url('admin/recipes/add_variant_save') ?>",
                type: 'post',
                data: formData,
                processData: false,
                contentType: false,
                success: function(data) {
                    if (data.error_code == "200") {
                        // alert(data.message);
                        $('.row_add_variant').hide();
                        $('.row_add_saving_variant').hide();
                        $('.row_add_variant_save').show();
                        if (data.data) {
                            let row = data.data;
                            $('.add_response_variant').html(data.message);
                            $('.add_response_variant').show();
                            location.reload();
                        }
                    } else {
                        $('.row_add_variant').show();
                        $('.row_add_saving_variant').hide();
                        $('.row_add_variant_save').show();
                        $('.add_response_variant').html(data.message);
                        $('.add_response_variant').show();
                    }
                }
            });
        });

        // Edit Variant
        $('.row_save_variant').on('click',function(e){
            e.preventDefault();
            let id = $(this).attr('id');
            $('.row_saving_variant_'+id).show();
            $('.row_save_variant_'+id).hide();
            $('.edit_response_variant_'+id).hide();
            if (id) {
                let form = $("#form_variant_"+id);
                let token = "{{ csrf_token() }}";
                console.log(form);
                let formData = new FormData(form[0]);
                formData.append('id', id);
                formData.append('_token', token);
                $.ajax({
                    url: "<?= url('admin/recipes/edit_variant_save') ?>",
                    type: 'post',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(data) {
                        if (data.error_code == "200") {
                            $('.row_saving_variant_'+id).hide();
                            $('.row_save_variant_'+id).show();
                            if (data.data) {
                                let row = data.data;
                                $('.edit_response_variant_'+id).html(data.message);
                                $('.edit_response_variant_'+id).show();
                            }
                        } else {
                            $('.row_saving_variant_'+id).hide();
                            $('.row_save_variant_'+id).show();
                            $('.edit_response_variant_'+id).html(data.message);
                            $('.edit_response_variant_'+id).show();
                        }
                    }
                });
            } else {
                alert("Something went wrong");
            }
        });


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
