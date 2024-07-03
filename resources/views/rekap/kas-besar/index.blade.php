@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>REKAP KAS BESAR</u></h1>
            <h1>{{$stringBulanNow}} {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                    <td>
                        <a href="{{route('rekap.kas-besar.print', ['bulan' => $bulan, 'tahun' => $tahun])}}" target="_blank"><img src="{{asset('images/print.svg')}}" alt="dokumen"
                            width="30"> PRINT PDF</a>
                    </td>
                </tr>
            </table>
        </div>
        <form action="{{route('rekap.kas-besar')}}" method="get" class="col-md-6">
            <div class="row mt-2">
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="bulan" id="bulan">
                        <option value="1" {{$bulan=='01' ? 'selected' : '' }}>Januari</option>
                        <option value="2" {{$bulan=='02' ? 'selected' : '' }}>Februari</option>
                        <option value="3" {{$bulan=='03' ? 'selected' : '' }}>Maret</option>
                        <option value="4" {{$bulan=='04' ? 'selected' : '' }}>April</option>
                        <option value="5" {{$bulan=='05' ? 'selected' : '' }}>Mei</option>
                        <option value="6" {{$bulan=='06' ? 'selected' : '' }}>Juni</option>
                        <option value="7" {{$bulan=='07' ? 'selected' : '' }}>Juli</option>
                        <option value="8" {{$bulan=='08' ? 'selected' : '' }}>Agustus</option>
                        <option value="9" {{$bulan=='09' ? 'selected' : '' }}>September</option>
                        <option value="10" {{$bulan=='10' ? 'selected' : '' }}>Oktober</option>
                        <option value="11" {{$bulan=='11' ? 'selected' : '' }}>November</option>
                        <option value="12" {{$bulan=='12' ? 'selected' : '' }}>Desember</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="tahun" id="tahun">
                        @foreach ($dataTahun as $d)
                        <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                </div>
                {{-- <div class="col-md-3 mb-3">
                    <label for="showPrint" class="form-label">&nbsp;</label>
                    <a href="{{route('rekap.kas-besar.preview', ['bulan' => $bulan, 'tahun' => $tahun])}}" target="_blank" class="btn btn-secondary form-control" id="btn-cari">Print Preview</a>
                </div> --}}
            </div>
        </form>
    </div>
</div>
<div class="container-fluid mt-5">

</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle">TGL</th>
                <th class="text-center align-middle">URAIAN</th>
                <th class="text-center align-middle">DEPOSIT</th>
                <th class="text-center align-middle">KAS<br>KECIL</th>
                <th class="text-center align-middle">BELI<br>BAHAN</th>
                <th class="text-center align-middle">MASUK</th>
                <th class="text-center align-middle">KELUAR</th>
                <th class="text-center align-middle">SALDO</th>
                <th class="text-center align-middle">TRANSFER KE<br>REKENING</th>
                <th class="text-center align-middle">BANK</th>
                <th class="text-center align-middle">MODAL<br>INVESTOR</th>
            </tr>
            <tr class="table-warning">
                <td colspan="6" class="text-center align-middle">Saldo Bulan
                    {{$stringBulan}} {{$tahunSebelumnya}}</td>
                <td></td>
                <td class="text-end align-middle">Rp. {{$dataSebelumnya ? $dataSebelumnya->nf_saldo : ''}}</td>
                <td></td>
                <td></td>
                <td class="text-end align-middle">Rp. {{$dataSebelumnya ?
                    number_format($dataSebelumnya->modal_investor_terakhir, 0,',','.') : ''}}</td>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-start align-middle">
                        @if ($d->invoice_tagihan_id)
                        <a href="{{route('rekap.kas-besar.detail-tagihan', ['invoice' => $d->invoice_tagihan_id])}}">{{$d->uraian}}</a>
                        @elseif($d->invoice_bayar_id)
                        <a href="{{route('rekap.kas-besar.detail-bayar', ['invoice' => $d->invoice_bayar_id])}}">{{$d->uraian}}</a>
                        @else
                        {{$d->uraian}}
                        @endif
                    </td>
                    <td class="text-center align-middle">{{$d->kode_deposit}}</td>
                    <td class="text-center align-middle">{{$d->kode_kas_kecil}}</td>
                    <td class="text-center align-middle">
                        @if ($d->invoice_belanja_id)
                        <a href="{{route('rekap.invoice-belanja.detail', ['invoice' => $d->invoice_belanja_id])}}">
                            {{$d->invoice_belanja->kode}}
                        </a>
                        {{-- <a href="{{route('rekap.kas-besar.detail-belanja', ['invoice' => $d->invoice_belanja_id])}}">{{$d->invoice_belanja->kode}}</a> --}}

                        @endif
                    </td>
                    <td class="text-end align-middle">{{$d->jenis === 1 ?
                       $d->nf_nominal : ''}}
                    </td>
                    <td class="text-end align-middle text-danger">{{$d->jenis === 0 ?
                        $d->nf_nominal : ''}}
                    </td>
                    <td class="text-end align-middle">{{$d->nf_saldo}}</td>
                    <td class="text-start align-middle">{{ Str::limit($d->nama_rek, 15) }}</td>
                    <td class="text-center align-middle">{{$d->bank}}</td>
                    <td class="text-end align-middle">{{$d->nf_modal_investor}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>

                <tr>
                    <td class="text-center align-middle" colspan="5"><strong>GRAND TOTAL</strong></td>
                    <td class="text-end align-middle"><strong>{{number_format($data->where('jenis',
                            1)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    <td class="text-end align-middle text-danger"><strong>{{number_format($data->where('jenis',
                            0)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    {{-- latest saldo --}}
                    <td class="text-end align-middle">
                        <strong>
                            {{$data->last() ? $data->last()->nf_saldo : ''}}
                        </strong>
                    </td>
                    <td></td>
                    <td></td>
                    <td class="text-end align-middle">
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
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<style>
    .dataTables_wrapper {
        width: 100%;
    }
</style>
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "info": true,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
            "scrollX": true,
            "autoWidth": false,
        });


    });

</script>
@endpush
