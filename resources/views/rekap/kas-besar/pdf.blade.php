@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h2>REKAP KAS BESAR</h2>
        <h2>{{$stringBulanNow}} {{$tahun}}</h2>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf" id="rekapTable">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle table-pdf text-pdf">Tanggal</th>
                <th class="text-center align-middle table-pdf text-pdf">Uraian</th>
                <th class="text-center align-middle table-pdf text-pdf">Deposit</th>
                <th class="text-center align-middle table-pdf text-pdf">Kas Kecil</th>
                <th class="text-center align-middle table-pdf text-pdf">Beli<br>Bahan</th>
                <th class="text-center align-middle table-pdf text-pdf">Masuk</th>
                <th class="text-center align-middle table-pdf text-pdf">Keluar</th>
                <th class="text-center align-middle table-pdf text-pdf">Saldo</th>
                <th class="text-center align-middle table-pdf text-pdf">Transfer Ke Rekening</th>
                <th class="text-center align-middle table-pdf text-pdf">Bank</th>
                <th class="text-center align-middle table-pdf text-pdf">Modal Investor</th>
            </tr>
            <tr class="table-warning">
                <td colspan="6" class="text-center align-middle table-pdf text-pdf">Saldo Bulan
                    {{$stringBulan}} {{$tahunSebelumnya}}</td>
                <td class="table-pdf text-pdf"></td>
                <td class="text-end align-middle table-pdf text-pdf table-pdf text-pdf">Rp. {{$dataSebelumnya ? number_format($dataSebelumnya->saldo,
                    0, ',','.') : ''}}</td>
                <td class="table-pdf text-pdf"></td>
                <td class="table-pdf text-pdf"></td>
                <td class="text-end align-middle table-pdf text-pdf table-pdf text-pdf">Rp. {{$dataSebelumnya ?
                    number_format($dataSebelumnya->modal_investor_terakhir, 0,',','.') : ''}}</td>
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
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->kode_deposit}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->kode_kas_kecil}}</td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        @if ($d->invoice_belanja_id)
                            {{$d->invoice_belanja->kode}}
                        @endif
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">{{$d->jenis === 1 ?
                       $d->nf_nominal : ''}}
                    </td>
                    <td class="text-end align-middle table-pdf text-pdf text-danger">
                        <span style="color:red">
                            {{$d->jenis === 0 ?
                        $d->nf_nominal : ''}}
                        </span>

                    </td>
                    <td class="text-end align-middle table-pdf text-pdf">{{$d->nf_saldo}}</td>
                    <td class="text-start align-middle table-pdf text-pdf">{{ Str::limit($d->nama_rek, 15) }}</td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->bank}}</td>
                    <td class="text-end align-middle table-pdf text-pdf">{{number_format($d->modal_investor, 0, ',', '.')}}</td>
                </tr>
                @endforeach

            </tbody>
            <tfoot>
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf" colspan="5"><strong>GRAND TOTAL</strong></td>
                    <td class="text-end align-middle table-pdf text-pdf"><strong>{{number_format($data->where('jenis',
                            1)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    <td class="text-end align-middle table-pdf text-pdf text-danger" ><strong >{{number_format($data->where('jenis',
                            0)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    {{-- latest saldo --}}
                    <td class="text-end align-middle table-pdf text-pdf">
                        <strong>
                            {{$data->last() ? number_format($data->last()->saldo, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="table-pdf text-pdf"></td>
                    <td class="text-end align-middle table-pdf text-pdf">
                        <strong>
                            {{$data->last() ? number_format($data->last()->modal_investor_terakhir, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
