@extends('layouts.app')
@section('content')
@php
$selectedBulan = request('month') ?? date('m');
$selectedTahun = request('year') ?? date('Y');
@endphp
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            @php
            use Carbon\Carbon;
            $bulanNama = Carbon::create()->month($selectedBulan)->locale('id')->isoFormat('MMMM');
            @endphp
            <h1><u>OMSET HARIAN SALES<br>{{ ucfirst($bulanNama) }} {{$selectedTahun}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>

                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <form action="{{url()->current()}}" method="get">
                {{-- select bulan dan tanggal --}}
                <div class="row mt-1">
                    <div class="col-md-6">

                        <select name="month" id="month" class="form-select" onchange="this.form.submit()">
                            @foreach ($dataBulan as $key => $value)
                            <option value="{{ $key }}" {{ $key==$selectedBulan ? 'selected' : '' }}>{{ $value }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">

                        <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                            @foreach ($dataTahun as $tahun)
                            <option value="{{ $tahun->tahun }}" {{ $tahun->tahun==$selectedTahun ? 'selected' : '' }}>{{
                                $tahun->tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="container table-responsive mt-2">
    <div class="row">
        <table class="table table-hover table-bordered" id="rekapTable" style="width: 100%">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    @foreach ($karyawans as $karyawan)
                    <th class="text-center align-middle">{{ $karyawan->nama }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                <tr>
                    <td class="text-center align-middle">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d') }}</td>
                    @foreach ($karyawans as $karyawan)
                    <td class="text-end align-middle" data-order="{{ $row[$karyawan->id] ?? 0 }}">
                         @if ($row[$karyawan->id] > 0)
                        <a href="{{route('statistik.omset-harian-sales.detail', ['tanggal' => $row['tanggal'], 'karyawan_id' => $karyawan->id])}}" />
                        @endif
                        {{ number_format($row[$karyawan->id] ?? 0, 0, ',', '.') }}
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                @php
                    $gt = 0;
                @endphp
                <tr>
                    <th class="text-center align-middle">Total</th>
                    @foreach ($karyawans as $karyawan)
                    @php
                    $total = collect($rows)->sum(function($row) use ($karyawan) {
                    return $row[$karyawan->id] ?? 0;
                    });
                    $gt += $total;
                    @endphp
                    <th class="text-end align-middle">

                        {{ number_format($total, 0, ',', '.') }}
                    </th>
                    @endforeach
                </tr>
                <tr>
                    <th class="text-center align-middle">Grand Total</th>
                    <th class="text-center align-middle" colspan="{{ count($karyawans) }}">
                        {{ number_format($gt, 0, ',', '.') }}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
    <hr>
    <div class="row">
        <canvas id="omsetChart"></canvas>
    </div>
</div>
@php
$tanggalLabels = collect($rows)->pluck('tanggal')->map(fn($t) => \Carbon\Carbon::parse($t)->format('d'));

// Ambil nilai maksimum untuk sumbu Y
$maxOmset = collect($rows)->flatMap(function($row) use ($karyawans) {
return $karyawans->map(function($k) use ($row) {
return $row[$k->id] ?? 0;
});
})->max();
$maxYAxis = ceil($maxOmset * 1.2); // tambahkan 20% ruang atas

// Warna kontras
$colors = [
'#e6194b', '#3cb44b', '#ffe119', '#4363d8', '#f58231',
'#911eb4', '#46f0f0', '#f032e6', '#bcf60c', '#fabebe',
'#008080', '#e6beff', '#9a6324', '#fffac8', '#800000',
'#aaffc3', '#808000', '#ffd8b1', '#000075', '#808080'
];
@endphp
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "60vh", // Set scrollY to 50% of the viewport height
            "scrollCollapse": true,
            "scrollX": true,

        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

    });


</script>
<script>
    const tanggalLabels = @json($tanggalLabels);
    const datasets = [];

    @foreach ($karyawans as $index => $karyawan)
        datasets.push({
            label: "{{ $karyawan->nama }}",
            data: [
                @foreach ($rows as $row)
                    {{ (int) ($row[$karyawan->id] ?? 0) }},
                @endforeach
            ],
            borderColor: "{{ $colors[$index % count($colors)] }}",
            backgroundColor: "transparent",
            borderWidth: 3,
            pointRadius: 4,
            pointHoverRadius: 6,
            tension: 0.3,
        });
    @endforeach

    const ctx = document.getElementById('omsetChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: tanggalLabels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Grafik Omset Harian Karyawan',
                    font: {
                        size: 18
                    }
                },
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.y || 0;
                            return context.dataset.label + ': Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: {{ $maxYAxis }},
                    title: {
                        display: true,
                        text: 'Total Omset (Rp)'
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                }
            }
        }
    });
</script>
@endpush
