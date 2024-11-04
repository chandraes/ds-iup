@extends('layouts.doc-nologo-3')
@section('content')
<div class="container-fluid">
    <center>
        <h2>INVOICE PEMBELIAN </h2>
         {{-- <h2>{{$stringBulanNow}} {{$tahun}}</h2> --}}
    </center>
</div>
<div class="container table-responsive ml-3">
    <div class="row">
        <div class="col-md-12 my-3">
            <div class="row" id="konsumenRow">
                <div class="row invoice-info">
                    <table style="width: 100%; font-size: 10px">
                        <tr id="namaTr">
                            <td class="text-start align-middle" style="width: 5%">Nomor PO</td>
                            <td class="text-start align-middle" style="width: 15px">:</td>
                            <td class="text-start align-middle" style="width: 200px">
                                <strong>{{$data->kode}}</strong>
                            </td>
                            <td style="width: 80px">&nbsp;</td>
                            <td class="text-start align-middle" style="width: 20%">Sistem Pembayaran</td>
                            <td class="text-start align-middle" style="width: 15px">:</td>
                            <td class="text-start align-middle" style="width: 20%">
                                <strong>{{$data->supplier->sistem_pembayaran}}</strong>
                            </td>

                        </tr>
                        <tr>
                            <td class="text-start align-middle" >Supplier</td>
                            <td class="text-start align-middle" >:</td>
                            <td class="text-start align-middle" >
                                <strong>{{$data->supplier->nama}}</strong>
                            </td>
                            <td style="width: 10%"></td>
                            <td class="text-start align-middle" >Tanggal Jatuh Tempo</td>
                            <td class="text-start align-middle" >:</td>
                            <td class="text-start align-middle" >
                                <strong>{{$data->id_jatuh_tempo}}</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        @if ($data->void == 0)
        <table class="table-pdf text-pdf" id="rekapTable" style=width:100%">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf">No</th>
                    <th class="text-center align-middle table-pdf text-pdf">Unit</th>
                    <th class="text-center align-middle table-pdf text-pdf">Type</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kelompok</th>
                    <th class="text-center align-middle table-pdf text-pdf">Barang</th>
                    <th class="text-center align-middle table-pdf text-pdf">Kode</th>
                    <th class="text-center align-middle table-pdf text-pdf">Merk</th>
                    <th class="text-center align-middle table-pdf text-pdf">Qty</th>
                    <th class="text-center align-middle table-pdf text-pdf">Satuan</th>
                    <th class="text-center align-middle table-pdf text-pdf">Harga Satuan</th>
                    <th class="text-center align-middle table-pdf text-pdf">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->items as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$loop->iteration}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->barang->unit->nama}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->barang->type->nama}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->barang->kategori->nama}}
                    </td>
                    <td class="text-start align-middle table-pdf text-pdf">
                        {{$d->barang->barang_nama->nama}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->barang->kode}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->barang->merk}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->nf_jumlah}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->barang->satuan ? $d->barang->satuan->nama : '-'}}
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">
                        {{$d->nf_harga}}
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">
                        {{$d->nf_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">Total DPP</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->dpp}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">Diskon</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_diskon}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">Total DPP Setelah Diskon</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->dpp_setelah_diskon}}</th>
                </tr>
                @if ($data->kas_ppn == 1)
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">PPN</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">Penyesuaian</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_add_fee}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">Grand Total</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_total}}</th>
                </tr>
                @if ($data->tempo == 1)
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">DP</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_dp}}</th>
                </tr>
                @if ($data->kas_ppn == 1)
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">DP PPN</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_dp_ppn}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">Sisa PPN</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_sisa_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="10">Sisa Tagihan</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{$data->nf_sisa}}</th>
                </tr>
                @endif

            </tfoot>
        </table>
        @else
        <H3>VOID PEMBELIAN</H3>
        @endif

    </div>
</div>
@endsection
