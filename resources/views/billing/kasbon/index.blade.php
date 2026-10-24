@extends('layouts.app')
@section('content')
<div class="container kasbon-ui">
    <div class="loan-breadcrumb"><a href="{{ route('billing') }}">Billing</a><i class="fa fa-angle-right" aria-hidden="true"></i><span>Kasbon pegawai</span></div>
    <header class="loan-header"><div><div class="loan-eyebrow">Layanan keuangan pegawai</div><h1>Form kasbon pegawai</h1><p>Pilih cara pembayaran yang sesuai. Pinjaman akan dicatat dan diperhitungkan pada penggajian pegawai.</p></div><span class="loan-account"><i class="fa fa-university" aria-hidden="true"></i>Kas Besar PPN</span></header>
    @include('db.karyawan.feedback')
    <div class="row g-4">
        <div class="col-md-6"><section class="loan-choice"><div class="loan-choice-top"><span class="loan-icon"><i class="fa fa-money" aria-hidden="true"></i></span><span class="loan-method">Sekali potong</span></div><h2>Potong gaji</h2><p>Untuk kebutuhan jangka pendek. Seluruh sisa pinjaman dipotong pada penggajian periode berjalan.</p><div class="loan-rate"><strong>50<span aria-hidden="true">%</span></strong><span>Batas total kasbon dari penghasilan acuan</span></div><ul class="loan-checks"><li><i class="fa fa-check-circle" aria-hidden="true"></i>Tersedia mulai tanggal 16 setiap bulan.</li><li><i class="fa fa-check-circle" aria-hidden="true"></i>Batas menyesuaikan angsuran dan kasbon berjalan.</li><li><i class="fa fa-check-circle" aria-hidden="true"></i>Pelunasan otomatis melalui penggajian.</li></ul><a class="btn btn-success" href="{{ route('billing.kasbon.create', ['jenis'=>'potong_gaji']) }}">Isi form potong gaji<i class="fa fa-arrow-right" aria-hidden="true"></i></a></section></div>
        @if(in_array(auth()->user()->role,['admin','su'],true))
        <div class="col-md-6"><section class="loan-choice"><div class="loan-choice-top"><span class="loan-icon"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span><span class="loan-method">Angsuran bulanan</span></div><h2>Cicilan</h2><p>Atur pembayaran bertahap. Tentukan jumlah angsuran dan bulan dimulainya potongan gaji.</p><div class="loan-rate"><strong>30<span aria-hidden="true">%</span></strong><span>Batas angsuran per bulan dari penghasilan acuan</span></div><ul class="loan-checks"><li><i class="fa fa-check-circle" aria-hidden="true"></i>Hanya satu kasbon cicilan aktif per pegawai.</li><li><i class="fa fa-check-circle" aria-hidden="true"></i>Memperhitungkan kasbon potong gaji belum lunas.</li><li><i class="fa fa-check-circle" aria-hidden="true"></i>Jadwal pembayaran ditentukan saat pengajuan.</li></ul><a class="btn btn-outline-success" href="{{ route('billing.kasbon.create',['jenis'=>'cicilan']) }}">Isi form cicilan<i class="fa fa-arrow-right" aria-hidden="true"></i></a></section></div>
        @endif
    </div>
    <p class="loan-bottom-note"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Penghasilan acuan: gaji pokok + tunjangan jabatan + tunjangan keluarga. Kasbon Sales belum tersedia sampai aturan khusus ditetapkan.</span></p>
</div>
@endsection
@push('css')<link rel="stylesheet" href="{{ asset('kasbon-ui.css').'?v='.filemtime(public_path('kasbon-ui.css')) }}">@endpush
