@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL INVOICE BELANJA</u></h1>
            <h1>{{$data->uraian}}</h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen" width="30">
                            REKAP</a></td>
                    <td><a href="{{url()->previous()}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30">
                        KEMBALI</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    @if ($data->rekap[0]->bahan_baku)
                    <th class="text-center align-middle">Kategori Barang</th>
                    @endif
                    <th class="text-center align-middle">Nama Barang</th>


                    <th class="text-center align-middle">Banyak</th>
                    <th class="text-center align-middle">Satuan</th>
                    <th class="text-center align-middle">Harga Satuan</th>
                    <th class="text-center align-middle">Biaya Tambahan</th>
                    <th class="text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->rekap as $d)
                <tr>
                    @if ($d->bahan_baku)
                    <td class="text-center align-middle">
                        {{$d->bahan_baku->kategori->nama}}
                    </td>
                    @elseif($d->kemasan)
                    <td class="text-center align-middle">
                        {{$d->kemasan->product->nama}}
                    </td>
                    @elseif($d->kemasan)
                    <td class="text-center align-middle">
                        {{$d->kemasan->product->nama}}
                    </td>
                    @elseif($d->packaging)

                    @endif

                    <td class="text-center align-middle">
                        {{$d->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->nf_jumlah}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->satuan->nama}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_harga}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_add_fee}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle"@if ($data->rekap[0]->bahan_baku) colspan="2" @endif>SUBTOTAL</th>
                    <th class="text-center align-middle">{{
                        number_format($data->rekap->sum('jumlah'), 0, ',','.')}}</th>
                    <th class="text-center align-middle"></th>
                    <th class="text-end align-middle">{{
                        number_format($data->rekap->sum('harga'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{
                        number_format($data->rekap->sum('add_fee'), 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{
                        number_format($data->rekap->sum('total'), 0, ',','.')}}</th>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="6">Total DPP</td>
                    <td class="text-end align-middle" id="tdTotal">
                        {{number_format($data->rekap->sum('total') + $data->rekap->sum('add_fee'), 0, ',','.')}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="6">Diskon</td>
                    <td class="text-end align-middle" id="tdDiskon">
                        {{$data->nf_diskon}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="6">Total DPP Setelah Diskon</td>
                    <td class="text-end align-middle" id="tdTotalSetelahDiskon">
                        {{number_format($data->rekap->sum('total')-$data->diskon, 0, ',','.')}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="6">PPN</td>
                    <td class="text-end align-middle" id="tdPpn">
                        {{$data->nf_ppn}}
                    </td>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="6">Grand Total</th>
                    <th class="text-end align-middle" id="grand_total">
                        {{$data->nf_total}}
                    </th>
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
