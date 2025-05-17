@extends('layouts.app')
@section('content')
@php
    $selectedBulan = request('bulan') ?? date('m');
    $selectedTahun = request('tahun') ?? date('Y');
@endphp
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>OMSET HARIAN SALES<br></u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>

                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container table-responsive ml-3">

    <form action="{{route('sales.omset-harian')}}" method="get">
        {{-- select bulan dan tanggal --}}
        <div class="row mt-3">
            <div class="col-md-3">
                <label for="month" class="form-label">Bulan</label>
                <select name="month" id="month" class="form-select" onchange="this.form.submit()">
                    @foreach ($dataBulan as $key => $value)
                    <option value="{{ $key }}" {{ $key==$selectedBulan ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="year" class="form-label">Tahun</label>
                <select name="year" id="year" class="form-select" onchange="this.form.submit()">
                    @foreach ($dataTahun as $tahun)
                    <option value="{{ $tahun->tahun }}" {{ $tahun==$selectedTahun ? 'selected' : '' }}>{{
                        $tahun->tahun }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
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
                    <td class="text-end align-middle">{{ number_format($row[$karyawan->id] ?? 0, 0, ',', '.') }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle">Grand Total</th>
                    @foreach ($karyawans as $karyawan)
                    @php
                    $total = collect($rows)->sum(function($row) use ($karyawan) {
                    return $row[$karyawan->id] ?? 0;
                    });
                    @endphp
                    <th class="text-end align-middle">{{ number_format($total, 0, ',', '.') }}</th>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>
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
@endpush
