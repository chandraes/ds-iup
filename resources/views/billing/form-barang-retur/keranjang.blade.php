@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mb-4"> <div class="col-md-12 text-center">
            <h1 class="display-6 fw-bold">Keranjang Form Retur</h1>
            <p class="lead text-muted">Periksa kembali barang retur Anda sebelum melanjutkan.</p>
        </div>
    </div>

    <div class="row mb-3 d-flex">
        <div class="col-md-6">
            <a href="{{route('billing.form-barang-retur.detail', $b->id)}}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-2"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <form action="{{route('billing.form-barang-retur.detail.lanjutkan', $b->id)}}" method="post" id="storeForm">
            @csrf
            <input type="hidden" name="barang_retur_id" value="{{ $b->id }}">

            <div class="card shadow-sm border-0"> <div class="card-body bg-white p-4"> <div class="row mt-3 mb-3">
                        <div class="col-md-12 my-3">
                            <div class="row" id="konsumenRow">
                                <div class="col-md-7">
                                    @if ($b->tipe == 2)
                                    <div class="mb-3">
                                        <label for="konsumen" class="form-label fw-bold">Konsumen</label>
                                        <input type="text" id="konsumen" class="form-control"
                                            value="{{ $konsumen->kode_toko->kode .' '. $konsumen->nama ?? '' }}"
                                            disabled style="background-color: #e9ecef; opacity: 1;">
                                    </div>
                                    @endif
                                    <div class="mb-3" id="namaTr" hidden>
                                        <label for="nama" class="form-label fw-bold">Nama</label>
                                        <input type="text" name="nama" id="nama" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4"> <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-start" style="min-width: 250px;">Nama Barang</th> <th class="text-start">Merek</th> <th class="text-center">Qty</th>
                                    <th class="text-center">Sat</th>
                                    <th class="text-center">PPN</th>
                                    <th class="text-center">Non<br>PPN</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($keranjang->where('barang_ppn', 0) as $b)
                                <tr class="{{$b->stok_kurang == 1 ? 'table-danger' : ''}}">
                                    <td class="text-center">{{$loop->iteration}}</td>
                                    <td class="text-start">
                                        {{$b->stok->barang_nama->nama}}, {{$b->stok->barang->kode}}
                                    </td>
                                    <td class="text-start">
                                        {{$b->stok->barang->merk}}
                                    </td>
                                    <td class="text-center">
                                        <strong>{{$b->nf_qty}}</strong>
                                    </td>
                                    <td class="text-center">
                                        {{$b->stok->barang->satuan ? $b->stok->barang->satuan->nama
                                        : '-'}}
                                    </td>
                                    <td class="text-center">
                                        @if ($b->stok->barang->jenis == 1)
                                        <i class="fa fa-check text-success"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($b->stok->barang->jenis == 2)
                                        <i class="fa fa-check text-success"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="deleteKeranjang({{$b->id}})">
                                            <i class="fa fa-trash" style="font-size: 1rem"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row mt-4"> <div class="col-md-6"></div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fa fa-arrow-right me-2"></i>
                                Lanjutkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <div class="col-md-6 text-end mt-2">
            @include('wa-status')
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
    // Semua skrip JS Anda (storeForm, deleteKeranjang) tidak diubah
    // dan akan tetap berfungsi seperti semula.

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
