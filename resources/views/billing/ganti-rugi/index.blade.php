@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>GANTI RUGI<br>BARANG HILANG</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('billing.ganti-rugi') }}" method="GET" class="form-inline">
                <div class="form-group mb-2">
                    <label for="karyawan" class="sr-only">Karyawan:</label>
                    <select name="karyawan" id="karyawan" class="form-control">
                        <option value="" disabled selected>-- Pilih Staff/Direksi --</option>
                        @foreach($karyawan as $sup)
                            <option value="{{ $sup->id }}" {{ request('karyawan') == $sup->id ? 'selected' : '' }}>{{ $sup->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Filter</button>
                <a href="{{ route('billing.ganti-rugi') }}" class="btn btn-secondary mb-2">Reset Filter</a>
            </form>
        </div>
    </div>
</div>
@include('billing.ganti-rugi.aksi')
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable" style="font-size: 10pt">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Nama</th>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merek<br>Barang</th>
                    <th class="text-center align-middle">Jumlah</th>
                    <th class="text-center align-middle">Satuan</th>
                    <th class="text-center align-middle">Harga Beli<br>PPN</th>
                    <th class="text-center align-middle">Harga Beli<br>NON PPN</th>
                    <th class="text-center align-middle">Total<br>Harga</th>
                    <th class="text-center align-middle">Total<br>Bayar</th>
                    <th class="text-center align-middle">Sisa</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                    <tr>
                        <td class="text-center align-middle">{{$loop->iteration}}</td>
                        <td class="text-start align-middle">{{$d->karyawan->nama}}</td>
                        <td class="text-center align-middle">{{$d->tanggal}}</td>
                        <td class="text-start align-middle">{{$d->barang_stok_harga->barang->barang_nama->nama}}</td>
                        <td class="text-center align-middle">{{$d->barang_stok_harga->barang->kode}}</td>
                        <td class="text-center align-middle">{{$d->barang_stok_harga->barang->merk}}</td>
                        <td class="text-center align-middle">{{$d->jumlah}}</td>
                        <td class="text-center align-middle">{{$d->barang_stok_harga->barang->satuan ? $d->barang_stok_harga->barang->satuan->nama : '-'}}</td>
                        <td class="text-end align-middle">
                            @if ($d->kas_ppn == 1)
                                {{$d->nf_harga}}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end align-middle">
                            @if ($d->kas_ppn == 0)
                                {{$d->nf_harga}}
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_total}}
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_total_bayar}}
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_sisa}}
                        </td>
                        <td class="text-center align-middle">
                            <div class="row m-1">
                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal"
                                data-bs-target="#bayarModal" onclick="bayar({{$d->id}})"><i class="fa fa-credit-card"></i> Bayar</button>
                            </div>
                            @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
                            <form action="{{route('billing.ganti-rugi.void', ['rugi' => $d])}}" method="post" id="voidForm{{ $d->id }}"
                                class="void-form m-3" data-id="{{ $d->id }}">
                                @csrf
                                <div class="row">
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-exclamation-circle"></i> Void</button>
                                </div>
                            </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="8">Grand Total : </th>
                    <th class="text-end align-middle">
                        {{number_format($data->where('kas_ppn', 1)->sum('harga'), 0, ',', '.')}}
                    </th>
                    <th class="text-end align-middle">
                        {{number_format($data->where('kas_ppn', 0)->sum('harga'), 0, ',', '.')}}
                    </th>
                    <th class="text-end align-middle">
                        {{number_format($data->sum('total'), 0, ',', '.')}}
                    </th>
                    <th class="text-end align-middle">
                        {{number_format($data->sum('total_bayar'), 0, ',', '.')}}
                    </th>
                    <th class="text-end align-middle">
                        {{number_format($data->sum('sisa'), 0, ',', '.')}}
                    </th>
                    <th class="text-center align-middle">

                    </th>
                </tr>
            </tfoot>

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

    function bayar(id) {
        document.getElementById('pembayaranForm').action = `{{route('billing.ganti-rugi.bayar', ':id')}}`.replace(':id', id);
    }

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#pembayaranForm').submit(function(e){
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


        $('.void-form').submit(function(e){
            e.preventDefault();
            var formId = $(this).data('id'); // Store a reference to the form

            Swal.fire({
                title: 'Apakah anda Yakin Ingin Melakukan Void? Masukkan Password Konfirmasi',
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: '{{route('pengaturan.password-konfirmasi-cek')}}',
                            type: 'POST',
                            data: JSON.stringify({ password: password }),
                            contentType: 'application/json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data) {
                                if (data.status === 'success') {
                                    resolve();
                                } else {
                                    // swal show error message\
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: data.message
                                    });
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: textStatus
                                    });
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#voidForm${formId}`).unbind('submit').submit();
                    $('#spinner').show();

                }
            });
        });

    });


</script>
@endpush
