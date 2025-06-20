@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1><u>STATISTIK</u></h1>
</div>
<div class="container mt-3">
    <div class="row justify-content-left">
        <h4 class="mt-3">OMSET SALES</h4>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('statistik.omset-harian-sales')}}" class="text-decoration-none">
                <img src="{{asset('images/omset-sales.svg')}}" alt="" width="70">
                <h4 class="mt-3">HARIAN</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">BULANAN</h4>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">TAHUNAN</h4>
            </a>
        </div>
    </div>
    <hr>
     <div class="row justify-content-left">
        <h4 class="mt-3">PROFIT INVOICE</h4>
         <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('statistik.profit.harian')}}" class="text-decoration-none">
                <img src="{{asset('images/profit-harian.svg')}}" alt="" width="70">
                <h4 class="mt-3">HARIAN</h4>
            </a>
        </div>
     </div>
    {{-- <div class="row justify-content-left">
        <h4 class="mt-3">REKAP</h4>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.rekap-ppn')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-ppn.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPN</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.ppn-expired')}}" class="text-decoration-none">
                <img src="{{asset('images/ppn-expired.svg')}}" alt="" width="70">
                <h4 class="mt-3">PPN EXPIRED</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mt-3">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD</h4>
            </a>
        </div>
    </div> --}}
</div>
@endsection
