@extends('layouts.app')
@section('content')
<div class="container-fluid">
    {{-- ... (Bagian header Anda tidak berubah) ... --}}
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            {{-- Menggunakan $title dari controller --}}
            <h1 class="display-6 fw-bold">{{ $title ?? 'KIRIM BARANG RETUR' }}</h1>
            <p class="lead text-muted">Manajemen data barang retur</p>
        </div>
    </div>
    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-7">
            <nav class="d-flex gap-4">
                <a href="{{route('home')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30" class="me-2"> Dashboard
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
</div>

{{-- ---------------------------------------------------- --}}
{{-- 1. TOMBOL KERANJANG MELAYANG (BARU) --}}
{{-- ---------------------------------------------------- --}}
<button class="btn btn-success btn-lg rounded-circle shadow" id="btn-open-cart" data-bs-toggle="offcanvas"
    data-bs-target="#offcanvasCart" aria-controls="offcanvasCart"
    style="position: fixed; bottom: 20px; right: 20px; z-index: 1050; width: 60px; height: 60px;">
    <i class="fa fa-shopping-cart"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cart-item-count">
        {{-- Hitung item di keranjang saat load halaman --}}
        {{ count(session()->get('stok_retur_cart', [])) }}
    </span>
</button>

<div class="container-fluid mt-4">

    {{-- ---------------------------------------------------- --}}
    {{-- 2. FORM FILTER (DIPERBARUI) --}}
    {{-- ---------------------------------------------------- --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h5 class="card-title text-muted mb-3">
                <i class="fa fa-filter me-2"></i> Filter Data
            </h5>
            {{-- Form mengarah ke route 'billing.stok-retur' dengan method GET --}}
            <form method="GET" action="{{ route('billing.stok-retur') }}" id="filterForm">
                <div class="row g-3">
                    {{-- Filter Perusahaan (Unit) --}}
                    <div class="col-md-3">
                        <label for="filter_unit" class="form-label">Perusahaan</label>
                        <select class="form-select select2-filter" id="filter_unit" name="unit_id">
                            <option value="">Semua Perusahaan</option>
                            @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @if(isset($filters['unit_id']) &&
                                $filters['unit_id']==$unit->id) selected @endif>
                                {{ $unit->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Kelompok Barang (Kategori) --}}
                    <div class="col-md-3">
                        <label for="filter_kategori" class="form-label">Kelompok Barang</label>
                        <select class="form-select select2-filter" id="filter_kategori" name="kategori_id">
                            <option value="">Semua Kelompok</option>
                            @foreach ($kategoris as $kategori)
                            <option value="{{ $kategori->id }}" @if(isset($filters['kategori_id']) &&
                                $filters['kategori_id']==$kategori->id) selected @endif>
                                {{ $kategori->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Nama Barang --}}
                    <div class="col-md-3">
                        <label for="filter_barang_nama" class="form-label">Nama Barang</label>
                        <select class="form-select select2-filter" id="filter_barang_nama" name="barang_nama_id">
                            <option value="">Semua Barang</option>
                            @foreach ($barangNamas as $nama)
                            <option value="{{ $nama->id }}" @if(isset($filters['barang_nama_id']) &&
                                $filters['barang_nama_id']==$nama->id) selected @endif>
                                {{ $nama->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Jenis PPN --}}
                    <div class="col-md-2">
                        <label for="filter_jenis_ppn" class="form-label">Jenis PPN</label>
                        <select class="form-select" id="filter_jenis_ppn" name="jenis_ppn">
                            <option value="">Semua Jenis</option>
                            <option value="1" @if(isset($filters['jenis_ppn']) && $filters['jenis_ppn']=='1' ) selected
                                @endif>PPN</option>
                            <option value="2" @if(isset($filters['jenis_ppn']) && $filters['jenis_ppn']=='2' ) selected
                                @endif>Non PPN</option>
                        </select>
                    </div>

                    {{-- Tombol Filter --}}
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                    {{-- Tombol Reset --}}
                    <div class="col-md-1 d-flex align-items-end">
                        <a href="{{ route('billing.stok-retur') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------------------------------------------------- --}}
    {{-- 3. TABEL DATA (DIPERBARUI) --}}
    {{-- ---------------------------------------------------- --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    {{-- ... (thead Anda tidak berubah) ... --}}
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
                            <th style="width: 200px;">Aksi</th> {{-- Lebarkan kolom aksi --}}
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        // Ambil keranjang dari session untuk cek
                        $cart = session()->get('stok_retur_cart', []);
                        @endphp
                        @forelse ($dataStok as $stok)
                        @php
                        $inCart = array_key_exists($stok->id, $cart);
                        $qtyInCart = $inCart ? $cart[$stok->id]['qty'] : 0;
                        @endphp
                        {{-- Beri background jika item sudah ada di keranjang --}}
                        <tr class="{{ $inCart ? 'table-info' : '' }}" id="row-stok-{{ $stok->id }}">
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $stok->barang_stok_harga->unit?->nama }}</td>
                            <td>{{ $stok->barang_stok_harga->kategori?->nama }}</td>
                            <td>{{ $stok->barang_stok_harga->barang_nama?->nama }}</td>
                            <td>{{ $stok->barang_stok_harga->barang?->kode }}</td>
                            <td>{{ $stok->barang_stok_harga->barang?->merk }}</td>
                            <td class="text-center">
                                <strong>{{ $stok->total_qty_karantina }}</strong>
                            </td>
                            <td>{{ $stok->barang_stok_harga->barang->satuan->nama }}</td>
                            <td class="text-center align-middle">
                                @if ($stok->barang_stok_harga->barang->jenis == 1) <i class="fa fa-check"></i> @endif
                            </td>
                            <td class="text-center align-middle">
                                @if ($stok->barang_stok_harga->barang->jenis == 2) <i class="fa fa-check"></i> @endif
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                    data-bs-target="#detailModal-{{ $stok->id }}">
                                    Lihat Sumber ({{ $stok->sources->count() }})
                                </button>
                                {{-- Pastikan Anda membuat modal ini di suatu tempat --}}
                            </td>
                            <td class="text-center">
                                {{-- Ganti form dengan input group dan tombol --}}
                                <div class="input-group">
                                    <input type="number" class="form-control form-control-sm" name="qty"
                                        value="{{ $inCart ? $qtyInCart : $stok->total_qty_karantina }}" min="1"
                                        max="{{ $stok->total_qty_karantina }}" id="qty-stok-{{ $stok->id }}" {{
                                        $stok->status != 0 ? 'disabled' : '' }}>

                                    <button type="button"
                                        class="btn btn-sm {{ $inCart ? 'btn-outline-success' : 'btn-success' }} btn-add-to-cart"
                                        data-stok-id="{{ $stok->id }}" {{ $stok->status != 0 ? 'disabled' : '' }}>
                                        <i class="fa {{ $inCart ? 'fa-check' : 'fa-plus' }}"></i>
                                        {{ $inCart ? 'Update' : 'Proses' }}
                                    </button>
                                </div>
                                @if($inCart)
                                <small class="text-success d-block mt-1">Sudah di keranjang</small>
                                @endif
                            </td>
                        </tr>
                        @empty

                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ---------------------------------------------------- --}}
{{-- 4. OFFCANVAS UNTUK KERANJANG (BARU) --}}
{{-- ---------------------------------------------------- --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasCart" aria-labelledby="offcanvasCartLabel">
    <div class="offcanvas-header">
        <h5 id="offcanvasCartLabel">
            <i class="fa fa-shopping-cart me-2"></i> Keranjang Proses Retur
        </h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body" id="offcanvas-cart-body">
        {{-- Konten keranjang akan dimuat di sini oleh AJAX --}}
        <div class="text-center p-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Memuat keranjang...</p>
        </div>
    </div>
    <div class="offcanvas-footer p-3 border-top bg-light">
        <form action="{{ route('billing.stok-retur.cart.process') }}" method="POST" id="form-process-cart">
            @csrf
            <button type="submit" class="btn btn-lg btn-success w-100">
                <i class="fa fa-check-circle me-2"></i> PROSES SEMUA ISI KERANJANG
            </button>
        </form>
    </div>
</div>

{{-- ---------------------------------------------------- --}}
{{-- 5. MODAL (Contoh, jika Anda belum punya) --}}
{{-- ---------------------------------------------------- --}}
@foreach ($dataStok as $stok)
<div class="modal fade" id="detailModal-{{ $stok->id }}" tabindex="-1"
    aria-labelledby="detailModalLabel-{{ $stok->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel-{{ $stok->id }}">
                    Sumber Retur: {{ $stok->barang_stok_harga->barang_nama?->nama }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Tgl. Retur</th>
                            <th>No. Retur</th>
                            <th>Konsumen</th>
                            <th>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stok->sources as $source)
                        <tr>
                            {{-- Asumsi relasi 'retur' ada di 'source' --}}
                            <td>{{ $source->barang_retur_detail->barang_retur->created_at->format('d-m-Y') }}</td>
                            <td>{{ $source->barang_retur_detail->barang_retur->kode }}</td>
                            <td>{{ $source->barang_retur_detail->barang_retur->konsumen->kode_toko->kode .' ' .$source->barang_retur_detail->barang_retur->konsumen->nama ?? '-' }}</td>
                            <td>{{ $source->qty_diterima }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data sumber.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/datatable/scroller.bootstrap5.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
{{-- Tambahkan CSS untuk Toast (Notifikasi) --}}
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/scroller.bootstrap5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>
{{-- Tambahkan JS untuk Toast (Notifikasi) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {
        // --- Setup Global AJAX untuk CSRF ---
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // --- Inisialisasi Select2 untuk Filter ---
        $('.select2-filter').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        // --- Inisialisasi DataTable ---
        var table2 = $('#dataTable').DataTable({
            "scrollY": "450px",
            scrollX: true,
            "scrollCollapse": true,
            "paging": false,
            "searching": true,
        });

        // -----------------------------------------------------------------
        // --- BARU: Konfigurasi Swal Toast (Pengganti Global Toastr) ---
        // -----------------------------------------------------------------
        const SwalToast = Swal.mixin({
            toast: true,
            position: 'top-end', // Posisi di pojok kanan atas
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        function resetAllCartButtonsInTable() {
            // 1. Temukan semua tombol yang sedang dalam status 'Update'
            $('.btn-add-to-cart.btn-outline-success').each(function() {
                var $button = $(this);
                // Kembalikan ke status 'Proses'
                $button.prop('disabled', false)
                       .removeClass('btn-outline-success')
                       .addClass('btn-success')
                       .html('<i class="fa fa-plus"></i> Proses');

                // Hapus teks 'Sudah di keranjang'
                $button.parent().next('small.text-success').remove();
            });

            // 2. Temukan semua baris yang ter-highlight
            $('tr.table-info').removeClass('table-info');
        }

        var $floatingCartButton = $('#btn-open-cart');
        var cartOffcanvas = document.getElementById('offcanvasCart');

        // Saat offcanvas MULAI DIBUKA:
        cartOffcanvas.addEventListener('show.bs.offcanvas', function () {
            // Sembunyikan tombol floating agar tidak menghalangi
            $floatingCartButton.hide();
        });

        // Saat offcanvas SUDAH TERTUTUP SEMPURNA:
        cartOffcanvas.addEventListener('hidden.bs.offcanvas', function () {
            // Tampilkan kembali tombol floating
            $floatingCartButton.show();
        });

        // ---------------------------------------------
        // --- LOGIKA KERANJANG (AJAX) ---
        // ---------------------------------------------

        // 1. Tombol "Proses / Update" di tabel
        $('.btn-add-to-cart').on('click', function() {
            var $button = $(this);
            var stokId = $button.data('stok-id');
            var qty = $('#qty-stok-' + stokId).val();
            var maxQty = parseInt($('#qty-stok-' + stokId).attr('max'));

            if (parseInt(qty) > maxQty) {
                // GANTI DARI TOASTR KE SWALTOAST
                SwalToast.fire({
                    icon: 'error',
                    title: 'Jumlah melebihi stok karantina (Maks: ' + maxQty + ')'
                });
                $('#qty-stok-' + stokId).val(maxQty); // Reset ke max
                return;
            }

            if (parseInt(qty) <= 0) {
                // GANTI DARI TOASTR KE SWALTOAST
                SwalToast.fire({
                    icon: 'error',
                    title: 'Jumlah harus minimal 1'
                });
                $('#qty-stok-' + stokId).val(1); // Reset ke 1
                return;
            }

            $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: '{{ route("billing.stok-retur.cart.add") }}',
                method: 'POST',
                data: {
                    stok_retur_id: stokId,
                    qty: qty
                },
                success: function(response) {
                    if (response.status == 'success') {
                        // GANTI DARI TOASTR KE SWALTOAST
                        SwalToast.fire({
                            icon: 'success',
                            title: response.message
                        });

                        $('#cart-item-count').text(response.totalItems); // Update counter
                        // ... (sisa logika update UI)
                        $button.prop('disabled', false).removeClass('btn-success').addClass('btn-outline-success')
                               .html('<i class="fa fa-check"></i> Update');
                        $('#row-stok-' + stokId).addClass('table-info');
                        if ($('#row-stok-' + stokId).find('small.text-success').length === 0) {
                            $button.parent().after('<small class="text-success d-block mt-1">Sudah di keranjang</small>');
                        }
                    }
                },
                error: function(xhr) {
                    var errorMsg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan.';
                    // GANTI DARI TOASTR KE SWALTOAST
                    SwalToast.fire({
                        icon: 'error',
                        title: errorMsg
                    });
                    $button.prop('disabled', false).html('<i class="fa fa-plus"></i> Proses');
                }
            });
        });

        // 2. Saat Offcanvas Keranjang Dibuka
        var cartOffcanvas = document.getElementById('offcanvasCart');
        cartOffcanvas.addEventListener('show.bs.offcanvas', function () {
            loadCartContents();
        });

        // 3. Fungsi untuk memuat isi keranjang
        function loadCartContents() {
            var $body = $('#offcanvas-cart-body');
            $body.html('<div class="text-center p-5"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Memuat keranjang...</p></div>');

            $.ajax({
                url: '{{ route("billing.stok-retur.cart.get") }}',
                method: 'GET',
                success: function(response) {
                    if (response.status == 'success') {
                        $body.html(response.html);
                        $('#cart-item-count').text(response.totalItems);
                    }
                },
                error: function() {
                    $body.html('<p class="text-center text-danger">Gagal memuat keranjang. Silakan coba lagi.</p>');
                }
            });
        }

        // 4. Tombol "Hapus" di dalam Offcanvas (Event delegation)
        $('#offcanvas-cart-body').on('click', '.btn-remove-from-cart', function() {
            var $button = $(this);
            var stokId = $button.data('stok-id');
            $button.prop('disabled', true);

            $.ajax({
                url: '{{ route("billing.stok-retur.cart.remove") }}',
                method: 'POST',
                data: {
                    stok_retur_id: stokId
                },
                success: function(response) {
                    if (response.status == 'success') {
                        // GANTI DARI TOASTR KE SWALTOAST
                        SwalToast.fire({
                            icon: 'info',
                            title: 'Item dihapus dari keranjang.'
                        });

                        loadCartContents(); // Muat ulang isi keranjang

                        // Reset tampilan di tabel utama
                        var $mainButton = $('.btn-add-to-cart[data-stok-id="' + stokId + '"]');
                        $mainButton.prop('disabled', false).removeClass('btn-outline-success').addClass('btn-success')
                                   .html('<i class="fa fa-plus"></i> Proses');
                        $('#row-stok-' + stokId).removeClass('table-info');
                        $('#row-stok-' + stokId).find('small.text-success').remove();
                    }
                },
                error: function() {
                    // GANTI DARI TOASTR KE SWALTOAST
                    SwalToast.fire({
                        icon: 'error',
                        title: 'Gagal menghapus item.'
                    });
                    $button.prop('disabled', false);
                }
            });
        });

        $('#offcanvas-cart-body').on('click', '#btn-clear-cart', function() {
            var $button = $(this);

            // Tampilkan konfirmasi Swal
            Swal.fire({
                title: 'Kosongkan Keranjang?',
                text: "Semua item di keranjang akan dihapus. Anda yakin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33', // Warna merah (danger)
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Kosongkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {

                    // Tampilkan loading...
                    Swal.fire({
                        title: 'Mengosongkan...',
                        text: 'Harap tunggu.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: '{{ route("billing.stok-retur.cart.clear") }}',
                        method: 'POST',
                        // Data tidak perlu, CSRF sudah di setup global
                        success: function(response) {
                            if (response.status == 'success') {
                                // 1. Muat ulang isi keranjang (otomatis jadi kosong)
                                loadCartContents();

                                // 2. Update counter di tombol floating
                                $('#cart-item-count').text(response.totalItems);

                                // 3. Reset semua tombol di tabel utama
                                resetAllCartButtonsInTable();

                                // 4. Tutup swal loading & tampilkan notifikasi sukses
                                Swal.close();
                                SwalToast.fire({
                                    icon: 'success',
                                    title: response.message
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Gagal mengosongkan keranjang.'
                            });
                        }
                    });
                }
            });
        });

        // -----------------------------------------------------------------
        // 5. BARU: Konfirmasi submit keranjang MENGGUNAKAN SWAL
        //    (Menggantikan logika `confirm()` sebelumnya)
        // -----------------------------------------------------------------
        $('#form-process-cart').on('submit', function(e) {
            e.preventDefault(); // SELALU cegah submit form default

            var $form = $(this);
            var $button = $form.find('button[type="submit"]');

            if ($('#cart-item-count').text() == '0') {
                // GANTI DARI TOASTR KE SWALTOAST
                SwalToast.fire({
                    icon: 'error',
                    title: 'Keranjang Anda kosong!'
                });
                return; // Hentikan eksekusi
            }

            // Tampilkan modal konfirmasi Swal
            Swal.fire({
                title: 'Proses Isi Keranjang?',
                text: "Semua item di keranjang akan diproses. Anda yakin?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745', // Warna hijau (success)
                cancelButtonColor: '#6c757d', // Warna abu-abu (secondary)
                confirmButtonText: 'Ya, Proses Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                // Hanya jika pengguna mengklik "Ya, Proses Sekarang!"
                if (result.isConfirmed) {
                    // Tampilkan loading di tombol
                    $button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> MEMPROSES...');

                    // Submit form-nya secara manual
                    $form.get(0).submit();
                }
            });
        });

    });
</script>
@endpush
