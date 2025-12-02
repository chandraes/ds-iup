<div class="p-4">
    {{-- Header Invoice di Modal --}}
    <div class="row mb-4 border-bottom pb-3 align-items-center">
        <div class="col-md-6">
            <h6 class="text-uppercase text-muted fw-bold small">Nomor Invoice</h6>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold text-primary mb-0">#{{ sprintf('%04d', $invoice->nomor) }}</h4>

                {{-- Logic Badge Status --}}
               @switch($invoice->tipe)
                    @case(0)
                        {{-- UBAH LABEL JADI: DIPROSES --}}
                        <span class="badge rounded-pill bg-warning text-dark border border-warning">
                            <i class="bi bi-gear-fill"></i> Diproses
                        </span>
                        @break
                    @case(1)
                        <span class="badge rounded-pill bg-info text-dark border border-info">
                            <i class="bi bi-truck"></i> Dikirim
                        </span>
                        @break
                    @case(2)
                        <span class="badge rounded-pill bg-success border border-success">
                            <i class="bi bi-check-circle-fill"></i> Selesai
                        </span>
                        @break
                    @case(99)
                        <span class="badge rounded-pill bg-danger border border-danger">
                            <i class="bi bi-x-circle"></i> Void
                        </span>
                        @break
                    @default
                        <span class="badge rounded-pill bg-secondary">Unknown</span>
                @endswitch
            </div>
        </div>

        <div class="col-md-6 text-md-end">
            <h6 class="text-uppercase text-muted fw-bold small">Tanggal Dibuat</h6>
            <p class="fw-bold fs-5 mb-0">
                {{ \Carbon\Carbon::parse($invoice->created_at)->translatedFormat('d F Y') }}
            </p>

        </div>
    </div>

    {{-- Info Supplier --}}
    <div class="alert alert-light border shadow-sm mb-4">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-circle p-2 me-3 d-flex justify-content-center align-items-center" style="width: 48px; height: 48px;">
                <i class="bi bi-building fs-4"></i>
            </div>
            <div>
                <small class="text-muted text-uppercase fw-bold">Supplier</small>
                <div class="fw-bold fs-5 text-dark">
                    {{ $invoice->barang_unit->nama ?? 'Tanpa Nama Unit' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Rincian Barang --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold mb-0"><i class="bi bi-box-seam"></i> Rincian Barang</h6>
        <span class="badge bg-light text-dark border">{{ $invoice->details->count() }} Item</span>
    </div>

    <div class="table-responsive rounded border">
        <table class="table table-striped table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr class="text-center small text-uppercase text-muted">
                    <th width="5%">No</th>
                    <th class="text-start">Nama Barang</th>
                    <th width="15%">Kode</th>
                    <th width="15%">Jumlah</th>
                    <th width="10%">Satuan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->details as $index => $item)
                <tr>
                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                    <td>
                        <span class="fw-bold text-dark">{{ $item->barang->barang_nama->nama }}</span><br>
                        <small class="text-muted">{{ $item->barang->merk }}</small>
                    </td>
                    <td class="text-center font-monospace small">{{ $item->barang->kode }}</td>
                    <td class="text-center fw-bold fs-6">
                        {{ number_format($item->qty, 0, ',', '.') }}
                    </td>
                    <td class="text-center text-muted small">
                        {{ $item->barang->satuan->nama ?? '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted fst-italic">
                        Tidak ada detail barang.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
