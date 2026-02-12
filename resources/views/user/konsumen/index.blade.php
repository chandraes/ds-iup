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
                    {{-- <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalSalesArea"><img
                                src="{{asset('images/area.svg')}}" width="30"> Sales Area</a>
                    </td> --}}
                </tr>
            </table>
        </div>
    </div>
</div>
@include('swal')
@include('user.konsumen.dokumen')

<div class="container-fluid mt-5 table-responsive">
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filterKodeToko" class="form-label">Kode Toko</label>
            <select id="filterKodeToko" name="kode_toko" class="form-select">
                <option value="" selected>-- Semua Kode Toko --</option>
                @foreach ($kode_toko as $k)
                <option value="{{ $k->id }}">
                    {{ $k->kode }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterSalesArea" class="form-label">Sales Area</label>

            <select id="filterSalesArea" name="area" class="form-select">
                <option value="" selected>-- Semua Sales Area --</option>
                @foreach ($sales_area as $salesArea)
                <option value="{{ $salesArea->id }}">
                    {{ $salesArea->nama }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterKecamatan" class="form-label">Kecamatan</label>
            <select id="filterKecamatan" name="kecamatan" class="form-select">
                <option value="">-- Semua Kecamatan --</option>
                @foreach ($kecamatan_filter as $kec)
                <option value="{{ $kec->id }}">
                    {{ $kec->nama_wilayah }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="filterKecamatan" class="form-label">Status</label>
            <select id="filterStatus" name="status" class="form-select">
                <option value="1">Aktif</option>
                <option value="0">Non
                    Aktif</option>
            </select>
        </div>
        <div class="col-md-1 mt-4">
            <div class="row">
                <button type="button" id="resetFilter" class="btn btn-secondary mt-2">Reset</button>
            </div>

        </div>
    </div>
    <table id="data" class="table table-bordered table-hover" style="font-size: 0.8rem;">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle">KODE</th>
                <th class="text-center align-middle">KODE TOKO</th>
                <th class="text-center align-middle">NAMA</th>
                <th class="text-center align-middle">DOKUMEN</th>
                <th class="text-center align-middle">CP</th>
                <th class="text-center align-middle">NPWP</th>
                <th class="text-center align-middle">NIK</th>
                <th class="text-center align-middle">KTP</th>

                <th class="text-center align-middle">Sales Area</th>
                <th class="text-center align-middle">Provinsi</th>
                <th class="text-center align-middle">Kab/Kota</th>
                <th class="text-center align-middle">Kecamatan</th>
                <th class="text-center align-middle">Alamat</th>
                <th class="text-center align-middle">Sistem<br>Pembayaran</th>
                <th class="text-center align-middle">Limit<br>Plafon</th>
                <th class="text-center align-middle">Diskon Khusus (%)</th>
            </tr>
        </thead>
    </table>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
    <link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.1.1/css/scroller.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
    <script src="{{asset('assets/js/dt5.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script src="https://cdn.datatables.net/scroller/2.1.1/js/dataTables.scroller.min.js"></script>
<script>
    $(document).ready(function() {


    let table = $('#data').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("user.konsumen.data") }}',
            data: function (d) {
                d.kode_toko = $('#filterKodeToko').val();
                d.area = $('#filterSalesArea').val();
                d.kecamatan = $('#filterKecamatan').val();
                d.status = $('#filterStatus').val();
            }
        },
        scrollY: '60vh', // tinggi area scroll, bisa disesuaikan
        scrollX: true,
        stateSave: true,
        scroller: {
            loadingIndicator: true
        },
        deferRender: true,
        columns: [
            { data: 'full_kode', name: 'kode', className: 'text-center' },
            { data: 'kode_toko', name: 'kode_toko.kode', className: 'text-center' },
            { data: 'nama', name: 'nama', className: 'text-wrap' },
            { data: 'dokumen', name: 'dokumen', searchable: false, orderable: false },
            { data: 'cp', name: 'cp', searchable: false, },
            { data: 'npwp', name: 'npwp' },
            { data: 'nik', name: 'nik' },
            { data: 'ktp', name: 'ktp', searchable: false, orderable: false },
            { data: 'karyawan.nama', name: 'karyawan.nama', className: 'text-wrap', searchable: false },
            { data: 'provinsi.nama_wilayah', name: 'provinsi.nama_wilayah', className: 'text-wrap' },
            { data: 'kabupaten_kota.nama_wilayah', name: 'kabupaten_kota.nama_wilayah', className: 'text-wrap' },
            { data: 'kecamatan.nama_wilayah', name: 'kecamatan.nama_wilayah', className: 'text-wrap' },
            { data: 'alamat', name: 'alamat', className: 'text-wrap', searchable: false },
            { data: 'pembayaran_raw', name: 'pembayaran_raw', className: 'text-wrap', searchable: false },
            { data: 'limit_plafon', name: 'plafon', className: 'text-end', searchable: false },
            { data: 'diskon', name: 'diskon_khusus', className: 'text-end', searchable: false },
        ]
    });

    const filters = $('#filterKodeToko, #filterSalesArea, #filterKecamatan, #filterStatus');

     filters.select2({
        theme: 'bootstrap-5',
        width: '100%'
    }).on('change', function () {
        table.draw();
    });

   $('#resetFilter').on('click', function () {
        $('#filterKodeToko, #filterSalesArea, #filterKecamatan, #filterStatus')
            .val('')
            .trigger('change');

        $('#filterStatus').val('1').trigger('change'); // jika ingin set default ke "Aktif"
    });
});

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


        $('#filterSalesArea').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });



        $('#filterKecamatan').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });


        $('#modalSalesArea').on('shown.bs.modal', function () {
            table.columns.adjust().draw();
        });

        $('#modalSalesArea').on('hidden.bs.modal', function () {
            if ($.fn.DataTable.isDataTable('#salesAreaTable')) {
                table.destroy();
                $('#salesAreaTable tbody').empty(); // bersihkan isi table
            }

            // 🔄 Refresh halaman
            location.reload();
        });


</script>

@endpush
