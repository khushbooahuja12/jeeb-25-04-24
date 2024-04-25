@extends('admin.layouts.dashboard_layout')
@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h4 class="page-title">Edit Offer</h4>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/offers'); ?>">Offers</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card m-b-30">
                <div class="card-body">                                       
                    <form method="post" id="addForm" enctype="multipart/form-data" action="{{route('admin.offers.update',[base64url_encode($offer->id)])}}">
                        @csrf
                        <div class="row">                            
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Heading (En)</label>
                                    <input type="text" name="heading_en" value="{{$offer->heading_en}}" class="form-control" placeholder="Heading (Eng)">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Heading (Ar)</label>
                                    <input type="text" name="heading_ar" value="{{$offer->heading_ar}}" class="form-control" placeholder="Heading (Arb)">
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Description (En)</label>
                                    <input type="text" name="description_en" value="{{$offer->description_en}}" class="form-control" placeholder="Description (Eng)">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Description (Ar)</label>
                                    <input type="text" name="description_ar" value="{{$offer->description_ar}}" class="form-control" placeholder="Description (Arb)">
                                </div>  
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Offer Image</label>
                                    <input type="file" name="offer_image" accept="image/*"  data-show-remove="false" data-default-file="{{!empty($offer->getOfferImage)?asset('images/offer_images').'/'.$offer->getOfferImage->file_name:asset('assets/images/no_img.png')}}" class="form-control dropify">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <div>
                                        <button type="submit" class="btn btn-primary waves-effect waves-light">
                                            Submit
                                        </button>
                                        <button type="button" class="btn btn-secondary waves-effect m-l-5" onclick="cancelForm(2);">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function getBrand(obj) {
        var id = $(obj).val();
        $('#fk_brand_id').empty();
        var html = "<option selected disabled>Select Brand</option>";
        if (id) {
            $.ajax({
                url: "<?= url('admin/products/get_category_brand') ?>",
                type: 'post',
                data: 'fk_category_id=' + id + '&_token=<?= csrf_token() ?>',
                success: function (data) {
                    if (data.error_code == "200") {
                        html += data.data;
                    } else {
                        alert(data.message);
                    }
                    $('#fk_brand_id').append(html);
                }
            });
        } else {
            alert("Something went wrong");
        }
    }

    $(document).ready(function () {
        $('#addForm').validate({
            rules: {
                fk_category_id: {
                    required: true
                },
                heading_en: {
                    required: true
                },
                heading_ar: {
                    required: true
                },
                description_en: {
                    required: true
                },
                description_ar: {
                    required: true
                }
            }
        });
    });
</script>
@endsection