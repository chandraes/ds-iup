@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>STOK BARANG ALL</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>

                    <td class="text-center align-middle">
                        <form action="{{route('db.stok-ppn.download')}}" method="get" target="_blank">
                            {{-- <input type="hidden" name="unit" value="{{request('unit')}}"> --}}
                            <input type="hidden" name="type" value="{{request('type')}}">
                            <input type="hidden" name="kategori" value="{{request('kategori')}}">
                            <input type="hidden" name="barang_nama" value="{{request('barang_nama')}}">
                            {{-- <div class="row">
                                <button type="submit" class="btn"><img src="{{asset('images/print.svg')}}" alt="dokumen"
                                        width="30">
                                    PDF</button>
                            </div> --}}

                        </form>
                    </td>
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
    <form method="GET" action="{{url()->current()}}" class="mt-3 mb-5">
        <div class="row">
            <div class="col-md-12 mb-2">
                <label for="unit">Perusahaan</label>
                <select name="unit[]" id="unit" class="form-select form-select-sm" multiple>
                    <option value=""> ---------- </option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ (is_array(request('unit')) && in_array($unit->id, request('unit'))) ? 'selected' : '' }}>
                        {{ $unit->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type">Bidang</label>
                <select name="type" id="type" class="form-select form-select-sm">
                    <option value=""> ---------- </option>
                    @foreach($selectType as $type)
                    <option value="{{ $type->id }}" {{ request('type')==$type->id ? 'selected' : '' }}>
                        {{ $type->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="kategori">Kelompok Barang</label>
                <select name="kategori" id="kategori" class="form-select form-select-sm">
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
                <select class="form-select form-select-sm" name="barang_nama" id="filter_barang_nama">
                    <option value=""> ---------- </option>
                    @foreach ($selectBarangNama as $bn)
                    <option value="{{ $bn->id }}" {{ request('barang_nama')==$bn->id ? 'selected' : '' }}>
                        {{ $bn->nama }}
                        @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="nama">
                    ---------------
                </label>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-sm">Apply Filter</button>
                    {{-- reset filter button --}}
                    <a href="{{route('user.stok-all')}}" class="btn btn-danger btn-sm">Reset Filter</a>
                </div>

            </div>

        </div>

    </form>
    <div class="table-container mt-4">
        <table class="table table-bordered" id="dataTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Perusahaan</th>
                    <th class="text-center align-middle">Bidang</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle">Stok<br>Awal</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">PPN</th>
                    <th class="text-center align-middle">NON PPN</th>


                </tr>
            </thead>
            @php
            $number = 1;
            $sumTotalHargaBeli = 0;
            $sumTotalHargaJual = 0;
            @endphp

            <tbody>
                @foreach ($data as $unitId => $types)
                @php $unitDisplayed = false; @endphp
                @foreach ($types as $typeId => $categories)
                @php $typeDisplayed = false; @endphp
                @foreach ($categories as $kategoriId => $barangs)
                @php $kategoriDisplayed = false; @endphp
                @foreach ($barangs as $namaId => $items)
                @php $namaDisplayed = false; @endphp
                @foreach ($items as $barangId => $stokHargas)
                @php $barangDisplayed = false; @endphp
                @foreach ($stokHargas as $stokHarga)
                {{-- @if ($stokHarga->stok > 0) --}}
                <tr>
                    @if (!$unitDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->unitRowspan }}">{{ $number++ }}</td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->unitRowspan }}">{{
                        $stokHarga->unit->nama }}</td>
                    @php $unitDisplayed = true; @endphp
                    @endif
                    @if (!$typeDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->typeRowspan }}">{{
                        $stokHarga->type->nama }}</td>
                    @php $typeDisplayed = true; @endphp
                    @endif
                    @if (!$kategoriDisplayed)
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
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalHistori"
                            onclick="getHistori({{$stokHarga->barang->id}})">
                            {{ $stokHarga->barang->kode }}
                        </a>
                    </td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#modalHistori"
                            onclick="getHistori({{$stokHarga->barang->id}})">
                            {{ $stokHarga->barang->merk }}
                        </a>
                    </td>
                    @php $barangDisplayed = true; @endphp
                    @endif
                    <td class="text-center align-middle">
                        @if (auth()->user()->role == 'su')
                        <a href="#" data-bs-toggle="modal" data-bs-target="#eModal"
                            onclick="editStok({{$stokHarga}})">{{ $stokHarga->nf_stok_awal }}</a>
                        @else
                        {{ $stokHarga->nf_stok_awal }}
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        {{-- <a href="#" data-bs-toggle="modal" data-bs-target="#actionModal"
                            onclick="actionFun({{$stokHarga}})">{{ $stokHarga->nf_stok }}</a> --}}
                        @if ($stokHarga->stok > 0)
                        <a href="#" data-bs-toggle="modal" data-bs-target="#actionModal"
                            onclick="actionFun({{$stokHarga}})">{{ $stokHarga->nf_stok }}</a>
                        @else
                        {{ $stokHarga->nf_stok }}
                        @endif
                    </td>
                    <td class="text-center align-middle">{{ $stokHarga->barang->satuan ?
                        $stokHarga->barang->satuan->nama : '-' }}</td>
                    <td class="text-center align-middle">
                        @if ($stokHarga->barang->jenis == 1)
                        <i class="fa fa-check"></i>
                        @endif
                    </td>
                        <td class="text-center align-middle">
                             @if ($stokHarga->barang->jenis == 2)
                        <i class="fa fa-check"></i>
                        @endif
                        </td>

                </tr>
                {{-- @endif --}}
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

@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $('#filter_barang_nama').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

     $('#kategori').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

      $('#type').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

     $('#unit').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: "-- Semua Perusahaan --",
        multiple: true,
    });

    $('#karyawan_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#actionModal')
    });

    function editStok(data)
    {
        console.log(data);
        document.getElementById('e_stok_awal').value = data.stok_awal;
        document.getElementById('e_stok').value = data.stok ?? '';
        document.getElementById('e_harga_beli').value = data.harga_beli;

        // document.getElementById('harga').value = data.stok_ppn.nf_harga;
        document.getElementById('eForm').action = `{{route('db.stok.edit-stok-su', ':id')}}`.replace(':id', data.id);
    }

    function editFun(data)
    {

        document.getElementById('harga').value = data.nf_harga;
        document.getElementById('min_jual').value = data.min_jual ?? '';
        document.getElementById('satuan_edit').innerHTML = data.barang.satuan ? data.barang.satuan.nama : '-';

        // document.getElementById('harga').value = data.stok_ppn.nf_harga;
        document.getElementById('editForm').action = `{{route('db.stok-ppn.store', ':id')}}`.replace(':id', data.id);
    }





</script>
@endpush
