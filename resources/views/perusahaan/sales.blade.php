@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>SALES</u></h1>
        </div>
    </div>
    @include('swal')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive ">
    <table class="table table-bordered" id="dataTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle" style="width: 15px">No</th>
                <th class="text-center align-middle">Nama</th>
                <th class="text-center align-middle">Panggilan</th>
                <th class="text-center align-middle">Jabatan</th>
                <th class="text-center align-middle">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-start align-middle">{{$d->nama}}</td>
                    <td class="text-start align-middle">{{$d->nickname}}</td>
                    <td class="text-center align-middle">{{$d->jabatan->nama}}</td>
                    <td class="text-center align-middle">
                        @if ($d->status == 1)
                        <h4><span class="badge bg-success text-white">Aktif</span></h4>
                        @elseif($d->status == 0)
                        <h4><span class="badge bg-danger text-white">Tidak Aktif</span></h4>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>

        </tfoot>
    </table>
</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>

    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

    } );

</script>
@endpush
