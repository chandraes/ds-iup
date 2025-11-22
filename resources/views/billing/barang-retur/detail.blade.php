@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>BARANG RETUR DETAIL</u></h1>
        </div>
    </div>
    <div class="row mb-3 d-flex">
        <div class="col-md-6">
            <a href="{{url()->previous()}}" class="btn btn-secondary"><i
                    class="fa fa-arrow-left"></i>
                Kembali</a>
        </div>
    </div>
    <div class="row">
        <div class="card">
            <div class="card-body bg-white">
                <h4 class="card-title">
                    {{-- <strong>#INVOICE : {{$invoice}}</strong> --}}
                </h4>
                <div class="row mt-3 mb-3">
                    <div class="col-md-12 my-3">
                        <div class="row" id="konsumenRow">
                            <div class="row invoice-info">
                                <div class="col-md-6 invoice-col">
                                    <table style="width: 90%">
                                        <tr style="height:50px">
                                            <td class="text-start align-middle">Kode</td>
                                            <td class="text-start align-middle" style="width: 10%">:</td>
                                            <td class="text-start align-middle">
                                                <input type="text" name="konsumen" id="konsumen" class="form-control"
                                                    value="{{ $data->kode  }}" disabled>
                                            </td>
                                        </tr>
                                        @if ($data->tipe == 2)
                                        <tr style="height:50px">
                                            <td class="text-start align-middle">Konsumen</td>
                                            <td class="text-start align-middle" style="width: 10%">:</td>
                                            <td class="text-start align-middle">
                                                <input type="text" name="konsumen" id="konsumen" class="form-control"
                                                    value="{{ $data->konsumen?->kode_toko->kode .' '. $data->konsumen?->nama ?? '' }}"
                                                    disabled>
                                            </td>
                                        </tr>
                                        @endif

                                        <tr id="namaTr" hidden style="height:50px">
                                            <td class="text-start align-middle">Nama</td>
                                            <td class="text-start align-middle" style="width: 10%">:</td>
                                            <td class="text-start align-middle">
                                                <input type="text" name="nama" id="nama" class="form-control">
                                            </td>
                                        </tr>


                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-success">
                            <tr>
                                <th class="text-center align-middle">No</th>
                                <th class="text-center align-middle">Nama Barang</th>
                                <th class="text-center align-middle">Merek</th>
                                <th class="text-center align-middle">Qty</th>
                                <th class="text-center align-middle">Sat</th>
                                <th class="text-center align-middle">PPN</th>
                                <th class="text-center align-middle">Non<br>PPN</th>
                                {{-- <th class="text-center align-middle">Act</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data->details as $b)
                            <tr class="{{$b->stok_kurang == 1 ? 'table-danger' : ''}}">
                                <td class="text-center align-middle">{{$loop->iteration}}</td>
                                <td class="text-start align-middle">
                                    {{$b->barang->barang_nama->nama}}, {{$b->barang->kode}}
                                </td>
                                <td class="text-start align-middle">
                                    {{$b->barang->merk}}
                                </td>
                                <td class="text-center align-middle">
                                    {{$b->nf_qty}}
                                </td>
                                <td class="text-center align-middle">
                                    {{$b->barang->satuan ? $b->barang->satuan->nama
                                    : '-'}}
                                </td>
                                <td class="text-center align-middle">
                                    @if ($b->barang->jenis == 1)
                                    <i class="fa fa-check text-success"></i>
                                    @endif
                                </td>
                                <td class="text-center align-middle">
                                    @if ($b->barang->jenis == 2)
                                    <i class="fa fa-check text-success"></i>
                                    @endif
                                </td>
                                {{-- <td class="text-center align-middle">
                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="deleteKeranjang({{$b->id}})"><i class="fa fa-trash"
                                            style="font-size: 1rem"></i></button>
                                </td> --}}
                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
                <div class="row ">
                    <div class="col-md-6"></div>
                    <div class="col-md-6 text-end">
                        {{-- <button type="submit" class="btn btn-success"><i class="fa fa-arrow-right"></i>
                            Lanjutkan</button> --}}
                        {{-- <button type="button" class="btn btn-info text-white"
                            onclick="javascript:window.print();"><i class="fa fa-print"></i> Print Invoice</button>
                        --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
{{-- <script src="{{asset('assets/js/cleave.min.js')}}"></script> --}}
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
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

    function deleteKeranjang(id)
    {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('billing.form-barang-retur.detail.preview.delete')}}',
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        location.reload();
                    }
                });
            }
        })
    }


</script>
@endpush
