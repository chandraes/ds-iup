@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PRE ORDER</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>

                </tr>
            </table>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>


</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable" style="width: 100%">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Karyawan</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Jumlah Barang</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @php
                $sumCicilan = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->karyawan->nama}}</td>
                    <td class="text-center align-middle">{{$d->konsumen->kode_toko->kode.' '.$d->konsumen->nama}}</td>
                    <td class="text-center align-middle">{{$d->jumlah}}</td>
                    <td class="text-end align-middle text-nowrap">
                        <div class="row px-3 pb-2">
                            <a href="{{route('billing.pre-order.detail', ['preorder' => $d->id])}}" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-file me-1"></i> Detail</a>
                        </div>
                        <div class="row px-3 pb-2">
                            <button type="button" class="btn btn-success btn-sm" onclick="finishOrder({{$d->id}})"><i class="fa fa-check me-1"></i> Selesai</button>
                        </div>
                        <div class="row px-3">
                            <button type="button" class="btn btn-danger btn-sm" onclick="voidOrder({{$d->id}})"><i class="fa fa-exclamation-circle me-1"></i> Void</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
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


    });

    function voidOrder(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Saya  Yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('billing.pre-order.void', ['preorder' => ':id'])}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {

                        if (data.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        }

                    }
                });
            }
        })
    }

    function finishOrder(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Saya  Yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('billing.pre-order.finish', ['preorder' => ':id'])}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {

                        if (data.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        }

                    }
                });
            }
        })
    }



</script>
@endpush
