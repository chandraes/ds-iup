@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>{{$kategori->nama}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('inventaris.index')}}"><img src="{{asset('images/inventaris.svg')}}"
                                alt="dokumen" width="30">
                            INVENTARIS</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Jenis</th>
                    <th class="text-center align-middle">Jumlah</th>
                    <th class="text-center align-middle">Total Harga</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $total = 0;
                @endphp
                @foreach ($data->jenis as $d)
                <tr>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle">
                        <div class="row px-3">
                            <a href="{{route('inventaris.detail.jenis', ['jenis' => $d, 'kategori' => $kategori])}}" class="btn btn-outline-dark">{{$d->nama}}</a>
                        </div>
                    </td>
                    <td class="text-center align-middle">{{$d->nf_sum_jumlah}}</td>
                    <td class="text-end align-middle">{{number_format($d->rekap->sum('total'), 0, ',', '.')}}</td>
                    @php
                        $total += $d->rekap->sum('total');
                    @endphp
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="3">Grand Total : </th>
                    <th class="text-end align-middle">{{number_format($total, 0, ',', '.')}}</th>
                </tr>
            </tfoot>
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
        var table = $('#rekapTable').DataTable({
            "paging": false,
            "info": true,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
            "scrollX": true,
            "autoWidth": false,
        });

        // Add row number in column 0
        table.on('order.dt search.dt', function() {
            table.column(0, {
                search: 'applied',
                order: 'applied'
            }).nodes().each(function(cell, i) {
                cell.innerHTML = i + 1;
            });
        }).draw();

    });


</script>
@endpush
