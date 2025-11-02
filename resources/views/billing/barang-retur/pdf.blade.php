@extends('layouts.doc')
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
<h4 style="text-align: center; margin-bottom: 10px; margin-top: -10px;">BARANG RETUR</h4>
<div class="tujuan-div">
    <table style="width: 100%; font-size: 10px">
        <tr>
            <td style="width: 10%">Kode Retur</td>
            <td style="width: 15px">:</td>
            <td style="width: 150px"><strong>{{$data->kode}}</strong></td>
            <td style="width: 80px">&nbsp;</td>
            <td style="width: 10%">Tanggal</td>
            <td style="width: 15px">:</td>
            <td style="width: 20%"><strong>{{$tanggal}}</strong></td>
        </tr>
        <tr>
            <td>Tipe Retur</td>
            <td>:</td>
            <td>{{$data->tipe == 1 ? 'Retur ke Supplier' : 'Retur dari Konsumen'}}</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td> {{-- Asumsi ada accessor 'status_word' di model --}}
        </tr>
        <tr>
            @if ($data->tipe == 1)
            {{-- Tipe 1 = Retur ke Supplier --}}
            <td style="vertical-align: top;">Supplier</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;"><strong>{{$data->barang_unit->nama}}</strong></td>
            @else
            {{-- Tipe 2 = Retur dari Konsumen --}}
            <td style="vertical-align: top;">Konsumen</td>
            <td style="vertical-align: top;">:</td>
            <td style="vertical-align: top;">
                <strong>{{$data->konsumen ? $data->konsumen->kode_toko?->kode.' '.$data->konsumen->nama : ''}}</strong>
                <br>
                {{$data->konsumen->alamat ?? ''}}
            </td>
            @endif
        </tr>
    </table>
</div>
{{-- <div class="tujuan-div">
    <table style="font-size: 12px">
        <div class="row invoice-info">
            <div class="col-md-6 invoice-col">
                <table style="width: 100%; font-size: 10px">
                    <tr id="namaTr">
                        <td style="width: 20%">Konsumen</td>
                        <td style="width: 15px">:</td>
                        <td style="width: 150px">
                            <strong>{{$data->konsumen ? $data->konsumen->kode_toko?->kode.' '.$data->konsumen->nama :
                                ''}}</strong>
                        </td>
                        <td style="width: 80px">&nbsp;</td>
                        <td style="width: 20%">Invoice</td>
                        <td style="width: 15px">:</td>
                        <td style="width: 20%">
                            <strong>{{$data->kode}}</strong>
                        </td>

                    </tr>
                    <tr>
                        <td>Sistem Pembayaran</td>
                        <td>:</td>
                        <td>
                            {{$data->sistem_pembayaran_word}}
                        </td>
                        <td style="width: 10%"></td>
                        <td>Tanggal Kirim</td>
                        <td>:</td>
                        <td>
                            {{$tanggal}}
                        </td>
                    </tr>

                    <tr>
                        <td style="vertical-align: top;">Alamat</td>
                        <td style="width: 15px; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">
                            @if ($data->konsumen)
                            {{$data->konsumen->alamat}}, {{$data->konsumen->kecamatan->nama_wilayah}},
                            {{$data->konsumen->kabupaten_kota->nama_wilayah}}
                            @else
                            {{$data->konsumen_temp->alamat}}
                            @endif
                        </td>
                        <td style="width: 5%; vertical-align: top;"></td>
                        <td style="vertical-align: top;">No HP</td>
                        <td style="width: 15px; vertical-align: top;">:</td>
                        <td style="vertical-align: top;">
                            {{$data->konsumen ? $data->konsumen->no_hp : $data->konsumen_temp->no_hp}}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </table>
</div> --}}
<div class="po-items">
    <table class="table-items" style="font-size: 12px">
        <thead class=" table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">NAMA BARANG/MEREK</th>
                <th class="text-center align-middle">MEREK</th>
                <th class="text-center align-middle">QTY</th>
                <th class="text-center align-middle">SAT</th>
                {{-- <th class="text-center align-middle">HARGA SATUAN</th>
                <th class="text-center align-middle">HARGA DISKON</th>
                <th class="text-center align-middle">TOTAL HARGA</th> --}}
            </tr>
        </thead>
        <tbody>
            @foreach ($data->details as $d)
            <tr>
                <td style="text-align: center">
                    {{$loop->iteration}}
                </td>
                <td style="text-align: left">
                    {{$d->stok->barang_nama->nama}}, {{$d->stok->barang->kode}}
                </td>
                <td style="text-align: left">
                    {{$d->stok->barang->merk}}
                </td>
                <td style="text-align: center">
                    {{$d->nf_qty}}
                    @if ($d->is_grosir == 1)
                    <br>
                    ({{$d->nf_jumlah_grosir}})
                    @endif
                </td>
                <td style="text-align: center">
                    {{$d->stok->barang->satuan ? $d->stok->barang->satuan->nama : '-'}}
                    @if ($d->is_grosir == 1)
                    <br>
                    ({{$d->satuan_grosir ? $d->satuan_grosir->nama : '-'}})
                    @endif
                </td>
                {{-- <td style="text-align: right; padding-left:0.5rem">
                    @if ($data->kas_ppn == 1)
                    {{number_format($d->harga_satuan + floor($ppn / 100 * $d->harga_satuan), 0, ',','.')}}
                    @else
                    {{$d->nf_harga_satuan}}
                    @endif
                </td>
                <td style="text-align: right;">{{number_format($d->harga_satuan - $d->diskon + $d->ppn, 0, ',','.')}}</td>
                <td style="text-align: right; padding-left:0.5rem">
                    {{$d->nf_total}}
                </td> --}}
            </tr>
            @endforeach
        </tbody>

    </table>


</div>
{{-- <div style="font-size: 11px; margin-top: 10px; font-weight: bold; underline;">
    Transfer ke: {{$rekening->bank}} {{$rekening->no_rek}} a.n {{$rekening->nama_rek}}
</div> --}}
<table style="width: 100%; table-layout: fixed; margin-top: 10px; font-size: 11px; page-break-inside: avoid;">
    <tr>
        <td style="width: 50%; text-align: center; vertical-align: top;">
            <p><strong>{{ $pt->nama }}</strong></p>
            <div style="height: 60px;"></div>
            <p style="margin-bottom: 0;">
                <strong>_________________________</strong>
                <br>PENGIRIM
            </p>
        </td>
        <td style="width: 50%; text-align: center; vertical-align: top;">
            <p><strong>{{ $data->konsumen ? $data->konsumen->kode_toko->kode.' '.$data->konsumen->nama : $data->konsumen_temp->nama }}</strong></p>
            <div style="height: 60px;"></div>
            <p style="margin-bottom: 0;">
                <strong>_________________________</strong>
                <br>PENERIMA
            </p>
        </td>
    </tr>
</table>
@endsection
