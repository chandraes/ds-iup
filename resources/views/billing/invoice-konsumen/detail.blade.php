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
        <div class="col-md-8">
            <div class="form-group row mt-2">
                <label class="col-form-label col-md-3">Invoice :</label>
                <div class="col-md-8">
                    <input type="text" name="invoice" id="invoice" class="form-control" value="{{$data->kode}}"
                        disabled>
                </div>
            </div>
            <div class="form-group row mt-2">
                <label class="col-form-label col-md-3">Konsumen :</label>
                <div class="col-md-8">
                    <input type="text" name="konsumen" id="konsumen" class="form-control" value="{{$data->konsumen->nama}}"
                        disabled>
                </div>
            </div>
            <div class="form-group row mt-2">
                <label class="col-form-label col-md-3">Sistem Pembayaran :</label>
                <div class="col-md-8">
                    <input type="text" name="pembayaran" id="pembayaran" class="form-control" value="{{$data->konsumen->sistem_pembayaran}}"
                        disabled>
                </div>
            </div>
            <div class="form-group row mt-2">
                <label class="col-form-label col-md-3">Alamat :</label>
                <div class="col-md-8">
                    <textarea name="alamat" id="alamat" class="form-control" disabled>{{$data->konsumen->alamat}}</textarea>
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
                <tr>
                    <th class="text-end align-middle" colspan="7">DP</th>
                    <th class="text-end align-middle">{{$data->nf_dp}}</th>
                </tr>
                @if ($data->ppn > 0)
                <tr>
                    <th class="text-end align-middle" colspan="7">DP PPn</th>
                    <th class="text-end align-middle">{{$data->nf_dp_ppn}}</th>
                </tr>
                @endif
                <tr>
                    <th class="text-end align-middle" colspan="7">Sisa PPN</th>
                    <th class="text-end align-middle">{{$data->sisa_ppn}}</th>
                </tr>
                <tr>
                    <th class="text-end align-middle" colspan="7">Sisa Tagihan</th>
                    <th class="text-end align-middle">{{$data->sisa_tagihan}}</th>
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
