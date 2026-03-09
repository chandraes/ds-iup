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
        </div>
    </div>

    {{-- Filter Area --}}
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label fw-bold">Tahun</label>
                    <select name="tahun" id="tahun" class="form-select form-select-sm">
                        @for($y = date('Y'); $y >= 2020; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Perusahaan</label>
                    <select name="barang_unit_id" id="barang_unit_id" class="form-select form-select-sm">
                        <option value="">-- Semua Perusahaan --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
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
                            <label for="kota" class="form-label">Kabupaten / Kota</label>
                            <select name="kabupaten_kota_id" id="kabupaten_kota_id" class="form-select form-select-sm" onchange="filterKecamatan()">
                                <option value="">-- Pilih Kabupaten / Kota --</option>
                                @foreach ($kabupatenKota as $kab)
                                    <option value="{{ $kab->id }}">{{ $kab->nama_wilayah }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="kota" class="form-label">Kecamatan</label>
                            <select name="kecamatan_id" id="kecamatan_id" class="form-select form-select-sm">
                                <option value="">-- Pilih Kecamatan --</option>
                                {{-- @foreach ($kecamatan as $kec)
                                    <option value="{{ $kec->id }}">{{ $kec->nama_wilayah }}</option>
                                @endforeach --}}
                            </select>
                        </div>
                    {{-- <div class="col-md-2">
                        <label class="form-label fw-bold small">Sales Area</label>
                        <select name="sales_id" id="sales_id" class="form-select form-select-sm">
                            <option value="">-- Semua Sales --</option>
                            @foreach($sales as $s)
                                <option value="{{ $s->id }}">{{ $s->nama }}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    {{-- FILTER BARU 2: STATUS OMSET --}}
                    <div class="col-md-2">
                        <label class="form-label fw-bold small">Status Omset</label>
                        <select name="status_omset" id="status_omset" class="form-select form-select-sm">
                            <option value="">-- Semua --</option>
                            <option value="ada">Ada Omset (> 0)</option>
                            <option value="nol">Tidak Ada (0)</option>
                        </select>
                    </div>
                     <div class="col-md-2">
                        <label class="form-label fw-bold small">Status</label>
                        <select name="status_invoice" id="status_invoice" class="form-select form-select-sm">
                            <option value="">Omset</option>
                            <option value="invoice">Invoice</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small">Pilih Bulan (Opsional)</label>
                        <select name="bulan[]" id="bulan" class="form-select form-select-sm" multiple="multiple">
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
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Kode</th>
                            <th class="text-center align-middle">Kode<br>Toko</th>
                            <th class="text-center align-middle">Nama<br>Toko</th>
                            <th class="text-center align-middle">Kab/Kota</th>
                            <th class="text-center align-middle">Kecamatan</th>
                            <th class="text-center align-middle">Sales Area</th>
                            <th class="text-center align-middle">Jan</th>
                            <th class="text-center align-middle">Feb</th>
                            <th class="text-center align-middle">Mar</th>
                            <th class="text-center align-middle">Apr</th>
                            <th class="text-center align-middle">Mei</th>
                            <th class="text-center align-middle">Jun</th>
                            <th class="text-center align-middle">Jul</th>
                            <th class="text-center align-middle">Agu</th>
                            <th class="text-center align-middle">Sep</th>
                            <th class="text-center align-middle">Okt</th>
                            <th class="text-center align-middle">Nov</th>
                            <th class="text-center align-middle">Des</th>
                            <th class="text-center align-middle">Total</th>
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
                            <th id="ft-total" class="text-end align-middle">0</th>
                        </tr>
                    </tfoot>
                    {{-- Footer untuk Grand Total (Optional, perlu request AJAX terpisah jika ingin akurat per page) --}}
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<!-- Or for RTL support -->

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
    function filterKecamatan() {
        var kabupatenKotaId = $('#kabupaten_kota_id').val();
        $('#kecamatan_id').val(null).trigger('change'); // Reset kecamatan
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
    $('#barang_unit_id').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

    $('#kabupaten_kota_id').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });

     $('#kecamatan_id').select2({
        theme: 'bootstrap-5',
        width: '100%'
    });


    $('#kode_toko_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        // placeholder: '-- Pilih Kode Toko --',
        // allowClear: true
    });


    $('#bulan').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: '-- Semua Bulan --',
        allowClear: true
    });

    // Inisialisasi DataTables
    var table = $('#omsetTable').DataTable({
        processing: true,
        serverSide: true, // PENTING: Mengaktifkan mode server-side
        ajax: {
            url: "{{ route('sales.omset-bulanan-konsumen') }}", // Ganti dengan nama route Anda
            data: function (d) {
                d.tahun = $('#tahun').val();
                d.barang_unit_id = $('#barang_unit_id').val();
                d.kode_toko_id = $('#kode_toko_id').val();
                d.status_omset = $('#status_omset').val();
                d.sales_id = $('#sales_id').val();
                d.kabupaten_kota_id = $('#kabupaten_kota_id').val();
                d.kecamatan_id = $('#kecamatan_id').val();
                d.status_invoice = $('#status_invoice').val();
                d.bulan = $('#bulan').val();
            }
        },
        columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'full_kode', name: 'konsumens.kode', className: 'text-wrap text-center'},
                {data: 'kode_toko', name: 'kode_toko', className: 'text-wrap text-center'},
                {data: 'nama', name: 'konsumens.nama', className: 'text-wrap'},
                {data: 'kabupaten_kota.nama_wilayah', name: 'kabupaten_kota.nama_wilayah', className: 'text-wrap text-start'},
                {data: 'kecamatan.nama_wilayah', name: 'kecamatan.nama_wilayah', className: 'text-wrap text-start'},
                {data: 'karyawan.nama', name: 'karyawan.nama', className: 'text-wrap text-start'},
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
                render: function(data, type, row) {
                    var selectedBulan = $('#bulan').val();
                    var rowTotal = 0;

                    // Looping untuk menjumlahkan baris hanya pada bulan yang dipilih / tampil
                    for (var i = 1; i <= 12; i++) {
                        if (!selectedBulan || selectedBulan.length === 0 || selectedBulan.includes(i.toString())) {
                            // Data dari controller sudah diformat titik (misal "1.500.000")
                            // Kita hilangkan titiknya dulu agar bisa dijumlahkan sebagai angka
                            var nilaiBulan = (row['bulan_' + i] || '0').toString().replace(/\./g, '');
                            rowTotal += parseInt(nilaiBulan) || 0;
                        }
                    }

                    // Format kembali menjadi Rupiah (titik)
                    var formattedTotal = new Intl.NumberFormat('id-ID').format(rowTotal);
                    var finalData = '<strong>' + formattedTotal + '</strong>';

                    // Panggil fungsi renderLink bawaan Anda
                    return renderLink(finalData, row, 'total');
                }}
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
        drawCallback: function(settings) {
            var api = this.api();
            var json = api.ajax.json(); // Ambil response JSON utuh dari server

            // Cek apakah ada ekstra data grand_totals yang dikirim dari Controller
            if (json && json.grand_totals) {
                var totals = json.grand_totals;
                var selectedBulan = $('#bulan').val();
                var dynamicSumTotal = 0; // Penampung Grand Total sudut kanan bawah

                // Helper format Rupiah (pakai titik)
                var formatUang = function(num) {
                    return new Intl.NumberFormat('id-ID').format(num || 0);
                };

                // Looping 12 bulan
                for(var i = 1; i <= 12; i++) {
                    var valBulan = parseFloat(totals['sum_b' + i] || 0);

                    // 1. Tulis angka total ke masing-masing footer bulan (meski tersembunyi)
                    $('#ft-b' + i).text(formatUang(valBulan));

                    // 2. Tambahkan ke Grand Total Akhir HANYA jika bulan tersebut dipilih/tampil
                    if (!selectedBulan || selectedBulan.length === 0 || selectedBulan.includes(i.toString())) {
                        dynamicSumTotal += valBulan;
                    }
                }

                // Bold untuk kolom total setahun di sudut kanan bawah yang sudah dinamis
                $('#ft-total').html('<strong>' + formatUang(dynamicSumTotal) + '</strong>');
            }
        }
    });

    // Tombol Filter
    $('#btn-filter').click(function(){
        var selectedBulan = $('#bulan').val(); // Menghasilkan array, misal: ['1', '3', '12']

        // Logika Menyembunyikan/Menampilkan Kolom Bulan
        // Indeks kolom di DataTables: Jan = 7, Feb = 8, ..., Des = 18
        for (var i = 1; i <= 12; i++) {
            var colIdx = i + 6; // Menyesuaikan urutan kolom (Kolom ke-7 adalah index 7, karena mulai dari 0)
            var column = table.column(colIdx);

            // Jika tidak ada bulan dipilih (tampil semua), atau bulan ini termasuk yang dipilih
            if (!selectedBulan || selectedBulan.length === 0 || selectedBulan.includes(i.toString())) {
                column.visible(true);
            } else {
                column.visible(false);
            }
        }

        table.draw();

    });

    $('#btn-reset').click(function(){
        // 1. Reset Select Tahun ke Tahun sekarang
        $('#tahun').val(new Date().getFullYear());

        // 2. Reset Select2 Unit Barang (Penting pakai trigger change)
        $('#barang_unit_id').val('').trigger('change');

        $('#kode_toko_id').val('').trigger('change');
        $('#status_omset').val('');
        $('#sales_id').val('').trigger('change');
        $('#kabupaten_kota_id').val('').trigger('change');
        $('#kecamatan_id').val('').trigger('change');
        $('#status_invoice').val('');

        $('#bulan').val(null).trigger('change');

        // Kembalikan semua kolom menjadi terlihat (visible)
        for (var i = 7; i <= 18; i++) {
            table.column(i).visible(true);
        }

        // 3. Refresh Tabel
        table.draw();
    });

    $('#btn-excel').click(function() {
        var params = $.param({
            tahun: $('#tahun').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            // TAMBAHAN
            kode_toko_id: $('#kode_toko_id').val(),
            status_omset: $('#status_omset').val(),
            sales_id: $('#sales_id').val(),
            kabupaten_kota_id: $('#kabupaten_kota_id').val(),
            kecamatan_id: $('#kecamatan_id').val(),
            status_invoice: $('#status_invoice').val(),
            bulan: $('#bulan').val()
        });
        window.location.href = "{{ route('sales.omset.excel') }}?" + params;
    });

    // TOMBOL PDF
   $('#btn-print').click(function() {
       var params = $.param({
            tahun: $('#tahun').val(),
            barang_unit_id: $('#barang_unit_id').val(),
            // TAMBAHAN
            kode_toko_id: $('#kode_toko_id').val(),
            status_omset: $('#status_omset').val(),
            sales_id: $('#sales_id').val(),
            kabupaten_kota_id: $('#kabupaten_kota_id').val(),
            kecamatan_id: $('#kecamatan_id').val(),
            status_invoice: $('#status_invoice').val(),
            bulan: $('#bulan').val()
        });
        var url = "{{ route('sales.omset.print') }}?" + params;
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
        var statusInvoice = $('#status_invoice').val();
        var selectedBulan = $('#bulan').val();
        // 3. Logic: Jika nilai > 0 buat Link, jika 0 kembalikan teks asli
        if (numericValue > 0) {
            // Base URL Route Laravel
            // Kita gunakan placeholder 'XXX', 'YYY', 'ZZZ' lalu replace manual dengan JS
            // Ini trik agar route() blade tidak error karena parameter JS belum ada
            var baseUrl = "{{ route('sales.omset.detail_page', ['konsumen' => 'XXX', 'bulan' => 'YYY', 'tahun' => 'ZZZ']) }}";

            // Ganti placeholder dengan data asli baris ini
            var finalUrl = baseUrl
                .replace('XXX', row.id)
                .replace('YYY', bulan)
                .replace('ZZZ', tahun);

            // Tambahkan query param untuk unit jika ada
            if(unitId) {
                finalUrl += "?unit_id=" + unitId;
            }

            if(statusInvoice) {
                finalUrl += (unitId ? '&' : '?') + "status_invoice=" + statusInvoice;
            }

            if(selectedBulan && selectedBulan.length > 0) {
                finalUrl += (unitId || statusInvoice ? '&' : '?') + "bulanFilter=" + selectedBulan.join(',');
            }

            return '<a href="' + finalUrl + '" class="text-decoration-none fw-bold" target="_blank" title="Lihat Detail">' + data + '</a>';
        }

        return data; // Kembalikan angka 0 atau teks asli
    }
});
</script>
@endpush
