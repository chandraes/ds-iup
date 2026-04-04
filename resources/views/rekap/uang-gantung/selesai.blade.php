@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1 class="display-6 fw-bold">REKAP UANG GANTUNG SELESAI</h1>
        </div>
    </div>

    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-7">
            <nav class="d-flex gap-4">
                <a href="{{route('home')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30" class="me-2"> Dashboard
                </a>
                <a href="{{route('rekap')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/rekap.svg')}}" alt="rekap" width="30" class="me-2"> Rekap
                </a>
            </nav>
        </div>
    </div>
</div>

<div class="container mt-4">
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h5 class="card-title text-muted mb-3">
                <i class="fa fa-filter me-2"></i> Filter Bulan & Tahun
            </h5>
            <form method="GET" action="#" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="filter_bulan" class="form-label fw-semibold">Bulan</label>
                        <select class="form-select" name="filter_bulan" id="filter_bulan">
                            <option value="" disabled>-- Pilih Bulan --</option>
                            @php
                                $bulanSekarang = date('m');
                                $daftarBulan = [
                                    '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                    '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                    '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                    '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                                ];
                            @endphp
                            @foreach ($daftarBulan as $angka => $nama)
                                <option value="{{ $angka }}" {{ $bulanSekarang == $angka ? 'selected' : '' }}>
                                    {{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_tahun" class="form-label fw-semibold">Tahun</label>
                        <select class="form-select" name="filter_tahun" id="filter_tahun">
                            <option value="" disabled>-- Pilih Tahun --</option>
                            @php
                                $tahunSekarang = date('Y');
                                $tahunAwal = 2023; // Sesuaikan dengan tahun awal aplikasi Anda berjalan
                            @endphp
                            @for ($tahun = $tahunSekarang; $tahun >= $tahunAwal; $tahun--)
                                <option value="{{ $tahun }}" {{ $tahunSekarang == $tahun ? 'selected' : '' }}>
                                    {{ $tahun }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="filter_ppn_kas" class="form-label fw-semibold">Kas</label>
                        <select class="form-select" name="filter_ppn_kas" id="filter_ppn_kas">
                            <option value="" selected>-- Semua Kas --</option>
                            <option value="1">Kas Besar PPN</option>
                            <option value="0">Kas Besar NON PPN</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="button" class="btn btn-primary w-100" id="btn-filter">
                            <i class="fa fa-search me-1"></i> Filter
                        </button>
                        <button type="button" class="btn btn-outline-secondary w-100" id="btn-reset">
                            <i class="fa fa-times me-1"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" id="rekapSelesaiTable" style="width: 100%;">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle" width="5%">No</th>
                            <th class="text-center align-middle">Tanggal</th>
                            <th class="text-center align-middle">Kas</th>
                            <th class="text-center align-middle">Pengirim</th>
                            <th class="text-center align-middle">Nominal</th>
                            <th class="text-center align-middle">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/datatable/scroller.bootstrap5.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/scroller.bootstrap5.min.js')}}"></script>
<script>
    $(document).ready(function() {

        var table = $('#rekapSelesaiTable').DataTable({
            "processing": true,
            "serverSide": true,
            "paging": true,
            "deferRender": true,
            "scrollY": "450px",
            "scrollX": true,
            "scrollCollapse": true,
            "scroller": true,
            "searching": false,
            "ordering": true,
            "order": [[ 1, "desc" ]], // Urutkan berdasarkan tanggal
            "ajax": {
                "url": "{{ route('rekap.uang-gantung.selesai.data') }}", // Sesuaikan nama route Anda
                "type": "GET",
                "data": function ( d ) {
                    // Kirim parameter filter ke controller
                    d.bulan = $('#filter_bulan').val();
                    d.tahun = $('#filter_tahun').val();
                    d.ppn_kas = $('#filter_ppn_kas').val();
                }
            },
            "columns": [
                { "data": "DT_RowIndex", "name": "DT_RowIndex", "orderable": false, "searchable": false, className: "text-center align-middle" },
                { "data": "tanggal", "name": "tanggal", className: "text-center align-middle" },
                { "data": "status_kas", "name": "ppn_kas", "searchable": false , className: "text-center align-middle"},
                { "data": "keterangan", "name": "keterangan", className: "align-middle" },
                { "data": "nf_nominal", "name": "nominal", className: "text-end align-middle" },
                { "data": "status_lunas", "name": "lunas", "searchable": false, className: "text-center align-middle text-nowrap" },
            ],
        });

        // Event Listener Tombol Filter
        $('#btn-filter').on('click', function(e) {
            e.preventDefault();
            table.draw();
        });

        // Event Listener Tombol Reset
        $('#btn-reset').on('click', function(e) {
            e.preventDefault();

            // Set ke bulan dan tahun saat ini
            let currentMonth = "{{ date('m') }}";
            let currentYear = "{{ date('Y') }}";

            $('#filter_bulan').val(currentMonth);
            $('#filter_tahun').val(currentYear);
            $('#filter_ppn_kas').val("");

            table.draw();
        });

    });
</script>
@endpush
