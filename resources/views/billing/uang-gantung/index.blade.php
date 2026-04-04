@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1 class="display-6 fw-bold">UANG GANTUNG</h1>
        </div>
    </div>

    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-7">
            <nav class="d-flex gap-4">
                <a href="{{route('home')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30" class="me-2"> Dashboard
                </a>
                <a href="{{route('billing')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30" class="me-2">
                    Billing
                </a>
            </nav>
        </div>
    </div>
</div>

<div class="container mt-4">

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h5 class="card-title text-muted mb-3">
                <i class="fa fa-filter me-2"></i> Filter Data
            </h5>
            <form method="GET" action="#" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <select class="form-select" name="filter_ppn_kas" id="filter_ppn_kas">
                            <option value="" disabled selected>-- Semua Kas --</option>
                            <option value="1" {{old('ppn_kas')==1 ? 'selected' : '' }}>Kas Besar PPN</option>
                            <option value="0" {{old('ppn_kas')==1 ? 'selected' : '' }}>Kas Besar NON PPN
                            </option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end gap-2">
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
                <table class="table table-hover table-bordered" id="rekapTable" style="width: 100%;">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle" width="5%">No</th>
                            <th class="text-center align-middle">Tanggal Input</th>
                            <th class="text-center align-middle">Kas</th>
                            <th class="text-center align-middle">Pengirim</th>
                            <th class="text-center align-middle">Nominal</th>
                            <th class="text-center align-middle">Tanggal Transaksi</th>
                            <th class="text-center align-middle">ACT</th>
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
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/plugins/datatable/scroller.bootstrap5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>
<script>
    $(document).ready(function() {


        var table = $('#rekapTable').DataTable({
            "processing": true,
            "serverSide": true,
            "paging":     true,
            "deferRender": true,
            "scrollY":    "450px",
            scrollX: true,
            "scrollCollapse": true,
             scroller: true,
            "searching": false,
            "ordering":  true,
            "order": [[ 0, "desc" ]],
            "ajax": {
                "url": "{{ route('billing.uang-gantung.data') }}",
                "type": "GET",
                "data": function ( d ) {
                    d.ppn_kas = $('#filter_ppn_kas').val();
                }
            },
            "columns": [
                { "data": "DT_RowIndex", "name": "DT_RowIndex", "orderable": false, "searchable": false, className: "text-center align-middle" },
                { "data": "tanggal_input", "name": "created_at", className: "text-center align-middle" },
                { "data": "status_kas", "name": "ppn_kas", "searchable": false , className: "text-center align-middle"},
                 { "data": "keterangan", "name": "keterangan", className: "align-middle" },
                { "data": "nf_nominal", "name": "nominal", className: "text-end align-middle" },
                { "data": "tanggal", "name": "tanggal", className: "text-center align-middle" },
                { "data": "aksi", "name": "aksi", "orderable": false, "searchable": false , className: "text-center align-middle text-nowrap" },
            ],
        });

        // 9. EVENT LISTENER UNTUK TOMBOL FILTER (Tetap)
        $('#btn-filter').on('click', function(e) {
            e.preventDefault();
            table.draw(); // Muat ulang data tabel dengan filter baru
        });

        // --- PERUBAHAN BARU: Event Listener untuk Tombol Reset ---
        $('#btn-reset').on('click', function(e) {
            e.preventDefault();

            // 1. Reset nilai <select> biasa
            $('#filter_ppn_kas').val("");

            // 3. Muat ulang (gambar ulang) DataTables
            table.draw();
        });

    });

    // Konfigurasi Header AJAX untuk CSRF Token
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // EVENT LISTENER: Tombol Selesaikan
    $('#rekapTable').on('click', '.btn-selesaikan', function() {
        let id = $(this).data('id');
        let url = "{{ route('billing.uang-gantung.lunas', ':id') }}";
        url = url.replace(':id', id);

        Swal.fire({
            title: 'Selesaikan Data?',
            text: "Status data ini akan diubah menjadi lunas.",
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#198754', // Hijau
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, selesaikan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: 'POST', // Gunakan POST untuk update lunas
                    success: function(response) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#rekapTable').DataTable().ajax.reload(null, false); // Reload tabel tanpa reset paging
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan pada server.', 'error');
                    }
                });
            }
        });
    });

    // EVENT LISTENER: Tombol Void
    $('#rekapTable').on('click', '.btn-void', function() {
        let id = $(this).data('id');
        let url = "{{ route('billing.uang-gantung.void', ':id') }}";
        url = url.replace(':id', id);

        Swal.fire({
            title: 'Void Data?',
            text: "Silakan masukkan alasan mengapa data ini di-void:",
            icon: 'warning',
            input: 'textarea', // Menampilkan form input area
            inputPlaceholder: 'Ketik alasan void di sini...',
            inputAttributes: {
                'aria-label': 'Ketik alasan void di sini'
            },
            showCancelButton: true,
            confirmButtonColor: '#dc3545', // Merah
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Void!',
            cancelButtonText: 'Batal',
            // Validasi agar input tidak boleh kosong
            preConfirm: (alasan) => {
                if (!alasan || alasan.trim() === '') {
                    Swal.showValidationMessage('Alasan void wajib diisi!');
                    return false;
                }
                return alasan;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Ambil text alasan dari inputan SweetAlert
                let alasan_void = result.value;

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        alasan: alasan_void // Kirim data alasan ke Controller
                    },
                    success: function(response) {
                        Swal.fire('Berhasil!', response.message, 'success');
                        $('#rekapTable').DataTable().ajax.reload(null, false); // Reload tabel
                    },
                    error: function(xhr) {
                        let errorMsg = 'Terjadi kesalahan pada server.';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', errorMsg, 'error');
                    }
                });
            }
        });
    });


</script>
@endpush
