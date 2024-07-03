@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL PPH MASA</u></h1>
            <h2>
            @php
                $monthNames = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
                ];
                echo $monthNames[$month];
            @endphp {{$year}}
            </h2>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap.pph-masa')}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30">
                        KEMBALI</a></td>
                </tr>
            </table>
        </div>
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
                        <a href="{{route('rekap.invoice-penjualan.detail', $d)}}" class="btn btn-outline-dark">
                            {{$d->invoice}}
                        </a>
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

    $(document).ready(function() {
        var table = $('#data-table').DataTable({
            "paging": false,
            "searching": true,
            "scrollCollapse": true,
            "scrollY": "500px",
            "info": false,

        });

        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    });

</script>
@endpush
