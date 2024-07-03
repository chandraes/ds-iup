@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>PAJAK</h1>
</div>
<div class="container mt-3">
    <div class="row justify-content-left">
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('pajak.ppn-masukan')}}" class="text-decoration-none">
                <img src="{{asset('images/ppn-masukan.svg')}}" alt="" width="70">
                <h4 class="mt-2">PPN MASUKAN</h4>
            </a>
        </div>
        <div class="col-md-3 text-center mb-5 mt-3">
            <a href="{{route('rekap')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap.svg')}}" alt="" width="70">
                <h4 class="mt-2">PPN KELUARAN</h4>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.pph-masa')}}" class="text-decoration-none">
                <img src="{{asset('images/pph-masa.svg')}}" alt="" width="70">
                <h3 class="mt-2">PPH MASA</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.pph-badan')}}" class="text-decoration-none">
                <img src="{{asset('images/pph-badan.svg')}}" alt="" width="70">
                <h3 class="mt-2">PPH BADAN</h3>
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
</div>
@endsection
