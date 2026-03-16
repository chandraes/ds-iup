@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('statistik.omset-barang.bulanan') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <h3 class="m-0">Detail Penjualan Barang</h3>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-left-primary">
        <div class="card-body">
            <div class="row">
                <div class="col-md-5">
                    <small class="text-muted text-uppercase fw-bold">Informasi Barang</small>
                    <h5 class="fw-bold text-primary">{{ $barang->kode }} - {{ $barang->barang_nama?->nama }}</h5>
                    <span>Merk: {{ $barang->merk ?? '-' }} | Perusahaan: {{ $barang->unit?->nama ?? '-' }}</span>
                </div>
                <div class="col-md-3">
                    <small class="text-muted text-uppercase fw-bold">Periode</small>
                    <h5 class="fw-bold">{{ $namaBulan }} {{ $tahun }}</h5>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted text-uppercase fw-bold">Total Halaman Ini</small>
                    <h5 class="fw-bold text-success">Qty: {{ number_format($details->sum('jumlah'), 0, ',', '.') }} {{ $barang->satuan?->nama }}</h5>
                    <h5 class="fw-bold text-success">Rp {{ number_format($details->sum('total'), 0, ',', '.') }}</h5>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="detailTable">
                    <thead class="table-dark text-nowrap">
                        <tr>
                            <th width="5%" class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>No Invoice</th>
                            <th>Konsumen / Toko</th>
                            <th class="text-end">Qty Terjual</th>
                            <th class="text-end">Harga Satuan</th>
                            <th class="text-end">Diskon</th>
                            <th class="text-end">PPn</th>
                            <th class="text-end">Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($details as $row)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ date('d-m-Y', strtotime($row->tanggal)) }}</td>
                            <td>{{ $row->no_invoice }}</td>
                            <td>{{ $row->kode_toko ? "[$row->kode_toko] " : '' }}{{ $row->nama_konsumen ?? '-' }}</td>
                            <td class="text-end fw-bold">{{ number_format($row->jumlah, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->diskon, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->ppn, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">{{ number_format($row->total, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center p-4"><h6 class="text-muted">Tidak ada data transaksi.</h6></td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        $('#detailTable').DataTable();
    });
</script>
@endpush
