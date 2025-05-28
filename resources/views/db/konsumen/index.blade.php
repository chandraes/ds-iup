@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BIODATA KONSUMEN</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    {{-- <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalSalesArea"><img
                                src="{{asset('images/area.svg')}}" width="30"> Sales Area</a>
                    </td> --}}
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalKodeToko"><img
                        src="{{asset('images/kode-toko.svg')}}" width="30"> Kode Toko</a>
            </td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#createInvestor"><img
                                src="{{asset('images/customer.svg')}}" width="30"> Tambah Konsumen</a>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('swal')
@include('db.konsumen.create')
@include('db.konsumen.edit')
@include('db.konsumen.kode-toko')

<div class="container-fluid mt-5 table-responsive">
    @include('db.konsumen.filter')
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle">KODE</th>
                <th class="text-center align-middle">KODE <br>TOKO</th>
                <th class="text-center align-middle">NAMA</th>
                <th class="text-center align-middle">CP</th>
                <th class="text-center align-middle">NPWP</th>
                <th class="text-center align-middle">Sales Area</th>
                <th class="text-center align-middle">Provinsi</th>
                <th class="text-center align-middle">Kab/Kota</th>
                <th class="text-center align-middle">Kecamatan</th>
                <th class="text-center align-middle">Alamat</th>
                @if (request()->has('status') && request('status') == 0)
                <th class="text-center align-middle">Alasan<br>Nonaktif</th>
                @else
                <th class="text-center align-middle">Sistem<br>Pembayaran</th>
                <th class="text-center align-middle">Limit<br>Plafon</th>
                @endif

                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>

        </tbody>
    </table>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/js/datatables.min.css')}}" rel="stylesheet">
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"> --}}
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.bootstrap5.min.css"> --}}
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/datatables.min.js')}}"></script>
<script>







        var table = $('#data').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('db.konsumen.data') }}",
                data: function(d) {
                    // Tambahkan filter ke parameter AJAX
                    d.area = $('#filterSalesArea').val();
                    d.kecamatan = $('#filterKecamatan').val();
                    d.provinsi = $('#filterProvinsi').val();
                    d.kabupaten_kota = $('#filterKota').val();
                    d.kode_toko = $('#filterKodeToko').val();
                    d.status = $('#filterStatus').val();
                }
            },
            scrollY: "450px",
            scrollCollapse: true,
            scroller: true, // Aktifkan infinite scroll
            columns: [
                { data: 'full_kode', className: 'text-center align-middle text-wrap' },
                { data: 'kode_toko', className: 'text-center align-middle text-wrap' },
                { data: 'nama', className: 'text-center align-middle text-wrap' },
                { data: 'cp', className: 'align-middle' },
                { data: 'npwp', className: 'text-center align-middle text-wrap' },
                { data: 'sales_area', className: 'text-center align-middle text-wrap' },
                { data: 'provinsi', className: 'text-center align-middle text-wrap' },
                { data: 'kab_kota', className: 'text-center align-middle text-wrap' },
                { data: 'kecamatan', className: 'text-center align-middle text-wrap' },
                { data: 'alamat', className: 'align-middle text-wrap' },
                { data: 'pembayaran', className: 'text-center align-middle text-wrap'},
                { data: 'limit_plafon', className: 'text-end align-middle text-wrap' },
                { data: 'act', orderable: false, searchable: false, className: 'text-center align-middle text-wrap',

                 }
            ],
            columnDefs: [
                { targets: [3, 12], orderable: false, searchable: false } // agar kolom HTML bisa dirender
            ]
        });

        function filterData() {
            var area = $('#filterSalesArea').val();
            var kecamatan = $('#filterKecamatan').val();
            var provinsi = $('#filterProvinsi').val();
            var kabupaten_kota = $('#filterKota').val();
            var kode_toko = $('#filterKodeToko').val();
            var status = $('#filterStatus').val();
            table.ajax.reload();
        }
</script>
<script>
$('#provinsi_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#createInvestor'),
        });
        $('#kabupaten_kota_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#createInvestor'),
        });

        $('#kecamatan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#createInvestor'),
        });

        $('#edit_karyawan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editInvestor'),
        });

        $('#edit_kode_toko_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editInvestor'),
        });





</script>
@endpush
