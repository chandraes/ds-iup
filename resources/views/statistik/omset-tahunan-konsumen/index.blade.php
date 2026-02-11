@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center my-4">
            <h3><u>OMSET TAHUNAN KONSUMEN</u></h3>
        </div>
    </div>
      <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <img src="{{ asset('images/dashboard.svg') }}" alt="dashboard" width="20">
                <span>Dashboard</span>
            </a>
            @if (auth()->user()->role != 'asisten-admin')
            <a href="{{ route('statistik') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <img src="{{ asset('images/statistik.svg') }}" alt="database" width="20">
                <span>Statistik</span>
            </a>
            @endif
        </div>
    </div>

    {{-- Filter Area --}}
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Perusahaan</label>
                    <select name="barang_unit_id" id="barang_unit_id" class="form-select">
                        <option value="">-- Semua Perusahaan --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    {{-- Tombol Filter --}}
                    <button type="button" id="btn-filter" class="btn btn-primary" title="Tampilkan Data">
                        <i class="fa fa-filter"></i>
                    </button>

                    {{-- Tombol Reset --}}
                    <button type="button" id="btn-reset" class="btn btn-secondary" title="Reset Filter">
                        <i class="fa fa-undo"></i>
                    </button>

                    {{-- Separator --}}
                    <div class="vr"></div>

                    {{-- Tombol Excel --}}
                    <button type="button" id="btn-excel" class="btn btn-success" title="Download Excel">
                        <i class="fa fa-file-csv"></i> Excel
                    </button>

                    {{-- Tombol PDF --}}
                    <button type="button" id="btn-print" class="btn btn-danger" title="Cetak / PDF">
                        <i class="fa fa-print"></i> Cetak PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Area --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="omsetTable" class="table table-bordered table-striped table-hover w-100" style="font-size: 0.8rem;">
                    <thead class="table-dark text-center text-nowrap">
                        <tr>
                            <th>No</th>
                            <th>Kode</th>
                            <th>Kode Toko</th>
                            <th>Nama Toko</th>
                            <th>Jan</th>
                            <th>Feb</th>
                            <th>Mar</th>
                            <th>Apr</th>
                            <th>Mei</th>
                            <th>Jun</th>
                            <th>Jul</th>
                            <th>Agu</th>
                            <th>Sep</th>
                            <th>Okt</th>
                            <th>Nov</th>
                            <th>Des</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    {{-- Footer untuk Grand Total (Optional, perlu request AJAX terpisah jika ingin akurat per page) --}}
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.bootstrap5.min.css">
<style>
    /* Agar kolom angka rata kanan */
    #omsetTable tbody td:nth-child(n+5) { text-align: right; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/scroller.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>
<script>

$(document).ready(function() {

    // Inisialisasi Select2
    $('#barang_unit_id').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Inisialisasi DataTables
    var table = $('#omsetTable').DataTable({
        processing: true,
        serverSide: true, // PENTING: Mengaktifkan mode server-side
        ajax: {
            url: "{{ route('statistik.omset-tahunan-konsumen') }}", // Ganti dengan nama route Anda
            data: function (d) {
                d.tahun = $('#tahun').val();
                d.barang_unit_id = $('#barang_unit_id').val();
            }
        },
        columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'full_kode', name: 'konsumens.kode'},
                {data: 'kode_toko', name: 'kode_toko'},
                {data: 'nama', name: 'konsumens.nama'},

                // --- MULAI KOLOM BULAN 1 s/d 12 ---
                {data: 'bulan_1', name: 'bulan_1', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 1); }},

                {data: 'bulan_2', name: 'bulan_2', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 2); }},

                {data: 'bulan_3', name: 'bulan_3', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 3); }},

                {data: 'bulan_4', name: 'bulan_4', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 4); }},

                {data: 'bulan_5', name: 'bulan_5', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 5); }},

                {data: 'bulan_6', name: 'bulan_6', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 6); }},

                {data: 'bulan_7', name: 'bulan_7', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 7); }},

                {data: 'bulan_8', name: 'bulan_8', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 8); }},

                {data: 'bulan_9', name: 'bulan_9', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 9); }},

                {data: 'bulan_10', name: 'bulan_10', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 10); }},

                {data: 'bulan_11', name: 'bulan_11', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 11); }},

                {data: 'bulan_12', name: 'bulan_12', orderable: false, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 12); }},

                // --- KOLOM TOTAL SETAHUN ---
                {data: 'total', name: 'transaksi.total_setahun', orderable: true, searchable: false, className: 'text-end',
                render: function(data, type, row) { return renderLink(data, row, 'total'); }}
            ],
        pageLength: 25, // Default jumlah data per halaman
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'asc']], // Default urut berdasarkan Nama Toko
        scrollY: 500,
        scrollX: true,
        scroller: true,
        scrollCollapse: true,
        deferRender: true,
        dom: 'frti',
    });

    // Tombol Filter
    $('#btn-filter').click(function(){
        table.draw();
    });

    $('#btn-reset').click(function(){
        // 1. Reset Select Tahun ke Tahun sekarang
        $('#tahun').val(new Date().getFullYear());

        // 2. Reset Select2 Unit Barang (Penting pakai trigger change)
        $('#barang_unit_id').val('').trigger('change');

        // 3. Refresh Tabel
        table.draw();
    });

    $('#btn-excel').click(function() {
        var tahun = $('#tahun').val();
        var unit = $('#barang_unit_id').val();

        // Redirect ke URL download dengan query params
        var url = "{{ route('statistik.omset.excel') }}?tahun=" + tahun + "&barang_unit_id=" + unit;
        window.location.href = url;
    });

    // TOMBOL PDF
   $('#btn-print').click(function() {
        var tahun = $('#tahun').val();
        var unit = $('#barang_unit_id').val();

        // Arahkan ke route cetak baru
        var url = "{{ route('statistik.omset.print') }}?tahun=" + tahun + "&barang_unit_id=" + unit;
        window.open(url, '_blank');
    });

    // Fungsi Helper Membuat Link
    function renderLink(data, row, bulan) {
        // 1. Bersihkan data dari tag HTML (jika ada bold) dan titik ribuan
        var cleanData = (data + '').replace(/<[^>]*>?/gm, '');
        var numericValue = parseInt(cleanData.replace(/\./g, ''));

        // 2. Ambil nilai filter saat ini
        var tahun = $('#tahun').val();
        var unitId = $('#barang_unit_id').val();

        // 3. Logic: Jika nilai > 0 buat Link, jika 0 kembalikan teks asli
        if (numericValue > 0) {
            // Base URL Route Laravel
            // Kita gunakan placeholder 'XXX', 'YYY', 'ZZZ' lalu replace manual dengan JS
            // Ini trik agar route() blade tidak error karena parameter JS belum ada
            var baseUrl = "{{ route('statistik.omset.detail_page', ['konsumen' => 'XXX', 'bulan' => 'YYY', 'tahun' => 'ZZZ']) }}";

            // Ganti placeholder dengan data asli baris ini
            var finalUrl = baseUrl
                .replace('XXX', row.id)
                .replace('YYY', bulan)
                .replace('ZZZ', tahun);

            // Tambahkan query param untuk unit jika ada
            if(unitId) {
                finalUrl += "?unit_id=" + unitId;
            }

            return '<a href="' + finalUrl + '" class="text-decoration-none fw-bold" target="_blank" title="Lihat Detail">' + data + '</a>';
        }

        return data; // Kembalikan angka 0 atau teks asli
    }
});
</script>
@endpush
