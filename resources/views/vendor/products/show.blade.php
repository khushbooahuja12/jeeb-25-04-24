@extends('vendor.layouts.dashboard_layout')
@section('content')
<style>
    label{
        font-size: 13px;
        color: #212529;
        font-weight: bold;
    }
    .owl-carousel .owl-item img {
        display: inline-block!important;
    }

    @media (max-width:992px) {
        .owl-carousel .owl-nav button.owl-prev, .owl-carousel .owl-nav button.owl-next, .owl-carousel button.owl-dot {
            display: none;
        }
    }

    .owl-carousel {
        position: relative;
    }
    .owl-carousel .owl-item {
        position: relative;
        cursor: url(../plugins/owl-carousel/cursor.html), move;
        overflow: hidden;
    }
    .owl-nav {
        display: block;
    }
    .owl-nav .owl-prev {
        position: absolute;
        top: 50%;
        left: -25px;
        right: -1.5em;
        margin-top: -1.65em;
    }
    .owl-nav .owl-next {
        position: absolute;
        top: 50%;
        right: -25px;
        margin-top: -1.65em;
    }
    @media (max-width:480px) {
        .owl-nav .owl-prev {
            left: -10px;
        }
        .owl-nav .owl-next {
            right: -10px;
        }
    }

    .owl-nav button {
        display: block;
        font-size: 1.3rem !important;
        line-height: 2em;
        border-radius: 50%;
        width: 3rem;
        height: 3rem;
        text-align: center;
        background: rgba(255, 255, 255, .5) !important;
        border: 1px solid #d8dde6 !important;
        z-index: 99;
        box-shadow: 0 4px 15px rgba(67, 67, 67, .15);
    }
    .owl-nav button:before {
        content: "";
        position: absolute;
        z-index: -1;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #2098d1;
        -webkit-transform: scaleY(0);
        transform: scaleY(0);
        -webkit-transform-origin: 50% 0;
        transform-origin: 50% 0;
        -webkit-transition-property: transform;
        transition-property: transform;
        -webkit-transition-duration: .5s;
        transition-duration: .5s;
        -webkit-transition-timing-function: ease-out;
        transition-timing-function: ease-out;
    }
    .owl-carousel:hover .owl-nav button {
        background: rgba(255, 255, 255) !important;
        -webkit-transition-property: transform;
        transition-property: transform;
        -webkit-transition-duration: .5s;
        transition-duration: .5s;
        -webkit-transition-timing-function: ease-out;
        transition-timing-function: ease-out;
        animation: sonarEffect 1.3s ease-out 75ms;
    }
    .owl-nav>div i {
        margin: 0;
    }
    .owl-theme .owl-dots {
        text-align: center;
        -webkit-tap-highlight-color: transparent;
        position: absolute;
        bottom: .65em;
        left: 0;
        right: 0;
        z-index: 99;
    }
    .owl-theme .owl-dots .owl-dot {
        display: inline-block;
        zoom: 1;
    }
    .owl-theme .owl-dots .owl-dot span {
        width: 1em;
        height: 1em;
        margin: 5px 7px;
        background: rgba(0, 0, 0, .3);
        display: block;
        -webkit-backface-visibility: visible;
        transition: opacity .2s ease;
        border-radius: 30px;
    }

    .owl-carousel.owl-drag .owl-item {
        left: 0 !important;
        right: 0;
        margin-bottom: 10px;
        transition-duration: 1s;
        transition-delay: .1s;
        -webkit-transform: perspective(1px) translateZ(0);
        transform: perspective(1px) translateZ(0);
    }
    .owl-carousel .owl-dots {
        margin: 0 auto;
        text-align: center;
    }
    .border-5 .owl-carousel .owl-dots {
        margin: 0 auto;
        text-align: center;
        bottom: 10px;
        position: absolute;
        right: 10px;
    }
    .owl-carousel button.owl-dot {
        margin: 10px 10px 0 10px;
        border-radius: 50%;
        width: 10px;
        height: 10px;
        text-align: center;
        display: inline-block;
        border: none;
    }
    .owl-carousel-icons5 .owl-nav .owl-prev {
        position: absolute;
        top: 42%;
        left: auto;
        right: -24px;
        margin-top: -1.65em;
    }
    .owl-carousel-icons5 .owl-nav .owl-next {
        position: absolute;
        top: 58%;
        left: auto;
        right: -24px;
        margin-top: -1.65em;
    }
    .owl-carousel-icons4.owl-carousel .owl-item img {
        margin: 0 auto;
    }
    #carousel-controls.owl-carousel .owl-item img {
        width: 100%;
    }
    .owl-carousel.owl-drag .owl-item:hover {
        box-shadow: 0px 5px 28px -15px #d4d4d4;
        transition-duration: 1s;
        transition-delay: .1s;
        -webkit-transform: translateY(-8px);
        transform: translateY(-8px);
    }

    .property-detail-slider{
        margin-top: 20px;
        margin-bottom: 20px;
    }
    .property-detail-slider .owl-nav .owl-prev {
        position: absolute;
        top: 50%;
        left: 0px;
        right: -1.5em;
        margin-top: -20px;
    }
    .property-detail-slider .owl-nav .owl-next {
        position: absolute;
        top: 50%;
        right: 0px;
        margin-top: -20px;
    }
    .property-detail-slider .owl-nav button {
        width: 30px;
        height: 30px;

    }
    img{
        max-width: 100%;
    }
    .thumb{
        height:200px;
    }

    .thumb img{
        height:100%;
    }

    .imgDiv{
        height:150px;
        width:150px;
    }

    .imgDiv img{
        width:100%;
        height:100%;
    }
    
</style> 
<link href="{{asset('assets/plugins/owl-carousel/owl.carousel.css')}}" rel="stylesheet" />
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Product Detail</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('vendor/all_products'); ?>">Products</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">  
                    <div class="row">
                        <div class=" offset-11 col-md-1">
                        </div>
                    </div> 
                    @csrf    


                    <div class="row">
                        <div class="col-md-12">
                            <div class="eventrow">
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="imgDiv"> 
                                            @if(!empty($product))
                                            <img src="{{$product->product_image_url??''}}">
                                            @endif
                                        </div>
                                    </div>
                                  
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mt-3">
                            <table class="table table-borderless">
                                <tr>
                                    <th>Product Name (En) </th>
                                    <td>{{$product->product_name_en??''}}</td>
                                </tr>
                                <tr>
                                    <th>Product Name (Ar) </th>
                                    <td>{{$product->product_name_ar ??''}}</td>
                                </tr>
                                <tr>
                                    <th> Category </th>
                                    <td>{{$product->category_name_en ??''}}</td>
                                </tr>
                                <tr>
                                    <th> Brand </th>
                                    <td>{{$product->brand_name_en ??''}}</td>
                                </tr>
                                <tr>
                                    <th>Quantity </th>
                                    <td>{{$product->unit ??''}}</td>
                                </tr>
                                <tr>
                                    <th> Price </th>
                                    <td>$ {{$product->base_price??''}}</td>
                                </tr>
                                <tr>
                                    <th>Status </th>
                                    
                                    <td>@if($product->stock == '0') Out of Stock
                                    @else  In Stock
                                    @endif</td>
                                </tr>
                                <tr>
                                    <th>Color </th>
                                    <td>{{$product->product_price??''}}</td>
                                </tr>
                                <tr>
                                    <th>HSN</th>
                                    <td>{{$product->product_price??''}}</td>
                                </tr>
                               
                                
                            </table>
                        </div>
                        <div class="col-md-12 text-end">
                            <a title="edit" href="<?= url('vendor/products/edit/' . base64url_encode($product->id)); ?>"><button >Edit</button></a>
                            <a onclick="delete_modal('{{base64url_encode($product->id)}}')"><button>Delete</button></a>
                        </div>
                    </div>
                    
                </div>

            </div>
        </div> <!-- end col -->       
    </div> <!-- end row -->      
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="product_delete_modal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content" style='width:100%;'>
            <div class="modal-header">
                <h5 class="modal-title"><span style="color:red"></span> Are you Sure! </h5>                        
            </div>
            <div class="modal-body">
                <form action="{{ url('vendor/products/delete/'.base64url_encode($product->id)) }}" >
                 
                <div class="form-group">
                    <button type="submit" class="btn btn-primary waves-effect waves-light">
                        Submit
                    </button>
                    <button type="button" class="btn btn-secondary waves-effect m-l-5"
                    class="close" data-dismiss="modal" aria-label="Close">
                        Cancel
                    </button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>


<script src="{{asset('assets/plugins/owl-carousel/owl.carousel.js')}}"></script>
<script src="{{asset('assets/js/owl-carousel.js')}}"></script>
<script>
$('#lightgallery').owlCarousel({
    loop: false,
    margin: 10,
    nav: true,
    mouseDrag: false,
    responsive: {
        0: {
            items: 1
        },
        600: {
            items: 3
        },
        1000: {
            items: 3
        }
    }
});
</script>

<script>
    function delete_modal(id){
        let prod_id = id;
        // console.log(prod_id);

        $('#product_delete_modal').modal('show');
        $('#delete_id').val(prod_id);

    }
</script>
@endsection