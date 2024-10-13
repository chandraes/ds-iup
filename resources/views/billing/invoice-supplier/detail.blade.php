@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL INVOICE SUPPLIER</u></h1>
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
                        <table style="width: 50%">
                            <tr>
                                <td class="text-start align-middle">Nomor PO</td>
                                <td class="text-start align-middle" style="width: 5%">:</td>
                                <td class="text-start align-middle">
                                    <strong>{{$data->kode}}</strong>
                                </td>
                            </tr>
                            <tr id="namaTr">
                                <td class="text-start align-middle">Supplier</td>
                                <td class="text-start align-middle" style="width: 5%">:</td>
                                <td class="text-start align-middle">
                                   <strong>{{$data->supplier->nama}}</strong>
                                </td>
                            </tr>

                        </table>
                    </div>
                    <!-- /.col -->
                    <div class="col-md-6 invoice-col" >
                        <div class="row d-flex justify-content-end">
                            <table style="width: 50%">
                                <tr>
                                    <td class="text-start align-middle">Sistem Pembayaran</td>
                                    <td class="text-start align-middle" style="width: 5%">:</td>
                                    <td class="text-start align-middle">
                                        <strong>{{$data->supplier->sistem_pembayaran}}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-start align-middle">Tanggal Jatuh Tempo</td>
                                    <td class="text-start align-middle" style="width: 5%">:</td>
                                    <td class="text-start align-middle">
                                       <strong>{{$data->id_jatuh_tempo}}</strong>
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
        @if ($data->void == 0)
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Perusahaan</th>
                    <th class="text-center align-middle">Bidang</th>
                    <th class="text-center align-middle">Kelompok</th>
                    <th class="text-center align-middle">Barang</th>
                    <th class="text-center align-middle">Kode</th>
                    <th class="text-center align-middle">Merk</th>
                    <th class="text-center align-middle">Qty</th>
                    <th class="text-center align-middle">Satuan</th>
                    <th class="text-center align-middle">Harga Satuan</th>
                    <th class="text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->items as $d)
                <tr>
                    <td class="text-center align-middle">
                        {{$loop->iteration}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->unit->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->type->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->kategori->nama}}
                    </td>
                    <td class="text-start align-middle">
                        {{$d->barang->barang_nama->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->kode}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->merk}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->nf_jumlah}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->satuan ? $d->barang->satuan->nama : '-'}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_harga}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="10">Total DPP</th>
                    <th class="text-end align-middle">{{$data->dpp}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="10">Diskon</th>
                    <th class="text-end align-middle">{{$data->nf_diskon}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="10">Total DPP Setelah Diskon</th>
                    <th class="text-end align-middle">{{$data->dpp_setelah_diskon}}</th>
                </tr>
                @if ($data->kas_ppn == 1)
                <tr>
                    <th class="text-end align-middle" colspan="10">PPN</th>
                    <th class="text-end align-middle">{{$data->nf_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th class="text-end align-middle" colspan="10">Penyesuaian</th>
                    <th class="text-end align-middle">{{$data->nf_add_fee}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="10">Grand Total</th>
                    <th class="text-end align-middle">{{$data->nf_total}}</th>
                </tr>
                @if ($data->tempo == 1)
                <tr>
                    <th class="text-end align-middle" colspan="10">DP</th>
                    <th class="text-end align-middle">{{$data->nf_dp}}</th>
                </tr>
                @if ($data->kas_ppn == 1)
                <tr>
                    <th class="text-end align-middle" colspan="10">DP PPN</th>
                    <th class="text-end align-middle">{{$data->nf_dp_ppn}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="10">Sisa PPN</th>
                    <th class="text-end align-middle">{{$data->nf_sisa_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th class="text-end align-middle" colspan="10">Sisa Tagihan</th>
                    <th class="text-end align-middle">{{$data->nf_sisa}}</th>
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
