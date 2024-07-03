@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Edit Informasi {{Str::ucfirst($data->untuk)}}</h1>
        </div>
    </div>
    <form action="{{route('pengaturan.aplikasi.update', $data->id)}}" method="post" enctype="multipart/form-data" id="masukForm">
        @csrf
        @method('PATCH')
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="nama" class="form-label">Nama Perusahaan</label>
                <input type="text" class="form-control @if ($errors->has('nama'))
                    is-invalid
                @endif" name="nama" id="nama" required value="{{$data->nama}}">
                @if ($errors->has('nama'))
                <div class="invalid-feedback">
                    {{$errors->first('nama')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="singkatan" class="form-label">Singkatan</label>
                <input type="text" class="form-control @if ($errors->has('singkatan'))
                    is-invalid
                @endif" name="singkatan" id="singkatan" required value="{{$data->singkatan}}">
                @if ($errors->has('singkatan'))
                <div class="invalid-feedback">
                    {{$errors->first('singkatan')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="nama_direktur" class="form-label">Nama Direktur</label>
                <input type="text" class="form-control @if ($errors->has('nama_direktur'))
                    is-invalid
                @endif" name="nama_direktur" id="nama_direktur" required value="{{$data->nama_direktur}}">
                @if ($errors->has('nama_direktur'))
                <div class="invalid-feedback">
                    {{$errors->first('nama_direktur')}}
                </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="alamat" class="form-label">Alamat</label>
                <textarea class="form-control @if ($errors->has('alamat')) is-invalid @endif" name="alamat" id="alamat" rows="3" required>{{$data->alamat}}</textarea>
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
                <label for="kode_pos" class="form-label">Kode Pos</label>
                <input type="text" class="form-control @if ($errors->has('kode_pos')) is-invalid @endif" name="kode_pos" id="kode_pos" required value="{{$data->kode_pos}}">
                @if ($errors->has('kode_pos'))
                <div class="invalid-feedback">
                    {{$errors->first('kode_pos')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="logo" class="form-label ">Logo <span class="text-danger">(*Kosongkan jika tidak ingin mengganti)</span></label>
                <input type="file" class="form-control @if ($errors->has('logo')) is-invalid @endif" name="logo" id="logo">
                @if ($errors->has('logo'))
                <div class="invalid-feedback">
                    {{$errors->first('logo')}}
                </div>
                @endif
            </div>
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('pengaturan.aplikasi')}}" class="btn btn-secondary" type="button">Batal</a>
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
