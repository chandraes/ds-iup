@extends('layouts.app')
@section('content')
<div class="container text-center">
    <h1><u>REKAP</u></h1>
</div>
@include('rekap.modal-konsumen')
<div class="container mt-5">
    <div class="row justify-content-left">
        <h2 class="mt-3">UMUM</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.kas-besar', ['ppn_kas' => 1])}}" class="text-decoration-none">
                <img src="{{asset('images/kas-besar.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS BESAR<br>PPN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.kas-besar', ['ppn_kas' => 0])}}" class="text-decoration-none">
                <img src="{{asset('images/kas-besar-non-ppn.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS BESAR<br>NON-PPN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">NOTA VOID TRANSAKSI</h5>
            </a>
        </div>


    </div>
    <div class="row justify-content-left">
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">DEPOSIT</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">DIVIDEN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">KASBON</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">LAIN-LAIN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">GANTI RUGI</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">CSR<br>(TIDAK TERTENTU)</h5>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">COST OPERATIONAL</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">OPERATIONAL</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.kas-kecil')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-kecil.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS KECIL</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.gaji')}}" class="text-decoration-none">
                <img src="{{asset('images/form-gaji.svg')}}" alt="" width="70">
                <h5 class="mt-3">GAJI</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">BUNGA INVESTOR</h5>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">KHUSUS</h2>
        <div class="col-md-2 text-center mt-5">
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
                <h5 class="mt-3">PRICE LIST</h5>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">TRANSAKSI</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">BELI</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">JUAL</h5>
            </a>
        </div>
    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">INVOICE</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE SUPPLIER PPN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE SUPPLIER NON PPN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE KONSUMEN PPN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none">
                <img src="{{asset('images/kosong.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE KONSUMEN NON PPN</h5>
            </a>
        </div>

    </div>
    <hr>
    <br>
    <div class="row justify-content-left">
        <h2 class="mt-3">DATA LAMA</h2>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('rekap.kas-investor')}}" class="text-decoration-none">
                <img src="{{asset('images/kas-investor.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS INVESTOR</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#kasKonsumenModal">
                <img src="{{asset('images/kas-konsumen.svg')}}" alt="" width="70">
                <h5 class="mt-3">KAS KONSUMEN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
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
                <h5 class="mt-3">INVOICE PEMBELIAN</h5>
            </a>


        </div>
        <div class="col-md-2 text-center mt-5">
            <div class="modal fade" id="modalPenjualan" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
                role="dialog" aria-labelledby="pricelistTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pricelistTitle">
                                INVOICE PENJUALAN
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{route('rekap.invoice-penjualan')}}" method="get">
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
            <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalPenjualan">
                <img src="{{asset('images/rekap-invoice-penjualan.svg')}}" alt="" width="70">
                <h5 class="mt-3">INVOICE PENJUALAN</h5>
            </a>
        </div>
        <div class="col-md-2 text-center mt-5">
            <a href="{{route('home')}}" class="text-decoration-none">
                <img src="{{asset('images/dashboard.svg')}}" alt="" width="70">
                <h5 class="mt-3">DASHBOARD</h5>
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
