@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Detail Invoice</u></h1>
        </div>
    </div>
    <div class="row mb-3 d-flex">
        <div class="col-md-6">
            <a href="{{route('billing.invoice-jual')}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i>
                Kembali</a>
        </div>
    </div>
    <div class="row">

        <div class="card">

            <div class="card-body">
                <h4 class="card-title">
                    <strong>#INVOICE : {{$invoice->invoice}}</strong>
                </h4>
                <div class="row mt-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-group row">
                            <label class="col-form-label col-md-3">Konsumen :</label>
                            <div class="col-md-8">
                                <input type="text" readonly class="form-control" value="{{$invoice->konsumen->nama}}">
                            </div>
                        </div>
                        <div class="form-group row mt-2">
                            <label class="col-form-label col-md-3">Alamat :</label>
                            <div class="col-md-8">
                                <textarea name="alamat" id="alamat" class="form-control"
                                    readonly>{{$invoice->konsumen->alamat}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center align-middle">No</th>
                                <th class="text-center align-middle">Kategori</th>
                                <th class="text-center align-middle">Jenis Barang</th>
                                <th class="text-center align-middle">Qty</th>
                                <th class="text-center align-middle">Satuan</th>
                                <th class="text-center align-middle">Harga Satuan</th>
                                <th class="text-center align-middle">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedData as $group)
                            @php
                            $i = 0;
                            $total = 0;
                            $qty = 0;
                            @endphp
                            @foreach($group as $d)
                            <tr>
                                @if($i++ == 0)
                                <td class="text-center align-middle" rowspan="{{ count($group) }}">
                                    {{$loop->iteration}}
                                </td>
                                <td class="text-center align-middle" rowspan="{{ count($group) }}">
                                    {{$d->product_jadi->product->kategori->nama}}</td>
                                @endif
                                <td class="text-center align-middle">
                                    {{$d->product_jadi->product->nama}}
                                </td>
                                <td class="text-center align-middle">
                                    {{$d->jumlah}}
                                </td>
                                <td class="text-center align-middle">
                                    @if ($d->product_jadi->kemasan->packaging)
                                    {{$d->product_jadi->kemasan->packaging->nama}}
                                    @else
                                    {{$d->product_jadi->kemasan->nama}}
                                    @endif
                                </td>
                                <td class="text-end align-middle">
                                    {{$d->nf_harga}}
                                </td>
                                <td class="text-end align-middle">
                                    {{$d->nf_total}}
                                    @php
                                    $total += $d->total;
                                    $qty += $d->jumlah;
                                    @endphp
                                </td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-end align-middle">TOTAL</th>
                                <th class="text-center align-middle">{{number_format($qty, 0, ',','.')}}</th>
                                <th></th>
                                <th></th>
                                <th class="text-end align-middle">{{number_format($total, 0, ',','.')}}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end align-middle">Ppn :</th>
                                <th class="text-end align-middle">{{number_format(($invoice->ppn), 0, ',','.')}}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end align-middle">Pph :</th>
                                <th class="text-end align-middle">{{number_format(($invoice->pph), 0, ',','.')}}</th>
                            </tr>
                            <tr>
                                <th colspan="6" class="text-end align-middle">Grand Total :</th>
                                <th class="text-end align-middle">
                                    {{number_format(($invoice->total+$invoice->ppn-$invoice->pph), 0, ',','.')}}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>


</script>
@endpush
