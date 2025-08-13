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
                   <th class="text-center align-middle">NO</th>
                    <th class="text-center align-middle">NAMA BARANG/MEREK</th>
                    <th class="text-center align-middle">QTY</th>
                    <th class="text-center align-middle">SAT</th>
                    <th class="text-center align-middle">HARGA SATUAN
                        {{$data->kas_ppn ? '(DPP)' : ''}}
                    </th>
                    <th class="text-center align-middle">
                        Diskon
                        {{$data->kas_ppn ? '(DPP)' : ''}}
                    </th>
                    <th class="text-center align-middle">HARGA DISKON
                        {{$data->kas_ppn ? '(DPP)' : ''}}</th>
                    @if ($data->kas_ppn == 1)
                        <th class="text-center align-middle">
                            HARGA DISKON
                            (PPN)
                        </th>
                    @endif
                    <th class="text-center align-middle">TOTAL HARGA</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->invoice_detail as $d)
                <tr>
                   <td class="text-center align-middle">
                        {{$loop->iteration}}
                    </td>
                    <td class="text-center align-middle">
                         {{$d->stok->barang_nama->nama}}, {{$d->stok->barang->kode}},
                        {{$d->stok->barang->merk}}
                    </td>
                     <td class="text-center align-middle">{{$d->nf_jumlah}}</td>
                    <td class="text-center align-middle">{{$d->barang->satuan ? $d->barang->satuan->nama
                        : '-'}}</td>
                    <td class="text-end align-middle">{{$d->nf_harga_satuan}}</td>
                        <td class="text-end align-middle">
                        {{$d->nf_diskon}}
                    </td>
                    @if ($data->kas_ppn == 1)
                    <td class="text-end align-middle">{{number_format($d->harga_satuan - $d->diskon, 0, ',','.')}}</td>
                    @endif
                    <td class="text-end align-middle">{{number_format($d->harga_satuan - $d->diskon + $d->ppn, 0, ',','.')}}</td>

                    <td class="text-end align-middle">
                        {{$d->nf_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                   <tr>
                    <th style="text-align: right" colspan="{{$data->kas_ppn ? '8' : '7'}}">Grand Total : </th>
                    <th style="text-align: right">{{$data->nf_grand_total}}</th>
                </tr>
                @if ($data->lunas == 0)
                @if ($data->konsumen && $data->konsumen->pembayaran == 2)
                <tr>
                    <th style="text-align: right" colspan="{{$data->kas_ppn ? '8' : '7'}}">DP : </th>
                    <th style="text-align: right">{{$data->nf_dp}}</th>
                </tr>
                @if ($data->ppn > 0)
                <tr>
                    <th style="text-align: right" colspan="{{$data->kas_ppn ? '8' : '7'}}">DP PPn : </th>
                    <th style="text-align: right">{{$data->nf_dp_ppn}}</th>
                </tr>
                <tr>
                    <th style="text-align: right" colspan="{{$data->kas_ppn ? '8' : '7'}}">Sisa PPN : </th>
                    <th style="text-align: right">{{$data->nf_sisa_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th style="text-align: right" colspan="{{$data->kas_ppn ? '8' : '7'}}">Sisa Tagihan : </th>
                    <th style="text-align: right">{{$data->nf_sisa_tagihan}}</th>
                </tr>
                @endif
                @endif

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
