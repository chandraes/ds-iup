@extends('layouts.app')
@section('content')
<div class="customer-ui">
<header class="customer-header"><div><p class="customer-eyebrow">DATABASE · RELASI PELANGGAN</p><h1>Biodata konsumen</h1><p>Kelola identitas, wilayah, dokumen, dan pengaturan pembayaran konsumen.</p></div><div class="customer-header-actions"><a href="{{ route('db') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Database</a><button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalKodeToko">Kode toko</button><button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createInvestor"><i class="fa fa-plus" aria-hidden="true"></i> Tambah konsumen</button></div></header>
@if($errors->any())<div class="alert alert-danger" role="alert"><strong>Data belum dapat disimpan.</strong><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@include('swal')
@include('db.konsumen.create')
@include('db.konsumen.edit')
@include('db.konsumen.kode-toko')
@include('db.konsumen.upload-foto')
@include('db.konsumen.diskon-khusus')
@include('db.konsumen.dokumen')
@include('db.konsumen.plafon')
@include('db.konsumen.sales-history')

<div class="customer-workspace">
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4 bg-light bg-opacity-50">

            <div class="d-flex align-items-center mb-3">
                <i class="fa fa-filter text-primary me-2"></i>
                <h6 class="mb-0 fw-bold text-secondary text-uppercase"
                    style="font-size: 0.85rem; letter-spacing: 0.5px;">Filter Pencarian</h6>
            </div>

            <div class="row g-3">

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label for="filterKodeToko" class="form-label small fw-bold text-muted mb-1">Kode Toko</label>
                    <select id="filterKodeToko" name="kode_toko"
                        class="form-select form-select-sm shadow-none border-secondary-subtle">
                        <option value="" selected>-- Semua Kode --</option>
                        @foreach ($kode_toko as $k)
                        <option value="{{ $k->id }}">{{ $k->kode }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label for="filterSalesArea" class="form-label small fw-bold text-muted mb-1">Sales Area</label>
                    <select id="filterSalesArea" name="area"
                        class="form-select form-select-sm shadow-none border-secondary-subtle">
                        <option value="" selected>-- Semua Area --</option>
                        @foreach ($sales_area as $salesArea)
                        <option value="{{ $salesArea->id }}">{{ $salesArea->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label for="filterKab" class="form-label small fw-bold text-muted mb-1">Kabupaten / Kota</label>
                    <select id="filterKab" name="kab_kot_id"
                        class="form-select form-select-sm shadow-none border-secondary-subtle"
                        onchange="filterKecamatan()">
                        <option value="">-- Semua Kab / Kota --</option>
                        @foreach ($kab_filter as $kab)
                        <option value="{{ $kab->id }}">{{ $kab->nama_wilayah }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label for="filterKecamatan" class="form-label small fw-bold text-muted mb-1">Kecamatan</label>
                    <select id="filterKecamatan" name="kecamatan"
                        class="form-select form-select-sm shadow-none border-secondary-subtle">
                        <option value="">-- Semua Kecamatan --</option>
                        {{-- @foreach ($kecamatan_filter as $kec)
                        <option value="{{ $kec->id }}">{{ $kec->nama_wilayah }}</option>
                        @endforeach --}}
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label for="filterStatus" class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select id="filterStatus" name="status"
                        class="form-select form-select-sm shadow-none border-secondary-subtle">
                        <option value="1">Aktif</option>
                        <option value="0">Non Aktif</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label for="filterStatusWa" class="form-label small fw-bold text-muted mb-1">WA Notif</label>
                    <select id="filterStatusWa" name="status_wa"
                        class="form-select form-select-sm shadow-none border-secondary-subtle">
                        <option value="">-- Semua --</option>
                        <option value="1">Aktif</option>
                        <option value="0">Non Aktif</option>
                    </select>
                </div>

                <div class="col-lg-6 col-md-12 col-sm-12 d-flex align-items-end justify-content-end">
                    <button type="button" id="resetFilter" class="btn btn-secondary btn-sm px-4 shadow-sm">
                        <i class="fa fa-sync-alt me-1"></i>Reset Filter
                    </button>
                </div>

            </div>
        </div>
</div>
<table id="data" class="table table-bordered table-hover" style="width:100%; font-size: 0.8rem;">
    <thead class="table-success">
        <tr>
            <th class="text-center align-middle">KODE</th>
            <th class="text-center align-middle">KODE TOKO</th>
            <th class="text-center align-middle">NAMA</th>
            <th class="text-center align-middle">DOKUMEN</th>
            <th class="text-center align-middle">CP</th>
            <th class="text-center align-middle">NPWP</th>
            <th class="text-center align-middle">NIK</th>
            <th class="text-center align-middle">KTP</th>
            <th class="text-center align-middle">Sales Area</th>
            <th class="text-center align-middle">Provinsi</th>
            <th class="text-center align-middle">Kab/Kota</th>
            <th class="text-center align-middle">Kecamatan</th>
            <th class="text-center align-middle">Alamat</th>
            <th class="text-center align-middle">Sistem<br>Pembayaran</th>
            <th class="text-center align-middle">Limit<br>Plafon</th>
            <th class="text-center align-middle">Diskon Khusus (%)</th>
            <th class="text-center align-middle">Checklist<br>Kunjungan</th>
            <th class="text-center align-middle">WA Notif</th>
            <th class="text-center align-middle">Alasan Penonaktifan</th>
            <th class="text-center align-middle">Aksi</th>
        </tr>
    </thead>
</table>
</div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('customer-biodata.css').'?v='.filemtime(public_path('customer-biodata.css')) }}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.1.1/css/scroller.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/cleave.min.js') }}"></script>
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.1.1/js/dataTables.scroller.min.js"></script>
<script id="customer-config" type="application/json">{!! json_encode([
    'dataUrl' => route('db.konsumen.data'), 'updateUrl' => route('db.konsumen.update', ':id'),
    'citiesUrl' => route('universal.get-kab-kota'), 'districtsUrl' => route('universal.get-kecamatan'),
    'salesUpdateUrl' => route('db.konsumen.update_sales', ':id'),
    'salesHistoryUrl' => route('db.konsumen.histori_sales', ':id'),
    'historyUrl' => route('db.konsumen.histori_plafon', ':id'), 'limitUrl' => route('db.konsumen.update_plafon', ':id'),
    'ktpUrl' => route('db.konsumen.upload-ktp', ':id'),
    'checklistUrl' => route('db.konsumen.toggle-checklist', ':id'), 'notificationUrl' => route('db.konsumen.toggle-notif', ':id'),
    'old' => old(), 'hasErrors' => $errors->any(),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('customer-biodata.js').'?v='.filemtime(public_path('customer-biodata.js')) }}"></script>
@endpush
