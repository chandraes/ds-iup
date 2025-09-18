@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BARANG RETUR</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>

                </tr>
            </table>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>


</div>
<div class="container table-responsive ml-3">

    <form method="GET" action="{{ url()->current() }}">
        {{-- <input type="hidden" name="kas_ppn" value="{{ request()->get('kas_ppn') }}"> --}}
        <div class="row text-end">
            {{-- <div class="col-md-3">
                <select class="form-select" name="karyawan_id" id="karyawan_id" onchange="this.form.submit()">
                    <option value="" selected>-- Semua Karyawan --</option>
                    @foreach ($karyawan as $k)
                    <option value="{{$k->id}}" @if (request()->get('karyawan_id') == $k->id) selected
                        @endif>{{$k->nama}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" name="kelompok_rute" id="kelompok_rute" onchange="this.form.submit()">
                    <option value="" selected>-- Semua Kelompok Rute --</option>
                    @foreach ($kelompokRute as $kel)
                    <option value="{{$kel->id}}" @if (request()->get('kelompok_rute') == $kel->id) selected
                        @endif>{{$kel->nama}}</option>
                    @endforeach
                </select>
            </div> --}}
        </div>
    </form>
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable" style="width: 100%; font-size: 0.8rem;">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Supplier</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Status</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @php
                $sumCicilan = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal_en}}</td>
                    <td class="text-start align-middle">{{$d->barang_unit->nama}}</td>
                    <td class="text-center align-middle">{{$d->konsumen ? $d->konsumen->kode_toko->kode : ''}}
                        {{$d->konsumen?->nama}}</td>
                    <td class="text-center align-middle">
                        {{-- 1 diajukan, 2 diproses, 3 selesai --}}
                        <h5>
                            @if ($d->status == 1)
                            <span class="badge bg-warning">Diajukan</span>
                            @elseif ($d->status == 2)
                            <span class="badge bg-primary">Diproses</span>
                            @elseif ($d->status == 3)
                            <span class="badge bg-success">Selesai</span>
                            @elseif ($d->status == 4)
                            <span class="badge bg-danger">Dibatalkan</span>
                            @endif
                        </h5>
                    </td>
                    <td class="text-end align-middle text-nowrap">
                        <div class="row p-2">
                            <a href="{{route('billing.barang-retur.detail', ['retur' => $d->id])}}" target="_blank"
                                class="btn btn-primary btn-sm"><i class="fa fa-file me-1"></i> Detail</a>
                        </div>
                        {{-- <div class="row p-2">
                            <button type="button" onclick="lanjutkanOrder({{$d->id}})" class="btn btn-success btn-sm"><i
                                    class="fa fa-credit-card me-1"></i> Lanjutkan</button>
                        </div>
                        <div class="row p-2">
                            <button type="button" class="btn btn-danger btn-sm" onclick="voidOrder({{$d->id}})"><i
                                    class="fa fa-exclamation-circle me-1"></i> Void</button>
                        </div> --}}
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

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
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
                    url: '{{route('billing.sales-order.void', ':id')}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        id: id,
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
                            });
                        }
                    }
                });
            }
        })
    }

    function lanjutkanOrder(id)
    {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Saya  Yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                // redirect to route
                window.location.href = '{{route('billing.sales-order.lanjutkan', ['order' => ':id'])}}'.replace(':id', id);
            }
        })
    }

</script>
@endpush
