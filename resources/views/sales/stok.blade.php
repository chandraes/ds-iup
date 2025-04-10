@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>STOK & HARGA JUAL BARANG</u></h1>
        </div>
    </div>
    @include('swal')
    {{-- @include('billing.stok.keranjang') --}}

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    {{-- <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td> --}}
                </tr>
            </table>
        </div>
    </div>
</div>
<style>
    .table-container {
        max-height: 500px;
        overflow-y: auto;
    }

    thead th {
        position: sticky;
        top: 0;
        background: white;
        z-index: 1;
    }
</style>
<div class="container-fluid mt-3 table-responsive ">
    <form method="GET" action="{{route('sales.stok')}}" class="mt-3 mb-5">
        <div class="row">

            <div class="col-md-2">
                <label for="kategori">Kelompok Barang</label>
                <select name="kategori" id="kategori" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($selectKategori as $kat)
                    <option value="{{ $kat->id }}" {{ request('kategori')==$kat->id ? 'selected' : '' }}>
                        {{ $kat->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="nama">Nama Barang</label>
                <select class="form-select" name="barang_nama" id="filter_barang_nama">
                    <option value=""> ---------- </option>
                    @foreach ($selectBarangNama as $bn)
                    <option value="{{ $bn->id }}" {{ request('barang_nama')==$bn->id ? 'selected' : '' }}>
                        {{ $bn->nama }}
                        @endforeach
                </select>
            </div>
            <div class="col-md-2">

                <div class="btn-group form-control mt-3">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    {{-- reset filter button --}}
                    <a href="{{url()->current()}}" class="btn btn-danger">Reset Filter</a>
                </div>

            </div>

        </div>

    </form>


    <center>
        <h2>Barang PPN</h2>
    </center>
    <div class="table-container mt-4">

        <table class="table table-bordered" id="dataTable" style="font-size:12px">
            <thead class="table-success">
            <tr>
                <th class="text-center align-middle" style="width: 15px">No</th>
                <th class="text-center align-middle">Kelompok<br>Barang</th>
                <th class="text-center align-middle">Nama<br>Barang</th>
                <th class="text-center align-middle">Kode<br>Barang</th>
                <th class="text-center align-middle">Merk<br>Barang</th>
                <th class="text-center align-middle" style="width: 20px">Harga DPP<br>Jual Barang</th>
                <th class="text-center align-middle">PPN<br>Keluaran</th>
                <th class="text-center align-middle">Harga+PPN<br>Jual Barang</th>
                <th class="text-center align-middle">Stok<br>Barang</th>
                <th class="text-center align-middle">Satuan<br>Barang</th>
            </tr>
            </thead>
            @php
            $number = 1;
            $sumTotalHargaBeli = 0;
            $sumTotalHargaJual = 0;
            @endphp

            <tbody>
            @foreach ($data as $unitId => $types)
            @foreach ($types as $typeId => $categories)
            @foreach ($categories as $kategoriId => $barangs)
            @php $kategoriDisplayed = false; @endphp
            @foreach ($barangs as $namaId => $items)
            @php $namaDisplayed = false; @endphp
            @foreach ($items as $barangId => $stokHargas)
            @php $barangDisplayed = false; @endphp
            @foreach ($stokHargas as $stokHarga)
            @if ($stokHarga->stok > 0)
            <tr>
                @if (!$kategoriDisplayed)
                <td class="text-center align-middle" rowspan="{{ $stokHarga->kategoriRowspan }}">{{ $number++ }}</td>
                <td class="text-center align-middle" rowspan="{{ $stokHarga->kategoriRowspan }}">{{
                $stokHarga->kategori->nama }}</td>
                @php $kategoriDisplayed = true; @endphp
                @endif
                @if (!$namaDisplayed)
                <td class="text-center align-middle" rowspan="{{ $stokHarga->namaRowspan }}">
                {{$stokHarga->barang_nama->nama }}</a>
                </td>
                @php $namaDisplayed = true; @endphp
                @endif
                @if (!$barangDisplayed)
                <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">{{
                $stokHarga->barang->kode }}</td>
                <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">{{
                $stokHarga->barang->merk }}</td>
                @php $barangDisplayed = true; @endphp
                @endif
                <td class="text-end align-middle">
                {{$stokHarga->nf_harga}}
                </td>
                <td class="text-end align-middle">
                {{ number_format($stokHarga->harga*$ppnRate/100, 0, ',','.') }}
                </td>
                <td class="text-end align-middle">
                {{ number_format($stokHarga->harga+($stokHarga->harga*$ppnRate/100), 0, ',','.') }}
                </td>
                <td class="text-center align-middle">{{ $stokHarga->nf_stok }}</td>
                <td class="text-center align-middle">{{ $stokHarga->barang->satuan ?
                $stokHarga->barang->satuan->nama : '-' }}</td>
            </tr>
            @endif
            @endforeach
            @endforeach
            @endforeach
            @endforeach
            @endforeach
            @if (!$loop->last)
            <tr>
                <td colspan="4" style="border: none; background-color:transparent; border-bottom-color:transparent">
                </td>
            </tr>
            @endif
            @endforeach
            </tbody>
        </table>
    </div>
    <br>
    <hr>
    <br>

    <center>
        <h2>Barang Non PPN</h2>
    </center>
    <div class="table-container mt-4">

        <table class="table table-bordered" id="dataTable" style="font-size:12px">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">PPN<br>Keluaran</th>
                    <th class="text-center align-middle">Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                </tr>
            </thead>
            @php
            $number = 1;
            $sumTotalHargaBeli = 0;
            $sumTotalHargaJual = 0;
            @endphp

            <tbody>
                @foreach ($nonPpn as $unitId => $types)
                @foreach ($types as $typeId => $categories)
                @foreach ($categories as $kategoriId => $barangs)
                @php $kategoriDisplayed = false; @endphp
                @foreach ($barangs as $namaId => $items)
                @php $namaDisplayed = false; @endphp
                @foreach ($items as $barangId => $stokHargas)
                @php $barangDisplayed = false; @endphp
                @foreach ($stokHargas as $stokHarga)
                @if ($stokHarga->stok > 0)
                <tr>
                    @if (!$kategoriDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->kategoriRowspan }}">{{ $number++ }}
                    </td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->kategoriRowspan }}">{{
                        $stokHarga->kategori->nama }}</td>
                    @php $kategoriDisplayed = true; @endphp
                    @endif
                    @if (!$namaDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->namaRowspan }}">{{
                        $stokHarga->barang_nama->nama }}</td>
                    @php $namaDisplayed = true; @endphp
                    @endif
                    @if (!$barangDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">{{
                        $stokHarga->barang->kode }}</td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">{{
                        $stokHarga->barang->merk }}</td>
                    @php $barangDisplayed = true; @endphp
                    @endif
                    <td class="text-end align-middle">
                        {{$stokHarga->nf_harga}}
                    </td>

                    <td class="text-end align-middle">
                        0
                    </td>
                    <td class="text-end align-middle">
                        {{$stokHarga->nf_harga}}
                    </td>
                    <td class="text-center align-middle">{{ $stokHarga->nf_stok }}</td>
                    <td class="text-center align-middle">{{ $stokHarga->barang->satuan ?
                        $stokHarga->barang->satuan->nama : '-' }}</td>
                </tr>
                @endif
                @endforeach
                @endforeach
                @endforeach
                @endforeach
                @endforeach
                @if (!$loop->last)
                <tr>
                    <td colspan="4" style="border: none; background-color:transparent; border-bottom-color:transparent">
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>


@if(session('pdfUrl'))
<script type="text/javascript">
    window.onload = function() {
            var pdfUrl = "{{ session('pdfUrl') }}";
            window.open(pdfUrl, '_blank');
        };
</script>
@endif
@endsection
@push('css')
{{--
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet"> --}}
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
{{-- <script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script> --}}
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $('#filter_barang_nama').select2({
    theme: 'bootstrap-5',
    width: '100%',
    });


</script>
@endpush
