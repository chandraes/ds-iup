@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>REKAP BARANG RETUR</u></h1>
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

    {{-- 1. MODIFIKASI FORM FILTER --}}
    <form method="GET" action="#" id="filterForm">
        <div class="row g-3 p-3 mb-3" style="border: 1px dashed #ccc; border-radius: 10px;">

            {{-- Filter Bulan & Tahun (BARU) --}}
            <div class="col-md-2">
                <label for="filter_bulan" class="form-label">Bulan</label>
                <select class="form-select" name="bulan" id="filter_bulan">
                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ $i }}" {{ $i == $currentMonth ? 'selected' : '' }}>
                            {{ Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter_tahun" class="form-label">Tahun</label>
                <select class="form-select" name="tahun" id="filter_tahun">
                    @for ($i = $currentYear; $i >= $currentYear - 5; $i--)
                        <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Filter yang sudah ada --}}
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
                    {{-- Defaultnya "Semua Status" di rekap --}}
                    <option value="" selected>-- Semua Status --</option>
                    <option value="1">Diajukan</option>
                    <option value="2">Diproses</option>
                    <option value="3">Selesai</option>
                    <option value="4">Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
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
                    <th class="text-center align-middle">View</th> {{-- Ganti ACT menjadi View --}}
                </tr>
            </thead>
            <tbody>
                {{-- Kosongkan, diisi oleh DataTables --}}
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

        // 2. INISIALISASI SELECT2 (Termasuk filter baru)
        $('#filter_konsumen, #filter_supplier').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });
        $('#filter_bulan, #filter_tahun').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        // 3. INISIALISASI DATATABLES
        var table = $('#rekapTable').DataTable({
            "processing": true,
            "serverSide": true,
            "paging":     true,
            "deferRender": true,
            "scrollY":    "60vh",
            "scrollCollapse": true,
            "scroller": {
                "loadingIndicator": true
            },
            "searching": false,
            "ordering":  true,
            "order": [[ 0, "desc" ]],

            // 4. UBAH SUMBER DATA AJAX
            "ajax": {
                "url": "{{ route('rekap.barang-retur.data') }}", // <-- Ganti URL
                "type": "GET",
                "data": function ( d ) {
                    // Kirim semua filter
                    d.bulan = $('#filter_bulan').val();
                    d.tahun = $('#filter_tahun').val();
                    d.konsumen_id = $('#filter_konsumen').val();
                    d.barang_unit_id = $('#filter_supplier').val();
                    d.tipe = $('#filter_tipe').val();
                    d.status = $('#filter_status').val();
                }
            },

            // 5. DEFINISI KOLOM (Sama, karena 'action' di-handle controller)
            "columns": [
                { "data": "tanggal_en", "name": "created_at" }, // Ingat, name adalah 'created_at'
                { "data": "kode", "name": "kode", "orderable": false, "searchable": false },
                { "data": "supplier", "name": "barang_unit.nama" },
                { "data": "konsumen_nama", "name": "konsumen.nama" },
                { "data": "status_badge", "name": "status" },
                { "data": "action", "name": "action", "orderable": false, "searchable": false }
            ],

            "drawCallback": function( settings ) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                  return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });

        // 6. EVENT LISTENER FILTER (Tetap sama)
        $('#btn-filter').on('click', function(e) {
            e.preventDefault();
            table.draw();
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


</script>
@endpush
