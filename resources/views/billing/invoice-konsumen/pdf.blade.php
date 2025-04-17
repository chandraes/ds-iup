@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>INVOICE KONSUMEN {{$stringKas}} {{Str::upper($stringTitipan)}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf">No</th>
                    <th class="text-center align-middle text-pdf table-pdf">Tanggal</th>
                    <th class="text-center align-middle text-pdf table-pdf">Konsumen</th>
                    <th class="text-center align-middle text-pdf table-pdf">Nota</th>
                    <th class="text-center align-middle text-pdf table-pdf">Nilai <br>DPP</th>
                    <th class="text-center align-middle text-pdf table-pdf">Diskon</th>
                    <th class="text-center align-middle text-pdf table-pdf">PPn</th>
                    <th class="text-center align-middle text-pdf table-pdf">Penyesuaian</th>
                    <th class="text-center align-middle text-pdf table-pdf">Total <br>Belanja</th>
                    <th class="text-center align-middle text-pdf table-pdf">DP</th>
                    <th class="text-center align-middle text-pdf table-pdf">DP <br>PPN</th>
                    <th class="text-center align-middle text-pdf table-pdf">Cicilan</th>
                    <th class="text-center align-middle text-pdf table-pdf">Sisa <br>PPN</th>
                    <th class="text-center align-middle text-pdf table-pdf">Sisa <br>Tagihan</th>
                    <th class="text-center align-middle text-pdf table-pdf">Jatuh <br>Tempo</th>
                </tr>
            </thead>
            <tbody>
                @php
                $sumCicilan = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle text-pdf table-pdf">{{$loop->iteration}}</td>
                    <td class="text-center align-middle text-pdf table-pdf">{{$d->tanggal}}</td>
                    <td class="text-center align-middle text-pdf table-pdf">{{$d->konsumen->nama}}</td>
                    <td class="text-center align-middle text-pdf table-pdf">
                        {{$d->kode}}
                    </td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->dpp}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->nf_diskon}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->nf_ppn}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->nf_add_fee}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->nf_grand_total}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->nf_dp}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->nf_dp_ppn}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">
                        @if ($d->invoice_jual_cicil && $d->invoice_jual_cicil->count() > 0)
                        {{number_format($d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn'), 0, ',', '.')}}
                        @php
                        $sumCicilan += $d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn');
                        @endphp
                        @else
                        0
                        @endif
                    </td>
                    <td class="text-end align-middle text-pdf table-pdf {{$d->ppn_dipungut ? '' : 'table-danger'}}">{{$d->nf_sisa_ppn}}
                    </td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->nf_sisa_tagihan}}</td>
                    <td class="text-end align-middle text-pdf table-pdf">{{$d->id_jatuh_tempo}}</td>
                </tr>
                @endforeach

            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle text-pdf table-pdf" colspan="4">Grand Toal</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('diskon'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('add_fee'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('grand_total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('dp'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('dp_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($sumCicilan, 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('sisa_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf">{{number_format($data->sum('sisa_tagihan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle text-pdf table-pdf"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
