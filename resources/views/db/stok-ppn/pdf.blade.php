@extends('layouts.doc-nologo-2')
@section('content')

<div class="container-fluid">
    <center>
        <h2>STOK PPN</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-bordered table-pdf text-pdf" id="dataTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf" style="width: 15px">No</th>
                    <th class="text-center align-middle table-pdf text-pdf">Perusahaan</th>
                    <th class="text-center align-middle table-pdf text-pdf">Bidang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kelompok<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Nama<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kode<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Merk<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Stok<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Satuan<br>Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Harga DPP<br>Beli Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Harga+PPN<br>Beli Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Total Harga+PPN<br>Beli Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Total Harga+PPN<br>Jual Barang</th>
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
                    <td class="text-end align-middle table-pdf text-pdf">{{ $stokHarga->nf_harga_beli }}</td>
                    <td class="text-end align-middle table-pdf text-pdf">{{ number_format(($stokHarga->harga_beli +
                        ($stokHarga->harga_beli * $ppnRate / 100)), 0, ',', '.') }}</td>
                    <td class="text-end align-middle text-pdf table-pdf">
                        {{ $stokHarga->nf_harga }}
                    </td>
                    <td class="text-end align-middle text-pdf table-pdf">
                        {{ number_format($stokHarga->harga+($stokHarga->harga*$ppnRate/100), 0, ',','.') }}
                    </td>

                    <td class="text-end align-middle table-pdf text-pdf">{{ number_format($totalHargaBeli, 0, ',','.') }}</td>
                    <td class="text-end align-middle table-pdf text-pdf">{{ number_format($totalHargaJual, 0, ',','.') }}</td>
                    <td class="text-end align-middle table-pdf text-pdf @if ($margin == '-')
                    table-warning
                    @else
                    @if ($margin < 10.01) table-danger @endif
                    @endif">
                    @if ($margin == '-')
                    {{$margin}}
                    @else
                    {{number_format($margin, 2, '.',',')}}%
                    @endif
                    </td>
                </tr>
                @endif
                @if ($stokHarga->unit_id != $data->last()->unit_id)
                <tr>
                    <td colspan="16" style="border: none; background-color:transparent; border-bottom-color:transparent; height:20px">
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="13" class="text-end align-middle text-pdf table-pdf">Grand Total</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($sumTotalHargaBeli, 0 ,',','.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($sumTotalHargaJual, 0 ,',','.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf"></th>
                </tr>
                <tr>
                    <th colspan="13" class="text-end align-middle text-pdf table-pdf">Estimasi Profit</th>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="2">{{number_format($sumTotalHargaJual-$sumTotalHargaBeli,
                        0 ,',','.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
