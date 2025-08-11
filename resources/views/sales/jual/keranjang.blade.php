@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>KERANJANG SALES ORDER</u></h1>
        </div>
    </div>
    <div class="row mb-3 d-flex">
        <div class="col-md-6">
            <a href="{{route('sales.jual.keranjang', $info->id)}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i>
                Kembali</a>
        </div>
    </div>
    <div class="row">
        <form action="{{route('sales.stok.keranjang.checkout')}}" method="post" id="storeForm">
            @csrf
            <input type="hidden" name="keranjang_jual_konsumen_id" value="{{ $info->id }}">
            <div class="card">
                <div class="card-body bg-white">
                    <h4 class="card-title">
                        {{-- <strong>#INVOICE : {{$invoice}}</strong> --}}
                    </h4>
                    <div class="row mt-3 mb-3">
                        <div class="row mb-3">
                            <div class="col-md-12 my-3">
                                Pre Order:
                            </div>
                            <div class="col-md-5 pt-1">
                                {{-- select barang nama and input jumlah --}}
                                <select class="form-select" name="barang_id" id="barang_id" onchange="setSatuan()">
                                    <option value="" disabled selected>-- Pilih Barang --</option>
                                    @foreach ($barang as $bInden)
                                    <option value="{{$bInden->id}}" data-satuan="{{$bInden->satuan->nama}}">{{$bInden->barang_nama->nama}} ({{$bInden->kode}}) ({{$bInden->merk}})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group mb-3">

                                    <input type="text" class="form-control" name="jumlah_inden" id="jumlah_inden" placeholder="Jumlah">
                                    <span class="input-group-text" id="satuan-inden">-</span>
                                  </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary" onclick="addKeranjangInden()"><i class="fa fa-plus"></i>
                                    Tambah</button>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-bordered table-hover">
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
                                    @foreach ($orderInden as $a)
                                    <tr>
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

                        <hr>
                        <div class="col-md-12 my-3">
                            <div class="row" id="konsumenRow">
                                <div class="row invoice-info">
                                    <div class="col-md-6 invoice-col">
                                        <table style="width: 90%">
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Konsumen</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <input type="text" name="konsumen" id="konsumen" class="form-control"
                                                        value="{{ $konsumen->kode_toko->kode .' '. $konsumen->nama ?? '' }}" disabled>
                                                </td>
                                            </tr>
                                            <tr id="namaTr" hidden style="height:50px">
                                                <td class="text-start align-middle">Nama</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <input type="text" name="nama" id="nama"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Sistem Pembayaran</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <input class="form-control" type="text" name="pembayaran" id="pembayaran" value="{{$info->sistem_pembayaran_word}}" disabled>
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Tempo</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="tempo_hari"
                                                            id="tempo_hari" disabled value="{{ $info->pembayaran != 1 ? $info->konsumen->tempo_hari : '-'}}">
                                                        <span class="input-group-text" id="basic-addon1">Hari</span>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Alamat</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <textarea name="alamat" id="alamat" class="form-control"
                                                        disabled>{{$info->konsumen->alamat }}, {{$info->konsumen->kecamatan->nama_wilayah}}
                                                    </textarea>
                                                </td>
                                            </tr>

                                        </table>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-md-6 invoice-col" >
                                        <div class="row d-flex justify-content-end">
                                            <table style="width: 90%">
                                                {{-- <tr style="height:50px">
                                                    <td class="text-start align-middle">Invoice</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="kode" id="kode" class="form-control"
                                                            disabled value="{{$kode}}">
                                                    </td>
                                                </tr> --}}
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">Tanggal</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="tanggal" id="tanggal" class="form-control"
                                                            value="{{$tanggal}}" disabled>
                                                    </td>
                                                </tr>
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">Jam</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="jam" id="jam" class="form-control"
                                                            value="{{$jam}}" disabled>
                                                    </td>
                                                </tr>
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">No WA</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="no_hp" id="no_hp" class="form-control" value="{{$info->konsumen->no_hp}}"
                                                            disabled>
                                                    </td>
                                                </tr>
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">NPWP</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="npwp" id="npwp" class="form-control"
                                                            disabled value="{{$info->konsumen->npwp}}">
                                                    </td>
                                                </tr>
                                                @if ($adaPpn == 1)
                                                <tr style="height:50px" hidden>
                                                    <td class="text-start align-middle">PPn Disetor Oleh</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <select class="form-select" name="dipungut" id="dipungut" required
                                                        onchange="ppnPungut()">
                                                            <option selected value="1">Sendiri</option>
                                                    </select>
                                                    </td>
                                                </tr>
                                                @endif
                                            </table>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <hr>
                    @if ($keranjang->where('barang_ppn' , 1)->count() > 0)
                    @include('sales.jual.table-ppn')
                    @endif
                    <hr>
                    @if ($keranjang->where('barang_ppn' , 0)->count() > 0)
                    @include('sales.jual.table-non-ppn')
                    @endif
                    <div class="row ">
                        <div class="col-md-6"></div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-success"><i class="fa fa-credit-card"></i>
                                Lanjutkan</button>
                            {{-- <button type="button" class="btn btn-info text-white"
                                onclick="javascript:window.print();"><i class="fa fa-print"></i> Print Invoice</button>
                            --}}
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

    var tempTempo = '-';

    $('#barang_id').select2({
        width: '100%',
    });

    // Format input angka dengan Cleave.js
    const cleaveOptions = {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.',
        negative: true
    };


    function setSatuan() {
        // Ambil elemen select dan span
        const selectElement = document.getElementById('barang_id');
        const satuanSpan = document.getElementById('satuan-inden');

        // Ambil opsi yang dipilih
        const selectedOption = selectElement.options[selectElement.selectedIndex];

        // Ambil data-satuan dari opsi yang dipilih
        const satuan = selectedOption.getAttribute('data-satuan');

        // Ganti innerText dari span dengan satuan
        satuanSpan.innerText = satuan || '-';
    }


    // Helper untuk menghapus format angka
    function parseNumber(value) {
        return parseFloat(value.replace(/\./g, '') || 0);
    }

    // Helper untuk memformat angka ke format Indonesia
    function formatNumber(value) {
        return value.toLocaleString('id-ID');
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
                    url: '{{route('sales.stok.keranjang.delete', ':id')}}'.replace(':id', id),
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

    function addKeranjangInden()
    {

        var barang_id = document.getElementById('barang_id').value;
        var jumlah = document.getElementById('jumlah_inden').value;

        if (barang_id == '' || barang_id == null) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Barang tidak boleh kosong!',
            });
            return;
        }

        if (jumlah == '' || jumlah == null) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Jumlah tidak boleh kosong!',
            });
            return;
        }

        $.ajax({
            url: '{{route('sales.stok.keranjang.inden.store')}}',
            type: 'POST',
            data: {
                barang_id: barang_id,
                jumlah: jumlah,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                // reset barang_id dan jumlah
                document.getElementById('barang_id').value = '';
                document.getElementById('jumlah_inden').value = '';
                location.reload();
            }
        });
    }

    function indenDelete(id)
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
                    url: '{{route('sales.stok.keranjang.inden.delete', ':id')}}'.replace(':id', id),
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
