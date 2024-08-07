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
                    <th class="text-center align-middle">Unit</th>
                    <th class="text-center align-middle">Type</th>
                    <th class="text-center align-middle">Kategori</th>
                    <th class="text-center align-middle">Barang</th>
                    <th class="text-center align-middle">Qty</th>
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
                    <th class="text-end align-middle" colspan="7">Total DPP</th>
                    <th class="text-end align-middle">{{$data->dpp}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Ppn</th>
                    <th class="text-end align-middle">{{$data->nf_ppn}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Pph</th>
                    <th class="text-end align-middle">{{$data->nf_pph}}</th>
                </tr>
                {{-- <tr>
                    <th class="text-end align-middle" colspan="7">Additional Fee</th>
                    <th class="text-end align-middle">{{$data->nf_add_fee}}</th>
                </tr> --}}
                <tr>
                    <th class="text-end align-middle" colspan="7">Grand Total</th>
                    <th class="text-end align-middle">{{$data->nf_grand_total}}</th>
                </tr>
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
