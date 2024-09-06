@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>INVENTARIS</h1>
</div>
<div class="container mt-3">
    <div class="row justify-content-left">
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.form-inventaris.beli')}}" class="text-decoration-none">
                <img src="{{asset('images/form-beli.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM INVENTARIS</h4>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.form-inventaris.hutang')}}" class="text-decoration-none">
                <img src="{{asset('images/hutang.svg')}}" alt="" width="70">
                <h4 class="mt-3">HUTANG INVENTARIS
                    @if($hi != 0) <span class="text-danger">({{$hi}})</span> @endif
                </h4>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kredit.svg')}}" alt="" width="70">
                <h4 class="mt-3">KREDIT INVENTARIS</h4>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        {{-- <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('rekap')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="70">
                <h4 class="mt-3">BARANG<br>HABIS PAKAI</h4>
            </a>
        </div>

        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('rekap')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="70">
                <h4 class="mt-3">BARANG<br>TIDAK HABIS PAKAI</h4>
            </a>
        </div> --}}
        @foreach ($kategori as $k)
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('inventaris.detail', $k)}}" class="text-decoration-none">
                <img src="{{asset('images/inventaris.svg')}}" alt="" width="70">
                @php
                $namaParts = explode(' ', $k->nama);
                @endphp

                @if(count($namaParts) == 2)
                    <h4 class="mt-3">{{ $namaParts[0] }}<br>{{ $namaParts[1] }}</h4>
                @else
                    <h4 class="mt-3">{{ $k->nama }}</h4>
                @endif
            </a>
        </div>
        @endforeach
        <div class="col-md-3 text-center mt-3">
            <a href="{{route('inventaris.invoice')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-invoice.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE<br>INVENTARIS</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div>
</div>
@endsection
