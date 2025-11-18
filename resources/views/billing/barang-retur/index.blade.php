@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1 class="display-6 fw-bold">TERIMA / KIRIM RETUR</h1>
            <p class="lead text-muted">Manajemen data barang retur</p>
        </div>
    </div>

    <div class="row justify-content-between align-items-center mt-3">
        <div class="col-md-7">
            <nav class="d-flex gap-4">
                <a href="{{route('home')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30" class="me-2"> Dashboard
                </a>
                <a href="{{route('billing')}}" class="text-decoration-none d-flex align-items-center fs-5 text-dark">
                    <img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30" class="me-2">
                            Billing
                </a>
            </nav>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>
</div>

<div class="container-fluid mt-4">

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <h5 class="card-title text-muted mb-3">
                <i class="fa fa-filter me-2"></i> Filter Data
            </h5>
            <form method="GET" action="#" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label for="filter_sales" class="form-label">Sales</label>
                        <select class="form-select" name="sales" id="filter_sales">
                            <option value="" selected>-- Semua Sales --</option>
                            @foreach ($sales as $sale)
                            <option value="{{$sale->id}}">{{$sale->nama}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_konsumen" class="form-label">Konsumen</label>
                        <select class="form-select" name="konsumen_id" id="filter_konsumen">
                            <option value="" selected>-- Semua Konsumen --</option>
                            @foreach ($konsumens as $k)
                            <option value="{{$k->id}}">{{$k->kode_toko?->kode}} {{$k->nama}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_supplier" class="form-label">Supplier</label>
                        <select class="form-select" name="barang_unit_id" id="filter_supplier">
                            <option value="" selected>-- Semua Supplier --</option>
                            @foreach ($barang_units as $bu)
                            <option value="{{$bu->id}}">{{$bu->nama}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_tipe" class="form-label">Tipe</label>
                        <select class="form-select" name="tipe" id="filter_tipe">
                            <option value="" selected>-- Semua Tipe --</option>
                            <option value="1">Retur ke Supplier</option>
                            <option value="2">Retur dari Konsumen</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_status" class="form-label">Status</label>
                        <select class="form-select" name="status" id="filter_status">
                            <option value="" selected>-- (Semua data) --</option>
                            <option value="1">Diajukan</option>
                            <option value="2">Diterima</option>
                            <option value="3">Diproses</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end gap-2">
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
                            <th class="text-center align-middle">Tanggal</th>
                            <th class="text-center align-middle">Kode</th>
                            <th class="text-center align-middle">Sales</th>
                            <th class="text-center align-middle">Supplier</th>
                            <th class="text-center align-middle">Konsumen</th>
                            <th class="text-center align-middle">Status</th>
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
        $('#filter_konsumen').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });
        $('#filter_supplier').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });
        $('#filter_sales').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

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
                "url": "{{ route('billing.barang-retur.data') }}",
                "type": "GET",
                "data": function ( d ) {
                    d.konsumen_id = $('#filter_konsumen').val();
                    d.barang_unit_id = $('#filter_supplier').val();
                    d.tipe = $('#filter_tipe').val();
                    d.status = $('#filter_status').val();
                    d.sales = $('#filter_sales').val();
                }
            },
            "columns": [
                { "data": "tanggal_en", "name": "created_at", className: "text-center align-middle" },
                { "data": "kode", "name": "kode", "orderable": false, "searchable": false , className: "text-center align-middle"},
                { "data": "sales", "name": "karyawan.nama" },
                { "data": "supplier", "name": "barang_unit.nama" },
                { "data": "konsumen_nama", "name": "konsumen.nama" },
                { "data": "status_badge", "name": "barang_returs.status", "orderable": true, "searchable": false , className: "text-center align-middle"},
                { "data": "action", "name": "action", "orderable": false, "searchable": false , className: "text-center align-middle text-nowrap" },
            ],
            "drawCallback": function( settings ) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                  return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
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
            $('#filter_tipe').val("");
            $('#filter_status').val("");

            // 2. Reset nilai <select> yang menggunakan Select2
            //    Kita perlu .val("").trigger("change") agar Select2 memperbarui tampilannya
            $('#filter_konsumen').val("").trigger('change');
            $('#filter_supplier').val("").trigger('change');
            $('#filter_sales').val("").trigger('change');

            // 3. Muat ulang (gambar ulang) DataTables
            table.draw();
        });
        // --- AKHIR PERUBAHAN BARU ---

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

    });

    // --- Semua fungsi SweetAlert Anda (voidOrder, lanjutkanOrder, selesaikanOrder) ---
    // --- tidak diubah dan akan tetap berfungsi seperti semula ---

    function voidOrder(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Saya  Yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('billing.barang-retur.void', ':id')}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message,
                            });
                        }
                    }
                });
            }
        })
    }

    function terimaOrder(id) {
        Swal.fire({
            title: 'Terima Retur Ini?',
            text: "Status akan diubah menjadi 'Diterima' dan PDF Bukti Terima akan dibuat.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107', // Warna kuning (Warning)
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Terima!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    // Panggil route BARU (Anda harus membuatnya di web.php)
                    url: '{{ route('billing.barang-retur.terima', ':id') }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                            }).then(() => {
                                // Buka PDF BARU dan muat ulang tabel
                                window.open(data.preview_url, '_blank');
                                $('#rekapTable').DataTable().draw(false);
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                    }
                });
            }
        });
    }

    function lanjutkanOrder(id) {
        Swal.fire({
            title: 'Proses Retur Ini?', // <-- Ubah teks
            text: "Retur akan diproses dan status diubah menjadi 'Diproses'.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Saya Yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('billing.barang-retur.kirim', ':id')}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                            }).then(() => {
                                window.open(data.preview_url, '_blank');
                                $('#rekapTable').DataTable().draw(false);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message,
                            });
                        }
                    }
                });
            }
        })
    }

    function selesaikanOrder(id) {
        Swal.fire({
           title: 'Selesaikan Retur Ini?',
            text: "Stok akan disesuaikan dan status diubah menjadi 'Selesai'. Aksi ini tidak dapat dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Selesaikan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('billing.barang-retur.selesaikan', ':id') }}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                            }).then(() => {
                                $('#rekapTable').DataTable().draw(false);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message,
                            });
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Gagal menghubungi server.', 'error');
                    }
                });
            }
        });
    }

</script>
@endpush
