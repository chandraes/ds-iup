@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP KAS PROJECT</h2>
        <h2>{{$stringBulanNow}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <table>
        <tr>
            <th>Nama Project</th>
            <th style="width: 1rem">:</th>
            <th>{{$project->nama}}</th>
        </tr>
        <tr>
            <th>Nomor Kontrak</th>
            <th>:</th>
            <th>{{$project->nomor_kontrak}}</th>
        </tr>
        <tr>
            <th>Nilai Kontrak</th>
            <th>:</th>
            <th>Rp {{$project->nf_nilai}}</th>
        </tr>
    </table>
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf" id="rekapTable">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf">Tanggal</th>
                <th class="text-center align-middle table-pdf text-pdf">Uraian</th>
                <th class="text-center align-middle table-pdf text-pdf">Masuk</th>
                <th class="text-center align-middle table-pdf text-pdf">Keluar</th>
                <th class="text-center align-middle table-pdf text-pdf">Sisa</th>
                <th class="text-center align-middle table-pdf text-pdf">Transfer Ke Rekening</th>
                <th class="text-center align-middle table-pdf text-pdf">Bank</th>
            </tr>
            <tr class="table-warning">
                <td colspan="3" class="text-center align-middle table-pdf text-pdf">Sisa Bulan
                    {{$stringBulan}} {{$tahunSebelumnya}}</td>
                <td class="table-pdf text-pdf"></td>
                <td class="text-end align-middle table-pdf text-pdf table-pdf text-pdf">Rp. {{$dataSebelumnya ? $dataSebelumnya->nf_sisa : ''}}</td>
                <td class="table-pdf text-pdf"></td>
                <td class="table-pdf text-pdf"></td>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->tanggal}}</td>
                    <td class="text-start align-middle table-pdf text-pdf">
                        @if ($d->invoice_tagihan_id)
                        <a href="{{route('rekap.kas-besar.detail-tagihan', ['invoice' => $d->invoice_tagihan_id])}}">{{$d->uraian}}</a>
                        @elseif($d->invoice_bayar_id)
                        <a href="{{route('rekap.kas-besar.detail-bayar', ['invoice' => $d->invoice_bayar_id])}}">{{$d->uraian}}</a>
                        @else
                        {{$d->uraian}}
                        @endif

                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">{{$d->jenis === 1 ?
                       $d->nf_nominal : ''}}
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf text-danger">
                        {{$d->jenis === 0 ?
                        $d->nf_nominal : ''}}
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">{{$d->nf_sisa}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->nama_rek}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->bank}}</td>
                </tr>
                @endforeach

            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf" colspan="2"><strong>GRAND TOTAL</strong></td>
                    <td class="text-end align-middle table-pdf text-pdf"><strong>{{number_format($data->where('jenis',
                            1)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    <td class="text-end align-middle table-pdf text-pdf text-danger" ><strong >{{number_format($data->where('jenis',
                            0)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    {{-- latest saldo --}}
                    <td class="text-end align-middle table-pdf text-pdf">
                        <strong>
                            {{$data->last() ? number_format($data->last()->sisa, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
