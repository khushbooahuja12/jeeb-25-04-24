@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Offer Formula Combinations</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="#"> Offers & Pricing Formula</a></li>
                    <li class="breadcrumb-item active">Offer Formula</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="ajax_alert"></div>

    <!-- Global Stores price formula -->

    <div class="row">
        <div class="col-md-12">
            <div class="card m-b-30">                               
                <div class="card-body">
                    <div class="mb-4">
                        <a href="<?= url('admin/products/filter_offer_formula?store_id=0&subcategory_id=0&brand_id=0'); ?>" class="btn btn-primary ">
                            <i class="fa fa-plus"></i> Add New Offer Formula
                        </a>
                    </div>
                        
                    <table class="table table-bordered dt-responsive"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <th><h5>Global Store</h5></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <tr>
                                    <td></td>
                                    <td style="text-align: center; "><h5>Offer Formula</h5></td>  
                                </tr>
                                <td></td>
                                <td>
                                    @php
                                    $global_store_offer_formula = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                        ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                        ->where('fk_store_id','=', 0)
                                        ->where('fk_offer_option_id','!=', 0)
                                        ->where('fk_brand_id','=', 0)
                                        ->where('fk_subcategory_id','=', 0)
                                        ->orderBy('x1', 'asc')
                                        ->get();
                                    @endphp
                                    @if(count($global_store_offer_formula) > 0)
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>Offer</th>
                                                    <th>Action</th>                               
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                                @foreach($global_store_offer_formula as $key => $value)
                                                <tr>
                                                    <td>{{$key+1}}</td>
                                                    <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                    <td>                                    
                                                        <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                        <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                    </td>                                
                                                </tr>   
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                </td>
                            </tr>

                            <!-- Global Store Brand-Subcategory -->

                            @php
                                $global_store_brand_subcategory_combinations = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
                                    ->leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
                                    ->select('product_price_formula.*','categories.category_name_en','brands.brand_name_en')
                                    ->where('fk_subcategory_id','!=', 0)
                                    ->where('fk_brand_id','!=', 0)
                                    ->where('fk_store_id','=', 0)
                                    ->where('fk_offer_option_id','!=', 0)
                                    ->groupBy('fk_subcategory_id','fk_brand_id')
                                    ->orderBy('x1', 'asc')
                                    ->get();
                            @endphp
                            @if(count($global_store_brand_subcategory_combinations) > 0)
                                <tr>
                                    <td></td>
                                    <td style="text-align: center; "><h5>Brands & Subcategories </h5></td>  
                                </tr>

                                <tr>
                                    <td></td>
                                    <td>
                                        @foreach ($global_store_brand_subcategory_combinations as $key => $value)
                                            
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th colspan="6">{{ $value->brand_name_en }} - {{ $value->category_name_en }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Offer</th>
                                                        <th>Action</th>                               
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $global_store_brand_subcategory_price_formula = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                                        ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                                        ->where('fk_subcategory_id','=', $value->fk_subcategory_id)
                                                        ->where('fk_brand_id','=', $value->fk_brand_id)
                                                        ->where('fk_store_id','=', 0)
                                                        ->where('fk_offer_option_id','!=', 0)
                                                        ->orderBy('x1', 'asc')
                                                        ->get();
                                                    @endphp
                                                    @foreach($global_store_brand_subcategory_price_formula as $key => $value)
                                                    <tr>
                                                        <td>{{$key+1}}</td>
                                                        <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                        <td>                                    
                                                            <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                            <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                        </td>                                
                                                    </tr> 
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif

                            <!-- Global Store Brand -->
                            @php
                            $global_store_brand_combinations = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
                                ->leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
                                ->select('product_price_formula.*','categories.category_name_en','brands.brand_name_en')
                                ->where('fk_subcategory_id','=', 0)
                                ->where('fk_brand_id','!=', 0)
                                ->where('fk_store_id','=', 0)
                                ->where('fk_offer_option_id','!=', 0)
                                ->groupBy('fk_subcategory_id','fk_brand_id')
                                ->orderBy('x1', 'asc')
                                ->get();
                            @endphp

                            @if(count($global_store_brand_combinations) > 0)
                                <tr>
                                    <td></td>
                                    <td style="text-align: center; "><h5>Brands</h5></td>  
                                </tr>
                                
                                <tr>
                                    <td></td>
                                    <td>
                                        @foreach ($global_store_brand_combinations as $key => $value)
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th colspan="6">{{ $value->brand_name_en }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Offer</th>
                                                        <th>Action</th>                               
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $global_store_brand_combination_price_formula = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                                        ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                                        ->where('fk_subcategory_id','=', 0)
                                                        ->where('fk_brand_id','=', $value->fk_brand_id)
                                                        ->where('fk_store_id','=', 0)
                                                        ->where('fk_offer_option_id','!=', 0)
                                                        ->orderBy('x1', 'asc')
                                                        ->get();
                                                    @endphp
                                                    @foreach($global_store_brand_combination_price_formula as $key => $value)
                                                    <tr>
                                                        <td>{{$key+1}}</td>
                                                        <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                        <td>                                    
                                                            <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                            <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                        </td>                                
                                                    </tr> 
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif

                            <!-- Global Store Subcategory -->
                            @php
                            $global_store_subcategory_combinations = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
                                ->leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
                                ->select('product_price_formula.*','categories.category_name_en','brands.brand_name_en')
                                ->where('fk_subcategory_id','!=', 0)
                                ->where('fk_brand_id','=', 0)
                                ->where('fk_store_id','=', 0)
                                ->where('fk_offer_option_id','!=',0)
                                ->groupBy('fk_subcategory_id','fk_brand_id')
                                ->orderBy('x1', 'asc')
                                ->get();
                            @endphp
                            @if(count($global_store_subcategory_combinations) > 0)
                            <tr>
                                <td></td>
                                <td style="text-align: center; "><h5>Subcategories</h5></td>  
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                @foreach ($global_store_subcategory_combinations as $key => $value)
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th colspan="6">{{ $value->category_name_en }}</th>
                                            </tr>
                                            <tr>
                                                <th>S.No.</th>
                                                <th>Offer</th>
                                                <th>Action</th>                               
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                            $global_store_subcategory_combination_price_formula = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                                ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                                ->where('fk_subcategory_id','=', $value->fk_subcategory_id)
                                                ->where('fk_brand_id','=', 0)
                                                ->where('fk_store_id','=', 0)
                                                ->where('fk_offer_option_id','!=',0)
                                                ->orderBy('x1', 'asc')
                                                ->get();
                                            @endphp
                                            @foreach($global_store_subcategory_combination_price_formula as $key => $value)
                                            <tr>
                                                <td>{{$key+1}}</td>
                                                <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                <td>                                    
                                                    <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                    <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                </td>                                
                                            </tr> 
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Price Formulas -->
    @if($price_formula_stores)
    <div class="row">
        <div class="col-md-12">
            <div class="card m-b-30">                               
                <div class="card-body">
                    
                    <table class="table table-bordered dt-responsive"
                        style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($price_formula_stores as $key => $row)
                                <tr class="hide<?= $row->id ?>">
                                    <td>{{ $key + 1 }}</td>
                                    <td><h5>{{ $row->store_name . '-' . $row->store_company_name }}<h5></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>S.No.</th>
                                                    <th>Offer</th>
                                                    <th>Action</th>                               
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $store_price_formulas = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                                        ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                                        ->where('fk_store_id',$row->fk_store_id)
                                                        ->where('fk_brand_id','=',0)
                                                        ->where('fk_subcategory_id','=',0)
                                                        ->where('fk_offer_option_id','!=',0)
                                                        ->orderBy('x1', 'asc')
                                                        ->get();
                                                @endphp
                                                @foreach($store_price_formulas as $key => $value)
                                                <tr>
                                                    <td>{{$key+1}}</td>
                                                    <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                    <td>                                    
                                                        <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                        <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                    </td>                                
                                                </tr> 
                                                @endforeach
                                                
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Stores Brands-Subcategories -->
                                @php
                                $store_brand_subcategory_combinations = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
                                    ->leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
                                    ->select('product_price_formula.*','categories.category_name_en','brands.brand_name_en')
                                    ->where('fk_subcategory_id','!=', 0)
                                    ->where('fk_brand_id','!=', 0)
                                    ->where('fk_store_id','=', $row->fk_store_id)
                                    ->where('fk_offer_option_id','!=',0)
                                    ->groupBy('fk_store_id','fk_subcategory_id','fk_brand_id')
                                    ->orderBy('x1', 'asc')
                                    ->get();
                                @endphp
                                
                            @if(count($store_brand_subcategory_combinations) > 0)
                                <tr>
                                    <td></td>
                                    <td style="text-align: center; "><h5>Brands & Subcategories </h5></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        @foreach ($store_brand_subcategory_combinations as $key => $value)
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th colspan="6">{{ $value->brand_name_en }} - {{ $value->category_name_en }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Offer</th>
                                                        <th>Action</th>                               
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $store_brand_subcategory_price_formula = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                                        ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                                        ->where('fk_store_id',$row->fk_store_id)
                                                        ->where('fk_subcategory_id','=', $value->fk_subcategory_id)
                                                        ->where('fk_brand_id','=', $value->fk_brand_id)
                                                        ->where('fk_offer_option_id','!=', 0)
                                                        ->get();
                                                    @endphp
                                                    @foreach($store_brand_subcategory_price_formula as $key => $value)
                                                    <tr>
                                                        <td>{{$key+1}}</td>
                                                        <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                        <td>                                    
                                                            <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                            <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                        </td>                                 
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                           
                                        @endforeach
                                    </td>
                                </tr>
                            @endif

                                <!-- Store Brands -->
                                @php
                                $store_brands_combinations = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
                                    ->leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
                                    ->select('product_price_formula.*','categories.category_name_en','brands.brand_name_en')
                                    ->where('fk_subcategory_id','=', 0)
                                    ->where('fk_brand_id','!=', 0)
                                    ->where('fk_store_id','=', $row->fk_store_id)
                                    ->where('fk_offer_option_id','!=',0)
                                    ->groupBy('fk_store_id','fk_subcategory_id','fk_brand_id')
                                    ->orderBy('x1', 'asc')
                                    ->get();
                                @endphp
                                @if(count($store_brands_combinations) > 0)
                                    <tr>
                                        <td></td>
                                        <td style="text-align: center; "><h5>Brands</h5></td>  
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td>
                                            @foreach ($store_brands_combinations as $key => $value)
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th colspan="6">{{ $value->brand_name_en }}</th>
                                                        </tr>
                                                        <tr>
                                                            <th>S.No.</th>
                                                            <th>Offer</th>
                                                            <th>Action</th>                               
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                        $store_brand_price_formula = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                                            ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                                            ->where('fk_subcategory_id','=', 0)
                                                            ->where('fk_brand_id','=', $value->fk_brand_id)
                                                            ->where('fk_store_id','=', $row->fk_store_id)
                                                            ->where('fk_offer_option_id','!=', 0)
                                                            ->orderBy('x1', 'asc')
                                                            ->get();
                                                        @endphp
                                                        @foreach($store_brand_price_formula as $key => $value)
                                                        <tr>
                                                            <td>{{$key+1}}</td>
                                                            <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                            <td>                                    
                                                                <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                                <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                            </td>                                 
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            @endforeach
                                        </tr>
                                    @endif

                                <!-- Stores Subcategories -->
                                @php
                                $store_subcategories_combinations = \App\Model\PriceFormula::leftJoin('categories','categories.id','=','product_price_formula.fk_subcategory_id')
                                    ->leftJoin('brands','brands.id','=','product_price_formula.fk_brand_id')
                                    ->select('product_price_formula.*','categories.category_name_en','brands.brand_name_en')
                                    ->where('fk_subcategory_id','!=', 0)
                                    ->where('fk_brand_id','=', 0)
                                    ->where('fk_store_id','=', $row->fk_store_id)
                                    ->where('fk_offer_option_id','!=', 0)
                                    ->groupBy('fk_store_id','fk_subcategory_id','fk_brand_id')
                                    ->orderBy('x1', 'asc')
                                    ->get();
                                @endphp
                                 @if(count($store_subcategories_combinations) > 0)
                                <tr>
                                    <td></td>
                                    <td style="text-align: center;"><h5>Subcatgeories</h5></td>  
                                </tr>
                               
                                <tr>
                                    <td></td>
                                    <td>
                                        @foreach ($store_subcategories_combinations as $key => $value)
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th colspan="6">{{ $value->category_name_en }}</th>
                                                    </tr>
                                                    <tr>
                                                        <th>S.No.</th>
                                                        <th>Offer</th>
                                                        <th>Action</th>                               
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $store_subcategory_price_formula = \App\Model\PriceFormula::join('product_offer_options','product_offer_options.id','=','product_price_formula.fk_offer_option_id')
                                                        ->select('product_price_formula.*','product_offer_options.name','product_offer_options.base_price_percentage','product_offer_options.discount_percentage')
                                                        ->where('fk_subcategory_id','=', $value->fk_subcategory_id)
                                                        ->where('fk_brand_id','=', $value->fk_brand_id)
                                                        ->where('fk_offer_option_id','!=', 0)
                                                        ->where('fk_store_id','=', $row->fk_store_id)
                                                        ->orderBy('x1', 'asc')
                                                        ->get();
                                                    @endphp
                                                    @foreach($store_subcategory_price_formula as $key => $value)
                                                    <tr>
                                                        <td>{{$key+1}}</td>
                                                        <td>{{ $value->name }} - Base Price Offer : ({{ $value->base_price_percentage}} % ), Discount Offer : ({{ $value->discount_percentage }} %)</td>
                                                        <td>                                    
                                                            <a class="btn-sm btn-primary" title="edit" href="<?= url('admin/products/edit-offer-formula/' . base64url_encode($value->id)); ?>">Edit</a>
                                                            <a class="btn-sm btn-danger" title="delete" href="<?= url('admin/products/delete-offer-formula/' . base64url_encode($value->id)); ?>" onclick="return confirm('Are you sure you want to delete this formula ?')">Delete</a>
                                                        </td>                                 
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @endforeach
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection