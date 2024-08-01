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
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
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
    <form method="GET" action="{{route('billing.lihat-stok')}}" class="mb-5">
        <div class="row">
            <div class="col-md-2">
                <label for="unit">Unit</label>
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
                <label for="type">Type</label>
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
                    <option value="{{ $bn->nama }}" {{ request('barang_nama')==$bn->nama ? 'selected' : '' }}>
                        {{ $bn->nama }}
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="nama">
                    ---------------
                </label>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    {{-- reset filter button --}}
                    <a href="{{ route('billing.lihat-stok') }}" class="btn btn-danger">Reset Filter</a>
                </div>

            </div>
        </div>
    </form>
    <center><h2>Barang PPN</h2></center>
    <div class="table-container mt-4">

        <table class="table table-bordered" id="dataTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Unit</th>
                    <th class="text-center align-middle">Tipe</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">PPN<br>Keluaran</th>
                    <th class="text-center align-middle">Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
            </thead>
            <tbody>
    @php
        $number = 1;
        $sumTotalHargaBeli = 0;
        $sumTotalHargaJual = 0;
    @endphp

    @foreach ($data as $unitId => $types)
        @foreach ($types as $typeId => $categories)
            @foreach ($categories as $kategoriId => $barangs)
                @foreach ($barangs as $namaBarangId => $items)
                    @php
                        $kategoriRowspan = $items->count();
                        $typeRowspan = $items->count();
                        $unitRowspan = $items->count();
                    @endphp

                    @foreach ($items as $item)
                        @php
                            $totalHargaBeli = ($item->harga_beli + ($item->harga_beli * $ppnRate / 100)) * $item->stok;
                            $totalHargaJual = ($item->harga + ($item->harga * $ppnRate / 100)) * $item->stok;
                            $sumTotalHargaJual += $totalHargaJual;
                            $sumTotalHargaBeli += $totalHargaBeli;
                            $margin = $item->harga_beli != 0 ? ($item->harga - $item->harga_beli) / $item->harga_beli * 100 : 0;
                        @endphp

                        <tr>
                            @if ($loop->first)
                                <td class="text-center align-middle" rowspan="{{ $unitRowspan }}">{{ $number++ }}</td>
                                <td class="text-center align-middle" rowspan="{{ $unitRowspan }}">{{ $items->first()->barang->unit->nama }}</td>
                            @endif
                            @if ($loop->first)
                                <td class="text-center align-middle" rowspan="{{ $typeRowspan }}">{{ $items->first()->barang->type->nama }}</td>
                            @endif
                            @if ($loop->first)
                                <td class="text-center align-middle" rowspan="{{ $kategoriRowspan }}">{{ $items->first()->barang->kategori->nama }}</td>
                            @endif
                            @if ($loop->first)
                                <td class="text-center align-middle" rowspan="{{ $items->count() }}">{{ $items->first()->barang->barangNama->nama }}</td>
                            @endif
                            <td class="text-center align-middle">{{ $items->first()->barang->kode }}</td>
                            <td class="text-center align-middle">{{ $items->first()->barang->merk }}</td>
                            <td class="text-end align-middle">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                            <td class="text-end align-middle">
                                {{ number_format($item->harga_beli + ($item->harga_beli * $ppnRate / 100), 0, ',', '.') }}
                            </td>
                            <td class="text-end align-middle">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#editModal" onclick="editFun({{$item}})">
                                    {{ number_format($item->harga, 0, ',', '.') }}
                                </a>
                            </td>
                            <td class="text-end align-middle">
                                {{ number_format($item->harga + ($item->harga * $ppnRate / 100), 0, ',', '.') }}
                            </td>
                            <td class="text-center align-middle">{{ $item->stok }}</td>
                            <td class="text-center align-middle">{{ $item->barang->satuan ? $item->barang->satuan->nama : '' }}</td>
                            <td class="text-end align-middle">
                                {{ number_format($totalHargaBeli, 0, ',', '.') }}
                            </td>
                            <td class="text-end align-middle">
                                {{ number_format($totalHargaJual, 0, ',', '.') }}
                            </td>
                            <td class="text-end align-middle @if ($margin < 10) table-danger @endif">
                                {{ number_format($margin, 2) }}%
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        @endforeach
    @endforeach
</tbody>

        </table>
    </div>
    <br>
    <hr>
    <br>

</div>

@endsection
@push('css')
{{-- <link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet"> --}}
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
    function setModalJumlah(data, id)
    {
        console.log(data);
        document.getElementById('titleJumlah').innerText = data.barang_nama.nama;
        document.getElementById('barang_stok_harga_id').value = id;

        if (data.jenis == 1) {
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
