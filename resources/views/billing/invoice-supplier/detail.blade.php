@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL INVOICE SUPPLIER</u></h1>
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
        @if ($data->void == 0)
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
                @foreach ($data->items as $d)
                <tr>
                    <td class="text-center align-middle">
                        {{$d->barang->type->unit->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->type->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->barang->kategori->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->nama}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->nf_jumlah}}
                    </td>
                    <td class="text-center align-middle">
                        bh
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
                    <th class="text-end align-middle" colspan="7">Total DPP</th>
                    <th class="text-end align-middle">{{$data->dpp}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Diskon</th>
                    <th class="text-end align-middle">{{$data->nf_diskon}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Total DPP Setelah Diskon</th>
                    <th class="text-end align-middle">{{$data->dpp_setelah_diskon}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">PPN</th>
                    <th class="text-end align-middle">{{$data->nf_ppn}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Additional Fee</th>
                    <th class="text-end align-middle">{{$data->nf_add_fee}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Grand Total</th>
                    <th class="text-end align-middle">{{$data->nf_total}}</th>
                </tr>
                @if ($data->tempo == 1)
                <tr>
                    <th class="text-end align-middle" colspan="7">DP</th>
                    <th class="text-end align-middle">{{$data->nf_dp}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">DP PPN</th>
                    <th class="text-end align-middle">{{$data->nf_dp_ppn}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Sisa PPN</th>
                    <th class="text-end align-middle">{{$data->nf_sisa_ppn}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Sisa Tagian</th>
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
