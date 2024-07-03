@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>INVENTARIS</h1>
</div>
<div class="container mt-3">
    <div class="row justify-content-left">
        {{-- <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('rekap')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="70">
                <h4 class="mt-2">BARANG<br>HABIS PAKAI</h4>
            </a>
        </div>

        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('rekap')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="70">
                <h4 class="mt-2">BARANG<br>TIDAK HABIS PAKAI</h4>
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
                    <h3 class="mt-2">{{ $namaParts[0] }}<br>{{ $namaParts[1] }}</h3>
                @else
                    <h3 class="mt-2">{{ $k->nama }}</h3>
                @endif
            </a>
        </div>
        @endforeach
        <div class="col-md-3 text-center mt-3">
            <a href="{{route('inventaris.invoice')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-invoice.svg')}}" alt="" width="70">
                <h4 class="mt-2">INVOICE<br>INVENTARIS</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-2">DASHBOARD</h4>
            </a>
        </div>
    </div>
</div>
@endsection
