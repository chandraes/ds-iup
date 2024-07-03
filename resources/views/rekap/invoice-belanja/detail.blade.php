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
                    <th class="text-center align-middle">Nama Kimia</th>
                    <th class="text-center align-middle">Singkatan</th>
                    @endif
                    @if ($data->rekap[0]->kemasan)
                    <th class="text-center align-middle">Product</th>
                    <th class="text-center align-middle">Kemasan</th>
                    @elseif ($data->rekap[0]->packaging)
                    <th class="text-center align-middle">Packaging</th>
                    @endif
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
                    @endif
                    @if($d->kemasan)
                    <td class="text-center align-middle">
                        {{$d->kemasan->product->nama}}
                    </td>
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
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}">Total DPP</td>
                    <td class="text-end align-middle" id="tdTotal">
                        {{number_format($data->rekap->sum('total') + $data->rekap->sum('add_fee'), 0, ',','.')}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}"
                    >Diskon</td>
                    <td class="text-end align-middle" id="tdDiskon">
                        {{$data->nf_diskon}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >Total DPP Setelah Diskon</td>
                    <td class="text-end align-middle" id="tdTotalSetelahDiskon">
                        {{number_format($data->rekap->sum('total')-$data->diskon, 0, ',','.')}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >PPN</td>
                    <td class="text-end align-middle" id="tdPpn">
                        {{$data->nf_ppn}}
                    </td>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >Grand Total</th>
                    <th class="text-end align-middle" id="grand_total">
                        {{$data->nf_total}}
                    </th>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >DP</td>
                    <td class="text-end align-middle" id="tdPpn">
                        {{$data->nf_dp}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >DP PPN</td>
                    <td class="text-end align-middle" id="tdPpn">
                        {{$data->nf_dp_ppn}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >Total DP</td>
                    <td class="text-end align-middle" id="tdPpn">
                        {{$data->nf_total_dp}}
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >Sisa PPN</td>
                    <td class="text-end align-middle" id="tdPpn">
                        {{$data->nf_sisa_ppn}}
                    </td>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="{{$data->rekap[0]->packaging ? 5 : 6}}" >Sisa Tagihan</th>
                    <th class="text-end align-middle" id="grand_total">
                        {{$data->nf_sisa}}
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
