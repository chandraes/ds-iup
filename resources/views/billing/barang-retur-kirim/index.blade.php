@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-3">
        <div class="col-md-12 text-center">
            <h1><u>DAFTAR STOK BARANG RETUR</u></h1>
        </div>
    </div>

<div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-7">
            <nav class="d-flex gap-4">
                <a href="{{route('home')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30" class="me-2"> Dashboard
                </a>
                <a href="{{route('billing')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30" class="me-2">
                            Billing
                </a>
            </nav>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Perusahaan</label>
                    <select id="filter_unit" class="form-select select2">
                        <option value="">Semua Perusahaan</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}">{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Filter Kelompok</label>
                    <select id="filter_kategori" class="form-select select2">
                        <option value="">Semua Kelompok</option>
                        @foreach($kategoris as $k)
                            <option value="{{ $k->id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button id="btn-reset" class="btn btn-secondary w-100"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLE SECTION --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-bordered table-hover table-striped w-100" id="bad-stok-datatable">
                <thead class="table-success">
                     <tr class="text-center align-middle">
                        <th>No</th>
                        <th>Perusahaan</th>
                        <th>Kelompok Barang</th>
                        <th>Nama Barang</th>
                        <th>Kode Barang</th>
                        <th>Merk Barang</th>
                        <th>Stok Retur</th>
                        <th>Satuan</th>
                        <th>PPN</th>
                        <th>Non PPN</th>
                        <th>Detail Sumber</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Data diisi oleh AJAX DataTables --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- INCLUDE MODAL KERANJANG (Gunakan file yang Anda kirim sebelumnya) --}}
{{-- @include('billing.form-barang-retur.modal-keranjang') --}}

{{-- INCLUDE MODAL HISTORY (Placeholder, nanti diisi via JS/AJAX jika perlu dinamis) --}}
<div class="modal fade" id="modalHistoryDynamic" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Sumber</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="history-content">
                Loading...
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/datatable/scroller.bootstrap5.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/scroller.bootstrap5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>

<script>
$(document).ready(function() {

    // 1. Inisialisasi DataTable
    var table = $('#bad-stok-datatable').DataTable({
        processing: true,
        serverSide: true, // PENTING: Load data bertahap dari server
        ajax: {
            url: "{{ route('billing.stok-retur.data') }}", // Sesuaikan route Anda
            data: function (d) {
                // Kirim parameter filter ke controller
                d.unit_filter = $('#filter_unit').val();
                d.kategori_filter = $('#filter_kategori').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'perusahaan', name: 'barang.unit.nama'},
            {data: 'kelompok', name: 'barang.kategori.nama'},
            {data: 'nama_barang', name: 'barang.barang_nama.nama'},
            {data: 'kode_barang', name: 'barang.kode'},
            {data: 'merk', name: 'barang.merk'},
            {data: 'stok_retur', name: 'total_qty_karantina', className: 'text-center'},
            {data: 'satuan', name: 'barang.satuan.nama'},
            {data: 'ppn', name: 'ppn', className: 'text-center'},
            {data: 'non_ppn', name: 'non_ppn', className: 'text-center'},
            {data: 'detail_sumber', name: 'detail_sumber', orderable: false, searchable: false, className: 'text-center'},
            {data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center'}
        ],
        // Konfigurasi Infinite Scroll (Scroller)
        scrollY: 500, // Tinggi table pixel
        scroller: {
            loadingIndicator: true
        },
        scrollCollapse: true,
        deferRender: true, // Render HTML hanya saat discroll (performa)
        dom: 'frti', // Hilangkan pagination bawaan (p) karena pakai scroller
    });

    // 2. Event Listener Filter
    $('#filter_unit, #filter_kategori').change(function() {
        table.draw(); // Redraw table saat filter berubah
    });

    $('#btn-reset').click(function(){
        $('#filter_unit').val('').trigger('change');
        $('#filter_kategori').val('').trigger('change');
        table.draw();
    });

    // 3. Logic Tombol AKSI (Masuk Keranjang)
    // Menggunakan Event Delegation karena tombol di-render via AJAX
    $('#bad-stok-datatable tbody').on('click', 'button.btn-modal-trigger', function() {
        var data = $(this).data('row');

        // Populate Modal (Sesuai modal-keranjang.blade.php Anda)
        // Sesuaikan ID element di bawah dengan file modal-keranjang.blade.php Anda

        // Contoh asumsi ID element di modal:
        $('#barang_id').val(data.id); // ID Barang
        // Jika modal butuh ID stok retur juga:
        // $('<input>').attr({type: 'hidden', name: 'stok_retur_id', value: data.stok_retur_id}).appendTo('#keranjangForm');

        $('#nm_barang_merk_retail').val(data.nama + ', ' + data.kode + ', ' + data.merk);

        // Set Label Satuan jika ada
        if(data.satuan) {
           $('#jumlah_satuan').text(data.satuan.nama);
        }

        // Reset input jumlah
        $('#jumlah').val('');

        // Tampilkan Modal
        $('#keranjangModal').modal('show');
    });

    // 4. Logic Tombol Detail Sumber (Opsional - Jika ingin load AJAX detail history)
   $('#bad-stok-datatable tbody').on('click', 'button.btn-history', function() {
       var id = $(this).data('id');
       var nama = $(this).data('nama');

       // 1. Update Judul Modal
       $('#modalHistoryDynamic .modal-title').text('Riwayat Sumber: ' + nama);

       // 2. Tampilkan Loading State dulu agar user tau sistem bekerja
       $('#history-content').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat riwayat...</p>
            </div>
       `);

       // 3. Buka Modal
       $('#modalHistoryDynamic').modal('show');

       // 4. Request Data ke Server
       var url = "{{ route('billing.stok-retur.history', ':id') }}";
       url = url.replace(':id', id);

       $.ajax({
           url: url,
           type: 'GET',
           success: function(response) {
               // Masukkan HTML dari server ke dalam modal body
               $('#history-content').html(response);
           },
           error: function(xhr) {
               $('#history-content').html(`
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle"></i> Gagal memuat data.<br>
                        Silakan coba lagi.
                    </div>
               `);
           }
       });
    });
});
</script>
@endpush
