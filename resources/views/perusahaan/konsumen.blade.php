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
    <form method="GET" action="{{ url()->current() }}">
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="filterSalesArea" class="form-label">Kode Toko</label>

                <select id="filterKodeToko" name="kode_toko" class="form-select" onchange="this.form.submit()">
                    <option value="" selected>-- Semua Kode Toko --</option>
                    @foreach ($kode_toko as $k)
                    <option value="{{ $k->id }}" {{ request('kode_toko')==$k->id ? 'selected' : '' }}>
                        {{ $k->kode }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterSalesArea" class="form-label">Sales Area</label>

                <select id="filterSalesArea" name="area" class="form-select" onchange="this.form.submit()">
                    <option value="" selected>-- Semua Sales Area --</option>
                    @foreach ($sales_area as $salesArea)
                    <option value="{{ $salesArea->id }}" {{ request('area')==$salesArea->id ? 'selected' : '' }}>
                        {{ $salesArea->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterKecamatan" class="form-label">Kecamatan</label>
                <select id="filterKecamatan" name="kecamatan" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Semua Kecamatan --</option>
                    @foreach ($kecamatan_filter as $kec)
                    <option value="{{ $kec->id }}" {{ request('kecamatan')==$kec->id ? 'selected' : '' }}>
                        {{ $kec->nama_wilayah }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filterKecamatan" class="form-label">Status</label>
                <select id="filterStatus" name="status" class="form-select" onchange="this.form.submit()">
                    <option value="1" {{ request()->has('status') && request('status') == 1 ? 'selected' : 'selected'
                        }}>Aktif</option>
                    <option value="0" {{ request()->has('status') && request('status') == 0 ? 'selected' : '' }}>Non
                        Aktif</option>
                </select>
            </div>
            <div class="col-md-1 mt-4">
                <div class="row">
                    <a href="{{ url()->current() }}" class="btn btn-secondary mt-2">Reset</a>
                </div>

            </div>
        </div>
    </form>
    <div class="container-fluid table-responsive">
        <table class="table table-bordered table-hover" id="data">
            <thead class="table-success bg-gradient">
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


document.addEventListener('DOMContentLoaded', function() {
    function getFilters() {
        return {
            area: document.getElementById('filterSalesArea').value,
            kecamatan: document.getElementById('filterKecamatan').value,
            kode_toko: document.getElementById('filterKodeToko').value,
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
            { data: 3, className: "text-start align-middle text-wrap" },
            { data: 4, className: "text-center align-middle" },
            { data: 5, className: "text-center align-middle text-wrap" },
            { data: 6, className: "text-start align-middle" },
            { data: 7, className: "text-start align-middle" },
            { data: 8, className: "text-start align-middle" },
            { data: 9, className: "text-start align-middle text-wrap" },
        ]
    });

    // Submit filter: reload datatable
    document.querySelectorAll('#filterSalesArea, #filterKecamatan, #filterKodeToko, #filterStatus').forEach(function(el) {
        el.addEventListener('change', function() {
            table.ajax.reload();
        });
    });
});
</script>
@endpush
