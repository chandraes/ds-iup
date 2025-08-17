@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>STOK & HARGA JUAL BARANG</u></h1>
        </div>
    </div>
    @include('swal')
    @include('sales.stok-harga.foto')
    @include('sales.jual.modal-jual')
    @include('sales.jual._grosir')
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
    <form method="GET" action="{{route('sales.jual.keranjang', ['keranjang'=> $id])}}" class="mt-3 mb-5">
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
            <div class="col-md-4">

                <div class="btn-group form-control mt-3">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    {{-- reset filter button --}}
                    <a href="{{url()->current()}}" class="btn btn-danger">Reset Filter</a>
                </div>

            </div>

        </div>

    </form>
    <div class="row my-3">
        <div class="col-md-6">
            <div class="row px-3">
                <a href="{{route('sales.jual.keranjang.review', ['keranjang' =>$id])}}" class="btn btn-success @if ($keranjang->count() == 0) disabled
                @endif" role="button"><i class="fa fa-shopping-cart"></i> Keranjang {{$keranjang->count() == 0 ? '' :
                    '('.$keranjang->count().')'}}</a>
            </div>
        </div>
        <div class="col-md-6">
            <form action="{{route('sales.jual.keranjang.empty', ['keranjang' => $id])}}" method="post"
                id="keranjangEmpty">
                @csrf
                <div class="row px-3">
                    <button class="btn btn-danger" @if ($keranjang->count() == 0) disabled
                        @endif><i class="fa fa-trash"></i> Kosongkan Keranjang</button>
                </div>
            </form>
        </div>
    </div>

    <center>
        <h2>Barang A</h2>
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
                    {{-- <th class="text-center align-middle">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">PPN<br>Keluaran</th> --}}
                    <th class="text-center align-middle">Harga<br>Jual Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">Kelipatan<br>Order</th>
                    <th class="text-center align-middle" style="width: 13%"> - </th>
                    <th class="text-center align-middle" style="width: 8%"> - </th>
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
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->kategoriRowspan }}">{{ $number++ }}
                    </td>
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
                        @if ($stokHarga->barang->foto)
                        <a href="javascript:void(0)"
                            onclick="viewImage('{{ asset('storage/'.$stokHarga->barang->foto) }}')">
                            @endif

                            {{ $stokHarga->barang->kode }}
                    </td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">
                        @if ($stokHarga->barang->foto)
                        <a href="javascript:void(0)"
                            onclick="viewImage('{{ asset('storage/'.$stokHarga->barang->foto) }}')">
                            @endif
                            {{ $stokHarga->barang->merk }}
                    </td>
                    @php $barangDisplayed = true; @endphp
                    @endif
                    {{-- <td class="text-end align-middle">
                        {{$stokHarga->nf_harga}}
                    </td>
                    <td class="text-end align-middle">
                        {{ number_format($stokHarga->harga*$ppnRate/100, 0, ',','.') }}
                    </td> --}}
                    <td class="text-end align-middle">
                        {{ number_format($stokHarga->harga+($stokHarga->harga*$ppnRate/100), 0, ',','.') }}
                    </td>
                    <td class="text-center align-middle">{{ $stokHarga->nf_stok }}</td>
                    <td class="text-center align-middle">{{ $stokHarga->barang->satuan ?
                        $stokHarga->barang->satuan->nama : '-' }}</td>
                    <td class="text-center align-middle">{{ $stokHarga->min_jual }}</td>
                    <td class="text-center align-middle">
                        @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first())
                            @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->is_grosir)
                            {{$keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->jumlah}}
                            @else
                            <div class="input-group">
                                <button class="btn btn-danger"
                                    onclick="updateCart({{$stokHarga->id}}, -{{$stokHarga->min_jual}}, {{$stokHarga->stok}})">-</button>
                                <input type="text" class="form-control text-center"
                                    value="{{$keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->jumlah}}"
                                    max="{{$stokHarga->stok}}" disabled>
                                <button class="btn btn-success"
                                    onclick="updateCart({{$stokHarga->id}}, {{$stokHarga->min_jual}}, {{$stokHarga->stok}})">+</button>
                            </div>
                            @endif
                        @else
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#keranjangModal"
                            onclick="setModalJumlah({{$stokHarga}}, {{$stokHarga->id}})">Jual Retail</button>
                        @endif
                    </td>
                    <td class="text-center align-middle" style="">
                        @if ($stokHarga->barang->is_grosir)
                        @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first() &&
                        $keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->is_grosir )

                        @else
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#grosirModal"
                            onclick="setModalGrosir({{$stokHarga}}, {{$stokHarga->id}})">Jual Grosir</button>
                        @endif
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

    <center>
        <h2>Barang B</h2>
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
                    {{-- <th class="text-center align-middle">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">PPN<br>Keluaran</th> --}}
                    <th class="text-center align-middle">Harga<br>Jual Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">Kelipatan<br>Order</th>
                    <th class="text-center align-middle" style="width: 13%"> - </th>
                    <th class="text-center align-middle" style="width: 8%"> - </th>
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
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">
                        @if ($stokHarga->barang->foto)
                        <a href="javascript:void(0)"
                            onclick="viewImage('{{ asset('storage/'.$stokHarga->barang->foto) }}')">
                            @endif

                            {{ $stokHarga->barang->kode }}
                    </td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">
                        @if ($stokHarga->barang->foto)
                        <a href="javascript:void(0)"
                            onclick="viewImage('{{ asset('storage/'.$stokHarga->barang->foto) }}')">
                            @endif
                            {{ $stokHarga->barang->merk }}
                    </td>
                    @php $barangDisplayed = true; @endphp
                    @endif
                    <td class="text-end align-middle">
                        {{$stokHarga->nf_harga}}
                    </td>

                    {{-- <td class="text-end align-middle">
                        0
                    </td>
                    <td class="text-end align-middle">
                        {{$stokHarga->nf_harga}}
                    </td> --}}
                    <td class="text-center align-middle">{{ $stokHarga->nf_stok }}</td>
                    <td class="text-center align-middle">{{ $stokHarga->barang->satuan ?
                        $stokHarga->barang->satuan->nama : '-' }}</td>
                    <td class="text-center align-middle">{{ $stokHarga->min_jual }}</td>
                    <td class="text-center align-middle">
                        @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first())
                          @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->is_grosir)
                            {{$keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->jumlah}}
                            @else
                            <div class="input-group">
                                <button class="btn btn-danger"
                                    onclick="updateCart({{$stokHarga->id}}, -{{$stokHarga->min_jual}}, {{$stokHarga->stok}})">-</button>
                                <input type="text" class="form-control text-center"
                                    value="{{$keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->jumlah}}"
                                    max="{{$stokHarga->stok}}" disabled>
                                <button class="btn btn-success"
                                    onclick="updateCart({{$stokHarga->id}}, {{$stokHarga->min_jual}}, {{$stokHarga->stok}})">+</button>
                            </div>
                            @endif
                        @else
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#keranjangModal"
                            onclick="setModalJumlah({{$stokHarga}}, {{$stokHarga->id}})">Jual Retail</button>
                        @endif
                    </td>
                    <td class="text-center align-middle" style="">
                        @if ($stokHarga->barang->is_grosir)
                        @if ($keranjang->where('barang_stok_harga_id', $stokHarga->id)->first() &&
                        $keranjang->where('barang_stok_harga_id', $stokHarga->id)->first()->is_grosir )

                        @else
                        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#grosirModal"
                            onclick="setModalGrosir({{$stokHarga}}, {{$stokHarga->id}})"> Jual Grosir</button>
                        @endif
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

@endsection
@push('css')

<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $('#filter_barang_nama').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    confirmAndSubmit("#keranjangEmpty", "Apakah anda yakin untuk mengosongkan keranjang?");
    confirmAndSubmit("#grosirForm", "Apakah anda yakin?");

    function setModalGrosir(data, id)
    {
         document.getElementById('barang_stok_harga_id_grosir').value = id;
         document.getElementById('nm_barang_merk').value = data.barang_nama.nama + ', ' + data.barang.kode + ', ' + data.barang.merk;

        if (data.barang.jenis == 1) {
            document.getElementById('barang_ppn_grosir').value = 1;
        } else {
            document.getElementById('barang_ppn_grosir').value = 0;
        }

        document.getElementById('rowGrosir').hidden = false;
        let grosirTbody = document.getElementById('grosirTableBody');
        grosirTbody.innerHTML = ''; // Clear previous rows

        // ajax request to get grosir data
        $.ajax({
            url: '{{ route("sales.jual.get-grosir") }}',
            method: 'GET',
            data: {
                barang_id: data.barang.id
            },
            success: function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    let grosirTableBody = document.getElementById('grosirTableBody');
                    grosirTableBody.innerHTML = ''; // Clear existing rows
                    response.data.forEach(function(grosir) {
                        let row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-center">${grosirTableBody.children.length + 1}</td>
                            <th class="text-center">${grosir.qty} ${grosir.satuan.nama}</th>
                            <td class="text-center">${grosir.qty_grosir} ${grosir.barang.satuan.nama}</td>
                            <td class="text-center">${grosir.diskon} %</td>
                        `;
                        grosirTableBody.appendChild(row);
                    });

                    response.satuans.forEach(function(satuan) {
                        let option = document.createElement('option');
                        option.value = satuan.id;
                        option.textContent = satuan.nama;
                        document.getElementById('satuan_grosir_id').appendChild(option);
                    });

                }
            },
            error: function() {
                alert('Terjadi kesalahan saat memuat data grosir.');
            }
        });

    }

    function setModalJumlah(data, id)
    {

        document.getElementById('titleJumlah').innerText = data.barang_nama.nama;
        document.getElementById('jumlah_satuan').innerText = data.barang.satuan ? data.barang.satuan.nama : '';
        document.getElementById('minJualSatuan').innerText = data.barang.satuan ? data.barang.satuan.nama : '';
        document.getElementById('minJual').value = data.nf_min_jual;
        document.getElementById('barang_stok_harga_id').value = id;

        document.getElementById('nm_barang_merk_retail').value = data.barang_nama.nama + ', ' + data.barang.kode + ', ' + data.barang.merk;

        if (data.barang.jenis == 1) {
            document.getElementById('barang_ppn').value = 1;
        } else {
            document.getElementById('barang_ppn').value = 0;
        }

        document.getElementById('rowGrosirRetail').hidden = false;
        let grosirTbody = document.getElementById('grosirTableBodyRetail');
        grosirTbody.innerHTML = ''; // Clear previous rows

        // ajax request to get grosir data
        $.ajax({
            url: '{{ route("sales.jual.get-grosir") }}',
            method: 'GET',
            data: {
                barang_id: data.barang.id
            },
            success: function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    let grosirTableBody = document.getElementById('grosirTableBodyRetail');
                    grosirTableBody.innerHTML = ''; // Clear existing rows
                    response.data.forEach(function(grosir) {
                        let row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-center">${grosirTableBody.children.length + 1}</td>
                            <th class="text-center">${grosir.qty} ${grosir.satuan.nama}</th>
                            <td class="text-center">${grosir.qty_grosir} ${grosir.barang.satuan.nama}</td>
                            <td class="text-center">${grosir.diskon} %</td>
                        `;
                        grosirTableBody.appendChild(row);
                    });

                }
            },
            error: function() {
                alert('Terjadi kesalahan saat memuat data grosir.');
            }
        });
    }

    function updateCart(productId, quantity, maxStock) {
        let currentQuantity = parseInt($(`button[onclick="updateCart(${productId}, 2, ${maxStock})"]`).siblings('input').val());

        if (currentQuantity + quantity > maxStock) {
            alert('Jumlah item tidak boleh melebihi stok yang tersedia.');
            return;
        }

        $.ajax({
            url: '{{route('sales.stok.keranjang.update')}}',
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
            url: '{{route('sales.stok.keranjang.set-jumlah')}}',
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

    function viewImage(imageUrl) {
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        const zoomableImage = document.getElementById('zoomableImage');
        zoomableImage.src = imageUrl;
        imageModal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const image = document.getElementById('zoomableImage');
        const slider = document.getElementById('zoomSlider');

        slider.addEventListener('input', function () {
            const scale = slider.value;
            image.style.transform = `scale(${scale})`;
        });
    });
</script>
@endpush
