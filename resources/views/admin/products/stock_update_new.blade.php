@extends('admin.layouts.dashboard_layout')
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
                            action="{{ route('admin.products.bulk_stock_update') }}">
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

                    if(completed_percent >= 100){
                        // $("#stepTwo").find('button').removeAttr('disabled');
                        // $("#stepTwo").find('button').css('cursor','pointer');
                        {{!! (isset($batch_id) && $batch_id!='') ? "window.location.href = \"/admin/products/stock-update/".$id."/".$batch_id."/update\"" : "" !!}}
                    }
                }
            })
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
