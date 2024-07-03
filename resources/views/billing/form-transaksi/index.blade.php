@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>FORM TRANSAKSI</h1>
</div>
<div class="container mt-3">
    {{-- <div class="row justify-content-left">
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.form-transaksi.bahan-baku.hutang-belanja')}}" class="text-decoration-none">
                <img src="{{asset('images/nota-belanja.svg')}}" alt="" width="80">
                <h3 class="mt-2">HUTANG BELANJA

                </h3>
            </a>
        </div>
    </div> --}}
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
