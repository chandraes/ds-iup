@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE PENJUALAN</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen" width="30">
                            REKAP</a></td>
                </tr>
            </table>
        </div>
        <form action="{{route('rekap.invoice-penjualan')}}" method="get" class="col-md-6">
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
                        @foreach ($dataTahun as $dt)
                        <option value="{{$dt->tahunArray}}" {{$dt->tahunArray == $tahun ? 'selected' : ''}}>{{$dt->tahunArray}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                </div>
                {{-- <div class="col-md-3 mb-3">
                    <label for="showPrint" class="form-label">&nbsp;</label>
                    <a href="{{route('rekap.kas-besar.preview', ['bulan' => $bulan, 'tahun' => $tahun])}}"
                        target="_blank" class="btn btn-secondary form-control" id="btn-cari">Print Preview</a>
                </div> --}}
            </div>
        </form>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover" id="data-table">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Invoice</th>
                    <th class="text-center align-middle">DPP</th>
                    <th class="text-center align-middle">PPn</th>
                    <th class="text-center align-middle">PPh</th>
                    <th class="text-center align-middle">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle">
                        {{$d->tanggal}}
                    </td>
                    <td class="text-start align-middle">
                        {{$d->konsumen->nama}}
                    </td>
                    <td class="text-center align-middle">
                        <div class="row px-3">
                            <a href="{{route('rekap.invoice-penjualan.detail', $d)}}" class="btn btn-outline-dark">
                                {{$d->invoice}}
                            </a>
                        </div>
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_total}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_ppn}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_pph}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_grand_total}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="4">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($data->sum('total'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('ppn'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('pph'),2,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('grand_total'),0,',','.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>

</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    function detailInvoice(data)
    {
        document.getElementById('detailModalTitle').innerHTML = 'INVOICE : '+data.invoice;
    }
    $(document).ready(function() {
        var table = $('#data-table').DataTable({
            "paging": false,
            "searching": true,
            "scrollCollapse": true,
            "scrollY": "500px",

        });

        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    });
</script>
@endpush
