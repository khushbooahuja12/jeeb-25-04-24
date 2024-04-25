@extends('admin.layouts.dashboard_layout')
@section('content')
<style>
    html { 
        overflow-y: scroll; 
        overflow-x:hidden; 
    }
    body { 
        position: absolute; 
    }
    .rating-stars i {
        color: #FFD700; /* Set the star color to gold*/
    }
</style>
<div class="container-fluid">
    <div class="page-title-box">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <ul class="nav nav-pills nav-justified" role="tablist" style="border:1px solid #30419b">
                    <li class="nav-item waves-effect waves-light">
                        <a href="<?= url('admin/order-reviews'); ?>" class="nav-link active" aria-selected="true">
                            <span class="d-none d-md-block">Reviews</span><span class="d-block d-md-none"><i class="mdi mdi-home-variant h5"></i></span> 
                        </a>
                    </li>
                    <li class="nav-item waves-effect waves-light">
                        <a href="<?= url('admin/user-feedback'); ?>" class="nav-link" aria-selected="false">
                            <span class="d-none d-md-block">Feedback</span><span class="d-block d-md-none"><i class="mdi mdi-account h5"></i></span>
                        </a>
                    </li>                   
                </ul>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-right">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard'); ?>">Home</a></li>
                    <li class="breadcrumb-item active">Order Review</li>
                </ol>
            </div>
        </div>
    </div>
    @include('partials.errors')
    @include('partials.success')
    <div class="row">
        <div class="col-12">
            <div class="card m-b-30">
                <div class="card-body">
                    <form class="form-inline filterForm" id="filterForm" method="GET">
                        <div class="form-group mb-2">
                            <label for="filterSelect" style="margin-right: 10px;">Products Quality:</label>
                            <select id="filterSelect" name="products_quality" class="form-control select2">
                                    <option value="">--select--</option>
                                    <option value="5" {{ $params['products_quality'] == 5 ? 'selected':'' }}>5★</option>
                                    <option value="4" {{ $params['products_quality'] == 4 ? 'selected':'' }}>4★</option>
                                    <option value="4-and-less" {{ $params['products_quality'] == '4-and-less' ? 'selected':'' }}>4★ & Less</option>
                                    <option value="3" {{ $params['products_quality'] == 3 ? 'selected':'' }}>3★</option>
                                    <option value="3-and-less" {{ $params['products_quality'] == '3-and-less' ? 'selected':'' }}>3★ & Less</option>
                                    <option value="2" {{ $params['products_quality'] == 2 ? 'selected':'' }}>2★</option>
                                    <option value="2-and-less" {{ $params['products_quality'] == '2-and-less' ? 'selected':'' }}>2★ & Less</option>
                                    <option value="1" {{ $params['products_quality'] == 1 ? 'selected':'' }}>1★</option>
                                    <option value="1-and-less" {{ $params['products_quality'] == '1-and-less' ? 'selected':'' }}>1★ & Less</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="filterSelect2" style="margin-right: 10px; margin-left: 10px;">Delivery Experience:</label>
                            <select id="filterSelect2" name="delivery_experience" class="form-control select2">
                                <option value="">--select--</option>
                                <option value="5" {{ $params['delivery_experience'] == 5 ? 'selected':'' }}>5★</option>
                                <option value="4" {{ $params['delivery_experience'] == 4 ? 'selected':'' }}>4★</option>
                                <option value="4-and-less" {{ $params['delivery_experience'] == '4-and-less' ? 'selected':'' }}>4★ & Less</option>
                                <option value="3" {{ $params['delivery_experience'] == 3 ? 'selected':'' }}>3★</option>
                                <option value="3-and-less" {{ $params['delivery_experience'] == '3-and-less' ? 'selected':'' }}>3★ & Less</option>
                                <option value="2" {{ $params['delivery_experience'] == 2 ? 'selected':'' }}>2★</option>
                                <option value="2-and-less" {{ $params['delivery_experience'] == '2-and-less' ? 'selected':'' }}>2★ & Less</option>
                                <option value="1" {{ $params['delivery_experience'] == 1 ? 'selected':'' }}>1★</option>
                                <option value="1-and-less" {{ $params['delivery_experience'] == '1-and-less' ? 'selected':'' }}>1★ & Less</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label for="filterSelect3" style="margin-right: 10px; margin-left: 10px;">Overall Satisfaction:</label>
                            <select id="filterSelect3" name="overall_satisfaction" class="form-control select2">
                                <option value="">--select--</option>
                                <option value="5" {{ $params['overall_satisfaction'] == 5 ? 'selected':'' }}>5★</option>
                                <option value="4" {{ $params['overall_satisfaction'] == 4 ? 'selected':'' }}>4★</option>
                                <option value="4-and-less" {{ $params['overall_satisfaction'] == '4-and-less' ? 'selected':'' }}>4★ & Less</option>
                                <option value="3" {{ $params['overall_satisfaction'] == 3 ? 'selected':'' }}>3★</option>
                                <option value="3-and-less" {{ $params['overall_satisfaction'] == '3-and-less' ? 'selected':'' }}>3★ & Less</option>
                                <option value="2" {{ $params['overall_satisfaction'] == 2 ? 'selected':'' }}>2★</option>
                                <option value="2-and-less" {{ $params['overall_satisfaction'] == '2-and-less' ? 'selected':'' }}>2★ & Less</option>
                                <option value="1" {{ $params['overall_satisfaction'] == 1 ? 'selected':'' }}>1★</option>
                                <option value="1-and-less" {{ $params['overall_satisfaction'] == '1-and-less' ? 'selected':'' }}>1★ & Less</option>
                            </select>
                        </div>
                        <button type="submit" id="filterButton" class="btn btn-primary" style="margin-left: 10px;">Filter</button>
                        <button type="button" id="clearFilterButton" class="btn btn-danger" style="margin-left: 10px;">Clear</button>
                    </form>
                    <table class="table table-bordered dt-responsive" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>User</th>
                                <th>Mobile</th>
                                <th>Order</th>
                                <th>Products Quality</th>
                                <th>Delivery Experience</th>
                                <th>Overall Satisfaction</th>
                                <th>Description</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($reviews)
                            @foreach($reviews as $key=>$value)
                            <tr>
                                <td>{{$key+1}}</td>
                                <td>
                                    <a href="<?= url('admin/users/show') . '/' . base64url_encode($value->fk_user_id); ?>" target="_blank">
                                        {{$value->getUser?($value->getUser->name?$value->getUser->name:"N/A"):"N/A"}}
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= url('admin/users/show') . '/' . base64url_encode($value->fk_user_id); ?>" target="_blank">
                                        {{$value->getUser?($value->getUser->mobile?$value->getUser->country_code.'-'.$value->getUser->mobile: "N/A"):"N/A"}}
                                    </a>
                                </td>
                                <td>
                                    <a href="<?= url('admin/orders/detail/' . base64url_encode($value->fk_order_id)); ?>" target="_blank">
                                        {{$value->getOrder?($value->getOrder->orderId?$value->getOrder->orderId:"N/A"):"N/A"}}
                                    </a>
                                </td>
                                <td>
                                    <div class="rating-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $value->products_quality)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $value->delivery_experience)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </td>
                                <td>
                                    <div class="rating-stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            @if ($i <= $value->overall_satisfaction)
                                                <i class="fas fa-star"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                </td>
                                <td>{{$value->review}}</td>
                                <td>{{date('d-m-Y h:i A',strtotime($value->created_at))}}</td>
                            </tr>
                            @endforeach                            
                            @else
                        <p>aaaa</p>
                        @endif
                        </tbody>
                    </table>
                    <div class="row">
                        <div class="col-sm-12 col-md-5">
                            <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                                Showing {{$reviews->count()}} of {{ $reviews->total() }} entries.
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-7">
                            <div class="dataTables_paginate" style="float:right">
                                {{ $reviews->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const clearFilterButton = document.getElementById("clearFilterButton");
        const filterForm = document.getElementById("filterForm");

        clearFilterButton.addEventListener("click", function () {
            // Reset all dropdowns to their default (empty) value
            const selectElements = filterForm.querySelectorAll("select");
            selectElements.forEach(function (select) {
                select.value = "";
            });

            // Submit the form to clear the filters
            filterForm.submit();
        });
    });
</script>