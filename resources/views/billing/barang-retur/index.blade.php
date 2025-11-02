@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BARANG RETUR</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>

                </tr>
            </table>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>


</div>
<div class="container table-responsive ml-3">

    <form method="GET" action="#" id="filterForm">
        <div class="row g-3 p-3 mb-3" style="border: 1px dashed #ccc; border-radius: 10px;">
            <div class="col-md-3">
                <label for="filter_konsumen" class="form-label">Konsumen</label>
                <select class="form-select" name="konsumen_id" id="filter_konsumen">
                    <option value="" selected>-- Semua Konsumen --</option>
                    @foreach ($konsumens as $k)
                    <option value="{{$k->id}}">{{$k->kode_toko?->kode}} {{$k->nama}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
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
                    <option value="2">Diproses</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                {{-- Tombol ini akan me-reload DataTables, bukan submit form --}}
                <button type="button" class="btn btn-primary" id="btn-filter">Filter</button>
            </div>
        </div>
    </form>
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable" style="width: 100%;">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Kode</th>
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

        // 6. INISIALISASI DATATABLES
        var table = $('#rekapTable').DataTable({
            "processing": true,     // Tampilkan indikator loading
            "serverSide": true,     // Aktifkan Server-Side Processing
            "paging":     true,     // Paging harus true untuk scroller
            "deferRender": true,    // Render baris saat dibutuhkan
            "scrollY":    "450px",   // Tinggi scroll
            scrollX: true,
            "scrollCollapse": true,
             scroller: true,
            "searching": false,     // Kita pakai filter custom, matikan search bawaan
            "ordering":  true,      // Izinkan sorting
            "order": [[ 0, "desc" ]], // Default order by tanggal desc

            // 7. SUMBER DATA AJAX
            "ajax": {
                "url": "{{ route('billing.barang-retur.data') }}",
                "type": "GET",
                "data": function ( d ) {
                    // Kirim data filter custom ke server
                    d.konsumen_id = $('#filter_konsumen').val();
                    d.barang_unit_id = $('#filter_supplier').val();
                    d.tipe = $('#filter_tipe').val();
                    d.status = $('#filter_status').val();
                }
            },

            // 8. DEFINISI KOLOM
            "columns": [
                { "data": "tanggal_en", "name": "created_at", className: "text-center align-middle" },
                { "data": "kode", "name": "kode", "orderable": false, "searchable": false , className: "text-center align-middle"},
                { "data": "supplier", "name": "barang_unit.nama" }, 
                { "data": "konsumen_nama", "name": "konsumen.nama" },
                { "data": "status_badge", "name": "status", "orderable": true, "searchable": false , className: "text-center align-middle"},
                { "data": "action", "name": "action", "orderable": false, "searchable": false , className: "text-center align-middle text-nowrap" },
            ],

            // Re-init tooltip setelah tabel di-draw
            "drawCallback": function( settings ) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                  return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });

        // 9. EVENT LISTENER UNTUK TOMBOL FILTER
        $('#btn-filter').on('click', function(e) {
            e.preventDefault();
            table.draw(); // Muat ulang data tabel dengan filter baru
        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
          return new bootstrap.Tooltip(tooltipTriggerEl)
        })

    });



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

    function lanjutkanOrder(id) {
        Swal.fire({
            title: 'Anda Yakin?',
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
                                // 1. BUKA PREVIEW DI TAB BARU
                                window.open(data.preview_url, '_blank');
                                // 2. REFRESH DATATABLES DI BELAKANG
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
            confirmButtonColor: '#28a745', // Warna hijau
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
