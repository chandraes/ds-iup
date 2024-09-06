@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>FORM INVENTARIS</h1>
</div>
<div class="container mt-3">
    <div class="row justify-content-left">
        
    </div>
    <div class="row justify-content-left">
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="70">
                <h4 class="mt-2">KEMBALI</h4>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-2">DASHBOARD</h4>
            </a>
        </div>
    </div>
</div>
@endsection

