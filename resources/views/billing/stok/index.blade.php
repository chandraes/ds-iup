@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM JUAL</u></h1>
        </div>
    </div>
    @include('swal')
    @include('billing.stok.keranjang')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>
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
    <form method="GET" action="{{route('billing.lihat-stok')}}" class="mt-3 mb-5">
        <div class="row">
            <div class="col-md-2">
                <label for="unit">Perusahaan</label>
                <select name="unit" id="unit" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit')==$unit->id ? 'selected' : '' }}>
                        {{ $unit->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type">Bidang</label>
                <select name="type" id="type" class="form-select">
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
                    <a href="{{route('billing.lihat-stok')}}" class="btn btn-danger">Reset Filter</a>
                </div>

            </div>

        </div>

    </form>
    <div class="row my-3">
        <div class="col-md-6">
            <div class="row px-3">
                <a href="{{route('billing.form-jual.keranjang')}}" class="btn btn-success @if ($keranjang->count() == 0) disabled
                @endif" role="button"><i class="fa fa-shopping-cart"></i> Keranjang {{$keranjang->count() == 0 ? '' :
                    '('.$keranjang->count().')'}}</a>
            </div>
        </div>
        <div class="col-md-6">
            <form action="{{route('billing.form-jual.keranjang.empty')}}" method="post" id="keranjangEmpty">
                @csrf
                <div class="row px-3">
                    <button class="btn btn-danger" @if ($keranjang->count() == 0) disabled
                        @endif><i class="fa fa-trash"></i> Kosongkan Keranjang</button>
                </div>
            </form>
        </div>
    </div>
    @if ($keranjang->where('barang_ppn', 0)->count() == 0)
    <center>
        <h2>Barang PPN</h2>
    </center>
    <div class="table-container mt-4">

        <table class="table table-bordered" id="dataTable" style="font-size:12px">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Perusahaan</th>
                    <th class="text-center align-middle">Bidang</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">PPN<br>Keluaran</th>
                    <th class="text-center align-middle">Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">Ket</th>
                    <th class="text-center align-middle">Action</th>
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
                @if ($stokHarga->stok > 0)
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
                    <td class="text-start align-middle">

                        @if ($stokHarga->barang->detail_types)
                        <ul>
                            @foreach ($stokHarga->barang->detail_types as $detailType)
                            <li>{{ $detailType->type->nama }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first())
                        <div class="input-group">
                            <button class="btn btn-danger"
                                onclick="updateCart({{$stokHarga->id}}, -1, {{$stokHarga->stok}})">-</button>
                            <input type="number" class="form-control text-center"
                                value="{{$keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->jumlah}}"
                                min="1" max="{{$stokHarga->stok}}"
                                onchange="changeQuantity({{$stokHarga->id}}, this.value, {{$stokHarga->stok}})">
                            <button class="btn btn-success"
                                onclick="updateCart({{$stokHarga->id}}, 1, {{$stokHarga->stok}})">+</button>
                        </div>
                        @else
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#keranjangModal"
                            onclick="setModalJumlah({{$stokHarga}}, {{$stokHarga->id}})">Jual</button>
                        @endif
                    </td>
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
    @endif
    @if ($keranjang->where('barang_ppn', 1)->count() == 0)
    <center>
        <h2>Barang Non PPN</h2>
    </center>
    <div class="table-container mt-4">

        <table class="table table-bordered" id="dataTable" style="font-size:12px">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Perusahaan</th>
                    <th class="text-center align-middle">Bidang</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">PPN<br>Keluaran</th>
                    <th class="text-center align-middle">Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">Ket</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
            </thead>
            @php
            $number = 1;
            $sumTotalHargaBeli = 0;
            $sumTotalHargaJual = 0;
            @endphp

            <tbody>
                @foreach ($nonPpn as $unitId => $types)
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
                @if ($stokHarga->stok > 0)
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
                    <td class="text-start align-middle">

                        @if ($stokHarga->barang->detail_types)
                        <ul>
                            @foreach ($stokHarga->barang->detail_types as $detailType)
                            <li>{{ $detailType->type->nama }}</li>
                            @endforeach
                        </ul>
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first())
                        <div class="input-group">
                            <button class="btn btn-danger"
                                onclick="updateCart({{$stokHarga->id}}, -1, {{$stokHarga->stok}})">-</button>
                            <input type="number" class="form-control text-center"
                                value="{{$keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->jumlah}}"
                                min="1" max="{{$stokHarga->stok}}"
                                onchange="changeQuantity({{$stokHarga->id}}, this.value, {{$stokHarga->stok}})">
                            <button class="btn btn-success"
                                onclick="updateCart({{$stokHarga->id}}, 1, {{$stokHarga->stok}})">+</button>
                        </div>
                        @else
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#keranjangModal"
                            onclick="setModalJumlah({{$stokHarga}}, {{$stokHarga->id}})">Jual</button>
                        @endif
                    </td>
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
@endif

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

    confirmAndSubmit("#keranjangEmpty", "Apakah anda yakin untuk mengosongkan keranjang?");

    function setModalJumlah(data, id)
    {
        console.log(data);
        document.getElementById('titleJumlah').innerText = data.barang_nama.nama;
        document.getElementById('jumlah_satuan').innerText = data.barang.satuan ? data.barang.satuan.nama : '';
        document.getElementById('barang_stok_harga_id').value = id;

        if (data.barang.jenis == 1) {
            document.getElementById('barang_ppn').value = 1;
        } else {
            document.getElementById('barang_ppn').value = 0;
        }
    }

    function updateCart(productId, quantity, maxStock) {
        let currentQuantity = parseInt($(`button[onclick="updateCart(${productId}, 1, ${maxStock})"]`).siblings('input').val());

        if (currentQuantity + quantity > maxStock) {
            alert('Jumlah item tidak boleh melebihi stok yang tersedia.');
            return;
        }

        $.ajax({
            url: '{{route('billing.form-jual.keranjang.update')}}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                barang_stok_harga_id: productId,
                quantity: quantity
            },
            success: function(response) {
                $('#spinner').show();
                console.log(response);
                if (response.success) {
                    location.reload(); // Reload the page to reflect the changes
                } else {
                    alert('Gagal memperbarui keranjang.');
                }
            }
        });
    }

    function changeQuantity(productId, newQuantity, maxStock) {
        newQuantity = parseInt(newQuantity);

        if (newQuantity > maxStock) {
            alert('Jumlah item tidak boleh melebihi stok yang tersedia.');
            return;
        }

        $.ajax({
            url: '{{route('billing.form-jual.keranjang.set-jumlah')}}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                barang_stok_harga_id: productId,
                quantity: newQuantity
            },
            success: function(response) {
                $('#spinner').show();
                if (response.success) {
                    location.reload(); // Reload the page to reflect the changes
                } else {
                    alert('Gagal memperbarui keranjang.');
                }
            }
        });
    }
</script>
@endpush
