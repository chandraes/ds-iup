@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center my-4">
            <h3><u>OMSET TAHUNAN PER BARANG</u></h3>
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
                    <label class="form-label fw-bold small">Tahun Awal</label>
                    <select name="tahun_awal" id="tahun_awal" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= 2015; $y--)
                            <option value="{{ $y }}" {{ $y == date('Y')-4 ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Tahun Akhir</label>
                    <select name="tahun_akhir" id="tahun_akhir" class="form-select form-select-sm">
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

                <div class="col-md-2">
                    <label class="form-label fw-bold small">Status Omset</label>
                    <select name="status_omset" id="status_omset" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        <option value="ada">Ada Omset (> 0)</option>
                        <option value="nol">Tidak Ada (0)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold small text-primary">Tampilkan Berdasarkan</label>
                    <select name="mode_tampil" id="mode_tampil" class="form-select form-select-sm border-primary">
                        <option value="qty">Qty Terjual (Jumlah Barang)</option>
                        <option value="nominal">Total Omset Uang (Rp)</option>
                    </select>
                </div>

                <div class="col-md-9 d-flex align-items-end justify-content-end gap-2">
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
                <table id="omsetTahunanBarangTable" class="table table-bordered table-striped table-hover w-100" style="font-size: 0.8rem;">
                    <thead class="table-dark text-center text-nowrap">
                        <tr>
                            <th class="align-middle">No</th>
                            <th class="align-middle">Perusahaan</th>
                            <th class="align-middle">Kelompok</th>
                            <th class="align-middle">Kode</th>
                            <th class="align-middle">Merk</th>
                            <th class="align-middle">Nama Barang</th>
                            <th class="align-middle">Satuan</th>
                            <th class="align-middle" id="th-t1">Tahun 1</th>
                            <th class="align-middle" id="th-t2">Tahun 2</th>
                            <th class="align-middle" id="th-t3">Tahun 3</th>
                            <th class="align-middle" id="th-t4">Tahun 4</th>
                            <th class="align-middle" id="th-t5">Tahun 5</th>
                            <th class="align-middle bg-primary border-primary">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot class="table-dark text-center text-nowrap">
                        <tr>
                            <th colspan="7" class="text-end align-middle pe-3">GRAND TOTAL</th>
                            <th id="ft-t1" class="text-end align-middle">0</th>
                            <th id="ft-t2" class="text-end align-middle">0</th>
                            <th id="ft-t3" class="text-end align-middle">0</th>
                            <th id="ft-t4" class="text-end align-middle">0</th>
                            <th id="ft-t5" class="text-end align-middle">0</th>
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
    /* Ratakan angka ke kanan, mulai dari kolom Tahun 1 (index 8) */
    #omsetTahunanBarangTable tbody td:nth-child(n+8) { text-align: right; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/scroller.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>

<script>
// --- FUNGSI HELPER UNTUK LINK DETAIL TAHUNAN ---
function renderLinkBarangTahunan(data, type, row, meta, indexTahun) {
    if(type !== 'display') return data;

    var cleanData = (data + '').replace(/<[^>]*>?/gm, '');
    var numericValue = parseInt(cleanData.replace(/\./g, ''));

    if (numericValue > 0) {
        var tahunAwal = parseInt($('#tahun_awal').val());
        var tahunAkhir = parseInt($('#tahun_akhir').val());
        var unitId = $('#barang_unit_id').val();
        var kategoriId = $('#barang_kategori_id').val();
        var modeTampil = $('#mode_tampil').val();

        var tahunKlik = 'total';
        if (indexTahun !== 'total') {
            tahunKlik = tahunAwal + (indexTahun - 1);
            if (tahunKlik > tahunAkhir) return data; // Cegah klik pada tahun yang melebihi range filter
        }

        // URL (Sesuaikan dengan route detail Anda nanti)
        var baseUrl = "{{ route('statistik.omset-barang.tahunan.detail_page', ['barang' => 'XXX', 'tahun' => 'YYY']) }}";
        var finalUrl = baseUrl.replace('XXX', row.id).replace('YYY', tahunKlik);

        var params = [];
        if(unitId) params.push("barang_unit_id=" + unitId);
        if(kategoriId) params.push("barang_kategori_id=" + kategoriId);
        if(modeTampil) params.push("mode_tampil=" + modeTampil);
        params.push("tahun_awal=" + tahunAwal);
        params.push("tahun_akhir=" + tahunAkhir);

        if(params.length > 0) finalUrl += "?" + params.join('&');

        return '<a href="' + finalUrl + '" class="text-decoration-none fw-bold text-primary" target="_blank" title="Lihat Detail Transaksi">' + data + '</a>';
    }

    return data;
}

$(document).ready(function() {

    $('#barang_unit_id, #barang_kategori_id, #tahun_awal, #tahun_akhir').select2({
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

    var table = $('#omsetTahunanBarangTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('statistik.omset-barang.tahunan') }}",
            data: function (d) {
                d.tahun_awal = $('#tahun_awal').val();
                d.tahun_akhir = $('#tahun_akhir').val();
                d.barang_unit_id = $('#barang_unit_id').val();
                d.barang_kategori_id = $('#barang_kategori_id').val();
                d.mode_tampil = $('#mode_tampil').val();
                d.status_omset = $('#status_omset').val();
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
            {data: 'tahun_1', name: 'tahun_1', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarangTahunan(d,t,r,m, 1); }},
            {data: 'tahun_2', name: 'tahun_2', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarangTahunan(d,t,r,m, 2); }},
            {data: 'tahun_3', name: 'tahun_3', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarangTahunan(d,t,r,m, 3); }},
            {data: 'tahun_4', name: 'tahun_4', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarangTahunan(d,t,r,m, 4); }},
            {data: 'tahun_5', name: 'tahun_5', orderable: false, searchable: false, render: function(d,t,r,m){ return renderLinkBarangTahunan(d,t,r,m, 5); }},
            {data: 'total', name: 'transaksi.total_semua', orderable: true, searchable: false, render: function(d,t,r,m){ return renderLinkBarangTahunan(d,t,r,m, 'total'); }}
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[5, 'asc']],
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
                // Sembunyikan/Tampilkan Header Tahun
                if (json.years_array) {
                    for(var i = 1; i <= 5; i++) {
                        var colIdx = i + 6; // Index kolom tabel (Satuan = 6, Tahun_1 = 7)
                        var column = api.column(colIdx);

                        if (json.years_array[i - 1]) {
                            $(column.header()).text(json.years_array[i - 1]);
                            column.visible(true);
                        } else {
                            column.visible(false);
                        }
                    }
                }

                // Kalkulasi Footer
                if (json.grand_totals) {
                    var totals = json.grand_totals;
                    var mode = json.mode;
                    var prefix = (mode === 'nominal') ? 'Rp ' : '';

                    var formatAngka = function(num) {
                        return prefix + new Intl.NumberFormat('id-ID').format(num || 0);
                    };

                    $('#ft-t1').text(formatAngka(totals.sum_t1));
                    $('#ft-t2').text(formatAngka(totals.sum_t2));
                    $('#ft-t3').text(formatAngka(totals.sum_t3));
                    $('#ft-t4').text(formatAngka(totals.sum_t4));
                    $('#ft-t5').text(formatAngka(totals.sum_t5));
                    $('#ft-total').html('<strong>' + formatAngka(totals.sum_total) + '</strong>');

                    var modeTeks = (mode === 'qty') ? 'QTY (Jumlah Barang)' : 'TOTAL OMSET UANG (Rp)';
                    $('#info-mode-teks').html('Menampilkan data berdasarkan <strong>' + modeTeks + '</strong>');
                }
            }
        }
    });

    $('#btn-filter').click(function(){
        var tAwal = parseInt($('#tahun_awal').val());
        var tAkhir = parseInt($('#tahun_akhir').val());

        if (tAwal > tAkhir) {
            alert('Tahun Awal tidak boleh lebih besar dari Tahun Akhir!');
            return;
        }

        if ((tAkhir - tAwal) > 4) {
            alert('Range tahun maksimal adalah 5 tahun! Silakan persempit filter Anda.');
            return;
        }
        table.draw();
    });

    $('#btn-reset').click(function(){
        var currentYear = new Date().getFullYear();
        $('#tahun_awal').val(currentYear - 4);
        $('#tahun_akhir').val(currentYear);
        $('#barang_unit_id').val('').trigger('change');
        $('#barang_kategori_id').val('').trigger('change');
        $('#status_omset').val('');
        $('#mode_tampil').val('qty');
        table.draw();
    });

    $('#btn-excel').click(function() {
        var params = $.param({
            tahun_awal: $('#tahun_awal').val(),
            tahun_akhir: $('#tahun_akhir').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            barang_kategori_id: $('#barang_kategori_id').val(),
            mode_tampil: $('#mode_tampil').val(),
            status_omset: $('#status_omset').val()
        });
        window.location.href = "{{ route('statistik.omset-barang.tahunan.excel') }}?" + params;
    });

    $('#btn-print').click(function() {
        var params = $.param({
            tahun_awal: $('#tahun_awal').val(),
            tahun_akhir: $('#tahun_akhir').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            barang_kategori_id: $('#barang_kategori_id').val(),
            mode_tampil: $('#mode_tampil').val(),
            status_omset: $('#status_omset').val()
        });
        window.open("{{ route('statistik.omset-barang.tahunan.print') }}?" + params, '_blank');
    });

});
</script>
@endpush
