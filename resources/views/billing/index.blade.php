@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>BILLING</h1>
</div>
@include('swal')
<div class="container mt-3">
    <div class="row justify-content-left">
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formDeposit">
                <img src="{{asset('images/form-deposit.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM DEPOSIT</h4>
            </a>
            @include('billing.modal-form-deposit')
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKecil">
                <img src="{{asset('images/form-kas-kecil.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM KAS KECIL</h4>
            </a>
            @include('billing.modal-form-kas-kecil')
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalLain">
                <img src="{{asset('images/form-lain.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM LAIN-LAIN</h4>
            </a>
            <div class="modal fade" id="modalLain" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="modalLainTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLainTitle">Form Lain-lain</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <select class="form-select" name="selectLain" id="selectLain">
                                <option value="masuk">Dana Masuk</option>
                                <option value="keluar">Dana Keluar</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="button" class="btn btn-primary" onclick="funLain()">Lanjutkan</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.form-dividen')}}" class="text-decoration-none">
                <img src="{{asset('images/form-deviden.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM DIVIDEN</h4>
            </a>
        </div>
    </div>


    <div class="row justify-content-left">
        @include('billing.modal-form-beli')
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalBeli">
                <img src="{{asset('images/form-beli.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM BELI</h4>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.lihat-stok')}}" class="text-decoration-none">
                <img src="{{asset('images/form-jual.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM JUAL</h4>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.form-cost-operational')}}" class="text-decoration-none">
                <img src="{{asset('images/form-cost-operational.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM COST<br>OPERATIONAL</h4>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.form-inventaris')}}" class="text-decoration-none">
                <img src="{{asset('images/form-inventaris.svg')}}" alt="" width="70">
                <h4 class="mt-2">FORM INVENTARIS
                </h4>
            </a>
        </div>
    </div>
    <div class="row justify-content-left">
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="{{route('billing.invoice-supplier')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-supplier.svg')}}" alt="" width="70">
                <h4 class="mt-2">INVOICE SUPPLIER
                    @if ($is > 0)
                    <span class="text-danger">({{$is}})</span>
                    @endif
                </h4>
            </a>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-6 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/invoice-konsumen.svg')}}" alt="" width="70">
                <h4 class="mt-2">INVOICE KONSUMEN</h4>
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
@push('js')
<script>
    function funDeposit(){
        var selectDeposit = document.getElementById('selectDeposit').value;
        if(selectDeposit == 'masuk'){
            window.location.href = "{{route('form-deposit.masuk')}}";
        }else if(selectDeposit == 'keluar'){
            window.location.href = "{{route('form-deposit.keluar')}}";
        }else if(selectDeposit == 'keluar-all'){
            window.location.href = "{{route('form-deposit.keluar-all')}}";
        }
    }

    function funLain(){
        var selectLain = document.getElementById('selectLain').value;
        if(selectLain == 'masuk'){
            window.location.href = "{{route('form-lain.masuk')}}";
        }else if(selectLain == 'keluar'){
            window.location.href = "{{route('form-lain.keluar')}}";
        }
    }

    function funKecil(){
        var selectKecil = document.getElementById('selectKecil').value;
        if(selectKecil == 'masuk'){
            window.location.href = "{{route('form-kas-kecil.masuk')}}";
        }else if(selectKecil == 'keluar'){
            window.location.href = "{{route('form-kas-kecil.keluar')}}";
        }
    }


</script>
@endpush
