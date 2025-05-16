@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PRE ORDER DETAIL<br>{{$order->konsumen->kode_toko->kode.' '.$order->konsumen->nama}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing.pre-order')}}"><img src="{{asset('images/back.svg')}}" alt="dashboard"
                                width="30"> Kembali</a></td>

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
            <thead class="table-primary">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Kelompok Barang</th>
                    <th class="text-center align-middle">Nama Barang</th>
                    <th class="text-center align-middle">Jumlah</th>
                    <th class="text-center align-middle">Satuan</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->detail as $a)
                <tr class="@if ($a->deleted)
                    table-danger
                @endif">
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-center align-middle">{{$a->barang->kategori->nama}}</td>
                    <td class="text-center align-middle">{{$a->barang->barang_nama->nama}}</td>
                    <td class="text-center align-middle">{{$a->nf_jumlah}}</td>
                    <td class="text-center align-middle">{{$a->barang->satuan->nama}}</td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-danger btn-sm" onclick="indenDelete({{$a->id}})"><i
                                class="fa fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="row mt-3">
        <form action="{{route('billing.pre-order.update', ['preorder' => $order->id])}}" method="post" id="storeForm">
            @csrf
            <div class="row px-5">
                <button class="btn btn-primary">
                   <i class="fa fa-save me-1"></i> Lanjutkan
                </button>
            </div>
        </form>
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
            "info": false,
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

    function indenDelete(id) {
        console.log(id);
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
                    url: '{{route('billing.pre-order.detail.delete', ['orderDetail' => ':id'])}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        console.log(data);
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

     $('#storeForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
            if (result.isConfirmed) {
                $('#spinner').show();
                this.submit();
            }
        })
    });

</script>
@endpush
