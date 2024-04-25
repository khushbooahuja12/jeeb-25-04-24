@extends('store.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Bulk upload stock</h4>
                </div>
                <div class="col-sm-6">

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
                            action="{{ route('store.products.bulk_stock_update_step1', [$storeId]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <h4>Step 1: Select products CSV to update stock</h4>
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
                <div class="card m-b-30">
                    <div class="card-body">
                        <form method="post" id="stepTwo" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <h4>Step 2: Merge products with Algolia</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-danger waves-effect waves-light"
                                            <?= $completed_percent != 100 ? 'disabled' : '' ?>
                                            style="cursor:<?= $completed_percent != 100 ? 'not-allowed' : 'pointer' ?>">
                                            Click me
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script>
        setInterval(function() {
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

                    if (completed_percent >= 100) {
                        $("#stepTwo").find('button').removeAttr('disabled');
                        $("#stepTwo").find('button').css('cursor', 'pointer');
                    }
                }
            })
        }

        $('#stepOne').validate({
            rules: {
                file: {
                    required: true
                }
            }
        });

        $("#stepTwo").on('submit', function(e) {
            e.preventDefault();

            $.ajax({
                url: '<?= url("store/$storeId/products/bulk_stock_update_step2") ?>',
                type: 'POST',
                data: {
                    storeId: '<?= $storeId ?>'
                },
                dataType: 'JSON',
                cache: false
            }).done(function(response) {
                if (response.error_code === 200) {
                    var success_str =
                        '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".alert_msg").html(success_str);
                } else {
                    var error_str =
                        '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                        '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                        '<strong>' + response.message + '</strong>.' +
                        '</div>';
                    $(".alert_msg").html(error_str);
                }
            })
        })
    </script>
@endsection
