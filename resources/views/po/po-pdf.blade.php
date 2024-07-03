@extends('layouts.doc')
@push('header')
<img src="{{ public_path('uploads/logo/'.$pt->logo) }}" alt="Logo" width="150">
<div class="header-div">
    <h3 style="margin-top: 20px; margin-bottom:0; padding: 0;">{{$pt->nama}}</h3>
    <p style="font-size:10px">{{$pt->alamat}}</p>
    <p style="font-size:10px">Kode Pos: {{$pt->kode_pos}}</p>
</div>
<hr style="margin-bottom: 0;">
@endpush
@section('content')
<div class="center-container">
    <h4 style="margin-bottom: 0; margin-top:0;">PURCHASE ORDER (PO)</h4>
    <p>{{$data->full_nomor}}</p>
</div>
<div class="tujuan-div">
    <table style="font-size: 12px">
        <tr>
            <th style="width: 100px; text-align: left" >Tanggal</th>
            <th style="width: 20px">:</th>
            <td>{{$data->tanggal}}</td>
        </tr>
        <tr>
            <th style="width: 100px; text-align: left" >Kepada</th>
            <th style="width: 20px">:</th>
            <td><strong>{{$data->kepada}}</strong></td>
        </tr>
        <tr>
            <th style="width: 100px; text-align: left" >Alamat</th>
            <th style="width: 20px">:</th>
            <td>{{$data->alamat}}</td>
        </tr>
        <tr>
            <th style="width: 100px; text-align: left" >Tlp</th>
            <th style="width: 20px">:</th>
            <td>{{$data->telepon}}</td>
        </tr>
    </table>
</div>
<div class="po-items">
    <table class="table-items">
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori Barang</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Sat</th>
                <th>HARGA</th>
                <th>TOTAL HARGA</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data->items as $item)
            <tr>
                <td style="text-align: center">{{$loop->iteration}}</td>
                <td>{{$item->kategori}}</td>
                <td>{{$item->nama_barang}}</td>
                <td style="text-align: center">{{number_format($item->jumlah, 0, ',', '.')}}</td>
                <td style="text-align: center">bh</td>
                <td style="text-align: right;">{{number_format($item->harga_satuan, 0, ',', '.')}}</td>
                <td style="text-align: right">{{number_format($item->total, 0, ',', '.')}}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="total">GRAND TOTAL</td>
                <td style="text-align: right">{{number_format($data->items->sum('total'), 0, ',', '.')}}</td>
            </tr>
            @if ($data->apa_ppn == 1)
            <tr>
                <td colspan="6" class="total">PPN (11%)</td>
                <td style="text-align: right">{{number_format($data->items->sum('total') * ($ppn/100), 0, ',', '.')}}</td>
            </tr>
            <tr>
                <td colspan="6" class="total">GRAND TOTAL + PPN</td>
                <td style="text-align: right">{{number_format($data->items->sum('total') + $data->items->sum('total') * ($ppn/100), 0, ',', '.')}}</td>
            </tr>
            @endif
            <tr>
                <td colspan="7" style="text-align: center">Terbilang: <strong>#{{ucfirst($terbilang)}} rupiah#</strong></td>
            </tr>
        </tfoot>
    </table>
</div>
<div class="po-notes">
    <h5 style="margin-bottom:5px;">Notes :</h5>
    @if ($data->notes())
    <ul style="font-size: 10px; margin-top:0">
        @foreach ($data->notes as $i)
        <li>{{$i->note}}</li>
        @endforeach
    </ul>
    @endif
</div>
<div class="footer">
    <p><strong>Hormat Kami,</strong></p>
    <br><br><br>
    <p style="margin-bottom: 0;">
        <strong>{{$pt->nama_direktur}}</strong>
    </p>
    <p style="margin-top: 0;">Direktur</p>
</div>
@endsection
