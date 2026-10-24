@extends('layouts.app')
@section('content')

<div class="container-fluid payroll-ui">
    <header class="payroll-header"><div><div class="payroll-eyebrow">Rekap · Penggajian</div><h1>Rekap gaji pegawai</h1><p>Rincian penghasilan, potongan, dan rekening penerima yang tersimpan.</p></div><div class="payroll-header-links"><a href="{{ route('rekap.gaji') }}" class="btn btn-outline-success">Ganti periode</a><a href="{{ route('rekap') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Rekap</a></div></header>
    <div class="payroll-period"><div><span>Periode</span><strong>{{ $bulan }} {{ $tahun }}</strong>@if($data->periode_mulai && $data->periode_selesai)<p>{{ \Carbon\Carbon::parse($data->periode_mulai)->locale('id')->translatedFormat('d F Y') }}–{{ \Carbon\Carbon::parse($data->periode_selesai)->locale('id')->translatedFormat('d F Y') }}</p>@endif</div><span class="payroll-tag"><i class="fa fa-check-circle" aria-hidden="true"></i> Tercatat</span></div>
    @include('rekap.gaji.summary',['rows'=>$rows])
    @include('rekap.gaji.table',['rows'=>$rows,'showAccounts'=>true])
    <p class="payroll-footnote"><i class="fa fa-lock" aria-hidden="true"></i> Angka pada halaman ini mengikuti data yang tersimpan saat gaji diproses.</p>
</div>
@endsection
@push('css')<link rel="stylesheet" href="{{ asset('payroll-ui.css').'?v='.filemtime(public_path('payroll-ui.css')) }}">@endpush
@push('js')<script src="{{ asset('payroll-ui.js').'?v='.filemtime(public_path('payroll-ui.js')) }}"></script>@endpush
