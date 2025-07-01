@extends('layouts.app')
@section('content')
@php
$selectedBulan = request('month') ?? date('m');
$selectedTahun = request('year') ?? date('Y');
@endphp
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            @php
            use Carbon\Carbon;
            $bulanNama = Carbon::create()->month($selectedBulan)->locale('id')->isoFormat('MMMM');
            @endphp
            <h1><u>SELLING OUT<br>{{ ucfirst($bulanNama) }} {{$selectedTahun}}</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <form action="{{url()->current()}}" method="get">
            <div class="row">
                <div class="col-md-6">
                    <table class="table">
                        <tr class="text-center">
                            <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                        width="30"> Dashboard</a></td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
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
                                <option value="{{ $tahun->tahun }}" {{ $tahun->tahun==$selectedTahun ? 'selected' : ''
                                    }}>{{
                                    $tahun->tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <select name="kabupaten_kota" id="kabupaten_kota" class="form-select select2" onchange="this.form.submit()">
                        <option value="">Semua Kab/Kota</option>
                        @foreach ($kabupaten_kota as $s)
                        <option value="{{$s->id}}" {{ request('kabupaten_kota')==$s->id ? 'selected' : '' }}>{{$s->nama_wilayah}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sales" id="sales" class="form-select select2" onchange="this.form.submit()">
                        <option value="">Semua Sales</option>
                        @foreach ($sales as $s)
                        <option value="{{$s->id}}" {{ request('sales')==$s->id ? 'selected' : '' }}>
                            {{$s->nama}}</option>
                        @endforeach
                    </select>
                </div>

            </div>
        </form>
    </div>
    <div class="container-fluid mt-2 table-responsive ">
        <table class="table table-bordered" id="dataTable" style="font-size: 12px">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Kode</th>
                    <th class="text-center align-middle">Nama Konsumen</th>
                    <th class="text-center align-middle">Alamat</th>
                    <th class="text-center align-middle">Kota</th>
                    <th class="text-center align-middle">Type</th>
                    <th class="text-center align-middle">Sales Area</th>
                    <th class="text-center align-middle">Kode Barang</th>
                    <th class="text-center align-middle">Nama Barang</th>
                    <th class="text-center align-middle">Merk</th>
                    <th class="text-center align-middle">PG</th>
                    <th class="text-center align-middle">SUBPG</th>
                    <th class="text-center align-middle">Total (with VAT)</th>
                    <th class="text-center align-middle">No Invoice</th>
                    <th class="text-center align-middle">QTY</th>
                    <th class="text-center align-middle">Total Net (Non VAT)</th>
                </tr>
            </thead>
            @php
            $total = 0;
            @endphp
            <tbody>
                @foreach ($data['data_jual'] as $d)
                @php
                $total_harga = $d->invoice->kas_ppn ? $d->total + floor($d->total * $ppn / 100) : $d->total;
                @endphp
                <tr>
                    <td class="text-center align-middle">{{$d->invoice->tanggal_en}}</td>
                    <td class="text-center align-middle">{{$d->invoice->konsumen ? $d->invoice->konsumen->full_kode :
                        ''}}
                    </td>
                    <td class="text-start align-middle">{{$d->invoice->konsumen ?
                        $d->invoice->konsumen->kode_toko->kode.'
                        '.$d->invoice->konsumen->nama : $d->invoice->konsumen_temp->nama}}</td>
                    <td class="text-start align-middle">{{$d->invoice->konsumen ? $d->invoice->konsumen->alamat :
                        $d->invoice->konsumen_temp->alamat}}</td>
                    <td class="text-start align-middle">{{$d->invoice->konsumen ?
                        $d->invoice->konsumen->kabupaten_kota->nama_wilayah : ''}}</td>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle">{{$d->invoice->karyawan->nama}}</td>
                    <td class="text-center align-middle">{{$d->barang->kode}}</td>
                    <td class="text-center align-middle">{{$d->barang->barang_nama->nama}}</td>
                    <td class="text-center align-middle">{{$d->barang->merk}}</td>
                    <td class="text-center align-middle">{{$d->barang->kategori->nama}}</td>
                    <td class="text-center align-middle"></td>
                    {{-- <td class="text-center align-middle">{{$d->total}}</td> --}}
                    <td class="text-center align-middle" data-order="{{$total_harga}}">
                        {{number_format($total_harga, 0 ,',','.')}}
                        @php
                        $total += $total_harga;
                        @endphp
                    </td>
                    <td class="text-center align-middle">{{$d->invoice->kode}}</td>
                    <td class="text-center align-middle">{{$d->nf_jumlah}}</td>
                    <td class="text-center align-middle" data-order="{{$d->total}}">{{$d->nf_total}}</td>
                </tr>
                @endforeach
                @foreach ($data['void'] as $d)
                @php
                $total_harga = $d->invoice->kas_ppn ? ($d->total + floor($d->total * $ppn / 100))*-1 : $d->total*-1;
                @endphp
                <tr class="">
                    <td class="text-danger text-center align-middle">{{
                        \Carbon\Carbon::parse($d->invoice->updated_at)->format('Y-m-d') }}</td>
                    <td class="text-danger text-center align-middle">{{$d->invoice->konsumen ?
                        $d->invoice->konsumen->full_kode : ''}}</td>
                    <td class="text-danger text-start align-middle">{{$d->invoice->konsumen ?
                        $d->invoice->konsumen->kode_toko->kode.' '.$d->invoice->konsumen->nama :
                        $d->invoice->konsumen_temp->nama}}</td>
                    <td class="text-danger text-start align-middle">{{$d->invoice->konsumen ?
                        $d->invoice->konsumen->alamat
                        : $d->invoice->konsumen_temp->alamat}}</td>
                    <td class="text-danger text-start align-middle">{{$d->invoice->konsumen ?
                        $d->invoice->konsumen->kabupaten_kota->nama_wilayah : ''}}</td>
                    <td class="text-danger text-center align-middle"></td>
                    <td class="text-danger text-center align-middle">{{$d->invoice->karyawan->nama}}</td>
                    <td class="text-danger text-center align-middle">{{$d->barang->kode}}</td>
                    <td class="text-danger text-center align-middle">{{$d->barang->barang_nama->nama}}</td>
                    <td class="text-danger text-center align-middle">{{$d->barang->merk}}</td>
                    <td class="text-danger text-center align-middle">{{$d->barang->kategori->nama}}</td>
                    <td class="text-danger text-center align-middle"></td>
                    {{-- <td class="text-center align-middle">{{$d->total}}</td> --}}
                    <td class="text-danger text-center align-middle" data-order="{{$total_harga}}">
                        {{number_format($total_harga, 0 ,',','.')}}
                        @php
                        $total += $total_harga;
                        @endphp
                    </td>
                    <td class="text-danger text-center align-middle">{{$d->invoice->kode}}</td>
                    <td class="text-danger text-center align-middle" data-order="{{$d->jumlah * -1}}">
                        -{{$d->nf_jumlah}}
                    </td>
                    <td class="text-danger text-center align-middle" data-order="{{$d->total*-1}}">-{{$d->nf_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle">Grand Total</th>
                    <th class="text-center align-middle">{{count($data['data_jual'])+count($data['void'])}}</th>
                    <th class="text-end align-middle" colspan="10"></th>
                    <th class="text-center align-middle">{{number_format($total, 0, ',','.')}}</th>
                    <th></th>
                    <th class="text-center align-middle"></th>
                    <th class="text-center align-middle">
                        {{number_format($data['data_jual']->sum('total')-$data['void']->sum('total'), 0, ',','.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    @endsection
    @push('css')
    <link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
    @endpush
    @push('js')
    <script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
    <script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
    <script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

    });

    </script>
    @endpush
