@extends('vendor.layouts.dashboard_layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Analytics

                    </h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('vendor/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('vendor/coupons') ?>">Coupons</a></li>
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
                            <div class="col-xl-6" id="funnel_chart">

                            </div>
                            <div class="col-xl-6" id="pie_chart">

                            </div>

                        </div>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card m-b-30">
                                            <div class="card-body">
                                                <h4 class="mt-0 header-title mb-4">Top Products in Cart</h4>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Product Name</th>
                                                                <th>Product ID</th>
                                                                <th>Quantity</th>
                                                                <th>Price</th>
                                                                <th>Times in Cart</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (!empty($topProductsinCart))
                                                                @foreach ($topProductsinCart as $key => $row)
                                                                    <tr>
                                                                        <td>{{ $row->product_name_en }}</td>
                                                                        <td>{{ $row->id }}</td>
                                                                        <td>{{ $row->unit }}</td>
                                                                        <td>{{ $row->base_price }} QAR</td>
                                                                        <td>{{ $row->total_orders }} Times</td>

                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="row">
                                    <div class="col-xl-12">
                                        <div class="card m-b-30">
                                            <div class="card-body">
                                                <h4 class="mt-0 header-title mb-4">Top Products Sold</h4>
                                                <div class="table-responsive">
                                                    <table class="table table-hover table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Product Name</th>
                                                                <th>Product ID</th>
                                                                <th>Quantity</th>
                                                                <th>Price</th>
                                                                <th>Times in Cart</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if (!empty($topSoldProducts))
                                                                @foreach ($topSoldProducts as $key => $row)
                                                                    <tr>
                                                                        <td>{{ $row->product_name_en }}</td>
                                                                        <td>{{ $row->id }}</td>
                                                                        <td>{{ $row->unit }}</td>
                                                                        <td>{{ $row->base_price }} QAR</td>
                                                                        <td>{{ $row->total_sold }} Times</td>

                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>


                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        let val1 = <?= $order_counts->online_count ?>;
        let val2 = <?= $order_counts->offline_count ?>;


        var options = {
            series: [val1, val2],
            chart: {
                type: 'donut',
            },
            title: {
                text: 'Chart',
                align: 'center',
                margin: 20,
                style: {
                    fontSize: '20px',
                }
            },
            labels: ['Online Orders', 'Offline Orders'], // Define series names
            responsive: [{
                breakpoint: 450,
                options: {
                    chart: {
                        width: 150
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };


        var chart = new ApexCharts(document.querySelector("#pie_chart"), options);
        chart.render();
    </script>

    <script>
        var options = {
            series: [{
                name: "Funnel Series",
                data: [1380, 1100, 990, 880, 740, ],
            }, ],
            chart: {
                type: 'bar',
                height: 450,
            },
            plotOptions: {
                bar: {
                    borderRadius: 0,
                    horizontal: true,
                    barHeight: '80%',
                    isFunnel: true,
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    return opt.w.globals.labels[opt.dataPointIndex] + ':  ' + val
                },
                dropShadow: {
                    enabled: true,
                },
            },
            title: {
                text: 'Chart',
                align: 'middle',
            },
            xaxis: {
                categories: [
                    '340 visitor enter website',
                    '340 visit product page',
                    '140 add product to cart',
                    '50 fill out shipping and billing info',
                    '22 buy',
                ],
            },
            legend: {
                show: false,
            },
        };

        var chart = new ApexCharts(document.querySelector("#funnel_chart"), options);
        chart.render();
    </script>
@endsection
