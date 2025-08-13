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
<div class="tujuan-div">
    <table style="font-size: 12px">
        <div class="row invoice-info">
            <div class="col-md-6 invoice-col">
                <table style="width: 100%; font-size: 10px">
                    <tr id="namaTr">
                        <td style="width: 20%">Konsumen</td>
                        <td style="width: 15px">:</td>
                        <td style="width: 150px">
                            <strong>{{$data->konsumen ? $data->konsumen->kode_toko->kode.' '.$data->konsumen->nama :
                                $data->konsumen_temp->nama}}</strong>
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
                        <td>Tempo</td>
                        <td style="width: 15px">:</td>
                        <td>
                            {{$data->sistem_pembayaran !== 1 ? $data->konsumen->tempo_hari . ' Hari' : '-'}} </td>
                        <td style="width: 5%"></td>
                        <td>Tanggal Tempo</td>
                        <td style="width: 15px">:</td>
                        <td>
                            {{$tanggal_tempo}}
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
</div>
<div class="po-items">
    <table class="table-items" style="font-size: 12px">
        <thead class=" table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">NAMA BARANG/MEREK</th>
                <th class="text-center align-middle">QTY</th>
                <th class="text-center align-middle">SAT</th>
                <th class="text-center align-middle">HARGA SATUAN
                    {{$data->kas_ppn == 1 ? '(DPP)' : ''}}
                </th>
                <th class="text-center align-middle">DISKON
                    {{$data->kas_ppn == 1 ? '(DPP)' : ''}}
                </th>
                <th class="text-center align-middle">HARGA DISKON
                    {{$data->kas_ppn == 1 ? '(DPP)' : ''}}
                </th>
                @if ($data->kas_ppn == 1)
                <th class="text-center align-middle">HARGA DISKON
                    (PPN)
                </th>
                @endif
                <th class="text-center align-middle">TOTAL HARGA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->invoice_detail as $d)
            <tr>
                <td style="text-align: center">
                    {{$loop->iteration}}
                </td>
                <td style="text-align: left">
                    {{$d->stok->barang_nama->nama}}, {{$d->stok->barang->kode}}, {{$d->stok->barang->merk}}
                </td>
                <td style="text-align: center">
                    {{$d->nf_jumlah}}
                </td>
                <td style="text-align: center">
                    {{$d->stok->barang->satuan ? $d->stok->barang->satuan->nama : '-'}}
                </td>
                <td style="text-align: right; padding-left:0.5rem">
                    {{$d->nf_harga_satuan}}
                </td>
                <td style="text-align: right;">
                    {{$d->nf_diskon}}
                </td>
                 @if ($data->kas_ppn == 1)
                <td style="text-align: right;">{{number_format($d->harga_satuan - $d->diskon, 0, ',','.')}}</td>
                @endif
                  <td style="text-align: right;">{{number_format($d->harga_satuan - $d->diskon + $d->ppn, 0, ',','.')}}</td>
                <td style="text-align: right; padding-left:0.5rem">
                    {{$d->nf_total}}
                </td>
            </tr>
            @endforeach
        </tbody>
        {{-- <tfoot style="page-break-inside: avoid;">
            <tr>
                <th style="text-align: right">Total DPP : </th>
                <th style="text-align: right; padding-left:0.5rem">{{$data->dpp}}</th>
            </tr>
            <tr>
                <th style="text-align: right">Diskon : </th>
                <th style="text-align: right">{{number_format($data->diskon, 0 ,',', '.')}}</th>
            </tr>
            <tr>
                <th style="text-align: right">DPP Setelah Diskon : </th>
                <th style="text-align: right">{{number_format($data->total-$data->diskon, 0 ,',', '.')}}</th>
            </tr>
            <tr>
                <th style="text-align: right">Ppn : </th>
                <th style="text-align: right">{{$data->nf_ppn}}</th>
            </tr>
            <tr>
                <th style="text-align: right">Penyesuaian : </th>
                <th style="text-align: right">{{number_format($data->add_fee, 0 ,',', '.')}}</th>
            </tr>
            <tr>
                <th style="text-align: right">Grand Total : </th>
                <th style="text-align: right">{{$data->nf_grand_total}}</th>
            </tr>
            @if ($data->konsumen && $data->konsumen->pembayaran == 2 && $data->lunas == 0)
            <tr>
                <th style="text-align: right">DP : </th>
                <th style="text-align: right">{{$data->nf_dp}}</th>
            </tr>
            @if ($data->ppn > 0)
            <tr>
                <th style="text-align: right">DP PPn : </th>
                <th style="text-align: right">{{$data->nf_dp_ppn}}</th>
            </tr>
            @endif
            <tr>
                <th style="text-align: right">Sisa Tagihan : </th>
                <th style="text-align: right">{{$data->nf_sisa_tagihan}}</th>
            </tr>
            <tr>
                <th colspan="8"><strong># {{$terbilang}} Rupiah #</strong></th>
            </tr>
            @else
            <tr>
                <th colspan="8"><strong># {{$terbilang}} Rupiah #</strong></th>
            </tr>
            @endif
        </tfoot> --}}
    </table>

    <div style="page-break-inside: avoid; display: table; width: 100%; margin-top: 10px;">
        <div style="display: table-footer-group;">
            <table style="width: 100%; font-size: 10px; text-align: right;">
                {{-- <tr>
                    <th style="text-align: right; width:80%">Total DPP </th>
                    <th style="text-align: right; width:5%">:</th>
                    <th style="text-align: right; padding-left:0.5rem; width:12%">{{$data->dpp}}</th>
                </tr>
                <tr>
                    <th style="text-align: right">Diskon </th>
                    <th style="text-align: right; padding-left:0.5rem">:</th>
                    <th style="text-align: right">{{number_format($data->diskon, 0 ,',', '.')}}</th>
                </tr>
                <tr>
                    <th style="text-align: right">DPP Setelah Diskon </th>
                    <th style="text-align: right; padding-left:0.5rem">:</th>
                    <th style="text-align: right">{{number_format($data->total-$data->diskon, 0 ,',', '.')}}</th>
                </tr>
                @if ($data->kas_ppn === 1)
                <tr>
                    <th style="text-align: right">Ppn</th>
                    <th style="text-align: right; padding-left:0.5rem">:</th>
                    <th style="text-align: right">{{$data->nf_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th style="text-align: right">Penyesuaian</th>
                    <th style="text-align: right; padding-left:0.5rem">:</th>
                    <th style="text-align: right">{{number_format($data->add_fee, 0 ,',', '.')}}</th>
                </tr> --}}
                <tr>
                    <th style="text-align: right; width:80%">Grand Total </th>
                    <th style="text-align: right; padding-left:0.5rem">:</th>
                    <th style="text-align: right">{{$data->nf_grand_total}}</th>
                </tr>
                @if ($data->konsumen && $data->konsumen->pembayaran == 2 && $data->lunas == 0)
                <tr>
                    <th style="text-align: right">DP </th>
                    <th style="text-align: right; padding-left:0.5rem">:</th>
                    <th style="text-align: right">{{number_format($data->dp + $data->dp_ppn, 0,',','.')}}</th>
                </tr>
                <tr>
                    <th style="text-align: right">Sisa Tagihan </th>
                    <th style="text-align: right; padding-left:0.5rem">:</th>
                    <th style="text-align: right">{{$data->nf_sisa_tagihan}}</th>
                </tr>
                <tr>
                    <th colspan="3"
                        style="border-top: 1px solid black; border-bottom: 1px solid black; padding-top: 0.5rem; padding-bottom: 0.5rem">
                        <strong># {{$terbilang}} Rupiah #</strong></th>
                </tr>
                @else
                <tr>
                    <th colspan="3"
                        style="border-top: 1px solid black; border-bottom: 1px solid black; padding-top: 0.5rem; padding-bottom: 0.5rem">
                        <strong># {{$terbilang}} Rupiah #</strong></th>
                </tr>
                @endif
            </table>
        </div>
    </div>
</div>
<div style="font-size: 11px; margin-top: 10px; font-weight: bold; underline;">
    Transfer ke: {{$rekening->bank}} {{$rekening->no_rek}} a.n {{$rekening->nama_rek}}
</div>
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
            <p><strong>{{ $data->konsumen ? $data->konsumen->nama : $data->konsumen_temp->nama }}</strong></p>
            <div style="height: 60px;"></div>
            <p style="margin-bottom: 0;">
                <strong>_________________________</strong>
                <br>PENERIMA
            </p>
        </td>
    </tr>
</table>
@endsection
