{{-- resources/views/billing/barang-retur-kirim/partials/cart_body.blade.php --}}

@if ($items->isEmpty())
    <div class="text-center p-5">
        <i class="fa fa-shopping-cart fa-3x text-muted"></i>
        <p class="mt-3 text-muted">Keranjang Anda masih kosong.</p>
        <p>Silakan tambahkan barang dari tabel untuk diproses.</p>
    </div>
@else
    {{-- MODIFIKASI BLOK INI --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="alert alert-info py-2 px-3 mb-0">
            Ada <strong>{{ $items->count() }} item</strong> di keranjang Anda.
        </div>

        {{-- TAMBAHKAN TOMBOL INI --}}
        <button class="btn btn-sm btn-outline-danger" id="btn-clear-cart">
            <i class="fa fa-trash me-1"></i> Kosongkan
        </button>
    </div>
    {{-- AKHIR MODIFIKASI --}}

    <ul class="list-group list-group-flush">
        @foreach ($items as $item)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <div>
                <strong class="d-block">{{ $item->barang_stok_harga->barang_nama?->nama }}</strong>
                <small class="text-muted">{{ $item->barang_stok_harga->barang?->kode }}</small>
                <div class="mt-1">
                    Qty: <strong class="text-primary">{{ $item->qty_in_cart }}</strong>
                    <small>{{ $item->barang_stok_harga->barang->satuan->nama }}</small>
                </div>
            </div>
            <button class="btn btn-sm btn-outline-danger btn-remove-from-cart" data-stok-id="{{ $item->id }}">
                <i class="fa fa-trash"></i>
            </button>
        </li>
        @endforeach
    </ul>
@endif
