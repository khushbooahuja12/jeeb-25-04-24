@extends('vendor.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Subscription

                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/subscription') ?>">subscription</a></li>
                        <li class="breadcrumb-item active">List</li>
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
                        <div class="row">
                          <div class="col-4">

                          </div>
                          <div class="col-2 border" id="classic">
                            <h5>Classic</h5>
                          </div>
                          <div class="col-2 border" id="standard">
                            <h5>Standard</h5>
                          </div>
                          <div class="col-2 border" id="enterprise">
                            <h5>Enterprise</h5>
                          </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    $('#classic').on('click',function(){


    });

    $('#classic').on('click',function(){

        
    });

    $('#classic').on('click',function(){


    });

   
</script>
@endsection
