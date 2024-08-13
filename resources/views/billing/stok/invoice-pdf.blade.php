@extends('layouts.doc')
@push('header')
@if ($pt->logo)
<img src="{{ public_path('uploads/logo/'.$pt->logo) }}" alt="Logo" style="width: 75px">
@endif
<div class="header-div">
    <h3 style="margin-top: 20px; margin-bottom:0; padding: 0;">{{$pt->nama}}</h3>
    <p style="font-size:10px">{{$pt->alamat}}</p>
    <p style="font-size:10px">Kode Pos: {{$pt->kode_pos}}</p>
</div>
<hr style="margin-bottom: 0;">
@endpush
@section('content')
{{-- <div class="center-container">
    <h4 style="margin-bottom: 0; margin-top:0;">PURCHASE ORDER (PO)</h4>
    <p>{{$data->full_nomor}}</p>
</div> --}}
<div class="tujuan-div">
    <table style="font-size: 12px">
        <div class="row invoice-info">
            <div class="col-md-6 invoice-col">
                <table style="width: 100%; font-size: 10px">
                    <tr id="namaTr">
                        <td class="text-start align-middle" style="width: 20%">Nama</td>
                        <td class="text-start align-middle" style="width: 15px">:</td>
                        <td class="text-start align-middle" style="width: 200px">
                            <strong>{{$data->konsumen ? $data->konsumen->nama : $data->konsumen_temp->nama}}</strong>
                        </td>
                        <td style="width: 80px">&nbsp;</td>
                        <td class="text-start align-middle" style="width: 20%">Invoice</td>
                        <td class="text-start align-middle" style="width: 15px">:</td>
                        <td class="text-start align-middle" style="width: 20%">
                            <strong>{{$data->kode}}</strong>
                        </td>

                    </tr>
                    <tr>
                        <td class="text-start align-middle" >Sistem Pembayaran</td>
                        <td class="text-start align-middle" >:</td>
                        <td class="text-start align-middle" >
                            {{$data->konsumen ? $data->konsumen->sistem_pembayaran : 'Cash'}}
                        </td>
                        <td style="width: 10%"></td>
                        <td class="text-start align-middle" >Tanggal</td>
                        <td class="text-start align-middle" >:</td>
                        <td class="text-start align-middle" >
                            {{$tanggal}}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-start align-middle">Tempo</td>
                        <td class="text-start align-middle" style="width: 15px">:</td>
                        <td class="text-start align-middle">
                            {{$data->konsumen ? $data->konsumen->tempo_hari . ' Hari' : '-'}} </td>
                        <td style="width: 5%"></td>
                        <td class="text-start align-middle">Jam</td>
                        <td class="text-start align-middle" style="width: 15px">:</td>
                        <td class="text-start align-middle">
                            {{$jam}}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-start align-middle">NPWP</td>
                        <td class="text-start align-middle" style="width: 15px">:</td>
                        <td class="text-start align-middle">
                            {{$data->konsumen ? $data->konsumen->npwp : $data->konsumen_temp->npwp}}
                        </td>
                        <td style="width: 5%"></td>
                        <td class="text-start align-middle">No HP</td>
                        <td class="text-start align-middle" style="width: 15px">:</td>
                        <td class="text-start align-middle">
                            {{$data->konsumen ? $data->konsumen->no_hp : $data->konsumen_temp->no_hp}}
                        </td>
                    </tr>
                    <tr>
                        <td class="text-start align-middle">Alamat</td>
                        <td class="text-start align-middle" style="width: 15px">:</td>
                        <td class="text-start align-middle">
                            {{$data->konsumen ? $data->konsumen->alamat : $data->konsumen_temp->alamat}}
                        </td>
                        <td style="width: 5%"></td>
                        <td style="width: 5%"></td>
                        <td style="width: 5%"></td>
                        <td style="width: 5%"></td>
                    </tr>

                </table>
            </div>

        </div>
</div>
<div class="po-items">
    <table class="table-items">
        <table class="table-items" style="font-size: 10px">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">Unit</th>
                    <th class="text-center align-middle">Type</th>
                    <th class="text-center align-middle">Kategori Barang</th>
                    <th class="text-center align-middle">Nama Barang</th>
                    <th class="text-center align-middle">Kode Barang</th>
                    <th class="text-center align-middle">Merk Barang</th>
                    <th class="text-center align-middle">Banyak</th>
                    <th class="text-center align-middle">Satuan</th>
                    <th class="text-center align-middle">Harga Satuan</th>
                    <th class="text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->invoice_detail as $d)
                <tr>
                    <td style="text-align: center">
                        {{$d->stok->unit->nama}}
                    </td>
                    <td style="text-align: center">
                        {{$d->stok->type->nama}}
                    </td>
                    <td style="text-align: center">
                        {{$d->stok->kategori->nama}}
                    </td>
                    <td style="text-align: center">
                        {{$d->stok->barang_nama->nama}}
                    </td>
                    <td style="text-align: center">
                        {{$d->stok->barang->kode}}
                    </td>
                    <td style="text-align: center">
                        {{$d->stok->barang->merk}}
                    </td>
                    <td style="text-align: center">
                        {{$d->nf_jumlah}}
                    </td>
                    <td style="text-align: center">
                        {{$d->stok->barang->satuan ? $d->stok->barang->satuan->nama : '-'}}
                    </td>
                    <td style="text-align: right">
                        {{$d->nf_harga_satuan}}
                    </td>
                    <td style="text-align: right">
                        {{$d->nf_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th style="text-align: right" colspan="9">Total DPP : </th>
                    <th style="text-align: right">{{$data->dpp}}</th>
                </tr>
                <tr>
                    <th style="text-align: right" colspan="9">Diskon : </th>
                    <th style="text-align: right">{{number_format($data->diskon, 0 ,',', '.')}}</th>
                </tr>
                <tr>
                    <th style="text-align: right" colspan="9">DPP Setelah Diskon : </th>
                    <th style="text-align: right">{{number_format($data->total-$data->diskon, 0 ,',', '.')}}</th>
                </tr>
                <tr>
                    <th style="text-align: right" colspan="9">Ppn : </th>
                    <th style="text-align: right">{{$data->nf_ppn}}</th>
                </tr>
                <tr>
                    <th style="text-align: right" colspan="9">Penyesuaian : </th>
                    <th style="text-align: right">{{number_format($data->add_fee, 0 ,',', '.')}}</th>
                </tr>
                <tr>
                    <th style="text-align: right" colspan="9">Grand Total : </th>
                    <th style="text-align: right">{{$data->nf_grand_total}}</th>
                </tr>
                @if ($data->konsumen && $data->konsumen->pembayaran == 2)
                <tr>
                    <th style="text-align: right" colspan="9">DP : </th>
                    <th style="text-align: right">{{$data->nf_dp}}</th>
                </tr>
                @if ($data->ppn > 0)
                <tr>
                    <th style="text-align: right" colspan="9">DP PPn : </th>
                    <th style="text-align: right">{{$data->nf_dp_ppn}}</th>
                </tr>
                <tr>
                    <th style="text-align: right" colspan="9">Sisa PPN : </th>
                    <th style="text-align: right">{{$data->sisa_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th style="text-align: right" colspan="9">Sisa Tagihan : </th>
                    <th style="text-align: right">{{$data->sisa_tagihan}}</th>
                </tr>
                @endif
            </tfoot>
        </table>
</div>
<div class="footer" style="margin-top: 50px">
    <p><strong>{{$pt->nama}}</strong></p>
    <br><br><br>
    <p style="margin-bottom: 0;">
        <strong>_________________________</strong>
    </p>
    {{-- <p style="margin-top: 0;">Direktur</p> --}}
</div>
@endsection
