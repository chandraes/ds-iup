<div class="p-4">
    {{-- Header Informasi Utama --}}
    <div class="row mb-3 border-bottom pb-3 align-items-center">
        <div class="col-md-6">
            <h6 class="text-uppercase text-muted fw-bold small">Nomor Invoice</h6>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold text-primary mb-0">#{{ sprintf('%04d', $invoice->nomor) }}</h4>
               @switch($invoice->tipe)
                    @case(0) <span class="badge rounded-pill bg-warning text-dark">Diproses</span> @break
                    @case(1) <span class="badge rounded-pill bg-primary">Dikirim</span> @break
                    @case(2) <span class="badge rounded-pill bg-info text-dark">Parsial</span> @break
                    @case(3) <span class="badge rounded-pill bg-success">Selesai</span> @break
                    @case(99) <span class="badge rounded-pill bg-danger">Void</span> @break
                @endswitch
            </div>
            <div class="text-muted small mt-1">Tanggal Kirim: {{ $invoice->created_at ? $invoice->created_at->format('d/m/Y H:i') : '-' }}</div>
        </div>
        <div class="col-md-6 text-end">
            <h6 class="text-uppercase text-muted fw-bold small">Tujuan Supplier</h6>
            <h5 class="fw-bold mb-0">{{ $invoice->barang_unit->nama ?? 'Tanpa Unit' }}</h5>
            <div class="text-muted small font-monospace" style="font-size: 0.85em;">Operator: {{ $invoice->user->name ?? 'Sistem' }}</div>
        </div>
    </div>

    {{-- HIGHLIGHT TOTAL REFUND (HANYA TAMPIL JIKA SUDAH ADA REFUND > 0) --}}
    @if(($invoice->total_refund ?? 0) > 0)
        <div class="card border-warning bg-warning-subtle shadow-sm mb-4">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-cash-stack text-warning-emphasis fs-4"></i>
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Total Refund Diterima (Ganti Uang)</h6>
                        <small class="text-muted" style="font-size: 0.78em;">Akumulasi pencairan dana dari transaksi retur ini</small>
                    </div>
                </div>
                <div class="fs-4 fw-bold text-dark">
                    Rp {{ number_format($invoice->total_refund, 0, ',', '.') }}
                </div>
            </div>
        </div>
    @endif

    {{-- Catatan Retur Jika Ada --}}
    @if($invoice->keterangan)
        <div class="alert alert-light border border-start border-3 border-primary p-2 mb-4" style="font-size: 0.9rem;">
            <strong><i class="bi bi-journal-text"></i> Catatan Awal Retur:</strong> {{ $invoice->keterangan }}
        </div>
    @endif

    {{-- TABEL KONSOLIDASI PROGRESS BARANG --}}
    <div class="table-responsive border rounded shadow-sm">
        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 0.88rem;">
            <thead class="table-light border-bottom text-center align-middle">
                <tr>
                    <th rowspan="2" width="4%" class="border-end">No</th>
                    <th rowspan="2" class="text-start border-end">Barang Retur</th>
                    <th rowspan="2" width="11%" class="border-end bg-light">Qty Awal<br><small class="text-muted">(Target)</small></th>
                    <th colspan="3" width="33%" class="border-end bg-light fw-bold py-1">Hasil Penerimaan Gudang</th>
                    <th rowspan="2" width="11%" class="border-end bg-warning-subtle text-warning-focus">Sisa<br><small class="text-muted">(Antrean)</small></th>
                    <th rowspan="2" width="21%">Catatan / Info Item</th>
                </tr>
                <tr class="small border-top">
                    <th width="11%" class="bg-success-subtle text-success border-end py-1">Masuk Stok</th>
                    <th width="11%" class="bg-danger-subtle text-danger border-end py-1">Tukar Barang</th>
                    <th width="11%" class="bg-warning-subtle text-warning-emphasis py-1">Ganti Uang</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach($processedItems as $barangId => $item)
                    @php
                        $satuan = $item['barang']->satuan->nama ?? 'pcs';

                        $diterima  = $item['diterima'] ?? 0;
                        $batal     = $item['batal'] ?? 0;        // Hapus (Tukar Barang)
                        $gantiUang = $item['ganti_uang'] ?? 0;   // Hapus (Ganti Uang)

                        $sisa = $item['qty_awal'] - ($diterima + $batal + $gantiUang);

                        // Deteksi jika ini barang pengganti murni (tidak ada di invoice awal)
                        $isBarangPenggantiMurni = ($item['qty_awal'] == 0);
                    @endphp
                    <tr class="{{ $isBarangPenggantiMurni ? 'table-info-subtle' : '' }}">
                        {{-- 1. Nomor --}}
                        <td class="text-center text-muted fw-bold border-end">{{ $no++ }}</td>

                        {{-- 2. Detail Barang --}}
                        <td class="border-end">
                            <div class="fw-bold text-dark">{{ $item['barang']->barang_nama->nama ?? '-' }}</div>
                            <small class="text-muted d-block font-monospace" style="font-size: 0.8em;">
                                {{ $item['barang']->kode ?? '-' }} | {{ $item['barang']->merk ?? '-' }}
                                @if($isBarangPenggantiMurni)
                                    <span class="badge bg-info text-dark ms-1" style="font-size: 0.85em;"><i class="bi bi-box-seam"></i> Pengganti</span>
                                @endif
                            </small>
                        </td>

                        {{-- 3. Qty Awal Invoice --}}
                        <td class="text-center fw-bold border-end bg-light">
                            {{ $isBarangPenggantiMurni ? '-' : $item['qty_awal'].' '.$satuan }}
                        </td>

                        {{-- 4. Sub Kolom: Masuk Stok --}}
                        <td class="text-center text-success fw-bold border-end">
                            @if($diterima > 0)
                                <i class="bi bi-box-arrow-in-down"></i> {{ $diterima }} {{ $satuan }}
                            @else
                                <span class="text-muted opacity-50">-</span>
                            @endif
                        </td>

                        {{-- 5. Sub Kolom: Tukar Barang (Hapus Fisik) --}}
                        <td class="text-center text-danger fw-bold border-end">
                            @if($batal > 0)
                                <i class="bi bi-arrow-repeat"></i> {{ $batal }} {{ $satuan }}
                            @else
                                <span class="text-muted opacity-50">-</span>
                            @endif
                        </td>

                        {{-- 6. Sub Kolom: Ganti Uang (Refund) --}}
                        <td class="text-center text-warning-emphasis fw-bold border-end">
                            @if($gantiUang > 0)
                                <i class="bi bi-cash"></i> {{ $gantiUang }} {{ $satuan }}
                            @else
                                <span class="text-muted opacity-50">-</span>
                            @endif
                        </td>

                        {{-- 7. Sisa Tungguan --}}
                        <td class="text-center border-end fw-bold">
                            @if($isBarangPenggantiMurni)
                                <span class="text-muted small fst-italic">-</span>
                            @elseif($sisa > 0)
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> {{ $sisa }} {{ $satuan }}</span>
                            @else
                                <span class="badge bg-success text-white px-2"><i class="bi bi-check2-all"></i> Beres</span>
                            @endif
                        </td>

                        {{-- 8. Log Catatan Item --}}
                        <td class="small text-muted">
                            @if(!empty($item['catatan']))
                                <ul class="list-unstyled mb-0 ps-0" style="font-size: 0.85em; line-height: 1.2;">
                                    @foreach($item['catatan'] as $catatan)
                                        <li class="mb-1"><i class="bi bi-dot text-secondary"></i> {{ $catatan }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <span class="text-muted opacity-50 fst-italic">Tidak ada catatan</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
