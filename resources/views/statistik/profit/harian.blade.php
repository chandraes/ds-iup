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
            <h1><u>PROFIT HARIAN<br>{{ ucfirst($bulanNama) }} {{$selectedTahun}}</u></h1>
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
                    <th class="text-center align-middle">Invoice</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Total DPP Jual</th>
                    <th class="text-center align-middle">Total DPP Beli</th>
                    <th class="text-center align-middle">Diskon</th>
                    <th class="text-center align-middle">Penyesuaian</th>
                    <th class="text-center align-middle">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['invoice'] as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal_en}}</td>
                    <td class="text-start align-middle text-nowrap">{{$d->kode}}</td>
                    <td class="text-start align-middle">
                        {{$d->konsumen ? ($d->konsumen->kode_toko ? $d->konsumen->kode_toko->kode.' ' : '' ). $d->konsumen->nama : $d->konsumen_temp->nama}}
                    </td>
                    <td class="text-end align-middle">{{$d->dpp}}</td>
                    <td class="text-end align-middle">{{number_format($d->total_beli, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{$d->nf_diskon}}</td>
                    <td class="text-end align-middle">{{$d->nf_add_fee}}</td>
                    <td class="text-end align-middle">{{number_format($d->profit, 0, ',','.')}}</td>
                </tr>
                @endforeach
                @foreach ($data['invoice_void'] as $void)
                 <tr class="table-danger">
                    <td class="text-center align-middle">{{ \Carbon\Carbon::parse($void->updated_at)->format('Y-m-d') }}</td>
                    <td class="text-start align-middle text-nowrap">{{$void->kode}}</td>
                    <td class="text-start align-middle">
                        {{$void->konsumen ? ($void->konsumen->kode_toko ? $void->konsumen->kode_toko->kode.' ' : '' ). $void->konsumen->nama : $void->konsumen_temp->nama}}
                    </td>
                    <td class="text-end align-middle">{{$void->dpp}}</td>
                    <td class="text-end align-middle">{{number_format($void->total_beli, 0, ',','.')}}</td>
                    <td class="text-end align-middle">{{$void->nf_diskon}}</td>
                    <td class="text-end align-middle">{{$void->nf_add_fee}}</td>
                    <td class="text-end align-middle">{{number_format($void->profit, 0, ',','.')}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle" colspan="3">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($data['invoice']->sum('total') - $data['invoice_void']->sum('total') , 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data['invoice']->sum('total_beli') - $data['invoice_void']->sum('total_beli'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data['invoice']->sum('diskon') - $data['invoice_void']->sum('diskon'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data['invoice']->sum('add_fee') - $data['invoice_void']->sum('add_fee'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data['invoice']->sum('profit') + $data['invoice_void']->sum('profit') , 0, ',', '.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <hr>
    {{-- <div class="row">
        <canvas id="omsetChart"></canvas>
    </div> --}}
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

    });

</script>
@endpush
