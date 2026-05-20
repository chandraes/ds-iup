@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fa fa-history text-info"></i> Rekapitulasi Invoice Janji Bayar</h1>
            <p class="text-muted small mb-0">Menampilkan seluruh riwayat dokumen janji bayar yang telah diselesaikan atau dibatalkan (void).</p>
        </div>
        <a href="{{ route('rekap') }}" class="btn btn-secondary shadow-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke Menu Rekap
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light">
            <form action="{{ route('rekap.janji-bayar') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Pilih Bulan</label>
                    <select name="bulan" class="form-select">
                        @foreach([
                            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
                            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
                            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                        ] as $key => $bulanNama)
                            <option value="{{ $key }}" {{ $selectedBulan == $key ? 'selected' : '' }}>{{ $bulanNama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Pilih Tahun</label>
                    <select name="tahun" class="form-select">
                        @foreach($daftarTahun as $tahun)
                            <option value="{{ $tahun }}" {{ $selectedTahun == $tahun ? 'selected' : '' }}>{{ $tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Filter Status</label>
                    <select name="status" class="form-select">
                        <option value="all" {{ $selectedStatus == 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="1" {{ $selectedStatus == '1' ? 'selected' : '' }}>Selesai (Success)</option>
                        <option value="99" {{ $selectedStatus == '99' ? 'selected' : '' }}>Void / Dibatalkan</option>
                    </select>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-filter"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="card border-0 border-start border-success border-4 shadow-sm">
                <div class="card-body py-3">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.75rem;">Total Dana Diselesaikan (Bulan Ini)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalSelesai, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 border-start border-danger border-4 shadow-sm">
                <div class="card-body py-3">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 0.75rem;">Total Dana Void / Dibatalkan (Bulan Ini)</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp {{ number_format($totalVoid, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle w-100" id="rekapTable" style="font-size: 0.9rem;">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Kode Dokumen</th>
                            <th>Tanggal Proses</th>
                            <th>Konsumen</th>
                            <th class="text-center">Metode</th>
                            <th class="text-end">Nominal</th>
                            <th class="text-center" width="12%">Status</th>
                            <th class="text-center" width="8%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($rekapData as $index => $data)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td class="fw-bold text-primary">{{ $data->kode }}</td>
                            <td>{{ \Carbon\Carbon::parse($data->updated_at)->format('d-m-Y H:i') }} WIB</td>
                            <td>{{ $data->konsumen->kode_toko?->kode ?? '' }} {{ $data->konsumen->nama }}</td>
                            <td class="text-center text-uppercase"><span class="badge bg-secondary text-white">{{ $data->metode }}</span></td>
                            <td class="text-end fw-bold">Rp {{ number_format($data->nominal, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($data->status == 1)
                                    <span class="badge bg-success"><i class="fa fa-check-circle"></i> Selesai</span>
                                @elseif($data->status == 99)
                                    <span class="badge bg-danger"><i class="fa fa-ban"></i> Void / Batal</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('billing.invoice-janji-bayar.detail', $data->id) }}" class="btn btn-xs btn-outline-info py-1 px-2" title="Lihat Detail">
                                    <i class="fa fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="{{ 'stylesheet' }}">
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Vanilla DataTables untuk mempermudah pencarian lokal di browser user
        $('#rekapTable').DataTable({
            order: [[2, 'desc']], // Default urutan berdasarkan Tanggal Proses terbaru
            pageLength: 25,
            language: {
                search: "Cari Rekap:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan halaman _PAGE_ dari _PAGES_",
                infoEmpty: "Tidak ada data tersedia",
                infoFiltered: "(disaring dari _MAX_ total data)"
            }
        });
    });
</script>
@endpush
