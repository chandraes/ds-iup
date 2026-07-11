@extends('layouts.app')

@section('content')
<div class="container">
    {{-- Header --}}
    <div class="d-flex justify-content-center align-items-center mb-4">
        <div class="row justify-content-center mb-3">
            <div class="col-md-12 text-center">
                <h1><u class="text-success">REKAP RETUR SELESAI</u></h1>
                <p class="text-muted mb-0">Arsip historis seluruh transaksi pengembalian barang yang sudah tuntas.</p>
            </div>
        </div>
    </div>

    <div class="row mb-3">
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
                <div class="col-md-3">
                    <label class="fw-bold small text-muted">Mulai Tanggal</label>
                    <input type="date" id="start_date" class="form-control form-control-sm border-secondary-subtle">
                </div>
                <div class="col-md-3">
                    <label class="fw-bold small text-muted">Sampai Tanggal</label>
                    <input type="date" id="end_date" class="form-control form-control-sm border-secondary-subtle">
                </div>
                <div class="col-md-4">
                    <label class="fw-bold small text-muted">Supplier / Unit</label>
                    <select id="unit_filter" class="form-select form-select-sm border-secondary-subtle">
                        <option value="">-- Semua Supplier --</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button id="btn-filter" class="btn btn-sm btn-success w-100 fw-bold"><i class="bi bi-filter"></i> Filter</button>
                    <button id="btn-reset" class="btn btn-sm btn-outline-secondary w-100 fw-bold"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Rekap --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive p-3">
                <table class="table table-striped table-hover align-middle w-100" id="rekap-table">
                    <thead class="table-success text-center">
                        <tr>
                            <th width="5%">No</th>
                            <th width="15%">Nomor Invoice</th>
                            <th width="15%">Tanggal Selesai</th>
                            <th width="25%">Supplier / Unit</th>
                            <th width="25%">Ringkasan Eksekusi</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DETAIL (Re-use Container Modal) --}}
<div class="modal fade" id="modalDetail" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white py-2">
                <h5 class="modal-title small fw-bold" id="modalDetailLabel"><i class="bi bi-journal-bookmark"></i> ARSIP DETAIL PENYELESAIAN RETUR</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-rollback="modal" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="modal-content-body">
                </div>
            <div class="modal-footer bg-light py-1">
                <button type="button" class="btn btn-sm btn-secondary fw-bold" data-bs-dismiss="modal">Tutup Arsip</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var table = $('#rekap-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('rekap.retur.data') }}",
            data: function (d) {
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
                d.unit_filter = $('#unit_filter').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
            { data: 'nomor_display', name: 'nomor', className: 'text-center' },
            { data: 'created_at', name: 'created_at', className: 'text-center' },
            { data: 'supplier', name: 'barang_unit.nama' },
            { data: 'ringkasan_barang', name: 'ringkasan_barang', orderable: false, searchable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center' }
        ],
        language: {
            processing: "<div class='spinner-border text-success spinner-sm'></div> Memuat data arsip..."
        }
    });

    $('#btn-filter').click(function(){ table.ajax.reload(); });
    $('#btn-reset').click(function(){
        $('#start_date').val('');
        $('#end_date').val('');
        $('#unit_filter').val('');
        table.ajax.reload();
    });

    // Panggil fungsi Detail Cantik yang barusan kita buat di modal lama!
    $('#rekap-table tbody').on('click', '.btn-detail', function() {
        var id = $(this).data('id');
        $('#modalDetail').modal('show');
        $('#modal-content-body').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-success"></div>
                <p class="mt-2 text-muted">Membuka lembar dokumen arsip...</p>
            </div>
        `);

        // Hebatnya: Kita panggil route detail penyelesaian-retur lama yang barusan kita bersihkan tabelnya!
        var url = "{{ route('billing.penyelesaian-retur.detail', ':id') }}".replace(':id', id);

        $.get(url, function(data) {
            $('#modal-content-body').html(data);
        }).fail(function() {
            $('#modal-content-body').html(`
                <div class="alert alert-danger m-4 text-center">
                    <i class="bi bi-exclamation-triangle"></i> Gagal memuat lembar arsip.
                </div>
            `);
        });
    });
});
</script>
@endsection
