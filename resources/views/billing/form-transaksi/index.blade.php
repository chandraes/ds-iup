@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>FORM TRANSAKSI</h1>
</div>
<div class="container mt-3">
    <div class="row justify-content-left">
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#bahanBakuModal">
                <img src="{{asset('images/form-bahan-baku.svg')}}" alt="" width="80">
                <h3 class="mt-2">FORM BAHAN BAKU</h3>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#kemasanModal">
                <img src="{{asset('images/kemasan.svg')}}" alt="" width="80">
                <h3 class="mt-2">FORM KEMASAN</h3>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#packagingModal">
                <img src="{{asset('images/packaging.svg')}}" alt="" width="80">
                <h3 class="mt-2">FORM PACKAGING</h3>
            </a>
        </div>
        @include('billing.form-transaksi.modal-bahan-baku')
        @include('billing.form-transaksi.modal-kemasan')
        @include('billing.form-transaksi.modal-packaging')
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.form-transaksi.bahan-baku.hutang-belanja')}}" class="text-decoration-none">
                <img src="{{asset('images/nota-belanja.svg')}}" alt="" width="80">
                <h3 class="mt-2">HUTANG BELANJA
                    @if ($hb > 0)
                    <span class="text-danger">({{$hb}})</span>
                    @endif
                </h3>
            </a>
        </div>
    </div>
    <div class="row justify-content-left">
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="80">
                <h3 class="mt-2">KEMBALI</h3>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="80">
                <h3 class="mt-2">DASHBOARD</h3>
            </a>
        </div>
    </div>
</div>
@endsection
