<div class="alert alert-light border mb-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <strong>{{ $stokRetur->barang->barang_nama->nama ?? '-' }}</strong>
            <br>
            <small class="text-muted">{{ $stokRetur->barang->kode }} | {{ $stokRetur->barang->merk }}</small>
        </div>
        <div class="text-end">
            Total Bad Stok:
            <h4 class="text-danger fw-bold mb-0">
                {{ number_format($stokRetur->total_qty_karantina) }}
                <span style="font-size: 0.6em; color: #666;">{{ $stokRetur->barang->satuan->nama ?? '' }}</span>
            </h4>
        </div>
    </div>
</div>

<div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
    <table class="table table-sm table-bordered table-striped mb-0">
        <thead class="table-dark sticky-top" style="z-index: 1;">
            <tr class="text-center">
                <th>Tgl Masuk</th>
                <th>Dari Konsumen</th>
                <th>Batch Asal</th>
                <th>Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stokRetur->sources as $source)
                <tr>
                    {{-- Tanggal --}}
                    <td class="text-center align-middle">
                        {{ $source->created_at->format('d/m/Y H:i') }}
                    </td>

                    {{-- Konsumen --}}
                    <td class="align-middle">
                        @if($source->detail)
                            <i class="bi bi-person-circle text-secondary me-1"></i>
                            {{ $source->detail->barang_retur->konsumen->kode_toko->kode . " " .$source->detail->barang_retur->konsumen->nama ?? 'Umum/Guest' }}
                            <div class="text-muted small" style="font-size: 0.8em;">
                                No. Retur: {{ $source->detail->barang_retur->nomor ?? '-' }}
                            </div>
                        @else
                            <span class="text-danger fst-italic">Data Retur Terhapus</span>
                        @endif
                    </td>

                    {{-- Batch Asal (Traceability) --}}
                    <td class="text-center align-middle">
                        @if($source->barang_stok_harga_id)
                            <span class="badge bg-info text-dark">ID Batch: {{ $source->barang_stok_harga_id }}</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>

                    {{-- Qty Masuk --}}
                    <td class="text-center align-middle">
                        <span class="fw-bold text-success">+{{ number_format($source->qty_diterima) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-3 text-muted">
                        Tidak ada data riwayat sumber.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
