@extends('layouts.doc-nologo-2')
@section('content')

<div class="container-fluid">
    <center>
        <h2>STOK NON PPN</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf" style="width: 15px">No</th>
                    <th class="text-center align-middle table-pdf text-pdf">Unit</th>
                    <th class="text-center align-middle table-pdf text-pdf">Tipe</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kelompok<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Nama<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kode<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Merk<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Stok<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Satuan<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Harga DPP<br>Beli Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Harga Jual<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Total Harga<br>Beli Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Total Harga<br>Jual Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Margin<br>Profit</th>
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

                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->barang_nama->nama }}</td>

                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->barang->kode }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{
                        $stokHarga->barang->merk }}</td>

                    <td class="text-center align-middle table-pdf text-pdf">{{ $stokHarga->nf_stok }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{ $stokHarga->barang->satuan ?
                        $stokHarga->barang->satuan->nama : '-' }}</td>
                    @php
                    $totalHargaBeli = ($stokHarga->harga_beli) *
                    $stokHarga->stok;
                    $totalHargaJual = ($stokHarga->harga) * $stokHarga->stok;
                    $sumTotalHargaJual += $totalHargaJual;
                    $sumTotalHargaBeli += $totalHargaBeli;
                    $margin = ($stokHarga->harga - $stokHarga->harga_beli) / $stokHarga->harga_beli * 100;
                    @endphp
                    <td class="text-end align-middle table-pdf text-pdf">{{ $stokHarga->nf_harga_beli }}</td>
                    <td class="text-end align-middle table-pdf text-pdf">
                      {{ $stokHarga->nf_harga }}

                    </td>


                    <td class="text-end align-middle table-pdf text-pdf">{{ number_format($totalHargaBeli, 0, ',','.') }}</td>
                    <td class="text-end align-middle table-pdf text-pdf">{{ number_format($totalHargaJual, 0, ',','.') }}</td>
                    <td class="text-end align-middle table-pdf text-pdf @if ($margin < 10)
                                    table-danger
                                @endif">
                        {{ number_format($margin, 2) }}%
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="11" class="text-end align-middle table-pdf text-pdf">Grand Total</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($sumTotalHargaBeli, 0 ,',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($sumTotalHargaJual, 0 ,',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf"></th>
                </tr>
                <tr>
                    <th colspan="11" class="text-end align-middle table-pdf text-pdf">Estimasi Profit</th>
                    <th class="text-end align-middle table-pdf text-pdf" colspan="2">{{number_format($sumTotalHargaJual-$sumTotalHargaBeli,
                        0 ,',','.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
