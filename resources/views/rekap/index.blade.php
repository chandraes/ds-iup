@extends('layouts.app')
@section('content')

<div class="container text-center">
    <h1>REKAP</h1>
</div>
@include('rekap.modal-konsumen')
<div class="container mt-5">
    <div class="row justify-content-left">
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.kas-besar')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-besar.svg')}}" alt="" width="70">
                <h3 class="mt-2">KAS BESAR</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.kas-kecil')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="70">
                <h3 class="mt-2">KAS KECIL</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.kas-investor')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-investor.svg')}}" alt="" width="70">
                <h3 class="mt-2">KAS INVESTOR</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#kasKonsumenModal">
                <img src="{{asset('images/kas-konsumen.svg')}}" alt="" width="70">
                <h3 class="mt-2">KAS KONSUMEN</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.invoice-belanja')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-invoice.svg')}}" alt="" width="70">
                <h3 class="mt-2">INVOICE BELANJA</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.invoice-penjualan')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-invoice-penjualan.svg')}}" alt="" width="70">
                <h3 class="mt-2">INVOICE PENJUALAN</h3>
            </a>
        </div>

        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.gaji')}}" class="text-decoration-none">
                <img src="{{asset('images/rekap-gaji.svg')}}" alt="" width="70">
                <h3 class="mt-2">GAJI STAFF</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h3 class="mt-2">DASHBOARD</h3>
            </a>
        </div>
    </div>
    <div class="row justify-content-left">

    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>
    $('#project').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#kasSupplier')
        });
</script>
@endpush
