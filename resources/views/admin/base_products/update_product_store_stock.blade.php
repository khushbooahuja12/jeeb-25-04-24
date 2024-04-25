@extends('admin.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Bulk upload stock</h4>
                    <a href="<?= url('admin/base_products/stock_update_stores/new_products') ?>" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Add New Products From Stock Update
                    </a>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/base_products/stock_update_stores') ?>">Stock Update</a></li>
                        <li class="breadcrumb-item active">List</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: <?= $completed_percent ?>%"
                aria-valuenow="<?= $completed_percent ?>" aria-valuemin="0" aria-valuemax="100">
                <?= number_format($completed_percent, 2) ?> %</div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="stepOne" enctype="multipart/form-data"
                            action="{{ route('admin.base_products.bulk_stock_update') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <h4>Select products CSV to update stock</h4>
                                        <input type="hidden" name="store_id" value="{{$id}}" class="form-control"><br/>
                                        <input type="file" name="file" accept=".csv" class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-success waves-effect waves-light">
                                            Submit
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form><br>
                    </div>
                </div>
                <div class="alert_msg"></div>
            </div>
        </div>
    </div>
    </div>
    <script>
        var interval = setInterval(function() {
            getUploadProgress("<?= $batchId ?>");
        }, 5000);

        function getUploadProgress(id) {
            $.ajax({
                url: '<?= url('admin/batch') . '/' ?>' + id,
                type: 'GET',
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                if (response) {
                    var completed_percent = ((response.processedJobs + response.failedJobs) / response.totalJobs) *
                        100;

                    $(".progress-bar").text(completed_percent.toFixed(2) + ' %');
                    $(".progress-bar").attr('style', 'width: ' + completed_percent + '%');
                    $(".progress-bar").attr('aria-valuenow', completed_percent.toFixed(2))

                    if(completed_percent >= 100){
                        // $("#stepTwo").find('button').removeAttr('disabled');
                        // $("#stepTwo").find('button').css('cursor','pointer');
                        stopInterval();
                        {{!! (isset($batch_id) && $batch_id!='') ? "window.location.href = \"/admin/base_products/stock_update/".$id."/".$batch_id."/update\"" : "" !!}}
                    }
                }
            })
        }

        function stopInterval() {
            clearInterval(interval); 
        }

        $('#stepOne').validate({
            rules: {
                store_id: {
                    required: true
                },
                file: {
                    required: true
                }
            }
        });

    </script>
@endsection
