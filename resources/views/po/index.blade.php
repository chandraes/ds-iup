@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>PURCHASE ORDER</h1>
</div>
<div class="container mt-5">
    <div class="row justify-content-left">
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('po.form')}}" class="text-decoration-none">
                <img src="{{asset('images/form-po.svg')}}" alt="" width="70">
                <h3 class="mt-2">FORM PO</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('po.rekap')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-po.svg')}}" alt="" width="70">
                <h3 class="mt-2">REKAP PO</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h3 class="mt-2">DASHBOARD</h3>
            </a>
        </div>
    </div>
</div>
@endsection

