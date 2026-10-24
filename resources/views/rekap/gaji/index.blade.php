@extends('layouts.app')
@section('content')
<div class="container payroll-ui payroll-picker">
    <header class="payroll-header"><div><div class="payroll-eyebrow">Rekap · Penggajian</div><h1>Rekap gaji pegawai</h1><p>Pilih periode untuk melihat rincian gaji yang telah diproses.</p></div><a href="{{ route('rekap') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Rekap</a></header>
    <section class="payroll-sheet payroll-picker-sheet"><span class="payroll-picker-icon"><i class="fa fa-calendar" aria-hidden="true"></i></span><h2>Pilih periode</h2><p class="payroll-footnote">Rekap menampilkan penghasilan, potongan kasbon, dan gaji yang dibayarkan pada periode pilihan.</p>
        @if($errors->any())<div class="alert alert-danger mt-3" role="alert">{{ $errors->first() }}</div>@endif
        <form action="{{ route('rekap.gaji.detail') }}" method="get" class="payroll-picker-form">
            <div><label for="bulan" class="form-label">Bulan periode</label><select class="form-select" name="bulan" id="bulan" required>@foreach([1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $value=>$label)<option value="{{ $value }}" @selected((int)old('bulan',now()->month) === $value)>{{ $label }}</option>@endforeach</select></div>
            <div><label for="tahun" class="form-label">Tahun</label><input type="number" min="1901" max="2155" name="tahun" id="tahun" class="form-control" value="{{ old('tahun',now()->year) }}" required></div>
            <button class="btn btn-success"><i class="fa fa-search" aria-hidden="true"></i> Lihat rekap</button>
        </form>
    </section>
</div>
@endsection
@push('css')<link rel="stylesheet" href="{{ asset('payroll-ui.css').'?v='.filemtime(public_path('payroll-ui.css')) }}">@endpush
