@extends('layouts.app')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center my-4">
            <h3><u>OMSET TAHUNAN KONSUMEN</u></h3>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <img src="{{ asset('images/dashboard.svg') }}" alt="dashboard" width="20">
                <span>Dashboard</span>
            </a>
            @if (!in_array(auth()->user()->role, ['asisten-admin', 'perusahaan', 'sales']))
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
                        <option value="{{ $y }}" {{ $y==date('Y')-4 ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Tahun Akhir</label>
                    <select name="tahun_akhir" id="tahun_akhir" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= 2015; $y--)
                        <option value="{{ $y }}" {{ $y==date('Y') ? 'selected' : '' }}>{{ $y }}</option>
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

                <div class="col-md-2">
                    <label class="form-label fw-bold small">Kode Toko</label>
                    <select name="kode_toko_id" id="kode_toko_id" class="form-select form-select-sm">
                        <option value="">-- Semua Kode Toko --</option>
                        @foreach($kodeTokos as $kt)
                        <option value="{{ $kt->id }}">{{ $kt->kode }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="kota" class="form-label fw-bold small">Kabupaten / Kota</label>
                    <select name="kabupaten_kota_id" id="kabupaten_kota_id" class="form-select form-select-sm"
                        onchange="filterKecamatan()">
                        <option value="">-- Pilih Kabupaten / Kota --</option>
                        @foreach ($kabupatenKota as $kab)
                        <option value="{{ $kab->id }}">{{ $kab->nama_wilayah }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="kota" class="form-label fw-bold small">Kecamatan</label>
                    <select name="kecamatan_id" id="kecamatan_id" class="form-select form-select-sm">
                        <option value="">-- Pilih Kecamatan --</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Sales Area</label>
                    <select name="sales_id" id="sales_id" class="form-select form-select-sm">
                        <option value="">-- Semua Sales --</option>
                        @foreach($sales as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
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
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Status Invoice</label>
                    <select name="status_invoice" id="status_invoice" class="form-select form-select-sm">
                        <option value="">Omset</option>
                        <option value="invoice">Invoice</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Status Konsumen</label>
                    <select name="status_konsumen" id="status_konsumen" class="form-select form-select-sm">
                        <option value="">-- Semua --</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Non Aktif</option>
                    </select>
                </div>

                <div class="col-md-12 d-flex align-items-end justify-content-end gap-2 mt-3">
                    <button type="button" id="btn-filter" class="btn btn-primary" title="Tampilkan Data">
                        <i class="fa fa-filter"></i> Filter
                    </button>
                    <button type="button" id="btn-reset" class="btn btn-secondary" title="Reset Filter">
                        <i class="fa fa-undo"></i> Reset
                    </button>
                    <div class="vr mx-2"></div>
                    <button type="button" id="btn-excel" class="btn btn-success" title="Download Excel">
                        <i class="fa fa-file-excel"></i> Excel
                    </button>
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
                <table id="omsetTable" class="table table-bordered table-striped table-hover w-100"
                    style="font-size: 0.8rem;">
                    <thead class="table-dark text-center text-nowrap">
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Kode</th>
                            <th class="text-center align-middle">Kode<br>Toko</th>
                            <th class="text-center align-middle">Nama<br>Toko</th>
                            <th class="text-center align-middle">Kab/Kota</th>
                            <th class="text-center align-middle">Kecamatan</th>
                            <th class="text-center align-middle">Sales Area</th>
                            <th class="text-center align-middle" id="th-t1">Tahun 1</th>
                            <th class="text-center align-middle" id="th-t2">Tahun 2</th>
                            <th class="text-center align-middle" id="th-t3">Tahun 3</th>
                            <th class="text-center align-middle" id="th-t4">Tahun 4</th>
                            <th class="text-center align-middle" id="th-t5">Tahun 5</th>
                            <th class="text-center align-middle">Total</th>
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
                            <th id="ft-total" class="text-end align-middle">0</th>
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
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.bootstrap5.min.css">
<style>
    /* Agar kolom angka rata kanan (Dimulai dari index 8 di CSS: 1-based index) */
    #omsetTable tbody td:nth-child(n+8) {
        text-align: right;
    }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/scroller.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>
<script>
    function filterKecamatan() {
        var kabupatenKotaId = $('#kabupaten_kota_id').val();
        $('#kecamatan_id').val(null).trigger('change');
        if(kabupatenKotaId) {
            $.ajax({
                url: "{{ route('universal.get-kecamatan') }}",
                data: { kabupaten_kota_id: kabupatenKotaId },
                success: function(data) {
                    var options = '<option value="">-- Pilih Kecamatan --</option>';
                    $.each(data.data, function(index, kec) {
                        options += '<option value="' + kec.id + '">' + kec.nama_wilayah + '</option>';
                    });
                    $('#kecamatan_id').html(options);
                }
            });
        } else {
            $('#kecamatan_id').html('<option value="">-- Pilih Kecamatan --</option>');
        }
    }
</script>
<script>
    $(document).ready(function() {

    // Inisialisasi Select2
    $('#barang_unit_id, #kabupaten_kota_id, #kecamatan_id, #kode_toko_id, #sales_id').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    // Inisialisasi DataTables
    var table = $('#omsetTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('statistik.omset-tahunan-konsumen') }}",
            data: function (d) {
                d.tahun_awal = $('#tahun_awal').val();
                d.tahun_akhir = $('#tahun_akhir').val();
                d.barang_unit_id = $('#barang_unit_id').val();
                d.kode_toko_id = $('#kode_toko_id').val();
                d.status_omset = $('#status_omset').val();
                d.sales_id = $('#sales_id').val();
                d.kabupaten_kota_id = $('#kabupaten_kota_id').val();
                d.kecamatan_id = $('#kecamatan_id').val();
                d.status_invoice = $('#status_invoice').val();
                d.status_konsumen = $('#status_konsumen').val();
            }
        },
        createdRow: function(row, data, dataIndex) {
            if (data.active == 0 || data.active === '0') {
                $(row).addClass('table-danger');
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'full_kode', name: 'konsumens.kode', className: 'text-wrap text-center'}, // Sesuaikan name ini jika perlu
            {data: 'kode_toko', name: 'kode_toko', className: 'text-wrap text-center'},
            {data: 'nama', name: 'konsumens.nama', className: 'text-wrap'},
            {data: 'kabupaten_kota.nama_wilayah', name: 'kabupaten_kota.nama_wilayah', className: 'text-wrap text-start'},
            {data: 'kecamatan.nama_wilayah', name: 'kecamatan.nama_wilayah', className: 'text-wrap text-start'},
            {data: 'karyawan.nama', name: 'karyawan.nama', className: 'text-wrap text-start'},
            {data: 'tahun_1', name: 'tahun_1', orderable: false, searchable: false, className: 'text-end', render: function(data, type, row, meta) { return renderLinkTahunan(data, type, row, meta, 1); }},
            {data: 'tahun_2', name: 'tahun_2', orderable: false, searchable: false, className: 'text-end', render: function(data, type, row, meta) { return renderLinkTahunan(data, type, row, meta, 2); }},
            {data: 'tahun_3', name: 'tahun_3', orderable: false, searchable: false, className: 'text-end', render: function(data, type, row, meta) { return renderLinkTahunan(data, type, row, meta, 3); }},
            {data: 'tahun_4', name: 'tahun_4', orderable: false, searchable: false, className: 'text-end', render: function(data, type, row, meta) { return renderLinkTahunan(data, type, row, meta, 4); }},
            {data: 'tahun_5', name: 'tahun_5', orderable: false, searchable: false, className: 'text-end', render: function(data, type, row, meta) { return renderLinkTahunan(data, type, row, meta, 5); }},
            {data: 'total', name: 'transaksi.total_semua', orderable: true, searchable: false, className: 'text-end', render: function(data, type, row, meta) { return renderLinkTahunan(data, type, row, meta, 'total'); }}
        ],
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[3, 'asc']], // Default urut berdasarkan Nama Toko (Index 3)
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
                // 1. Dinamis Header Tabel Tahun
                if (json.years_array) {
                    for(var i = 1; i <= 5; i++) {
                        var colIdx = i + 6; // Index DataTables untuk kolom tahun (mulai dr 7)
                        var column = api.column(colIdx);

                        if (json.years_array[i - 1]) {
                            $(column.header()).text(json.years_array[i - 1]);
                            column.visible(true);
                        } else {
                            column.visible(false);
                        }
                    }
                }

                // 2. Kalkulasi Grand Totals
                if (json.grand_totals) {
                    var totals = json.grand_totals;
                    var formatUang = function(num) { return new Intl.NumberFormat('id-ID').format(num || 0); };

                    $('#ft-t1').text(formatUang(totals.sum_t1));
                    $('#ft-t2').text(formatUang(totals.sum_t2));
                    $('#ft-t3').text(formatUang(totals.sum_t3));
                    $('#ft-t4').text(formatUang(totals.sum_t4));
                    $('#ft-t5').text(formatUang(totals.sum_t5));
                    $('#ft-total').html('<strong>' + formatUang(totals.sum_total) + '</strong>');
                }
            }
        }
    });

    function renderLinkTahunan(data, type, row, meta, indexTahun) {
        if(type !== 'display') return data;

        var cleanData = (data + '').replace(/<[^>]*>?/gm, '');
        var numericValue = parseInt(cleanData.replace(/\./g, ''));

        if (numericValue > 0) {
            var tahunAwal = parseInt($('#tahun_awal').val());
            var tahunAkhir = parseInt($('#tahun_akhir').val());
            var unitId = $('#barang_unit_id').val();
            var statusInvoice = $('#status_invoice').val();

            // Cari tahu Tahun aktual berdasarkan urutan kolom (1-5)
            var tahunKlik = 'total';
            if (indexTahun !== 'total') {
                tahunKlik = tahunAwal + (indexTahun - 1);

                // Jangan beri link pada tahun yang melebihi tahun_akhir
                // (karena kolom disembunyikan secara visual)
                if (tahunKlik > tahunAkhir) return data;
            }

            // Trik Base URL (Ganti dengan nama route Anda yang sebenarnya)
            var baseUrl = "{{ route('statistik.omset.detail_tahunan_page', ['konsumen' => 'XXX', 'tahun' => 'YYY']) }}";

            var finalUrl = baseUrl
                .replace('XXX', row.id)
                .replace('YYY', tahunKlik);

            // Sisipkan Parameter Filter
            var queryParams = [];
            if(unitId) queryParams.push("unit_id=" + unitId);
            if(statusInvoice) queryParams.push("status_invoice=" + statusInvoice);
            queryParams.push("tahun_awal=" + tahunAwal);
            queryParams.push("tahun_akhir=" + tahunAkhir);

            if(queryParams.length > 0) {
                finalUrl += "?" + queryParams.join('&');
            }

            return '<a href="' + finalUrl + '" class="text-decoration-none fw-bold text-primary" target="_blank" title="Lihat Detail Transaksi">' + data + '</a>';
        }
        return data;
    }

    // --- TOMBOL FILTER ---
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

    // --- TOMBOL RESET ---
    $('#btn-reset').click(function(){
        var currentYear = new Date().getFullYear();

        // Kembalikan filter tahun ke 5 tahun terakhir
        $('#tahun_awal').val(currentYear - 4);
        $('#tahun_akhir').val(currentYear);

        // Reset semua Dropdown Select2
        $('#barang_unit_id').val('').trigger('change');
        $('#kode_toko_id').val('').trigger('change');
        $('#sales_id').val('').trigger('change');
        $('#kabupaten_kota_id').val('').trigger('change');
        $('#kecamatan_id').val('').trigger('change');

        // Reset Dropdown Biasa
        $('#status_omset').val('');
        $('#status_invoice').val('');
        $('#status_konsumen').val('');

        // Refresh Tabel
        table.draw();
    });

    // --- TOMBOL EXCEL ---
   $('#btn-excel').click(function() {
        var params = $.param({
            tahun_awal: $('#tahun_awal').val(),
            tahun_akhir: $('#tahun_akhir').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            kode_toko_id: $('#kode_toko_id').val(),
            status_omset: $('#status_omset').val(),
            sales_id: $('#sales_id').val(),
            kabupaten_kota_id: $('#kabupaten_kota_id').val(),
            kecamatan_id: $('#kecamatan_id').val(),
            status_invoice: $('#status_invoice').val(),
            status_konsumen: $('#status_konsumen').val()
        });
        // UBAH ROUTE KE VERSI TAHUNAN
        window.location.href = "{{ route('statistik.omset.excel_tahunan') }}?" + params;
    });

    // --- TOMBOL PDF TAHUNAN ---
    $('#btn-print').click(function() {
       var params = $.param({
            tahun_awal: $('#tahun_awal').val(),
            tahun_akhir: $('#tahun_akhir').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            kode_toko_id: $('#kode_toko_id').val(),
            status_omset: $('#status_omset').val(),
            sales_id: $('#sales_id').val(),
            kabupaten_kota_id: $('#kabupaten_kota_id').val(),
            kecamatan_id: $('#kecamatan_id').val(),
            status_invoice: $('#status_invoice').val(),
            status_konsumen: $('#status_konsumen').val()
        });
        // UBAH ROUTE KE VERSI TAHUNAN
        var url = "{{ route('statistik.omset.print_tahunan') }}?" + params;
        window.open(url, '_blank');
    });

});
</script>
@endpush
