<div class="container iup-employees">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><div class="text-primary small fw-bold text-uppercase">Direksi & Staff</div><h2 class="fw-bold mb-1">{{ $editing ? 'Ubah pegawai' : 'Tambah pegawai' }}</h2><p class="text-muted mb-0">Lengkapi biodata, penggajian, dan dokumen pegawai.</p></div><a class="btn btn-outline-secondary" href="{{ route('db.staff') }}"><i class="fa fa-arrow-left fa-fw me-1" aria-hidden="true"></i>Kembali ke daftar</a></div>
@include('db.karyawan.feedback')
<form class="employee-mutation" data-confirm="{{ $editing ? 'Simpan perubahan pegawai ini?' : 'Tambahkan pegawai ini?' }}" action="{{ $editing ? route('db.staff.update',$employee) : route('db.staff.store') }}" method="post" enctype="multipart/form-data">
@csrf
@if($editing) @method('PATCH') @endif
<div class="row g-4"><div class="col-lg-3"><nav class="card border-0 shadow-sm rounded-4 p-3 employee-nav"><strong class="mb-2">Informasi pegawai</strong>@foreach(['identity'=>'Biodata','salary'=>'Gaji & tunjangan','personal'=>'Identitas & kontak','bank'=>'Rekening bank','documents'=>'Dokumen'] as $id=>$label)<a class="nav-link py-2" href="#employee-section-{{ $id }}">{{ $label }}</a>@endforeach</nav></div><div class="col-lg-9">
@php
$sections = [
'identity'=>['Biodata',['nama'=>'Nama lengkap','nickname'=>'Nama panggilan','jabatan_id'=>'Jabatan','status'=>'Status pegawai','mulai_bekerja'=>'Tanggal mulai bekerja']],
'salary'=>['Gaji & tunjangan',['gaji_pokok'=>'Gaji pokok','tunjangan_jabatan'=>'Tunjangan jabatan','tunjangan_keluarga'=>'Tunjangan keluarga','apa_bpjs_tk'=>'Ikut BPJS Ketenagakerjaan','apa_bpjs_kes'=>'Ikut BPJS Kesehatan']],
'personal'=>['Identitas & kontak',['nik'=>'NIK','npwp'=>'NPWP','bpjs_tk'=>'Nomor BPJS Ketenagakerjaan','bpjs_kesehatan'=>'Nomor BPJS Kesehatan','tempat_lahir'=>'Tempat lahir','tanggal_lahir'=>'Tanggal lahir','alamat'=>'Alamat','no_hp'=>'Nomor HP']],
'bank'=>['Rekening bank',['bank'=>'Nama bank','no_rek'=>'Nomor rekening','nama_rek'=>'Nama pemilik rekening']]];
@endphp
@foreach($sections as $id=>[$heading,$fields])<section id="employee-section-{{ $id }}" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><h5 class="fw-bold mb-4">{{ $heading }}</h5><div class="row g-3">
@foreach($fields as $field=>$label)<div class="{{ $field==='alamat' ? 'col-12' : 'col-md-6' }} employee-field"><label class="form-label fw-semibold" for="{{ $field }}">{{ $label }}</label>
@if($field==='jabatan_id')<select id="{{ $field }}" name="{{ $field }}" class="form-select employee-select" required><option value="">Cari dan pilih jabatan</option>@foreach($jabatan as $j)<option data-is-sales="{{ $j->isSales() ? 1 : 0 }}" value="{{ $j->id }}" @selected((string)old($field,$employee->$field)===(string)$j->id)>{{ $j->nama }}</option>@endforeach</select>
@elseif(in_array($field,['status','apa_bpjs_tk','apa_bpjs_kes']))<select id="{{ $field }}" name="{{ $field }}" class="form-select" required>@foreach([1=>($field==='status'?'Aktif':'Ya'),0=>($field==='status'?'Nonaktif':'Tidak')] as $value=>$text)<option value="{{ $value }}" @selected((string)old($field,$employee->$field ?? ($field==='status'?1:0))===(string)$value)>{{ $text }}</option>@endforeach</select>
@elseif($field==='alamat')<textarea class="form-control" id="{{ $field }}" name="{{ $field }}" rows="3" required>{{ old($field,$employee->$field) }}</textarea>
@else<input class="form-control @error($field) is-invalid @enderror {{ in_array($field,['gaji_pokok','tunjangan_jabatan','tunjangan_keluarga'])?'employee-money':'' }}" id="{{ $field }}" name="{{ $field }}" type="{{ in_array($field,['mulai_bekerja','tanggal_lahir'])?'date':'text' }}" value="{{ old($field,$employee->$field ?? (in_array($field,['gaji_pokok','tunjangan_jabatan','tunjangan_keluarga'])?0:'')) }}" required @if(!in_array($field,['mulai_bekerja','tanggal_lahir'])) maxlength="255" @endif>@endif
@error($field)<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>@endforeach
</div>
@if($id === 'salary')
@php($salesSelected = $jabatan->firstWhere('id', old('jabatan_id', $employee->jabatan_id))?->isSales() ?? false)
<div class="employee-sales-compensation mt-4" @if(!$salesSelected) hidden @endif>
<h6 class="fw-bold">Kompensasi Sales</h6><p class="small text-muted">Wajib diisi untuk pegawai dengan jabatan Sales.</p>
<div class="row g-3">
<div class="col-md-6"><label class="form-label fw-semibold" for="persen_komisi">Persentase komisi (%)</label><input class="form-control" type="number" min="0" max="100" step="0.01" name="persen_komisi" id="persen_komisi" value="{{ old('persen_komisi', $employee->persen_komisi) }}" @required($salesSelected) @disabled(!$salesSelected)>@error('persen_komisi')<div class="text-danger small">{{ $message }}</div>@enderror</div>
<div class="col-md-6"><label class="form-label fw-semibold" for="tunjangan_bbm">Tunjangan BBM (Rp)</label><input class="form-control employee-money" type="text" inputmode="numeric" name="tunjangan_bbm" id="tunjangan_bbm" value="{{ old('tunjangan_bbm', $employee->tunjangan_bbm) }}" @required($salesSelected) @disabled(!$salesSelected)>@error('tunjangan_bbm')<div class="text-danger small">{{ $message }}</div>@enderror</div>
</div></div>
@endif
</div></section>@endforeach
<section id="employee-section-documents" class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4"><h5 class="fw-bold"><i class="fa fa-file-image-o fa-fw me-1" aria-hidden="true"></i>Dokumen pegawai</h5><p class="small text-muted">JPG atau PNG, maksimal 10 MB per foto. {{ $editing?'Kosongkan jika tidak mengganti foto.':'Kedua foto wajib diunggah.' }}</p><div class="row g-3">@foreach(['foto_diri'=>'Foto diri','foto_ktp'=>'Foto KTP'] as $field=>$label)<div class="col-md-6"><label class="form-label fw-semibold" for="{{ $field }}">{{ $label }}</label><input class="form-control employee-photo" type="file" name="{{ $field }}" id="{{ $field }}" accept="image/jpeg,image/png" @required(!$editing)><div class="employee-preview mt-3"><img alt="Pratinjau {{ $label }}" @if($employee->$field) src="{{ asset('storage/'.preg_replace('#^public/#','',$employee->$field)) }}" @else hidden @endif><span class="text-muted small" @if($employee->$field) hidden @endif>Belum ada foto</span></div>@error($field)<div class="text-danger small">{{ $message }}</div>@enderror</div>@endforeach</div></div></section>
<section class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-4">
<h5 class="fw-bold">Perjanjian kerja</h5>
<label class="form-label fw-semibold" for="perjanjian_kerja">Dokumen perjanjian kerja</label>
<input class="form-control @error('perjanjian_kerja') is-invalid @enderror" type="file" id="perjanjian_kerja" name="perjanjian_kerja" accept="application/pdf,.pdf">
<p class="small text-muted mt-2">Opsional. Hanya PDF, maksimal 5 MB. Kosongkan jika tidak mengganti dokumen.</p>
@error('perjanjian_kerja')<div class="text-danger small">{{ $message }}</div>@enderror
@if($employee->perjanjian_kerja)<a href="{{ route('db.staff.document', [$employee, 'perjanjian_kerja']) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary"><i class="fa fa-file-pdf-o me-1" aria-hidden="true"></i>Lihat perjanjian kerja</a>@endif
</div></section>
<div class="employee-save card border-0 shadow p-3 d-flex flex-row flex-wrap align-items-center justify-content-between gap-3"><span class="text-muted small">Periksa kembali sebelum menyimpan.</span><div class="d-flex gap-2"><a href="{{ route('db.staff') }}" class="btn btn-outline-secondary"><i class="fa fa-times fa-fw me-1" aria-hidden="true"></i>Batal</a><button class="btn btn-primary" type="submit"><i class="fa fa-save fa-fw me-1" aria-hidden="true"></i>Simpan pegawai</button></div></div>
</div></div></form></div>
@include('db.karyawan.assets')
