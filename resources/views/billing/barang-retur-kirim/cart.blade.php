@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header & Navigasi --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Review Keranjang Retur</h3>
            <p class="text-muted mb-0">Cek jumlah barang sebelum memproses Invoice.</p>
        </div>
        <a href="{{ route('billing.stok-retur') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    @if($carts->count() > 0)
        {{-- Info Supplier --}}
        <div class="alert alert-info shadow-sm border-0 d-flex align-items-center mb-4">
            <i class="bi bi-building fs-3 me-3"></i>
            <div>
                <small class="text-uppercase fw-bold opacity-75">Supplier / Unit:</small>
                <div class="fs-5 fw-bold">{{ $supplier->nama ?? 'Tanpa Unit' }}</div>
            </div>
        </div>

        {{-- Form Utama --}}
        <form action="{{ route('billing.stok-retur.checkout') }}" method="POST" id="formCheckout">
            @csrf

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 card-title"><i class="bi bi-list-check"></i> Daftar Barang yang akan Diproses</h5>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="ps-4 py-3">Detail Barang</th>
                                    <th class="text-center">Stok Gudang</th>
                                    <th style="width: 220px;">Qty Retur</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($carts as $item)
                                <tr id="row-{{ $item->id }}">
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark fs-6">{{ $item->stok_retur->barang->barang_nama->nama }}</div>
                                        <div class="text-muted small">
                                            <span class="badge bg-light text-dark border">{{ $item->stok_retur->barang->kode }}</span>
                                            {{ $item->stok_retur->barang->merk }}
                                        </div>
                                    </td>

                                    {{-- [MODIFIKASI] Tampilan Stok dengan Format Ribuan --}}
                                    <td class="text-center">
                                        <span class="badge bg-secondary rounded-pill px-3 fs-6">
                                            {{ number_format($item->stok_retur->total_qty_karantina, 0, ',', '.') }}
                                        </span>
                                    </td>

                                    <td>
                                        <div class="d-flex flex-column align-items-start">
                                            <div class="input-group input-group-sm" style="width: 150px;">
                                                <button type="button" class="btn btn-outline-secondary btn-decrease"
                                                        data-id="{{ $item->id }}"><i class="bi bi-dash-lg"></i></button>

                                                {{-- [MODIFIKASI] Input Type Text agar bisa ada titiknya --}}
                                                <input type="text" class="form-control text-center fw-bold fs-6 qty-input"
                                                       id="input-qty-{{ $item->id }}"
                                                       data-id="{{ $item->id }}"
                                                       data-max="{{ $item->stok_retur->total_qty_karantina }}"
                                                       value="{{ number_format($item->qty, 0, ',', '.') }}"
                                                       readonly>

                                                <button type="button" class="btn btn-outline-secondary btn-increase"
                                                        data-id="{{ $item->id }}"><i class="bi bi-plus-lg"></i></button>
                                            </div>
                                            {{-- Status Simpan --}}
                                            <div class="mt-1 ps-1" id="status-{{ $item->id }}" style="min-height: 18px; font-size: 0.8em;">
                                                <span class="text-muted fst-italic">Siap</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-light text-danger border btn-sm btn-delete-item shadow-sm"
                                                data-id="{{ $item->id }}" title="Hapus Item">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                             <span id="global-status" class="text-danger fw-bold d-none small">
                                <span class="spinner-border spinner-border-sm me-1"></span> Menyimpan perubahan...
                            </span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow" id="btn-checkout">
                            <i class="bi bi-send-check-fill me-2"></i> Proses Invoice
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @else
        <div class="text-center py-5">
            <img src="https://cdn-icons-png.flaticon.com/512/11329/11329060.png" width="120" class="mb-4 opacity-50" alt="Empty">
            <h4>Keranjang Retur Kosong</h4>
            <p class="text-muted">Silakan pilih barang dari daftar stok retur terlebih dahulu.</p>
            <a href="{{ route('billing.stok-retur') }}" class="btn btn-primary px-4 mt-2">
                Lihat Daftar Stok
            </a>
        </div>
    @endif
</div>

{{-- JAVASCRIPT --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let debounceTimer;

        // HELPER: Format Ribuan (1000 -> "1.000")
        function formatRibuan(angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // HELPER: Parse Ribuan ("1.000" -> 1000)
        function parseRibuan(str) {
            // Hapus titik, lalu ubah ke integer
            return parseInt(str.replace(/\./g, '')) || 0;
        }

        // 1. Logic Tombol Tambah (+)
        document.querySelectorAll('.btn-increase').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                let input = document.getElementById('input-qty-' + id);

                // Ambil nilai asli (tanpa titik)
                let currentVal = parseRibuan(input.value);
                let maxVal = parseInt(input.dataset.max);

                if (currentVal < maxVal) {
                    let newVal = currentVal + 1;
                    // Tampilkan kembali dengan format titik
                    input.value = formatRibuan(newVal);
                    // Kirim data asli (integer) ke server
                    triggerUpdate(id, newVal);
                } else {
                    Swal.fire({ toast: true, position: 'center', icon: 'warning', title: 'Mencapai batas stok', showConfirmButton: false, timer: 1500 });
                }
            });
        });

        // 2. Logic Tombol Kurang (-)
        document.querySelectorAll('.btn-decrease').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                let input = document.getElementById('input-qty-' + id);

                let currentVal = parseRibuan(input.value);

                if (currentVal > 1) {
                    let newVal = currentVal - 1;
                    input.value = formatRibuan(newVal);
                    triggerUpdate(id, newVal);
                }
            });
        });

        // 3. Logic AJAX Update
        function triggerUpdate(id, qtyAsli) {
            let statusElem = document.getElementById('status-' + id);
            let btnCheckout = document.getElementById('btn-checkout');
            let globalStatus = document.getElementById('global-status');

            statusElem.innerHTML = '<span class="text-primary fw-bold"><i class="spinner-border spinner-border-sm" style="width:0.7em;height:0.7em;"></i> Simpan...</span>';
            btnCheckout.disabled = true;
            globalStatus.classList.remove('d-none');

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetch("{{ route('billing.stok-retur.cart-update') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({id: id, qty: qtyAsli}) // Kirim qty integer asli
                })
                .then(r => r.json())
                .then(d => {
                    if(d.status == 'success') statusElem.innerHTML = '<span class="text-success"><i class="bi bi-check-all"></i> Tersimpan</span>';
                    else { statusElem.innerHTML = '<span class="text-danger">Gagal</span>'; Swal.fire('Error', d.message, 'error'); }
                })
                .catch(() => statusElem.innerHTML = '<span class="text-danger">Koneksi Error</span>')
                .finally(() => {
                    btnCheckout.disabled = false;
                    globalStatus.classList.add('d-none');
                });
            }, 500);
        }

        // 4. Logic Hapus Item
        document.querySelectorAll('.btn-delete-item').forEach(btn => {
            btn.addEventListener('click', function() {
                let id = this.dataset.id;
                Swal.fire({
                    title: 'Hapus Item?',
                    text: "Item akan dihapus dari keranjang.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545'
                }).then((result) => {
                    if (result.isConfirmed) window.location.href = "{{ url('billing/stok-retur/cart-delete') }}/" + id;
                });
            });
        });

        // 5. Logic Konfirmasi Checkout
        const formCheckout = document.getElementById('formCheckout');
        if (formCheckout) {
            formCheckout.addEventListener('submit', function(e) {
                e.preventDefault();

                let totalItems = {{ $carts->count() }};
                let supplierName = "{{ $supplier->nama ?? '-' }}";

                Swal.fire({
                    title: 'Proses Invoice?',
                    html: `Memproses <b>${totalItems} Item</b> dari <b>${supplierName}</b>.<br><span class="text-muted small">Stok akan dipotong otomatis.</span>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Cek Lagi',
                    confirmButtonColor: '#0d6efd'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formCheckout.submit();
                    }
                });
            });
        }
    });
</script>
@endsection
