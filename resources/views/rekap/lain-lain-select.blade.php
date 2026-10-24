@extends('layouts.app')
@section('content')
<div class="container py-4" style="max-width:760px">
    <a href="{{ route('rekap') }}" class="btn btn-outline-secondary mb-4">Kembali ke rekap</a>
    <div class="card border-0 shadow-sm rounded-4"><div class="card-body p-4 p-md-5">
        <img src="{{ asset('images/form-lain.svg') }}" alt="" width="64" class="mb-3">
        <h1 class="h3 fw-bold">Rekap Form Lain-lain</h1><p class="text-muted">Pilih tipe kas untuk melihat rincian dana masuk dan dana keluar.</p>
        <form action="{{ route('rekap.lain-lain') }}" method="get">
            <label class="form-label fw-bold" for="cash-type">Tipe kas</label>
            <select name="ppn_kas" id="cash-type" class="form-select form-select-lg mb-4" required><option value="1">Kas Besar PPN</option><option value="0">Kas Besar Non-PPN</option></select>
            <button class="btn btn-success px-4">Lihat rekap</button>
        </form>
    </div></div>
</div>
@endsection
