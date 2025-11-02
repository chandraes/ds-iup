@extends('layouts.app') {{-- Atau layout utama Anda --}}
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Preview Cetak Barang Retur: {{$retur->kode}}</h4>
                    <div class="card-options">
                        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm me-2">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ $pdfUrl }}" target="_blank" class="btn btn-info btn-sm me-2">
                            <i class="fa fa-print"></i> Cetak
                        </a>
                        <a href="{{ $downloadUrl }}" class="btn btn-success btn-sm">
                            <i class="fa fa-download"></i> Download PDF
                        </a>
                    </div>
                </div>
                <div class="card-body" style="height: 80vh; padding: 0;">
                    <iframe src="{{ $pdfUrl }}" width="100%" height="100%" frameborder="0">
                        Browser Anda tidak mendukung iframe. Silakan klik "Cetak" atau "Download".
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
/* Memastikan card-header punya ruang untuk tombol */
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
</style>
@endpush
