@extends('layouts.app')
@section('content')
<div class="container kasbon-ui">
    <div class="loan-breadcrumb"><a href="{{ route('billing') }}">Billing</a><i class="fa fa-angle-right" aria-hidden="true"></i><a href="{{ route('billing.kasbon.index') }}">Kasbon pegawai</a><i class="fa fa-angle-right" aria-hidden="true"></i><span>{{ $type === 'cicilan' ? 'Cicilan' : 'Potong gaji' }}</span></div>
    <header class="loan-header"><div><div class="loan-eyebrow">Pengajuan pinjaman</div><h1>Kasbon {{ $type === 'cicilan' ? 'Cicilan' : 'Potong Gaji' }}</h1><p>{{ $type === 'cicilan' ? 'Tentukan nominal dan jadwal angsuran pegawai.' : 'Catat pinjaman yang akan dilunasi melalui penggajian periode berjalan.' }}</p></div><div class="loan-header-actions"><a href="{{ route('billing.kasbon.index') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i>Pilih metode</a></div></header>
    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())
    <div class="loan-error-summary" id="loan-errors" role="alert" tabindex="-1" aria-labelledby="loan-error-title"><h2 id="loan-error-title">Periksa kembali data pengajuan</h2><ul>@foreach(['karyawan_id'=>'loan-employee','nominal'=>'loan-amount','cicil_kali'=>'loan-count','mulai_bulan'=>'loan-month','mulai_tahun'=>'loan-year'] as $field=>$target)@foreach($errors->get($field) as $error)<li><a href="#{{ $target }}">{{ $error }}</a></li>@endforeach @endforeach @foreach($errors->get('submission_token') as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    @if($type === 'potong_gaji' && now()->day < 16)<div class="loan-notice is-warning"><i class="fa fa-clock-o" aria-hidden="true"></i><span>Kasbon potong gaji baru dapat disimpan mulai tanggal 16.</span></div>@endif
    @if($employees->isEmpty())<div class="loan-notice is-warning"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Belum ada pegawai yang memenuhi syarat pengajuan kasbon.</span></div>@endif
    <div class="row g-4"><div class="col-lg-8"><div class="loan-sheet">
        <form action="{{ route('billing.kasbon.store') }}" method="post" class="employee-mutation" data-confirm="Cairkan kasbon ini?" data-description="Dana akan dicatat keluar dari Kas Besar PPN. Periksa pegawai, nominal, dan jadwal sebelum menyimpan.">
            @csrf<input type="hidden" name="jenis" value="{{ $type }}"><input type="hidden" name="submission_token" value="{{ old('submission_token',$token) }}">
            <section class="loan-form-section"><div class="loan-section-title"><span class="loan-step">01</span><h2>Informasi penerima</h2></div><div class="row g-3">
                <div class="col-md-7"><label class="form-label" for="loan-employee">Pegawai penerima</label><select class="form-select employee-select @error('karyawan_id') is-invalid @enderror" name="karyawan_id" id="loan-employee" data-placeholder="Cari dan pilih pegawai" required @error('karyawan_id') aria-invalid="true" aria-describedby="employee-error" @enderror><option value="">Cari dan pilih pegawai</option>@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected((string)old('karyawan_id') === (string)$employee->id)>{{ $employee->nama }} · {{ $employee->jabatan?->nama }}</option>@endforeach</select>@error('karyawan_id')<p class="loan-field-error" id="employee-error">{{ $message }}</p>@enderror</div>
                <div class="col-md-5"><label class="form-label" for="loan-date">Tanggal pencairan</label><input id="loan-date" class="form-control bg-light" value="{{ today()->format('d-m-Y') }}" readonly aria-describedby="date-help"><div class="form-text" id="date-help">Mengikuti tanggal transaksi disimpan.</div></div>
            </div></section>
            <section class="loan-form-section"><div class="loan-section-title"><span class="loan-step">02</span><h2>Nominal pinjaman</h2></div><label class="form-label" for="loan-amount">Nominal pinjaman (Rp)</label><div class="loan-currency"><span aria-hidden="true">Rp</span><input class="form-control" type="text" inputmode="numeric" name="nominal" id="loan-amount" value="{{ old('nominal') }}" placeholder="0" required maxlength="20" aria-describedby="amount-help @error('nominal') amount-error @enderror" @error('nominal') aria-invalid="true" @enderror></div><div class="form-text" id="amount-help">Rupiah bulat lebih dari nol. Pemisah ribuan otomatis, contoh 1.000.000.</div>@error('nominal')<p class="loan-field-error" id="amount-error">{{ $message }}</p>@enderror</section>
            @if($type === 'cicilan')
            <section class="loan-form-section"><div class="loan-section-title"><span class="loan-step">03</span><h2>Jadwal angsuran</h2></div><div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="loan-count">Jumlah cicilan</label><input type="number" min="1" class="form-control @error('cicil_kali') is-invalid @enderror" name="cicil_kali" id="loan-count" value="{{ old('cicil_kali',1) }}" required @error('cicil_kali') aria-invalid="true" aria-describedby="count-error" @enderror>@error('cicil_kali')<p class="loan-field-error" id="count-error">{{ $message }}</p>@enderror</div>
                <div class="col-md-4"><label class="form-label" for="loan-month">Mulai bulan</label><select name="mulai_bulan" id="loan-month" class="form-select @error('mulai_bulan') is-invalid @enderror" required @error('mulai_bulan') aria-invalid="true" aria-describedby="month-error" @enderror>@foreach(range(1,12) as $month)<option value="{{ $month }}" @selected((int)old('mulai_bulan',$minimumPeriod->month) === $month)>{{ \Carbon\Carbon::create(2026,$month,1)->locale('id')->monthName }}</option>@endforeach</select>@error('mulai_bulan')<p class="loan-field-error" id="month-error">{{ $message }}</p>@enderror</div>
                <div class="col-md-4"><label class="form-label" for="loan-year">Mulai tahun</label><input type="number" min="{{ $minimumPeriod->year }}" max="2155" name="mulai_tahun" id="loan-year" class="form-control @error('mulai_tahun') is-invalid @enderror" value="{{ old('mulai_tahun',$minimumPeriod->year) }}" required @error('mulai_tahun') aria-invalid="true" aria-describedby="year-error" @enderror>@error('mulai_tahun')<p class="loan-field-error" id="year-error">{{ $message }}</p>@enderror</div>
            </div><p class="form-text mt-3 mb-0">Angsuran dibulatkan ke rupiah; sisa pembulatan dilunasi pada angsuran terakhir.</p></section>
            <div class="loan-simulation" aria-live="polite" aria-atomic="true">
                <h3>Simulasi cicilan</h3>
                <p id="loan-simulation-help">Isi nominal pinjaman dan jumlah cicilan untuk melihat angsuran per bulan.</p>
                <div id="loan-simulation-values" hidden>
                    <span>Angsuran per bulan</span><strong id="loan-monthly-payment"></strong>
                    <p id="loan-payment-schedule"></p>
                    <p id="loan-limit-comparison"></p>
                </div>
            </div>
            @endif
            <div class="loan-form-actions"><span><i class="fa fa-lock" aria-hidden="true"></i>Periksa data sebelum pencairan.</span><div><a href="{{ route('billing.kasbon.index') }}" class="btn btn-outline-secondary">Batal</a><button class="btn btn-success" @disabled($employees->isEmpty() || ($type === 'potong_gaji' && now()->day < 16))>Simpan dan cairkan<i class="fa fa-arrow-right" aria-hidden="true"></i></button></div></div>
        </form>
    </div></div>
    <aside class="col-lg-4"><div class="loan-summary-card"><span class="loan-account"><i class="fa fa-university" aria-hidden="true"></i>Kas Besar PPN</span><h2>Ringkasan batas</h2><p class="loan-summary-caption">Dihitung dari penghasilan dan pinjaman pegawai yang masih berjalan.</p><div id="loan-summary" aria-live="polite" data-url="{{ route('billing.kasbon.summary') }}" data-type="{{ $type }}" data-month="{{ $minimumPeriod->month }}" data-year="{{ $minimumPeriod->year }}">Pilih pegawai untuk melihat batas kasbon.</div><p id="loan-summary-rule" class="loan-summary-rule">{{ $type === 'cicilan' ? 'Batas angsuran: 30% × (penghasilan − kasbon potong gaji belum lunas). Satu cicilan aktif per pegawai.' : 'Batas total: 50% × (penghasilan − angsuran berjalan). Sisa pengajuan dikurangi kasbon potong gaji yang belum lunas.' }}</p></div></aside>
    </div>
    <p class="form-text mt-3">Batas periode gaji adalah tanggal 25. Kasbon yang dicairkan mulai tanggal 26 masuk periode berikutnya.</p>
    <p class="loan-bottom-note"><i class="fa fa-info-circle" aria-hidden="true"></i><span>Potong gaji maksimal satu kali per pegawai per periode (26–25), termasuk yang sudah lunas; pengajuan dibatalkan dapat diajukan ulang. Penghasilan acuan non-Sales adalah gaji pokok + tunjangan jabatan + tunjangan keluarga. Untuk Sales, batas potong gaji adalah 50% komisi sampai tanggal proses dikurangi angsuran periode tersebut. Cicilan Sales tanpa batas nominal berbasis penghasilan.</span></p>
</div>
@include('db.karyawan.assets')
@endsection
@push('css')<link rel="stylesheet" href="{{ asset('kasbon-ui.css').'?v='.filemtime(public_path('kasbon-ui.css')) }}">@endpush
@push('js')<script src="{{ asset('kasbon.js').'?v='.filemtime(public_path('kasbon.js')) }}"></script>@endpush
