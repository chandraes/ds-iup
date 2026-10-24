@extends('layouts.app')
@section('content')
<div class="container iup-employees employee-detail">
    <div class="employee-detail-toolbar">
        <a href="{{ route('db.staff') }}" class="employee-back"><i class="fa fa-arrow-left" aria-hidden="true"></i> Manajemen pegawai</a>
        <a href="{{ route('db.staff.edit', $employee) }}" class="btn btn-primary rounded-3"><i class="fa fa-pencil me-2" aria-hidden="true"></i>Ubah pegawai</a>
    </div>
    <header class="employee-profile">
        <div class="employee-profile-avatar">
            @if($employee->foto_diri)
                <img src="{{ route('db.staff.document', [$employee, 'foto_diri']) }}" alt="Foto diri {{ $employee->nama }}">
            @else
                <i class="fa fa-user" aria-hidden="true"></i>
            @endif
        </div>
        <div class="employee-profile-title">
            <div class="employee-eyebrow">Profil pegawai</div>
            <h1>{{ $employee->nama }}</h1>
            <p>{{ $employee->jabatan?->nama ?? 'Jabatan belum tersedia' }} <span aria-hidden="true">&middot;</span> Nomor pegawai {{ $employee->nomor }}</p>
        </div>
        <span class="employee-status {{ $employee->status ? 'is-active' : 'is-inactive' }}"><span aria-hidden="true"></span>{{ $employee->status ? 'Aktif' : 'Nonaktif' }}</span>
    </header>
    @php
        $sections = [
            'Biodata' => ['nomor' => 'Nomor pegawai', 'nama' => 'Nama lengkap', 'nickname' => 'Nama panggilan', 'jabatan' => 'Jabatan', 'status' => 'Status pegawai', 'mulai_bekerja' => 'Tanggal mulai bekerja'],
            'Gaji & tunjangan' => ['gaji_pokok' => 'Gaji pokok', 'tunjangan_jabatan' => 'Tunjangan jabatan', 'tunjangan_keluarga' => 'Tunjangan keluarga', 'apa_bpjs_tk' => 'Ikut BPJS Ketenagakerjaan', 'apa_bpjs_kes' => 'Ikut BPJS Kesehatan'],
            'Identitas & kontak' => ['nik' => 'NIK', 'npwp' => 'NPWP', 'bpjs_tk' => 'Nomor BPJS Ketenagakerjaan', 'bpjs_kesehatan' => 'Nomor BPJS Kesehatan', 'tempat_lahir' => 'Tempat lahir', 'tanggal_lahir' => 'Tanggal lahir', 'alamat' => 'Alamat', 'no_hp' => 'Nomor HP'],
            'Rekening bank' => ['bank' => 'Nama bank', 'no_rek' => 'Nomor rekening', 'nama_rek' => 'Nama pemilik rekening'],
        ];
        if ($employee->jabatan?->isSales()) {
            $sections['Gaji & tunjangan'] += ['persen_komisi' => 'Persentase komisi', 'tunjangan_bbm' => 'Tunjangan BBM'];
        }
    @endphp
    <div class="row g-4">
        <div class="col-xl-8">
            <div class="row g-4">
                @foreach($sections as $heading => $fields)
                    <div class="col-12">
                        <section class="employee-info-card">
                            <div class="employee-section-heading">
                                <span class="employee-section-icon"><i class="fa fa-{{ ['Biodata' => 'user-o', 'Gaji & tunjangan' => 'money', 'Identitas & kontak' => 'id-card-o', 'Rekening bank' => 'bank'][$heading] }}" aria-hidden="true"></i></span>
                                <h2>{{ $heading }}</h2>
                            </div>
                            <dl class="employee-data-grid">
                                @foreach($fields as $field => $label)
                                    @php
                                        $value = $field === 'jabatan' ? $employee->jabatan?->nama : $employee->$field;
                                        if ($field === 'status') {
                                            $value = $value ? 'Aktif' : 'Nonaktif';
                                        } elseif (in_array($field, ['apa_bpjs_tk', 'apa_bpjs_kes'])) {
                                            $value = $value ? 'Ya' : 'Tidak';
                                        } elseif (in_array($field, ['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga', 'tunjangan_bbm'])) {
                                            $value = $value === null ? null : 'Rp '.number_format($value, 0, ',', '.');
                                        } elseif ($field === 'persen_komisi') {
                                            $value = $value === null ? null : rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',').' %';
                                        } elseif ($value && in_array($field, ['mulai_bekerja', 'tanggal_lahir'])) {
                                            $value = \Carbon\Carbon::parse($value)->format('d-m-Y');
                                        }
                                    @endphp
                                    <div class="employee-data-item {{ $field === 'alamat' ? 'employee-data-wide' : '' }}">
                                        <dt>{{ $label }}</dt>
                                        <dd>{{ $value === null || $value === '' ? '—' : $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </section>
                    </div>
                @endforeach
            </div>
        </div>
        <aside class="col-xl-4">
            <section class="employee-info-card employee-document-panel">
                <div class="employee-section-heading"><span class="employee-section-icon"><i class="fa fa-folder-open-o" aria-hidden="true"></i></span><h2>Dokumen pegawai</h2></div>
                <p class="employee-section-description">Identitas dan dokumen kepegawaian.</p>
                @foreach(['foto_diri' => ['Foto diri', 'picture-o'], 'foto_ktp' => ['Foto KTP', 'id-card-o'], 'perjanjian_kerja' => ['Perjanjian kerja', 'file-pdf-o']] as $document => [$label, $icon])
                    <div class="employee-document">
                        <div class="employee-document-heading"><i class="fa fa-{{ $icon }}" aria-hidden="true"></i><h3>{{ $label }}</h3></div>
                        @if($employee->$document)
                            @if($document !== 'perjanjian_kerja')
                                <a class="employee-document-preview" href="{{ route('db.staff.document', [$employee, $document]) }}" target="_blank" rel="noopener"><img src="{{ route('db.staff.document', [$employee, $document]) }}" alt="{{ $label }} {{ $employee->nama }}" loading="lazy"></a>
                            @else
                                <div class="employee-pdf-summary"><span class="employee-pdf-icon">PDF</span><div><strong>Perjanjian kerja</strong><span>Dokumen PDF tersedia</span></div></div>
                            @endif
                            <a class="employee-document-link" href="{{ route('db.staff.document', [$employee, $document]) }}" target="_blank" rel="noopener">Lihat {{ strtolower($label) }}<i class="fa fa-external-link" aria-hidden="true"></i></a>
                        @else
                            <div class="employee-document-empty"><i class="fa fa-file-o" aria-hidden="true"></i><p>{{ $document === 'perjanjian_kerja' ? 'Perjanjian kerja belum diunggah.' : 'Belum ada '.strtolower($label).'.' }}</p></div>
                        @endif
                    </div>
                @endforeach
            </section>
        </aside>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('employee-crud.css').'?v='.filemtime(public_path('employee-crud.css')) }}">
@endpush
