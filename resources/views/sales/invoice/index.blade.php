@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE KONSUMEN<br> {{isset($titipan) && $titipan==1 ? 'TITIPAN' : 'TEMPO'}}</u></h1>
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
    <div class="row">
        <form action="{{url()->current()}}" method="get">
            <div class="row">
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="expired" id="expired" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Data --</option>
                        <option value="yes" {{ request('expired')=='yes' ? 'selected' : '' }}>Expired</option>
                        <option value="no" {{ request()->has('expired') && request('expired') == 'no' ? 'selected' : ''
                            }}>Belum
                            Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="apa_ppn" id="apa_ppn" class="form-select" onchange="this.form.submit()">
                        <option value="">-- PPN & Non PPN --</option>
                        <option value="yes" {{ request('apa_ppn')=='yes' ? 'selected' : '' }}>PPN</option>
                        <option value="no" {{ request()->has('apa_ppn') && request('apa_ppn') == 'no' ? 'selected' : ''
                            }}>Non PPN</option>
                    </select>
                </div>
                <div class="col-md-3">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="kabupaten_id" id="kabupaten_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kab/Kota --</option>
                        @foreach ($kabupaten as $kab)
                        <option value="{{$kab->id}}" {{ request('kabupaten_id')==$kab->id ? 'selected' : '' }}>{{$kab->nama_wilayah}}</option>
                        @endforeach
                    </select>
                </div>
                 <div class="col-md-3">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="kecamatan_id" id="kecamatan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kecamatan --</option>
                        @foreach ($kecamatan as $k)
                        <option value="{{$k->id}}" {{ request('kecamatan_id')==$k->id ? 'selected' : '' }}>{{$k->nama_wilayah}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </form>
        <div class="row mt-2">
        <div class="col-md-2">

                <a href="{{ url()->current() }}" class="btn btn-secondary mb-2"><i class="fa fa-repeat"></i> Reset Filter</a>
            </div>
        </div>
        </div>
    </div>

</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable" style="font-size: 0.8rem;">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Daerah</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Nilai</th>
                    <th class="text-center align-middle">Total <br>Belanja</th>
                    <th class="text-center align-middle">DP</th>
                    <th class="text-center align-middle">DP <br>PPN</th>
                    <th class="text-center align-middle">Cicilan</th>
                    <th class="text-center align-middle">Sisa <br>PPN</th>
                    <th class="text-center align-middle">Sisa <br>Tagihan</th>
                    <th class="text-center align-middle">Jatuh <br>Tempo</th>

                </tr>
            </thead>
            <tbody>
                @php
                $sumCicilan = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal_en}}</td>
                     <td class="text-start align-middle text-wrap">
                            {{$d->konsumen->kabupaten_kota ? $d->konsumen->kabupaten_kota->nama_wilayah.', ' : ''}}
                            {{$d->konsumen->kecamatan ? $d->konsumen->kecamatan->nama_wilayah : ''}}
                    </td>
                    <td class="text-start align-middle">{{$d->konsumen->kode_toko ? $d->konsumen->kode_toko->kode.' ' :
                        '' }}{{$d->konsumen->nama}}</td>
                    <td class="text-start align-middle text-nowrap" data-order="{{$d->nomor}}">
                        {{$d->kode}}
                    </td>
                    <td class="text-start align-middle text-nowrap">
                        <ul style="margin: 0; padding: 0; list-style: none;">
                            <li>DPP : <strong>{{$d->dpp}}</strong></li>
                            <li>Diskon : <strong>{{$d->nf_diskon}}</strong></li>
                            <li>PPN : <strong>{{$d->nf_ppn}}</strong></li>
                            <li>Penyesuaian : <strong>{{$d->nf_add_fee}}</strong></li>
                        </ul>

                    </td>
                    <td class="text-end align-middle" data-order="{{$d->grand_total}}">{{$d->nf_grand_total}}</td>
                    <td class="text-end align-middle" data-order="{{$d->dp}}">{{$d->nf_dp}}</td>
                    <td class="text-end align-middle" data-order="{{$d->dp_ppn}}">{{$d->nf_dp_ppn}}</td>
                    <td class="text-end align-middle">
                        @if ($d->invoice_jual_cicil && $d->invoice_jual_cicil->count() > 0)
                        {{number_format($d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn'),
                            0, ',', '.')}}
                        @php
                        $sumCicilan += $d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn');
                        @endphp
                        @else
                        0
                        @endif
                    </td>
                    <td class="text-end align-middle {{$d->ppn_dipungut ? '' : 'table-danger'}}">{{$d->nf_sisa_ppn}}
                    </td>
                    <td class="text-end align-middle" data-order="{{$d->sisa_tagihan}}">{{$d->nf_sisa_tagihan}}</td>
                    <td class="text-end align-middle {{date($d->jatuh_tempo) < now() ? 'text-danger' : ''}}">{{$d->jatuh_tempo}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="4">Grand Total</th>
                    <th class="text-start text-nowrap align-middle">

                        <ul style="margin: 0; padding: 0; list-style: none;">
                            <li>DPP : <strong>{{number_format($data->sum('total'), 0, ',', '.')}}</strong></li>
                            <li>Diskon : <strong>{{number_format($data->sum('diskon'), 0, ',', '.')}}</strong></li>
                            <li>PPN : <strong>{{number_format($data->sum('ppn'), 0, ',', '.')}}</strong></li>
                            <li>Penyesuaian : <strong>{{number_format($data->sum('add_fee'), 0, ',', '.')}}</strong>
                            </li>
                        </ul>
                    </th>
                    <th class="text-end align-middle">{{number_format($data->sum('grand_total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dp'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dp_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($sumCicilan, 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_tagihan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle"></th>
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
            "scrollX": true,
        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#karyawan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#kecamatan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

           $('#kabupaten_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });


    });

</script>
@endpush
