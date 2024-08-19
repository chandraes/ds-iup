@extends('layouts.doc-nologo-2')
@section('content')

<div class="container-fluid">
    <center>
        <h2>Pricelist
            {{$ppn_kas == 1 ? 'Barang PPN' : 'Barang Non PPN'}}
        </h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered table-pdf text-pdf" id="dataTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf" style="width: 15px">No</th>
                    <th class="text-center align-middle table-pdf text-pdf">Unit</th>
                    <th class="text-center align-middle table-pdf text-pdf">Tipe</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kelompok<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Nama<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kode<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Merk<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Satuan<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    @if ($ppn_kas == 1)
                    <th class="text-center align-middle table-pdf text-pdf">Harga+PPN<br>Jual Barang</th>
                    @endif
                </tr>
            </thead>
            @php
            $number = 1;
            $sumTotalHargaBeli = 0;
            $sumTotalHargaJual = 0;
            @endphp

            <tbody>
                @foreach ($data as $stokHarga)
                @if ($stokHarga->stok > 0)
                <tr>

                    <td class="text-center align-middle table-pdf text-pdf">{{ $number++ }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->unit->nama }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->type->nama }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->kategori->nama }}</td>
                    <td class="text-start align-middle table-pdf text-pdf">{{
                        $stokHarga->barang_nama->nama }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->barang->kode }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->barang->merk }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{ $stokHarga->barang->satuan ?
                        $stokHarga->barang->satuan->nama : '-' }}</td>
                    @php
                    $totalHargaBeli = ($stokHarga->harga_beli + ($stokHarga->harga_beli * $ppnRate / 100)) *
                    $stokHarga->stok;
                    $totalHargaJual = ($stokHarga->harga + ($stokHarga->harga * $ppnRate / 100)) * $stokHarga->stok;
                    $sumTotalHargaJual += $totalHargaJual;
                    $sumTotalHargaBeli += $totalHargaBeli;
                    if ($stokHarga->harga_beli == 0) {
                        $margin = '-';
                    } else {
                        $margin = ($stokHarga->harga - $stokHarga->harga_beli) / $stokHarga->harga_beli * 100;

                    }
                    @endphp
                    <td class="text-end align-middle text-pdf table-pdf">
                        {{ $stokHarga->nf_harga }}
                    </td>
                    @if ($ppn_kas == 1)
                    <td class="text-end align-middle text-pdf table-pdf">
                        {{ number_format($stokHarga->harga+($stokHarga->harga*$ppnRate/100), 0, ',','.') }}
                    </td>
                    @endif

                </tr>
                @endif
                @if ($stokHarga->unit_id != $data->last()->unit_id)
                <tr>
                    <td colspan="11" style="border: none; background-color:transparent; border-bottom-color:transparent; height:20px">
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
