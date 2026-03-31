@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('statistik.omset-barang.tahunan') }}" class="btn btn-secondary btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
                <h3 class="m-0">Detail Transaksi Tahunan Barang</h3>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm border-left-primary">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted text-uppercase fw-bold">Barang</small>
                    <h5 class="fw-bold text-primary">{{ $dbBarang->barang_nama->nama ?? '-' }}</h5>
                    <span>Kode: {{ $dbBarang->kode }} | Kategori: {{ $dbBarang->kategori->nama ?? '-' }}</span>
                </div>
                <div class="col-md-4">
                    <small class="text-muted text-uppercase fw-bold">Periode Filter</small>
                    <h5 class="fw-bold">{{ $periodeLabel }}</h5>
                </div>
                <div class="col-md-4 text-end">
                    <small class="text-muted text-uppercase fw-bold">Total Nilai Halaman Ini</small>
                    <h4 class="fw-bold text-success mb-0">Rp {{ number_format($details->sum('total'), 0, ',', '.') }}</h4>
                    <small class="text-muted">Total Qty: {{ number_format($details->sum('jumlah'), 0, ',', '.') }} {{ $dbBarang->satuan->nama ?? '' }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="detailTable" style="font-size: 0.85rem;">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>No. Invoice</th>
                            <th>Konsumen</th>
                            <th>Qty</th>
                            <th>Harga Satuan</th>
                            <th>PPN</th>
                            <th>Diskon</th>
                            <th>Subtotal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($details as $index => $row)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') }}</td>
                            <td>{{ $row->kode_invoice }}</td>
                            <td>{{ $row->nama_konsumen }}</td>
                            <td class="text-end">{{ number_format($row->jumlah, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->harga_satuan, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->ppn, 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row->diskon, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">{{ number_format($row->total, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center p-4">
                                <h6 class="text-muted">Tidak ada data transaksi untuk filter ini.</h6>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#detailTable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true
        });
    });
</script>
@endpush
