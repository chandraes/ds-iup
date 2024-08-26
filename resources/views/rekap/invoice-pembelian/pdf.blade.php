@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>INVOICE PEMBELIAN {{$ppn_kas == 1 ? "PPN" : "NON PPN" }}</h2>
        <h2>{{$stringBulanNow}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf">Tanggal</th>
                    <th class="text-center align-middle table-pdf text-pdf">Supplier</th>
                    <th class="text-center align-middle table-pdf text-pdf">Nota</th>
                    <th class="text-center align-middle table-pdf text-pdf">Uraian</th>
                    <th class="text-center align-middle table-pdf text-pdf">Nilai<br>DPP</th>
                    <th class="text-center align-middle table-pdf text-pdf">Diskon</th>
                    @if ($ppn_kas == 1)
                    <th class="text-center align-middle table-pdf text-pdf">PPn</th>
                    @endif
                    <th class="text-center align-middle table-pdf text-pdf">Penyesuaian</th>
                    <th class="text-center align-middle table-pdf text-pdf">Total<br>Belanja</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->tanggal}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->supplier->nama}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">
                            {{$d->kode}}
                    </td>
                    <td class="text-start align-middle table-pdf text-pdf">{{$d->uraian}}</td>
                    <td class="text-end align-middle table-pdf text-pdf">
                        {{$d->dpp}}
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">
                        {{$d->nf_diskon}}
                    </td>
                    @if ($ppn_kas == 1)
                    <td class="text-end align-middle table-pdf text-pdf">
                        {{$d->nf_ppn}}
                    </td>
                    @endif
                    <td class="text-end align-middle table-pdf text-pdf">
                        {{$d->nf_add_fee}}
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">
                        {{$d->nf_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle table-pdf text-pdf" colspan="4">Grand Toal</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('total')-$data->sum('ppn')+$data->sum('diskon'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('diskon'), 0, ',', '.')}}</th>
                    @if ($ppn_kas == 1)
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('ppn'), 0, ',', '.')}}</th>
                    @endif
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('add_fee'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle table-pdf text-pdf">{{number_format($data->sum('total'), 0, ',', '.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
