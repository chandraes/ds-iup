@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>REKAP PPN</u></h1>
            {{-- <h1>{{$stringBulanNow}} {{$tahun}}</h1> --}}
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/pajak.svg')}}" alt="dokumen"
                                width="30">
                            PAJAK</a></td>
                </tr>
            </table>
        </div>

    </div>
</div>
<div class="container table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">Masuk</th>
                    <th class="text-center align-middle">Keluar</th>
                    <th class="text-center align-middle">Saldo</th>
                </tr>
            </thead>
            <tbody></tbody>
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
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "400px",
            // default order column 1
            "order": [
                [1, 'asc']
            ],
            // "rowCallback": function(row, data, index) {
            //     // Update the row number
            //     $('td:eq(0)', row).html(index + 1);
            // }

        });
    });
</script>
@endpush
