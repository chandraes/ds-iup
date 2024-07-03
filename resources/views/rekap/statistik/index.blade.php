@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1>{{$customer->nama}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="container-fluid mt-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <table class="table">
                    <tr class="text-center">
                        <td class="text-center align-middle"><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                    width="30"> Dashboard</a></td>
                        <td class="text-center align-middle"><a href="{{route('rekap')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                    width="30"> REKAP</a></td>
                        <td class="text-center align-middle">
                            <a href="{{route('statistik.print', ['customer' => $customer->id,'bulan' => $bulan, 'tahun' => $tahun])}}" target="_blank"><img src="{{asset('images/print.svg')}}" alt="dokumen"
                                width="30"> PRINT PDF</a>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <form action="{{route('statistik.index', $customer->id)}}" method="get">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <select class="form-select" name="bulan" id="bulan">
                                <option value="1" {{$bulan=='01' ? 'selected' : '' }}>Januari</option>
                                <option value="2" {{$bulan=='02' ? 'selected' : '' }}>Februari</option>
                                <option value="3" {{$bulan=='03' ? 'selected' : '' }}>Maret</option>
                                <option value="4" {{$bulan=='04' ? 'selected' : '' }}>April</option>
                                <option value="5" {{$bulan=='05' ? 'selected' : '' }}>Mei</option>
                                <option value="6" {{$bulan=='06' ? 'selected' : '' }}>Juni</option>
                                <option value="7" {{$bulan=='07' ? 'selected' : '' }}>Juli</option>
                                <option value="8" {{$bulan=='08' ? 'selected' : '' }}>Agustus</option>
                                <option value="9" {{$bulan=='09' ? 'selected' : '' }}>September</option>
                                <option value="10" {{$bulan=='10' ? 'selected' : '' }}>Oktober</option>
                                <option value="11" {{$bulan=='11' ? 'selected' : '' }}>November</option>
                                <option value="12" {{$bulan=='12' ? 'selected' : '' }}>Desember</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <select class="form-select" name="tahun" id="tahun">
                                @foreach ($dataTahun as $d)
                                <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                        </div>
                        {{-- <div class="col-md-3 mb-3">
                            <label for="showPrint" class="form-label">&nbsp;</label>
                            <a href="{{route('rekap.kas-besar.preview', ['bulan' => $bulan, 'tahun' => $tahun])}}" target="_blank" class="btn btn-secondary form-control" id="btn-cari">Print Preview</a>
                        </div> --}}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid table-responsive mt-2">
    <div class="row text-center">
        <h1>Statistik {{$nama_bulan}} {{$tahun}}</h1>
    </div>
    <button id="toggleChartButton" class="btn btn-secondary">Tampilkan Chart</button>
    <canvas id="myChart" style="display: none;"></canvas>
    <div class="row mt-3">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 10%">Tanggal</th>
                    <th class="text-center align-middle">Berat (Kg)</th>
                    <th class="text-center align-middle">Belanja</th>
                    <th class="text-center align-middle">Tagihan</th>
                    <th class="text-center align-middle">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statistics_monthly as $day => $data)
                    <tr>
                        <td class="text-center align-middle">{{ $day }}</td>
                        <td class="text-center align-middle">{{ number_format($data['total_berat'], 0, ',','.') }}</td>
                        <td class="text-center align-middle">{{ number_format($data['total_bayar'], 0, ',','.') }}</td>
                        <td class="text-center align-middle">{{ number_format($data['total_tagihan'], 0,',','.') }}</td>
                        <td class="text-center align-middle">{{ number_format($data['total_profit'], 0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle">Grand Total</th>
                    <th class="text-center align-middle">{{ number_format($grand_total_berat, 0, ',','.') }}</th>
                    <th class="text-center align-middle">{{ number_format($grand_total_bayar, 0, ',','.') }}</th>
                    <th class="text-center align-middle">{{ number_format($grand_total_tagihan, 0, ',','.') }}</th>
                    <th class="text-center align-middle">{{ number_format($grand_total_profit, 0, ',','.') }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <br>
    <div class="row mt-3 text-center">
        <h1>Statistik {{$tahun}}</h1>

        <button id="toggleYearlyChartButton" class="btn btn-secondary col-md-3 m-3">Tampilkan Chart Tahunan</button>
        <canvas id="yearlyChart" style="display: none;"></canvas>
        <table class="table table-hover table-bordered" id="rekapTahunan">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Bulan</th>
                    @for ($i = 1; $i <= 12; $i++)
                        <th class="text-center align-middle">{{$i}}</th> <!-- Month name -->
                    @endfor
                    <th class="text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center align-middle">Berat (Kg)</td>
                    @foreach ($statistics_yearly as $data)
                        <td class="text-center align-middle">{{ number_format($data['total_berat'], 0,',','.') }}</td>
                    @endforeach
                    <td class="text-center align-middle">{{ number_format($yearly_total_berat, 0,',','.') }}</td>
                </tr>
                <tr>
                    <td class="text-center align-middle">Bayar</td>
                    @foreach ($statistics_yearly as $data)
                        <td class="text-center align-middle">{{ number_format($data['total_bayar'], 0,',','.') }}</td>
                    @endforeach
                    <td class="text-center align-middle">{{ number_format($yearly_total_bayar, 0,',','.') }}</td>
                </tr>
                <tr>
                    <td class="text-center align-middle">Tagihan</td>
                    @foreach ($statistics_yearly as $data)
                        <td class="text-center align-middle">{{ number_format($data['total_tagihan'], 0,',','.') }}</td>
                    @endforeach
                    <td class="text-center align-middle">{{ number_format($yearly_total_tagihan, 0,',','.') }}</td>
                </tr>
                <tr>
                    <td class="text-center align-middle">Profit</td>
                    @foreach ($statistics_yearly as $data)
                        <td class="text-center align-middle">{{ number_format($data['total_profit'], 0,',','.') }}</td>
                    @endforeach
                    <td class="text-center align-middle">{{ number_format($yearly_total_profit, 0,',','.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/plugins/chart/Chart.bundle.js')}}"></script>
<script>
    document.getElementById('toggleYearlyChartButton').addEventListener('click', function() {
        var chart = document.getElementById('yearlyChart');
        var button = document.getElementById('toggleYearlyChartButton');

        // Toggle the display of the chart
        if (chart.style.display === 'none') {
            chart.style.display = 'block';
            button.textContent = 'Sembunyikan Chart Tahunan';
        } else {
            chart.style.display = 'none';
            button.textContent = 'Tampilkan Chart Tahunan';
        }
    });

    // Prepare the data for the yearly chart
    var yearly_labels = @json(array_keys($statistics_yearly));
    var yearly_data_bayar = @json(array_column($statistics_yearly, 'total_bayar'));
    var yearly_data_tagihan = @json(array_column($statistics_yearly, 'total_tagihan'));
    var yearly_data_profit = @json(array_column($statistics_yearly, 'total_profit'));

    // Initialize the yearly chart
    var yearlyCtx = document.getElementById('yearlyChart').getContext('2d');
    var yearlyChart = new Chart(yearlyCtx, {
        type: 'line',
        data: {
            labels: yearly_labels,
            datasets: [{
                label: 'Bayar',
                data: yearly_data_bayar,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Tagihan',
                data: yearly_data_tagihan,
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }, {
                label: 'Profit',
                data: yearly_data_profit,
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
<script>

    document.getElementById('toggleChartButton').addEventListener('click', function() {
        var chart = document.getElementById('myChart');
        var button = document.getElementById('toggleChartButton');

        // Toggle the display of the chart
        if (chart.style.display === 'none') {
            chart.style.display = 'block';
            button.textContent = 'Sembunyikan Chart';
        } else {
            chart.style.display = 'none';
            button.textContent = 'Tampilkan Chart';
        }
    });
    // Prepare the data for the chart
    var labels = @json(array_keys($statistics_monthly));
    var data_berat = @json(array_column($statistics_monthly, 'total_berat'));
    var data_bayar = @json(array_column($statistics_monthly, 'total_bayar'));
    var data_tagihan = @json(array_column($statistics_monthly, 'total_tagihan'));
    var data_profit = @json(array_column($statistics_monthly, 'total_profit'));

    // Initialize the chart
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Belanja',
                data: data_bayar,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }, {
                label: 'Tagihan',
                data: data_tagihan,
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }, {
                label: 'Profit',
                data: data_profit,
                borderColor: 'rgba(255, 159, 64, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Include a comma in the ticks
                        callback: function(value, index, values) {
                            return value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.datasets[tooltipItem.datasetIndex].label || '';

                        if (label) {
                            label += ': ';
                        }
                        label += tooltipItem.yLabel.toLocaleString('id-ID');
                        return label;
                    }
                }
            }
        }
    });
    </script>
<script>

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "info": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "400px",
        });

        $('#rekapTahunan').DataTable({
            "paging": false,
            "info": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "450px",
        });

    });

</script>
@endpush
