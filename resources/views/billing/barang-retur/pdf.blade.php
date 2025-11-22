@extends('layouts.doc')

@push('header')
{{-- Bagian header ini sengaja tidak diubah sesuai permintaan Anda --}}
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
<style>
    /* * PERUBAHAN CSS:
     * Semua style untuk PDF ini ada di sini
     */
    body {
        font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
        font-size: 11px;
        line-height: 1.4;
    }
    .content-wrapper {
        margin: 0 10px; /* Memberi sedikit margin dari tepi kertas */
    }

    /* Judul Utama */
    .title {
        text-align: center;
        margin-bottom: 15px;
        margin-top: -10px;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Bagian Info (Metadata) */
    .info-section {
        width: 100%;
        font-size: 10px;
        margin-bottom: 20px;
    }
    .info-left {
        float: left;
        width: 50%;
    }
    .info-right {
        float: right;
        width: 50%;
    }
    .info-table {
        width: 100%;
    }
    .info-table td {
        padding: 2px 0; /* Jarak vertikal antar baris info */
    }
    .info-table td:nth-child(1) { width: 30%; } /* Label (mis: Kode Retur) */
    .info-table td:nth-child(2) { width: 5%; }  /* Titik dua (:) */
    .info-table td:nth-child(3) { width: 65%; } /* Value */

    /* Tabel Data Utama */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-bottom: 20px;
    }
    .data-table thead {
        /* Latar belakang header tabel (abu-abu muda profesional) */
        background-color: #e9ecef;
    }
    .data-table th,
    .data-table td {
        /* Border yang lebih bersih */
        border: 1px solid #0d0d0d;
        padding: 6px 8px; /* Padding sel */
    }
    .data-table th {
        text-align: center;
        font-weight: bold;
        vertical-align: middle;
        text-transform: uppercase;
    }
    .data-table tbody tr:nth-child(even) {
        /* Zebra-striping untuk kemudahan membaca */
        background-color: #f8f9fa;
    }
    .data-table .text-center { text-align: center; }
    .data-table .text-left { text-align: left; }
    .data-table .text-right { text-align: right; }

    /* Tabel Tanda Tangan */
    .signature-table {
        width: 100%;
        table-layout: fixed;
        margin-top: 25px;
        font-size: 11px;
        page-break-inside: avoid; /* Mencegah terpotong di halaman */
    }
    .signature-table td {
        text-align: center;
        vertical-align: top;
        padding: 0 15px;
    }
    .signature-space {
        /* Menggantikan <div> kosong dengan padding */
        height: 70px;
    }
    .signature-label {
        /* Garis bawah untuk tanda tangan (lebih rapi) */
        border-top: 1px solid #333;
        padding-top: 5px;
        display: inline-block; /* Agar garis tidak full-width */
        min-width: 150px;
    }

    /* Utility */
    .clearfix {
        clear: both;
    }
</style>

<div class="content-wrapper">
    <h4 class="title">Barang Retur</h4>

    <div class="info-section">
        <div class="info-left">
            <table class="info-table">
                <tr>
                    <td>Kode Retur</td>
                    <td>:</td>
                    <td><strong>{{$data->kode}}</strong></td>
                </tr>
                <tr>
                    <td>Tipe Retur</td>
                    <td>:</td>
                    <td>{{$data->tipe == 1 ? 'Retur ke Supplier' : 'Retur dari Konsumen'}}</td>
                </tr>

                @if ($data->tipe == 1)
                {{-- Tipe 1 = Retur ke Supplier --}}
                <tr>
                    <td style="vertical-align: top;">Supplier</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;"><strong>{{$data->barang_unit->nama}}</strong></td>
                </tr>
                @else
                {{-- Tipe 2 = Retur dari Konsumen --}}
                <tr>
                    <td style="vertical-align: top;">Konsumen</td>
                    <td style="vertical-align: top;">:</td>
                    <td style="vertical-align: top;">
                        <strong>{{$data->konsumen ? $data->konsumen->kode_toko?->kode.' '.$data->konsumen->nama : ''}}</strong>
                        <br>
                        {{-- Dibuat lebih kecil agar tidak terlalu dominan --}}
                        <small style="color: #555;">{{$data->konsumen->alamat ?? ''}}</small>
                    </td>
                </tr>
                @endif
            </table>
        </div>
        <div class="info-right">
            <table class="info-table">
                <tr>
                    <td>Tanggal</td>
                    <td>:</td>
                    <td><strong>{{$tanggal}}</strong></td>
                </tr>
            </table>
        </div>
        <div class="clearfix"></div>
    </div>
     <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 55%;">Nama Barang</th>
                <th style="width: 15%;">Qty</th>
                <th style="width: 25%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->details as $d)
            <tr>
                <td class="text-center">{{$loop->iteration}}</td>
                <td class="text-left">
                    {{$d->barang?->barang_nama->nama}}, {{$d->barang?->kode}}
                    <br>
                    <small style="color: #555;">Merek: {{$d->barang?->merk}}</small>
                </td>
                <td class="text-center">{{$d->nf_qty}}</td>
                <td class="text-center">{{$d->barang?->satuan ? $d->barang?->satuan->nama : '-'}}</td>

            </tr>
            @endforeach
        </tbody>


    </table>

    <table class="signature-table">
        <tr>
            <td style="width: 50%;">
                <p><strong>{{ $pt->nama }}</strong></p>
                <div class="signature-space"></div>
                <p class="signature-label">
                    <strong>PENGIRIM</strong>
                </p>
            </td>
            <td style="width: 50%;">
                <p><strong>{{ $data->konsumen ? $data->konsumen->kode_toko->kode.' '.$data->konsumen->nama : $data->konsumen_temp->nama }}</strong></p>
                <div class="signature-space"></div>
                <p class="signature-label">
                    <strong>PENERIMA</strong>
                </p>
            </td>
        </tr>
    </table>
</div>
@endsection
