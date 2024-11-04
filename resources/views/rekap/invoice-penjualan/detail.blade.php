@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL INVOICE KONSUMEN</u></h1>
            <h1>{{$data->uraian}}</h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.invoice-penjualan.detail.download', ['invoice' => $data])}}" target="_blank"><img src="{{asset('images/print.svg')}}" alt="print"
                        width="30"> PDF</a></td>
                    <td><a href="{{url()->previous()}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30">
                        KEMBALI</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container table-responsive ml-3">
    <div class="row">
        <div class="col-md-12 my-3">
            <div class="row" id="konsumenRow">
                <div class="row invoice-info">
                    <div class="col-md-6 invoice-col">
                        <table style="width: 90%">
                            <tr>
                                <td class="text-start align-middle">Konsumen</td>
                                <td class="text-start align-middle" style="width: 10%">:</td>
                                <td class="text-start align-middle">
                                    {{$data->konsumen ? $data->konsumen->nama : $data->konsumen_temp->nama}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start align-middle">Sistem Pembayaran</td>
                                <td class="text-start align-middle" style="width: 10%">:</td>
                                <td class="text-start align-middle">
                                    {{$data->konsumen ? $data->konsumen->sistem_pembayaran : 'Cash'}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start align-middle">Tempo</td>
                                <td class="text-start align-middle" style="width: 10%">:</td>
                                <td class="text-start align-middle">
                                    {{$data->konsumen ? $data->konsumen->tempo_hari . ' Hari' : '-'}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start align-middle">NPWP</td>
                                <td class="text-start align-middle" style="width: 10%">:</td>
                                <td class="text-start align-middle">
                                    {{$data->konsumen ? $data->konsumen->npwp : $data->konsumen_temp->npwp}}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-start align-middle">Alamat</td>
                                <td class="text-start align-middle" style="width: 10%">:</td>
                                <td class="text-start align-middle">
                                    {{$data->konsumen ? $data->konsumen->alamat : $data->konsumen_temp->alamat}}
                                </td>
                            </tr>

                        </table>
                    </div>
                    <!-- /.col -->
                    <div class="col-md-6 invoice-col" >
                        <div class="row d-flex justify-content-end">
                            <table style="width: 90%">
                                <tr>
                                    <td class="text-start align-middle">Invoice</td>
                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                    <td class="text-start align-middle">
                                        <strong>
                                            {{$data->kode}}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-start align-middle">Tanggal</td>
                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                    <td class="text-start align-middle">
                                        {{$tanggal}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-start align-middle">Jam</td>
                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                    <td class="text-start align-middle">
                                        {{$jam}}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-start align-middle">No WA</td>
                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                    <td class="text-start align-middle">
                                        {{$data->konsumen ? $data->konsumen->no_hp : $data->konsumen_temp->no_hp}}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
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
                    <td class="text-center align-middle">
                        {{$d->stok->unit->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->stok->type->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->stok->kategori->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->stok->barang_nama->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->stok->barang->kode}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->stok->barang->merk}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->nf_jumlah}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->stok->barang->satuan ? $d->stok->barang->satuan->nama : '-'}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_harga_satuan}}
                    </td>
                    <td class="text-end align-middle">
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
                {{-- @if ($data->konsumen && $data->konsumen->pembayaran == 2)
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
                @endif --}}
            </tfoot>
        </table>


    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "info": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",

        });

    });


</script>
@endpush
