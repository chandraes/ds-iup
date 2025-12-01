@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="row justify-content-center mb-3">
        <div class="col-md-12 text-center">
            <h1><u>DAFTAR STOK BARANG RETUR</u></h1>
        </div>
    </div>

    {{-- Toolbar Navigasi & Status Keranjang --}}
    <div class="row justify-content-between align-items-center mt-3 mb-3">
        <div class="col-md-6">
            <nav class="d-flex gap-3">
                <a href="{{route('home')}}" class="btn btn-outline-dark border-0">
                    <img src="{{asset('images/dashboard.svg')}}" width="25" class="me-1"> Dashboard
                </a>
                <a href="{{route('billing')}}" class="btn btn-outline-dark border-0">
                    <img src="{{asset('images/billing.svg')}}" width="25" class="me-1"> Billing
                </a>
            </nav>
        </div>

        {{-- AREA KERANJANG BARU --}}
        <div class="col-md-6 text-end">
            <div class="d-inline-flex gap-2">
                 {{-- Tombol Kosongkan --}}
                <button id="btn-empty-cart" class="btn btn-danger btn-sm">
                    <i class="bi bi-trash"></i> Kosongkan Keranjang
                </button>

                 {{-- Tombol Lihat Keranjang --}}
                <a href="{{ route('billing.stok-retur.cart') }}" class="btn btn-primary position-relative">
                    <i class="bi bi-cart-check-fill"></i> Lihat Keranjang
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-badge">
                        0
                        <span class="visually-hidden">item di keranjang</span>
                    </span>
                </a>
            </div>
            <div class="mt-2">
                 @include('wa-status')
            </div>
        </div>
    </div>

    {{-- Filter Section (Sama seperti sebelumnya) --}}
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
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL 1: ADD/EDIT CART --}}
<div class="modal fade" id="modalCart" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCartTitle">Tambah ke Keranjang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCart">
                @csrf
                <input type="hidden" id="cart_stok_retur_id" name="stok_retur_id">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Barang</label>
                        <input type="text" class="form-control" id="cart_barang_nama" readonly>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="fw-bold">Stok Tersedia</label>
                            <input type="text" class="form-control" id="cart_stok_max" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Jumlah Proses</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="cart_qty" name="qty" min="1" required>
                                <span class="input-group-text" id="cart_satuan">-</span>
                            </div>
                            <small class="text-muted" id="cart_hint"></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-cart">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: HISTORY (Tetap) --}}
<div class="modal fade" id="modalHistoryDynamic" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Riwayat Sumber</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="history-content"></div>
        </div>
    </div>
</div>

@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>

<script>
$(document).ready(function() {

    // Setup CSRF Token untuk semua AJAX Request
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // --- 1. SETUP DATATABLE ---
    var table = $('#bad-stok-datatable').DataTable({
        // ... (konfigurasi datatable sama seperti sebelumnya) ...
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('billing.stok-retur.data') }}",
            data: function (d) {
                d.unit_filter = $('#filter_unit').val();
                d.kategori_filter = $('#filter_kategori').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'perusahaan', name: 'barang.unit.nama'},
            {data: 'kelompok', name: 'barang.kategori.nama'},
            {data: 'nama_barang', name: 'barang.barang_nama.nama'},
            {data: 'kode_barang', name: 'barang.kode'},
            {data: 'merk', name: 'barang.merk'},
            {data: 'stok_retur', name: 'total_qty_karantina', className: 'text-center'},
            {data: 'satuan', name: 'barang.satuan.nama'},
            {data: 'ppn', name: 'ppn', className: 'text-center'},
            {data: 'non_ppn', name: 'non_ppn', className: 'text-center'},
            {data: 'detail_sumber', orderable: false, searchable: false, className: 'text-center'},
            {data: 'aksi', orderable: false, searchable: false, className: 'text-center'}
        ],
        scrollY: 500,
        scroller: true,
        scrollCollapse: true,
        deferRender: true,
        dom: 'frti',
    });

    table.on('draw', function () { updateCartBadge(); });
    $('#filter_unit, #filter_kategori').change(function() { table.draw(); });
    $('#btn-reset').click(function(){
        $('#filter_unit').val('').trigger('change');
        $('#filter_kategori').val('').trigger('change');
        table.draw();
    });

    // --- LOGIC MODAL POPUP (Sama seperti sebelumnya) ---
    $('#bad-stok-datatable tbody').on('click', '.btn-cart-action', function() {
        var data = $(this).data('row');
        $('#cart_stok_retur_id').val(data.id);
        $('#cart_barang_nama').val(data.barang_nama);
        $('#cart_stok_max').val(data.stok_max);
        $('#cart_satuan').text(data.satuan);
        $('#cart_qty').attr('max', data.stok_max);

        if(data.current_qty > 0) {
            $('#modalCartTitle').text('Edit Jumlah di Keranjang');
            $('#cart_qty').val(data.current_qty);
            $('#btn-save-cart').text('Update Keranjang').removeClass('btn-primary').addClass('btn-warning');
            $('#cart_hint').text('Barang ini sudah ada di keranjang.');
        } else {
            $('#modalCartTitle').text('Tambah ke Keranjang');
            $('#cart_qty').val('');
            $('#btn-save-cart').text('Simpan').removeClass('btn-warning').addClass('btn-primary');
            $('#cart_hint').text('');
        }
        $('#modalCart').modal('show');
        setTimeout(() => { $('#cart_qty').focus(); }, 500);
    });

    // --- [PERUBAHAN 1] SUBMIT FORM CART DENGAN SWEETALERT ---
    $('#formCart').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: "{{ route('billing.stok-retur.add-to-cart') }}",
            type: "POST",
            data: formData,
            success: function(response) {
                if(response.status == 'success') {
                    $('#modalCart').modal('hide');
                    table.draw(false);

                    // Ganti Alert Biasa dengan Toast/Swal Kecil
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false,
                        position: 'center'
                    });
                } else {
                    // Alert Error
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        html: response.message // Pakai html karena ada tag <br> atau <b>
                    });
                }
            },
            error: function(xhr) {
                var err = JSON.parse(xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Terjadi Kesalahan',
                    text: err.message || 'Server Error'
                });
            }
        });
    });

    // --- [PERUBAHAN 2] TOMBOL KOSONGKAN KERANJANG ---
    $('#btn-empty-cart').click(function() {
        // Ganti confirm() bawaan dengan Swal.fire
        Swal.fire({
            title: 'Kosongkan Keranjang?',
            text: "Semua barang yang Anda pilih akan dihapus dari keranjang!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Kosongkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Eksekusi AJAX jika user klik "Ya"
                $.ajax({
                    url: "{{ route('billing.stok-retur.empty-cart') }}",
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        table.draw();
                        updateCartBadge();
                        Swal.fire(
                            'Dikosongkan!',
                            'Keranjang belanja Anda sudah kosong.',
                            'success'
                        );
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat mengosongkan keranjang.', 'error');
                    }
                });
            }
        });
    });

    // --- HISTORY (Tetap sama, hanya loadingnya bisa dipercantik) ---
    $('#bad-stok-datatable tbody').on('click', 'button.btn-history', function() {
        // ... (kode history tetap sama) ...
        var id = $(this).data('id');
        var nama = $(this).data('nama');
        $('#modalHistoryDynamic .modal-title').text('Riwayat Sumber: ' + nama);
        $('#history-content').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
        $('#modalHistoryDynamic').modal('show');

        var url = "{{ route('billing.stok-retur.history', ':id') }}".replace(':id', id);
        $.ajax({
            url: url,
            type: 'GET',
            success: function(res) { $('#history-content').html(res); },
            error: function() { $('#history-content').html('<div class="alert alert-danger">Gagal memuat data.</div>'); }
        });
    });

    function updateCartBadge() {
        $.get("{{ route('billing.stok-retur.cart-info') }}", function(data){
            $('#cart-badge').text(data.total_items || 0);
        });
    }
});
</script>
@endpush
