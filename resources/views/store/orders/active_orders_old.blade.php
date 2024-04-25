@extends('store.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Slots</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Slots</li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <h4 class="mt-0 header-title">Note</h4>
                        <p class="sub-title">
                            Following list shows slots from last 3 days. Click any one to find the orders
                        </p>
                        <div class="">
                            @if ($slots)
                                @foreach ($slots as $key => $value)
                                    <a
                                        href="<?= url('store/' . $storeId . '/orders/' . base64url_encode($value->date . '|' . $value->from . '-' . $value->to)) ?>">
                                        <div class="alert alert-<?php
                                        if ($current_time >= $value->from && $current_time <= $value->to && $value->date == $current_date) {
                                            echo 'success';
                                        } else {
                                            echo 'secondary';
                                        }
                                        ?>" role="alert">
                                            <?= date('d-m-Y', strtotime($value->date)) ?>
                                            &ensp;&ensp;&ensp;
                                            <?= date('h:i A', strtotime($value->from)) ?> to
                                            <?= date('h:i A', strtotime($value->to)) ?>
                                            <p class="float-right">
                                                <?php
                                                $order_count = count_slot_orders($value->date, $value->from . '-' . $value->to);
                                                if ($order_count) {
                                                    echo $order_count . ' order(s)';
                                                } else {
                                                    echo '0 order(s)';
                                                }
                                                ?>
                                            </p>
                                        </div>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
