@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center my-4">
            <h3><u>OMSET BULANAN PER BARANG</u></h3>
            <p class="text-muted small" id="info-mode-teks">Menampilkan data berdasarkan <strong>QTY (Jumlah Barang)</strong></p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
       <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <img src="{{ asset('images/dashboard.svg') }}" alt="dashboard" width="20">
                <span>Dashboard</span>
            </a>
            @if (auth()->user()->role != 'asisten-admin' && auth()->user()->role != 'perusahaan')
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
                    <label class="form-label fw-bold small">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= 2015; $y--)
                            <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                @if (auth()->user()->role != 'perusahaan')
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Perusahaan</label>
                    <select name="barang_unit_id" id="barang_unit_id" class="form-select form-select-sm">
                        <option value="">-- Semua Perusahaan --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @endif


                <div class="col-md-3">
                    <label class="form-label fw-bold small">Kelompok Barang</label>
                    <select name="barang_kategori_id" id="barang_kategori_id" class="form-select form-select-sm">
                        <option value="">-- Semua Kelompok --</option>
                        @foreach($kategoris as $kategori)
                            <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- TOGGLE MODE TAMPILAN --}}
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-primary">Tampilkan Berdasarkan</label>
                    <select name="mode_tampil" id="mode_tampil" class="form-select form-select-sm border-primary">
                        <option value="qty">Qty Terjual (Jumlah Barang)</option>
                        <option value="nominal">Total Omset Uang (Rp)</option>
                    </select>
                </div>

                {{-- FILTER BULAN --}}
                <div class="col-md-5">
                    <label class="form-label fw-bold small">Pilih Bulan</label>
                    <select name="bulan[]" id="bulan" class="form-select form-select-sm" multiple="multiple" data-placeholder="-- Semua Bulan --">
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>

                {{-- FILTER STATUS OMSET --}}
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Status Omset</label>
                    <select name="status_omset" id="status_omset" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        <option value="ada">Ada Omset (> 0)</option>
                        <option value="nol">Tidak Ada (0)</option>
                    </select>
                </div>

                <div class="col-md-4 d-flex align-items-end justify-content-end gap-2">
                    <button type="button" id="btn-filter" class="btn btn-primary btn-sm" title="Tampilkan Data">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button type="button" id="btn-reset" class="btn btn-secondary btn-sm" title="Reset Filter">
                        <i class="fa fa-undo"></i> Reset
                    </button>
                    <div class="vr mx-1"></div>
                    <button type="button" id="btn-excel" class="btn btn-success btn-sm" title="Download Excel">
                        <i class="fa fa-file-excel"></i> Excel
                    </button>
                    <button type="button" id="btn-print" class="btn btn-danger btn-sm" title="Cetak / PDF">
                        <i class="fa fa-print"></i> PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabel Area --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="omsetBarangTable" class="table table-bordered table-striped table-hover w-100" style="font-size: 0.8rem;">
                    <thead class="table-dark text-center text-nowrap">
                        <tr>
                            <th class="align-middle">No</th>
                            <th class="align-middle">Perusahaan</th>
                            <th class="align-middle">Kelompok</th>
                            <th class="align-middle">Kode</th>
                            <th class="align-middle">Merk</th>
                            <th class="align-middle">Nama Barang</th>
                            <th class="align-middle">Satuan</th>
                            <th class="align-middle">Jan</th>
                            <th class="align-middle">Feb</th>
                            <th class="align-middle">Mar</th>
                            <th class="align-middle">Apr</th>
                            <th class="align-middle">Mei</th>
                            <th class="align-middle">Jun</th>
                            <th class="align-middle">Jul</th>
                            <th class="align-middle">Agu</th>
                            <th class="align-middle">Sep</th>
                            <th class="align-middle">Okt</th>
                            <th class="align-middle">Nov</th>
                            <th class="align-middle">Des</th>
                            <th class="align-middle bg-primary border-primary">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot class="table-dark text-center text-nowrap">
                        <tr>
                            <th colspan="7" class="text-end align-middle pe-3">GRAND TOTAL</th>
                            <th id="ft-b1" class="text-end align-middle">0</th>
                            <th id="ft-b2" class="text-end align-middle">0</th>
                            <th id="ft-b3" class="text-end align-middle">0</th>
                            <th id="ft-b4" class="text-end align-middle">0</th>
                            <th id="ft-b5" class="text-end align-middle">0</th>
                            <th id="ft-b6" class="text-end align-middle">0</th>
                            <th id="ft-b7" class="text-end align-middle">0</th>
                            <th id="ft-b8" class="text-end align-middle">0</th>
                            <th id="ft-b9" class="text-end align-middle">0</th>
                            <th id="ft-b10" class="text-end align-middle">0</th>
                            <th id="ft-b11" class="text-end align-middle">0</th>
                            <th id="ft-b12" class="text-end align-middle">0</th>
                            <th id="ft-total" class="text-end align-middle bg-primary border-primary">0</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.bootstrap5.min.css">
<style>
    /* Ratakan angka ke kanan, mulai dari kolom Jan (index 8/nth-child 8 pada CSS) */
    #omsetBarangTable tbody td:nth-child(n+8) { text-align: right; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/scroller.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>

<script>
// --- FUNGSI HELPER UNTUK LINK DETAIL ---
function renderLinkBarang(data, type, row, meta, bulan) {
    if(type !== 'display') return data;

    // Bersihkan html dan titik pemisah ribuan
    var cleanData = (data + '').replace(/<[^>]*>?/gm, '');
    var numericValue = parseInt(cleanData.replace(/\./g, ''));

    // Jika nilainya lebih dari 0, buat jadi link
    if (numericValue > 0) {
        var tahun = $('#tahun').val();
        var unitId = $('#barang_unit_id').val();
        var kategoriId = $('#barang_kategori_id').val();
        var modeTampil = $('#mode_tampil').val();
        var selectedBulan = $('#bulan').val();

        // Base URL ke halaman Detail
        var baseUrl = "{{ route('statistik.omset-barang.bulanan.detail_page', ['barang' => 'XXX', 'bulan' => 'YYY', 'tahun' => 'ZZZ']) }}";
        var finalUrl = baseUrl.replace('XXX', row.id).replace('YYY', bulan).replace('ZZZ', tahun);

        // Sisipkan Parameter Filter Lainnya (unit, kategori, dll)
        var params = [];
        if(unitId) params.push("barang_unit_id=" + unitId);
        if(kategoriId) params.push("barang_kategori_id=" + kategoriId);
        if(modeTampil) params.push("mode_tampil=" + modeTampil);
        if(selectedBulan && selectedBulan.length > 0) params.push("bulanFilter=" + selectedBulan.join(','));

        if(params.length > 0) {
            finalUrl += "?" + params.join('&');
        }

        return '<a href="' + finalUrl + '" class="text-decoration-none fw-bold text-primary" target="_blank" title="Lihat Detail Transaksi">' + data + '</a>';
    }

    // Jika 0, biarkan saja teks biasa (tidak usah link)
    return data;
}

$(document).ready(function() {

    // Inisialisasi Select2
    $('#barang_unit_id, #barang_kategori_id, #bulan').select2({
        theme: 'bootstrap-5',
        width: '100%',
        allowClear: true
    });

    $('#barang_unit_id').on('change', function() {
        var unitId = $(this).val();
        var kategoriSelect = $('#barang_kategori_id');

        // Kosongkan dropdown kategori dan beri opsi default
        kategoriSelect.empty().append('<option value="">-- Semua Kelompok --</option>');

        // Lakukan AJAX request ke route baru
        $.ajax({
            url: "{{ route('universal.get-kategori-by-unit') }}",
            type: "GET",
            data: {
                barang_unit_id: unitId
            },
            success: function(data) {
                // Looping data dari response JSON dan masukkan ke dropdown
                $.each(data, function(key, kategori) {
                    kategoriSelect.append('<option value="'+ kategori.id +'">'+ kategori.nama +'</option>');
                });

                // Refresh Select2 UI agar opsi baru muncul
                kategoriSelect.trigger('change.select2');
            },
            error: function() {
                console.error("Gagal mengambil data kategori.");
            }
        });
    });

    // Inisialisasi DataTables
    var table = $('#omsetBarangTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('statistik.omset-barang.bulanan') }}",
            data: function (d) {
                d.tahun = $('#tahun').val();
                d.barang_unit_id = $('#barang_unit_id').val();
                d.barang_kategori_id = $('#barang_kategori_id').val();
                d.mode_tampil = $('#mode_tampil').val();
                d.status_omset = $('#status_omset').val();
                d.bulan = $('#bulan').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'perusahaan', name: 'unit.nama', className: 'text-wrap'},
            {data: 'kategori', name: 'kategori.nama', className: 'text-wrap'},
            {data: 'kode', name: 'barangs.kode', className: 'text-center'},
            {data: 'merk', name: 'barangs.merk', className: 'text-wrap'},
            {data: 'nama_barang', name: 'barang_nama.nama', className: 'text-wrap'},
            {data: 'satuan', name: 'satuan.nama', className: 'text-center'},
            // Kolom Bulan menggunakan fungsi renderLinkBarang
            {data: 'bulan_1', name: 'bulan_1', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 1); }},
            {data: 'bulan_2', name: 'bulan_2', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 2); }},
            {data: 'bulan_3', name: 'bulan_3', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 3); }},
            {data: 'bulan_4', name: 'bulan_4', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 4); }},
            {data: 'bulan_5', name: 'bulan_5', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 5); }},
            {data: 'bulan_6', name: 'bulan_6', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 6); }},
            {data: 'bulan_7', name: 'bulan_7', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 7); }},
            {data: 'bulan_8', name: 'bulan_8', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 8); }},
            {data: 'bulan_9', name: 'bulan_9', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 9); }},
            {data: 'bulan_10', name: 'bulan_10', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 10); }},
            {data: 'bulan_11', name: 'bulan_11', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 11); }},
            {data: 'bulan_12', name: 'bulan_12', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 12); }},
            // Kolom Total
            {data: 'total', name: 'transaksi.total_setahun', orderable: true, searchable: false, render: function(d,t,r,m){ return renderLinkBarang(d,t,r,m, 'total'); }}
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[5, 'asc']], // Urut berdasarkan Nama Barang
        scrollY: 500,
        scrollX: true,
        scroller: true,
        scrollCollapse: true,
        deferRender: true,
        dom: 'frti',
        drawCallback: function(settings) {
            var api = this.api();
            var json = api.ajax.json();

            if (json) {
                // 1. Dinamis Sembunyikan/Tampilkan Kolom Bulan berdasarkan filter Select2 Multiple
                if (json.bulan_array !== undefined) {
                    var selectedBulan = json.bulan_array;

                    for (var i = 1; i <= 12; i++) {
                        var colIdx = i + 6; // Index kolom tabel. (No=0 ... Satuan=6, Jan=7)
                        var column = api.column(colIdx);

                        if (selectedBulan.length === 0 || selectedBulan.includes(i.toString())) {
                            column.visible(true);
                        } else {
                            column.visible(false);
                        }
                    }
                }

                // 2. Kalkulasi Grand Totals Footer
                if (json.grand_totals) {
                    var totals = json.grand_totals;
                    var mode = json.mode;
                    var prefix = (mode === 'nominal') ? '' : '';

                    var formatAngka = function(num) {
                        return prefix + new Intl.NumberFormat('id-ID').format(num || 0);
                    };

                    for(let i=1; i<=12; i++){
                        $('#ft-b' + i).text(formatAngka(totals['sum_b' + i]));
                    }
                    $('#ft-total').html('<strong>' + formatAngka(totals.sum_total) + '</strong>');

                    // Ganti teks panduan QTY/Rp
                    var modeTeks = (mode === 'qty') ? 'QTY (Jumlah Barang)' : 'TOTAL OMSET UANG (Rp)';
                    $('#info-mode-teks').html('Menampilkan data berdasarkan <strong>' + modeTeks + '</strong>');
                }
            }
        }
    });

    // --- TOMBOL FILTER ---
    $('#btn-filter').click(function(){
        table.draw();
    });

    // --- TOMBOL RESET ---
    $('#btn-reset').click(function(){
        $('#tahun').val(new Date().getFullYear());
        $('#barang_unit_id').val('').trigger('change');
        $('#barang_kategori_id').val('').trigger('change');
        $('#bulan').val(null).trigger('change');
        $('#status_omset').val('');
        $('#mode_tampil').val('qty');
        table.draw();
    });

    // --- TOMBOL EXCEL ---
    $('#btn-excel').click(function() {
        var params = $.param({
            tahun: $('#tahun').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            barang_kategori_id: $('#barang_kategori_id').val(),
            mode_tampil: $('#mode_tampil').val(),
            status_omset: $('#status_omset').val(),
            bulan: $('#bulan').val()
        });
        window.location.href = "{{ route('statistik.omset-barang.bulanan.excel') }}?" + params;
    });

    // --- TOMBOL PDF ---
    $('#btn-print').click(function() {
        var params = $.param({
            tahun: $('#tahun').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            barang_kategori_id: $('#barang_kategori_id').val(),
            mode_tampil: $('#mode_tampil').val(),
            status_omset: $('#status_omset').val(),
            bulan: $('#bulan').val()
        });
        window.open("{{ route('statistik.omset-barang.bulanan.print') }}?" + params, '_blank');
    });

});
</script>
@endpush
