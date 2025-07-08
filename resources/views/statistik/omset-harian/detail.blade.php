@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL OMSET HARIAN {{$karyawan->nama}}<br>{{ \Carbon\Carbon::parse(request('tanggal'))->format('d-m-Y') }}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-8">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('statistik.omset-harian-sales')}}"><img src="{{asset('images/back.svg')}}" alt="dashboard"
                                width="30"> Kembali</a></td>

                </tr>
            </table>
        </div>

    </div>
</div>
<div class="container table-responsive mt-2">
    <div class="row">
        <table class="table table-hover table-bordered" id="rekapTable" style="width: 100%">
            <thead>
                <tr class="table-success">
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Kode<br>Toko</th>
                    <th class="text-center align-middle">Nama Toko</th>
                    <th class="text-center align-middle">Kab/Kota</th>
                    <th class="text-center align-middle">Kecamatan</th>
                    <th class="text-center align-middle">Invoice</th>
                    <th class="text-center align-middle">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = 1;
                @endphp
                @foreach ($data['data'] as $d)
                <tr>
                    <td class="text-center align-middle">{{$no++}}</td>
                    <td class="text-center align-middle">{{$d->konsumen->kode_toko ? $d->konsumen->kode_toko->kode : ''}}</td>
                    <td class="text-center align-middle">{{$d->konsumen->nama}}</td>
                    <td class="text-center align-middle">{{$d->konsumen->kabupaten_kota->nama_wilayah}}</td>
                    <td class="text-center align-middle">{{$d->konsumen->kecamatan->nama_wilayah}}</td>
                    <td class="text-start align-middle">{{$d->kode}}</td>
                    <td class="text-end align-middle" data-order="{{$d->grand_total}}">{{$d->nf_grand_total}}</td>
                </tr>
                @endforeach
                 @foreach ($data['void'] as $v)
                <tr>
                    <td class="text-center align-middle text-danger">{{$no++}}</td>
                    <td class="text-center align-middle text-danger">{{$v->konsumen->kode_toko ? $v->konsumen->kode_toko->kode : ''}}</td>
                    <td class="text-center align-middle text-danger">{{$v->konsumen->nama}}</td>
                     <td class="text-center align-middle text-danger">{{$v->konsumen->kabupaten_kota->nama_wilayah}}</td>
                    <td class="text-center align-middle text-danger">{{$v->konsumen->kecamatan->nama_wilayah}}</td>
                    <td class="text-start align-middle text-danger">{{$v->kode}}</td>
                    <td class="text-end align-middle text-danger" data-order="{{$v->grand_total*-1}}">-{{$v->nf_grand_total}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="3">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($data['data']->sum('grand_total')+($data['void']->sum('grand_total')*-1), 0,',','.')}}</th>
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
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "60vh", // Set scrollY to 50% of the viewport height
            "scrollCollapse": true,
            "scrollX": true,

        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

    });
</script>
@endpush
