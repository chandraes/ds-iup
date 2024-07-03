@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>FORM COST OPERATIONAL</h1>
</div>
<div class="container mt-5">
    <div class="row justify-content-left">
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('billing.form-cost-operational.cost-operational')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-besar.svg')}}" alt="" width="70">
                <h3 class="mt-2">FORM OPERATIONAL</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('billing.form-cost-operational.gaji')}}" class="text-decoration-none">
                <img src="{{asset('images/form-gaji.svg')}}" alt="" width="70">
                <h3 class="mt-2">FORM GAJI</h3>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 mb-3 mt-3 text-center">
            <a href="{{route('billing')}}" class="text-decoration-none">
                <img src="{{asset('images/back.svg')}}" alt="" width="70">
                <h3 class="mt-2">KEMBALI</h3>
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
