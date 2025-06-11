@extends('layouts.doc-min-margin')
@push('header')
@if ($pt->logo !== null && file_exists(public_path('uploads/logo/'.$pt->logo)))
<img src="{{ public_path('uploads/logo/'.$pt->logo) }}" alt="Logo" style="width: 75px">
@endif
<div class="header-div">
    <h3 style="margin-top: 20px; margin-bottom:0; padding: 0;">{{$pt->nama}}</h3>
    <p style="font-size:10px">{{$pt->alamat}}</p>
    <p style="font-size:10px">Kode Pos: {{$pt->kode_pos}}</p>
</div>
<hr style="margin-bottom: 0;">
@endpush
@section('content')
<div class="title-div" style="margin-bottom: 5px">
    <center>
        <h3 style="margin-top: -10px; margin-bottom:0; padding: 0;">DAFTAR KUNJUNGAN SALES<br>{{date('Y')}}</h3>
    </center>
</div>
<div class="tujuan-div">
    <table style="font-size: 12px">
        <div class="row invoice-info">
            <div class="col-md-6 invoice-col">
                <table style="width: 100%; font-size: 10px">
                    <tr id="namaTr">
                        <td style="vertical-align:top; width: 20%">
                            <strong>
                                NAMA TOKO
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td style="vertical-align:top; width: 150px">
                            <strong>{{$konsumen->kode_toko ? $konsumen->kode_toko->kode : ''}} {{$konsumen->nama}}</strong>
                        </td>
                        <td style="vertical-align:top; width: 80px">&nbsp;</td>
                        <td style="vertical-align:top; width: 20%">
                             <strong>
                                KODE
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td style="vertical-align:top; width: 20%">
                            <strong>{{$konsumen->full_kode}}</strong>
                        </td>

                    </tr>
                     <tr id="namaTr">
                        <td style="vertical-align:top; width: 20%">
                            <strong>
                                ALAMAT
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td style="vertical-align:top; width: 150px">
                            <strong>
                                {{$konsumen->alamat}},  {{$konsumen->kecamatan ? $konsumen->kecamatan->nama_wilayah : ''}}
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 80px">&nbsp;</td>
                        <td style="vertical-align:top; width: 20%">
                             <strong>
                                KONTAK PERSON
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td style="vertical-align:top; width: 20%">
                            <strong>{{$konsumen->cp}}</strong>
                        </td>

                    </tr>
                    <tr id="namaTr">
                        <td style="vertical-align:top; width: 20%">
                            <strong>
                                KAB/KOTA, PROVINSI
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td style="vertical-align:top; width: 150px">
                            <strong>
                                {{$konsumen->kabupaten_kota ? $konsumen->kabupaten_kota->nama_wilayah : ''}},
                                {{$konsumen->provinsi ? $konsumen->provinsi->nama_wilayah : ''}}
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 80px">&nbsp;</td>
                        <td style="vertical-align:top; width: 20%">
                             <strong>
                                NAMA SALES
                            </strong>
                        </td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td style="vertical-align:top; width: 20%">
                            <strong>{{$konsumen->karyawan ? $konsumen->karyawan->nama : ''}}</strong>
                        </td>

                    </tr>
                    {{-- <tr>
                        <td>Sistem Pembayaran</td>
                        <td>:</td>
                        <td>
                            {{$data->sistem_pembayaran_word}}
                        </td>
                        <td style="vertical-align:top; width: 10%"></td>
                        <td>Tanggal Kirim</td>
                        <td>:</td>
                        <td>
                            {{$tanggal}}
                        </td>
                    </tr>
                    <tr>
                        <td>Tempo</td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td>
                            {{$data->sistem_pembayaran !== 1 ? $data->konsumen->tempo_hari . ' Hari' : '-'}} </td>
                        <td style="vertical-align:top; width: 5%"></td>
                        <td>Tanggal Tempo</td>
                        <td style="vertical-align:top; width: 15px">:</td>
                        <td>
                            {{$tanggal_tempo}}
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top; vertical-align: top;">Alamat</td>
                        <td style="vertical-align:top; width: 15px; vertical-align: top;">:</td>
                        <td style="vertical-align:top; vertical-align: top;">
                            @if ($data->konsumen)
                            {{$data->konsumen->alamat}}, {{$data->konsumen->kecamatan->nama_wilayah}},
                            {{$data->konsumen->kabupaten_kota->nama_wilayah}}
                            @else
                            {{$data->konsumen_temp->alamat}}
                            @endif
                        </td>
                        <td style="vertical-align:top; width: 5%; vertical-align: top;"></td>
                        <td style="vertical-align:top; vertical-align: top;">No HP</td>
                        <td style="vertical-align:top; width: 15px; vertical-align: top;">:</td>
                        <td style="vertical-align:top; vertical-align: top;">
                            {{$data->konsumen ? $data->konsumen->no_hp : $data->konsumen_temp->no_hp}}
                        </td>
                    </tr> --}}
                </table>
            </div>
        </div>
    </table>
</div>
<div class="po-items">
    <table class="table-items" style="font-size: 10px">
        <tbody>
            <tr>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">JANUARI</td>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">JULI</td>
            </tr>
             <tr>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">FEBRUARI</td>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">AGUSTUS</td>
            </tr>
             <tr>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">MARET</td>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">SEPTEMBER</td>
            </tr>
             <tr>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">APRIL</td>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">OKTOBER</td>
            </tr>
             <tr>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">MEI</td>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">NOVEMBER</td>
            </tr>
             <tr>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">JUNI</td>
                <td class="text-left" style="vertical-align: top; height: 120px; font-weight:bolder">DESEMBER</td>
            </tr>
        </tbody>
    </table>

</div>
@endsection
