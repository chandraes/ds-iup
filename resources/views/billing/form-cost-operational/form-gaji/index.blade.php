@extends('layouts.app')
@section('content')
@php
    $total = $rows->sum('sisa_gaji_dibayar');
    $periodLabel = $month.' '.$period->year;
@endphp
<div class="container-fluid payroll-ui">
    <header class="payroll-header"><div><div class="payroll-eyebrow">Billing · Penggajian</div><h1>Form gaji pegawai</h1><p>Periksa komponen penghasilan dan potongan sebelum memproses gaji.</p></div><a href="{{ route('billing') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali ke Billing</a></header>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="payroll-period"><div><span>Periode</span><strong>{{ $periodLabel }}</strong><p>{{ $period->copy()->subMonth()->day(26)->locale('id')->translatedFormat('d F Y') }}–{{ $period->copy()->day(25)->locale('id')->translatedFormat('d F Y') }}</p></div><span class="payroll-tag"><i class="fa fa-clock-o" aria-hidden="true"></i> Menunggu diproses</span></div>
    @include('billing.form-cost-operational.form-gaji.attendance-status')
    @include('rekap.gaji.summary',['rows'=>$rows])
    @include('rekap.gaji.table',['rows'=>$rows,'showAccounts'=>false,'showRowNumbers'=>true,'showAttendanceDeduction'=>true])
    <div class="payroll-notice"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Komisi Sales mengikuti omset periode × persentase pegawai. Klik nominal komisi untuk melihat rinciannya. Potongan absensi non-sales mengikuti data kehadiran. BBM sales mengikuti absen masuk dan keterlambatan; klik nominal untuk rincian harian. Jika integrasi dimatikan, keduanya bernilai 0.</span></div>
    <div class="payroll-actions"><div><span>Sumber dana</span><strong><i class="fa fa-university" aria-hidden="true"></i> Kas Besar PPN</strong><p>Gaji dicatat keluar saat proses berhasil disimpan.</p></div><div class="payroll-action-buttons"><a href="{{ route('billing') }}" class="btn btn-outline-secondary">Batal</a><form action="{{ route('billing.form-cost-operational.gaji.store') }}" method="post" id="lanjutForm" data-period="{{ $periodLabel }}" data-total="{{ $total }}">@csrf<input type="hidden" name="periode" value="{{ $period->format('Y-m') }}"><input type="hidden" name="attendance_revision" value="{{ $attendanceSnapshot['revision'] ?? '' }}"><input type="hidden" name="total" value="{{ $total }}"><button class="btn btn-success" type="submit" @disabled(!$attendanceReady)><i class="fa fa-check" aria-hidden="true"></i> Proses gaji</button></form></div></div>
</div>
@endsection
@push('css')<link rel="stylesheet" href="{{ asset('payroll-ui.css').'?v='.filemtime(public_path('payroll-ui.css')) }}">@endpush
@push('js')<script src="{{ asset('payroll-ui.js').'?v='.filemtime(public_path('payroll-ui.js')) }}"></script>@endpush
