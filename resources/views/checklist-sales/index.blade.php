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
                    {{-- <td><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td> --}}
                    {{-- <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalSalesArea"><img
                                src="{{asset('images/area.svg')}}" width="30"> Sales Area</a>
                    </td> --}}
                    {{-- <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalKodeToko"><img
                        src="{{asset('images/kode-toko.svg')}}" width="30"> Kode Toko</a>
            </td> --}}
                    <td><a href="#" onclick="downloadPDF()"><img
                                src="{{asset('images/print.svg')}}" width="30"> Print</a>

                    </td>
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
                            <option value="{{ $kec->id }}" {{ request('kecamatan') == $kec->id ? 'selected' : '' }}>
                                {{ $kec->nama_wilayah }}
                            </option>
                        @endforeach
                    </select>
            </div>

            <div class="col-md-1 mt-4">
                <div class="row">
                    <a href="{{ url()->current() }}" class="btn btn-secondary mt-2">Reset</a>
                </div>

            </div>
        </div>
    </form>
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle">KODE <br>TOKO</th>
                <th class="text-center align-middle">NAMA</th>
                <th class="text-center align-middle">Kecamatan</th>
                <th class="text-center align-middle">Sales Area</th>
                @foreach ($months as $item => $month)
                <th class="text-center align-middle">{{ $item }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)

            <tr>
                <td class="text-center align-middle">
                    {{$d->full_kode}}
                </td>
                <td class="text-start align-middle text-wrap">
                    {{$d->kode_toko->kode. ' ' .$d->nama}}
                </td>
                <td class="text-start align-middle">
                    {{$d->kecamatan ? str_replace('Kec. ','',$d->kecamatan->nama_wilayah) : ''}}
                </td>
                <td class="text-center align-middle">{{$d->karyawan ? $d->karyawan->nickname : ''}}</td>
                @foreach ($months as $item => $month)
                <td></td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
{{-- <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script> --}}
<script>

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

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        stateSave: true,
        scrollY: "550px",
    });

    function downloadPDF() {

        var kodeToko = $('#filterKodeToko').val();
        var area = $('#filterSalesArea').val();
        var kecamatan = $('#filterKecamatan').val();
        var url = "{{ route('checklist-sales.download') }}";
        var params = new URLSearchParams();
        if (kodeToko) {
            params.append('kode_toko', kodeToko);
        }
        if (area) {
            params.append('area', area);
        }
        if (kecamatan) {
            params.append('kecamatan', kecamatan);
        }
        window.open(url + '?' + params.toString(), '_blank');


    }


</script>
@endpush
