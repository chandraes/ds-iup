@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1><u>BILLING</u></h1>
</div>
@include('swal')
<div class="container mt-3">
    <div class="row justify-content-left">
        <h4 class="mt-3">UMUM</h4>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formDeposit">
                <img src="{{asset('images/form-deposit.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM DEPOSIT</h4>
            </a>
            @include('billing.modal-form-deposit')
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.form-dividen')}}" class="text-decoration-none">
                <img src="{{asset('images/form-deviden.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM DIVIDEN</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM KASBON</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalLain">
                <img src="{{asset('images/form-lain.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM LAIN-LAIN</h4>
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
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.ganti-rugi')}}" class="text-decoration-none">
                <img src="{{asset('images/ganti-rugi.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM GANTI RUGI
                    @if ($gr > 0)
                    <span class="text-danger">({{$gr}})</span>
                    @endif
                </h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM CSR<br>(TIDAK TERTENTU)</h4>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">COST OPERATIONAL</h4>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.form-cost-operational.cost-operational')}}" class="text-decoration-none">
                <img src="{{asset('images/form-cost-operational.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM OPERATIONAL</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#formKecil">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM KAS KECIL</h4>
            </a>
            @include('billing.modal-form-kas-kecil')
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.form-cost-operational.gaji')}}" class="text-decoration-none">
                <img src="{{asset('images/form-gaji.svg')}}" alt="" width="70">
                <h3 class="mt-3">FORM GAJI</h3>
            </a>
        </div>
        <div class="modal fade" id="modalFormBungaInvestor" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false" role="dialog" aria-labelledby="bungaInvestorTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bungaInvestorTitle">
                            Form Bunga Kreditur
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('billing.bunga-investor')}}" method="get">
                        <div class="modal-body">
                            <div class="mb-3">
                                <select class="form-select" name="kas_ppn" id="kas_ppn" required>
                                    <option value="1">Kas PPN</option>
                                    <option value="0">Kas Non PPN</option>
                                </select>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Batalkan
                            </button>
                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalFormBungaInvestor">
                <img src="{{asset('images/bunga-kreditor.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM BUNGA KREDITUR</h4>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">TRANSAKSI</h4>
        @include('billing.modal-form-beli')
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalBeli">
                <img src="{{asset('images/form-beli.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM BELI</h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.lihat-stok')}}" class="text-decoration-none">
                <img src="{{asset('images/form-jual.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM JUAL</h4>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">INVOICE</h4>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.invoice-supplier')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-supplier.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE SUPPLIER<br>PPN
                    @if ($is > 0)
                    <span class="text-danger">({{$is}})</span>
                    @endif
                </h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.invoice-supplier.non-ppn')}}" class="text-decoration-none">
                <img src="{{asset('images/invoice-jual-non-ppn.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE SUPPLIER<br>NON PPN
                    @if ($isn > 0)
                    <span class="text-danger">({{$isn}})</span>
                    @endif
                </h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
           @include('billing.modal-invoice-konsumen-ppn')
            <a href="#" class="text-decoration-none" data-bs-toggle="modal"
            data-bs-target="#modalKonsumenPpn">
                <img src="{{asset('images/invoice-konsumen.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE KONSUMEN<br>PPN
                    @if ($ik > 0)
                    <span class="text-danger">({{$ik}})</span>
                    @endif
                </h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">

            @include('billing.modal-invoice-konsumen-non-ppn') 

            <a href="#" class="text-decoration-none" data-bs-toggle="modal"
            data-bs-target="#modalKonsumenNonPpn">
                <img src="{{asset('images/invoice-konsumen-non.svg')}}" alt="" width="70">
                <h4 class="mt-3">INVOICE KONSUMEN<br>NON PPN
                    @if ($ikn > 0)
                    <span class="text-danger">({{$ikn}})</span>
                    @endif
                </h4>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h4 class="mt-3">DASHBOARD

                </h4>
            </a>
        </div>

    </div>
    <hr>
    {{-- <br>
    <div class="row justify-content-left">
        <h4 class="mt-3">INVENTARIS</h4>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('billing.form-inventaris')}}" class="text-decoration-none">
                <img src="{{asset('images/form-inventaris.svg')}}" alt="" width="70">
                <h4 class="mt-3">FORM INVENTARIS
                </h4>
            </a>
        </div>
    </div> --}}
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
