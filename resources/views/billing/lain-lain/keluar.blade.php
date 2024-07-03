@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Lain-lain Keluar</u></h1>
        </div>
    </div>
    @php
        $role = ['admin', 'su'];
    @endphp
    @include('swal')
    <form action="{{route('form-lain.keluar.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-3 mb-3">
                <label for="uraian" class="form-label">Tanggal</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="tanggal" id="tanggal" value="{{date('d M Y')}}" disabled>
            </div>
            <div class="col-md-9 mb-3">
                <label for="uraian" class="form-label">Uraian</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="uraian" id="uraian" required maxlength="20">
            </div>
            <div class="col-md-12 mb-3">
                <label for="nominal" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal'))
                    is-invalid
                @endif" name="nominal" id="nominal"  {{ !in_array(auth()->user()->role, $role) ? 'onkeyup=checkNominal()' : '' }} required>
                  </div>
                @if ($errors->has('nominal'))
                <div class="invalid-feedback">
                    {{$errors->first('nominal')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <h3>Transfer Ke</h3>
        <br>
        <div class="row">

            <div class="col-md-4 mb-3">
                <label for="nama_rek" class="form-label">Nama</label>
                <input type="text" class="form-control @if ($errors->has('nama_rek'))
                    is-invalid
                @endif" name="nama_rek" id="nama_rek" required maxlength="15">
                @if ($errors->has('nama_rek'))
                <div class="invalid-feedback">
                    {{$errors->first('nama_rek')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">Bank</label>
                <input type="text" class="form-control @if ($errors->has('bank'))
                    is-invalid
                @endif" name="bank" id="bank" required maxlength="10">
                @if ($errors->has('bank'))
                <div class="invalid-feedback">
                    {{$errors->first('bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="no_rek" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('no_rek'))
                    is-invalid
                @endif" name="no_rek" id="no_rek" required>
                @if ($errors->has('no_rek'))
                <div class="invalid-feedback">
                    {{$errors->first('no_rek')}}
                </div>
                @endif
            </div>
        </div>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{route('billing')}}" class="btn btn-secondary" type="button">Batal</a>
          </div>
    </form>
</div>
@endsection
@push('js')
    <script src="{{asset('assets/js/cleave.min.js')}}"></script>
    <script>
        function checkNominal() {
            var nominal = document.getElementById('nominal').value;
            nominal = nominal.replace(/\./g, '');
            var batasan = {!! $batasan !!};
            if (nominal > batasan) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Nominal melebihi batasan!',
                })
                document.getElementById('nominal').value = '';
            }
        }
        var nominal = new Cleave('#nominal', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        var no_rek = new Cleave('#no_rek', {
            delimiter: '-',
            blocks: [4, 4, 8]
        });
        // masukForm on submit, sweetalert confirm
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
