@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Biodata Direksi / Staff</u></h1>
        </div>
    </div>
    <form action="{{route('db.staff.store')}}" method="post" enctype="multipart/form-data" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control @if ($errors->has('nama'))
                    is-invalid
                @endif" name="nama" id="nama" required value="{{old('nama')}}">
                @if ($errors->has('nama'))
                <div class="invalid-feedback">
                    {{$errors->first('nama')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="nickname" class="form-label">Nickname</label>
                <input type="text" class="form-control @if ($errors->has('nickname'))
                    is-invalid
                @endif" name="nickname" id="nickname" required value="{{old('nickname')}}">
                @if ($errors->has('nickname'))
                <div class="invalid-feedback">
                    {{$errors->first('nickname')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="jabatan_id" class="form-label">Jabatan</label>
                <select class="form-select @if ($errors->has('jabatan_id'))
                    is-invalid
                @endif" name="jabatan_id" id="jabatan_id" required>
                    <option value="">-- Pilih --</option>
                    @foreach ($jabatan as $j)
                    <option value="{{$j->id}}">{{$j->nama}}</option>
                    @endforeach
                </select>
                @if ($errors->has('jabatan_id'))
                <div class="invalid-feedback">
                    {{$errors->first('jabatan_id')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <h3>Gaji & Tunjangan</h3>
        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <label for="gaji_pokok" class="form-label">Gaji Pokok</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('gaji_pokok'))
                    is-invalid
                @endif" name="gaji_pokok" id="gaji_pokok" required value="{{old('gaji_pokok')}}">
                </div>
                @if ($errors->has('gaji_pokok'))
                <div class="invalid-feedback">
                    {{$errors->first('gaji_pokok')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="tunjangan_jabatan" class="form-label">Tunjangan Jabatan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('tunjangan_jabatan'))
                    is-invalid
                @endif" name="tunjangan_jabatan" id="tunjangan_jabatan" value="{{old('tunjangan_jabatan')}}" required>
                </div>
                @if ($errors->has('tunjangan_jabatan'))
                <div class="invalid-feedback">
                    {{$errors->first('tunjangan_jabatan')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="tunjangan_keluarga" class="form-label">Tunjangan Keluarga</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('tunjangan_keluarga'))
                    is-invalid
                @endif" name="tunjangan_keluarga" id="tunjangan_keluarga" value="{{old('tunjangan_keluarga')}}" required>
                </div>
                @if ($errors->has('tunjangan_keluarga'))
                <div class="invalid-feedback">
                    {{$errors->first('tunjangan_keluarga')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="nik" class="form-label">NIK</label>
                <input type="text" class="form-control @if ($errors->has('nik'))
                    is-invalid
                @endif" name="nik" id="nik" required value="{{old('nik')}}">
                @if ($errors->has('nik'))
                <div class="invalid-feedback">
                    {{$errors->first('nik')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="npwp" class="form-label">NPWP</label>
                <input type="text" class="form-control @if ($errors->has('npwp'))
                    is-invalid
                @endif" name="npwp" id="npwp" required value="{{old('npwp')}}">
                @if ($errors->has('npwp'))
                <div class="invalid-feedback">
                    {{$errors->first('npwp')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="bpjs_tk" class="form-label">Nomor BPJS Tenaga Kerja</label>
                <input type="text" class="form-control @if ($errors->has('bpjs_tk'))
                    is-invalid
                @endif" name="bpjs_tk" id="bpjs_tk" required value="{{old('bpjs_tk')}}">
                @if ($errors->has('bpjs_tk'))
                <div class="invalid-feedback">
                    {{$errors->first('bpjs_tk')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="bpjs_kesehatan" class="form-label">Nomor BPJS Kesehatan</label>
                <input type="text" class="form-control @if ($errors->has('bpjs_kesehatan'))
                    is-invalid
                @endif" name="bpjs_kesehatan" id="bpjs_kesehatan" required value="{{old('bpjs_kesehatan')}}">
                @if ($errors->has('bpjs_kesehatan'))
                <div class="invalid-feedback">
                    {{$errors->first('bpjs_kesehatan')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="tempat_lahir" class="form-label">Tempat Lahir</label>
                <input type="text" class="form-control @if ($errors->has('tempat_lahir')) is-invalid @endif" name="tempat_lahir" id="tempat_lahir" required value="{{old('tempat_lahir')}}">
                @if ($errors->has('tempat_lahir'))
                <div class="invalid-feedback">
                    {{$errors->first('tempat_lahir')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control @if ($errors->has('tanggal_lahir')) is-invalid @endif" name="tanggal_lahir" id="tanggal_lahir" required value="{{old('tanggal_lahir')}}">
                @if ($errors->has('tanggal_lahir'))
                <div class="invalid-feedback">
                    {{$errors->first('tanggal_lahir')}}
                </div>
                @endif
            </div>
            <div class="col-md-12 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control @if ($errors->has('alamat')) is-invalid @endif" name="alamat" id="alamat" rows="3" required>{{old('alamat')}}</textarea>
                @if ($errors->has('alamat'))
                <div class="invalid-feedback">
                    {{$errors->first('alamat')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="no_hp" class="form-label">Nomor HP</label>
                <input type="text" class="form-control @if ($errors->has('no_hp')) is-invalid @endif" name="no_hp" id="no_hp" required value="{{old('no_hp')}}">
                @if ($errors->has('no_hp'))
                <div class="invalid-feedback">
                    {{$errors->first('no_hp')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">Nama Bank</label>
                <input type="text" class="form-control @if ($errors->has('bank')) is-invalid @endif" name="bank" id="bank" required value="{{old('bank')}}">
                @if ($errors->has('bank'))
                <div class="invalid-feedback">
                    {{$errors->first('bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="no_rek" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('no_rek')) is-invalid @endif" name="no_rek" id="no_rek" required value="{{old('no_rek')}}">
                @if ($errors->has('no_rek'))
                <div class="invalid-feedback">
                    {{$errors->first('no_rek')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="nama_rek" class="form-label">Nama Rekening</label>
                <input type="text" class="form-control @if ($errors->has('nama_rek')) is-invalid @endif" name="nama_rek" id="nama_rek" required value="{{old('nama_rek')}}">
                @if ($errors->has('nama_rek'))
                <div class="invalid-feedback">
                    {{$errors->first('nama_rek')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="mulai_bekerja" class="form-label">Mulai Bekerja</label>
                <input type="date" class="form-control @if ($errors->has('mulai_bekerja')) is-invalid @endif" name="mulai_bekerja" id="mulai_bekerja" required value="{{old('mulai_bekerja')}}">
                @if ($errors->has('mulai_bekerja'))
                <div class="invalid-feedback">
                    {{$errors->first('mulai_bekerja')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select @if ($errors->has('status')) is-invalid @endif" name="status" id="status" required>
                    <option value="1" selected>Aktif</option>
                    <option value="0">Non-aktif</option>
                </select>
                @if ($errors->has('status'))
                <div class="invalid-feedback">
                    {{$errors->first('status')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="foto_ktp" class="form-label ">Foto KTP</label>
                <input type="file" class="form-control @if ($errors->has('foto_ktp')) is-invalid @endif" name="foto_ktp" id="foto_ktp" required>
                @if ($errors->has('foto_ktp'))
                <div class="invalid-feedback">
                    {{$errors->first('foto_ktp')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="foto_diri" class="form-label">Foto Diri</label>
                <input type="file" class="form-control @if ($errors->has('foto_diri')) is-invalid @endif" name="foto_diri" id="foto_diri" required>
                @if ($errors->has('foto_diri'))
                <div class="invalid-feedback">
                    {{$errors->first('foto_diri')}}
                </div>
                @endif
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('db.staff')}}" class="btn btn-secondary" type="button">Batal</a>
          </div>
    </form>
</div>
@endsection
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
    <script>
           $(document).ready(function(){
            var gaji_pokok = new Cleave('#gaji_pokok', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
            var tunjangan_jabatan = new Cleave('#tunjangan_jabatan', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
            var tunjangan_keluarga = new Cleave('#tunjangan_keluarga', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

        });

        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah anda yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
    </script>
@endpush
