@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1>REKAP</h1>
</div>
@include('rekap.modal-konsumen')
<div class="container mt-5">
    <div class="row justify-content-left">
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.kas-besar', ['ppn_kas' => 1])}}" class="text-decoration-none">
                <img src="{{asset('images/kas-besar.svg')}}" alt="" width="70">
                <h3 class="mt-2">KAS BESAR<br>PPN</h3>
            </a>
        </div>
        <div class="col-lg-3 mt-3 mb-3 text-center">
            <a href="{{route('rekap.kas-besar', ['ppn_kas' => 0])}}" class="text-decoration-none">
                <img src="{{asset('images/kas-besar-non-ppn.svg')}}" alt="" width="70">
                <h3 class="mt-2">KAS BESAR<br>NON-PPN</h3>
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
            <div class="modal fade" id="modalPembelian" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="pricelistTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pricelistTitle">
                                INVOICE PEMBELIAN
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('rekap.invoice-pembelian')}}" method="get">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <select class="form-select form-select-lg" name="ppn_kas" id="ppn_kas">
                                        <option value="1">Barang PPN</option>
                                        <option value="0">Barang Non PPN</option>
                                    </select>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-primary">Lanjutkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalPembelian">
                <img src="{{asset('images/rekap-invoice.svg')}}" alt="" width="70">
                <h3 class="mt-2">INVOICE PEMBELIAN</h3>
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

            <div class="modal fade" id="modalPricelist" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="pricelistTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pricelistTitle">
                                Barang PPN / NON PPN
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('rekap.pricelist')}}" method="get">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <select class="form-select form-select-lg" name="ppn_kas" id="ppn_kas">
                                        <option value="1">Barang PPN</option>
                                        <option value="2">Barang Non PPN</option>
                                    </select>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                                <button type="submit" class="btn btn-primary">Lanjutkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalPricelist">
                <img src="{{asset('images/pricelist.svg')}}" alt="" width="70">
                <h3 class="mt-2">PRICE LIST</h3>
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
