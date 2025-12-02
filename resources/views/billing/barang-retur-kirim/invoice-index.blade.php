@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header --}}
    <div class="d-flex justify-content-center align-items-center mb-4">
        <div class="row justify-content-center mb-3">
            <div class="col-md-12 text-center">
                <h1><u>PENYELESAIAN RETUR</u></h1>
                <p class="text-muted mb-0">Daftar transaksi pengembalian barang (Invoice Retur).</p>
            </div>
        </div>

    </div>
    <div class="row">
         <div class="col-md-6">
            <nav class="d-flex gap-3">
                <a href="{{route('home')}}" class="btn btn-outline-dark border-0">
                    <img src="{{asset('images/dashboard.svg')}}" width="25" class="me-1"> Dashboard
                </a>
                <a href="{{route('billing')}}" class="btn btn-outline-dark border-0">
                    <img src="{{asset('images/billing.svg')}}" width="25" class="me-1"> Billing
                </a>
            </nav>
        </div>
    </div>
    {{-- Filter Section --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body bg-light rounded">
            <div class="row g-3 align-items-end">
                {{-- <div class="col-md-3">
                    <label class="fw-bold small text-muted">Mulai Tanggal</label>
                    <input type="date" id="start_date" class="form-control" value="{{ date('Y-m-01') }}">
                </div>
                <div class="col-md-3">
                    <label class="fw-bold small text-muted">Sampai Tanggal</label>
                    <input type="date" id="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div> --}}
                <div class="col-md-3">
                    <label class="fw-bold small text-muted">Filter Supplier</label>
                    <select id="unit_filter" class="form-select select2">
                        <option value="">Semua Supplier</option>
                        @foreach($units as $u)
                        <option value="{{ $u->id }}">{{ $u->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button id="btn-filter" class="btn btn-primary w-100 shadow-sm">
                        <i class="bi bi-search"></i> Cari
                    </button>
                    <button id="btn-reset" class="btn btn-outline-secondary w-25" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <table class="table table-hover table-striped align-middle w-100" id="invoice-table">
                <thead class="table-success">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="15%">No. Invoice</th>
                        <th width="15%">Tanggal</th>
                        <th>Supplier</th>
                        <th width="15%" class="text-center">Status</th>
                        <th width="10%" class="text-center">Total</th>
                        <th width="10%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL DETAIL --}}
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-white border-bottom-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-receipt"></i> Detail Invoice Retur</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="modal-content-body">
                {{-- Content AJAX akan dimuat di sini --}}
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="alert('Fitur cetak dalam pengembangan')">
                    <i class="bi bi-printer"></i> Cetak
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
@endpush

@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>

<script>
    $(document).ready(function() {
    // Init Select2
    $('.select2').select2({ theme: 'bootstrap-5' });

    // Init DataTable
    var table = $('#invoice-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('billing.penyelesaian-retur.data') }}",
            data: function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.unit_filter = $('#unit_filter').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'nomor_display', name: 'nomor'},
            {data: 'created_at', name: 'created_at'},
            {data: 'supplier', name: 'barang_unit.nama'}, // Relasi barang_unit
            {data: 'status_label', name: 'tipe', className: 'text-center'}, // Status Badge
            {data: 'total_item', name: 'details_count', className: 'text-center'},
            {data: 'aksi', orderable: false, searchable: false, className: 'text-center'}
        ],
        order: [[2, 'desc']], // Urutkan berdasarkan created_at (kolom index 2)
        dom: 'rtip'
    });

    // Event Listeners
    $('#btn-filter').click(function(){ table.draw(); });

    $('#btn-reset').click(function(){
        $('#unit_filter').val('').trigger('change');
        // Optional: Reset tanggal ke default jika diinginkan
        table.draw();
    });

    // Modal Logic
    $('#invoice-table tbody').on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        $('#modalDetail').modal('show');

        // Loading State
        $('#modal-content-body').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2 text-muted">Memuat data transaksi...</p>
            </div>
        `);

        var url = "{{ route('billing.penyelesaian-retur.detail', ':id') }}".replace(':id', id);

        $.get(url, function(data) {
            $('#modal-content-body').html(data);
        }).fail(function() {
            $('#modal-content-body').html(`
                <div class="alert alert-danger m-4 text-center">
                    <i class="bi bi-exclamation-triangle"></i> Gagal memuat detail transaksi.
                </div>
            `);
        });
    });
});
</script>
@endpush
