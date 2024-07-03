@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>PPH MASA</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30">
                        Back</a></td>
                </tr>
            </table>
        </div>
        <form action="{{route('rekap.pph-masa')}}" method="get" class="col-md-6">
            <div class="row mt-2">
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="tahun" id="tahun">
                        @foreach ($dataTahun as $dt)
                        <option value="{{$dt->tahunArray}}" {{$dt->tahunArray == $tahun ? 'selected' : ''}}>{{$dt->tahunArray}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover" id="data-table">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Bulan</th>
                    <th class="text-center align-middle">Nilai DPP</th>
                    <th class="text-center align-middle">Nilai PPh</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle" style="width: 5%"></td>
                    <td class="text-center align-middle">
                        <div class="row px-4">
                            <a href="{{route('rekap.pph-masa.detail', ['month' => $d['bulan_angka'], 'year' => $tahun])}}" class="btn btn-outline-dark">
                               <strong>{{$d['bulan']}}</strong>
                            </a>
                        </div>

                    </td>
                    <td class="text-end align-middle">
                        {{number_format($d['total_dpp'], 0, ',','.')}}
                    </td>
                    <td class="text-end align-middle">
                        {{number_format($d['total_pph'], 0, ',','.')}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="2">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($grandTotalDpp,0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($grandTotalPph,0,',','.')}}</th>
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
