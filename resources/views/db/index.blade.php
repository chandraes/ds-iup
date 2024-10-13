@extends('layouts.app')

@section('content')
<div class="container text-center">
    <h1><u>DATABASE</u></h1>
</div>
<div class="container mt-5">
    <div class="row justify-content-left">
        <h2>Data Lama</h2>

        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.staff')}}" class="text-decoration-none">
                <img src="{{asset('images/karyawan.svg')}}" alt="" width="70">
                <h5 class="mt-2">BIODATA & GAJI<br>DIREKSI & STAFF</h5>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left mt-4">
        <h2>Data Internal</h2>
        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.investor')}}" class="text-decoration-none">
                <img src="{{asset('images/investor.svg')}}" alt="" width="70">
                <h5 class="mt-2">PERSENTASE DIVIDEN<br>PENGELOLA & INVESTOR</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.pengelola')}}" class="text-decoration-none">
                <img src="{{asset('images/pengelola.svg')}}" alt="" width="70">
                <h5 class="mt-2">PERSENTASE DIVIDEN<br>PENGELOLA</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.investor-modal')}}" class="text-decoration-none">
                <img src="{{asset('images/investor-modal.svg')}}" alt="" width="70">
                <h5 class="mt-2">PERSENTASE DIVIDEN<br>INVESTOR</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-2">BIODATA & GAJI<br>DIREKSI</h4>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-2">BIODATA & GAJI<br>STAFF</h4>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-2">BONUS STAFF</h4>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left mt-4">
        <h2>Data Eksternal</h2>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.supplier')}}" class="text-decoration-none">
                <img src="{{asset('images/supplier.svg')}}" alt="" width="70">
                <h5 class="mt-2">BIODATA<br>SUPPLIER<br></h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.konsumen')}}" class="text-decoration-none">
                <img src="{{asset('images/customer.svg')}}" alt="" width="70">
                <h5 class="mt-2">BIODATA<br>KONSUMEN</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.rekening')}}" class="text-decoration-none">
                <img src="{{asset('images/rekening.svg')}}" alt="" width="70">
                <h5 class="mt-2">REKENING<br>TRANSAKSI</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.pajak')}}" class="text-decoration-none">
                <img src="{{asset('images/pajak.svg')}}" alt="" width="70">
                <h5 class="mt-2">PERSENTASE<br>PAJAK</h5>
            </a>
        </div>
    </div>
    <hr>
    <div class="row justify-content-left mt-4">
        <h2>Data Kategori</h2>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.cost-operational')}}" class="text-decoration-none">
                <img src="{{asset('images/cost-operational.svg')}}" alt="" width="70">
                <h5 class="mt-2">KATEGORI<br>COST OPERASIONAL</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.kategori-inventaris')}}" class="text-decoration-none">
                <img src="{{asset('images/kategori-inventaris.svg')}}" alt="" width="70">
                <h5 class="mt-2">KATEGORI<br>INVENTARIS</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.satuan')}}" class="text-decoration-none">
                <img src="{{asset('images/satuan.svg')}}" alt="" width="70">
                <h5 class="mt-2">KATEGORI<br>SATUAN</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.unit')}}" class="text-decoration-none">
                <img src="{{asset('images/unit.svg')}}" alt="" width="70">
                <h5 class="mt-2">KATEGORI<br>PERUSAHAAN & BIDANG</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.barang-kategori')}}" class="text-decoration-none">
                <img src="{{asset('images/kelompok-barang.svg')}}" alt="" width="70">
                <h5 class="mt-2">KATEGORI<br>KELOMPOK & NAMA<br>BARANG</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.barang')}}" class="text-decoration-none">
                <img src="{{asset('images/barang.svg')}}" alt="" width="70">
                <h5 class="mt-2">KATEGORI<br>KODE & MERK<br>BARANG</h5>
            </a>
        </div>

        @endif
    </div>
    <hr>
    <div class="row justify-content-left mt-4">
        <h2>Data Transaksi</h2>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.stok-ppn')}}" class="text-decoration-none">
                <img src="{{asset('images/stok-ppn.svg')}}" alt="" width="70">
                <h5 class="mt-2">STOK &<br>HARGA JUAL<br>BARANG PPN</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('db.stok-non-ppn')}}" class="text-decoration-none">
                <img src="{{asset('images/stok-non-ppn.svg')}}" alt="" width="70">
                <h5 class="mt-2">STOK &<br>HARGA JUAL<br>BARANG NON PPN</h5>
            </a>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h5 class="mt-2">DASHBOARD</h5>
            </a>
        </div>
    </div>


</div>
@endsection
