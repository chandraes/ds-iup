@extends('layouts.app')
@section('content')
<div class="container">
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
                </tr>
            </table>
        </div>
    </div>
</div>
@include('swal')
<div class="container-fluid mt-5 table-responsive">

    {{-- buat filter untuk sales area --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="card-title mb-0 fw-bold text-dark">
                <i class="fa fa-filter me-1 text-primary"></i> Filter Data
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url()->current() }}">
                <div class="row g-3">
                    {{-- Baris 1: Lokasi Utama & Status --}}
                    <div class="col-md-4 col-lg-3">
                        <label for="filterKodeToko" class="form-label small fw-bold text-secondary">Kode Toko</label>
                        <select id="filterKodeToko" name="kode_toko" class="form-select select2-bs5" onchange="this.form.submit()">
                            <option value="" selected>-- Semua Kode Toko --</option>
                            @foreach ($kode_toko as $k)
                            <option value="{{ $k->id }}" {{ request('kode_toko')==$k->id ? 'selected' : '' }}>
                                {{ $k->kode }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <label for="filterSalesArea" class="form-label small fw-bold text-secondary">Sales Area</label>
                        <select id="filterSalesArea" name="area" class="form-select select2-bs5" onchange="this.form.submit()">
                            <option value="" selected>-- Semua Sales Area --</option>
                            @foreach ($sales_area as $salesArea)
                            <option value="{{ $salesArea->id }}" {{ request('area')==$salesArea->id ? 'selected' : '' }}>
                                {{ $salesArea->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <label for="filterStatus" class="form-label small fw-bold text-secondary">Status</label>
                        <select id="filterStatus" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Non Aktif</option>
                        </select>
                    </div>

                    {{-- Baris 2: Wilayah Administratif --}}
                    <div class="col-md-4 col-lg-3">
                        <label for="filterProvinsi" class="form-label small fw-bold text-secondary">Provinsi</label>
                        <select id="filterProvinsi" name="provinsi" class="form-select select2-bs5" onchange="this.form.submit()">
                            <option value="">-- Semua Provinsi --</option>
                            @foreach ($provinsi as $prov)
                            <option value="{{ $prov->id }}" {{ request('provinsi')==$prov->id ? 'selected' : '' }}>
                                {{ $prov->nama_wilayah }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <label for="filterKota" class="form-label small fw-bold text-secondary">Kab/Kota</label>
                        <select id="filterKota" name="kabupaten_kota" class="form-select select2-bs5" onchange="this.form.submit()">
                            <option value="">-- Semua Kab/Kota --</option>
                            @foreach ($kabupaten_kota as $kab)
                            <option value="{{ $kab->id }}" {{ request('kabupaten_kota')==$kab->id ? 'selected' : '' }}>
                                {{ $kab->nama_wilayah }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 col-lg-3">
                        <label for="filterKecamatan" class="form-label small fw-bold text-secondary">Kecamatan</label>
                        <select id="filterKecamatan" name="kecamatan" class="form-select select2-bs5" onchange="this.form.submit()">
                            <option value="">-- Semua Kecamatan --</option>
                            @foreach ($kecamatan_filter as $kec)
                            <option value="{{ $kec->id }}" {{ request('kecamatan')==$kec->id ? 'selected' : '' }}>
                                {{ $kec->nama_wilayah }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tombol Reset --}}
                   <div class="col-md-12 col-lg-3 d-flex align-items-end">
                        <div class="btn-group w-100" role="group">
                            <button type="button" onclick="exportExcel()" class="btn btn-success text-white">
                                <i class="fa fa-file me-1"></i> Export Excel
                            </button>
                            <a href="{{ url()->current() }}" class="btn btn-secondary">
                                <i class="fa fa-refresh me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="container-fluid table-responsive">
        <table class="table table-bordered table-hover" id="data">
            <thead class="table-success bg-gradient">
                <tr>
                    <th class="text-center align-middle">KODE</th>
                    <th class="text-center align-middle">KODE <br>TOKO</th>
                    <th class="text-center align-middle">NAMA</th>
                    <th class="text-center align-middle">Sales Area</th>
                    <th class="text-center align-middle">Provinsi</th>
                    <th class="text-center align-middle">Kab/Kota</th>
                    <th class="text-center align-middle">Kecamatan</th>
                    <th class="text-center align-middle">Alamat</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.4.3/css/scroller.bootstrap5.min.css">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/scroller.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.4.3/js/dataTables.scroller.min.js"></script>
<script>
    $('#filterSalesArea').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filterKodeToko').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filterKecamatan').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filterKota').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filterProvinsi').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

document.addEventListener('DOMContentLoaded', function() {
    function getFilters() {
        return {
            area: document.getElementById('filterSalesArea').value,
            kecamatan: document.getElementById('filterKecamatan').value,
            kode_toko: document.getElementById('filterKodeToko').value,
            kabupaten_kota: document.getElementById('filterKota').value,
            provinsi: document.getElementById('filterProvinsi').value,
            status: document.getElementById('filterStatus').value,
        };
    }

    // Inisialisasi DataTable
    var table = $('#data').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('perusahaan.konsumen.data') }}",
            data: function(d) {
                // Tambahkan filter ke parameter AJAX
                var filters = getFilters();
                d.area = filters.area;
                d.kecamatan = filters.kecamatan;
                d.provinsi = filters.provinsi;
                d.kabupaten_kota = filters.kabupaten_kota;
                d.kode_toko = filters.kode_toko;
                d.status = filters.status;
            }
        },
        scrollY: "450px",

        scrollCollapse: true,
        scroller: true, // Aktifkan infinite scroll
        columns: [
            { data: 0, className: "text-center align-middle" },
            { data: 1, className: "text-center align-middle" },
            { data: 2, className: "text-start align-middle text-wrap" },
            { data: 3, className: "text-center align-middle text-wrap" },
            { data: 4, className: "text-start align-middle" },
            { data: 5, className: "text-start align-middle" },
            { data: 6, className: "text-start align-middle" },
            { data: 7, className: "text-start align-middle text-wrap" },
        ]
    });

    // Submit filter: reload datatable
    document.querySelectorAll('#filterSalesArea, #filterKecamatan, #filterKodeToko, #filterStatus').forEach(function(el) {
        el.addEventListener('change', function() {
            table.ajax.reload();
        });
    });
});

function exportExcel() {
        // Ambil nilai filter saat ini
        var area = document.getElementById('filterSalesArea').value;
        var kecamatan = document.getElementById('filterKecamatan').value;
        var kode_toko = document.getElementById('filterKodeToko').value;
        var kabupaten_kota = document.getElementById('filterKota').value;
        var provinsi = document.getElementById('filterProvinsi').value;
        var status = document.getElementById('filterStatus').value;

        // Ambil nilai search dari Datatable jika user ingin export hasil search juga
        // var search = $('#data').DataTable().search();

        // Bangun URL dengan query string
        var url = "{{ route('perusahaan.konsumen.export') }}";
        var params = new URLSearchParams({
            area: area,
            kecamatan: kecamatan,
            kode_toko: kode_toko,
            kabupaten_kota: kabupaten_kota,
            provinsi: provinsi,
            status: status,
            // search: search // uncomment jika ingin fitur search
        });

        // Redirect window untuk memicu download
        window.location.href = url + "?" + params.toString();
    }
</script>
@endpush
