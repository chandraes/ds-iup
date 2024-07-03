@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Pengembalian Deposit All</u></h1>
        </div>
    </div>
    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '{{session('error')}}',
        })
    </script>
    @endif
    <form action="{{route('form-deposit.keluar-all.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="uraian" class="form-label">Tanggal</label>
                <input type="text" class="form-control @if ($errors->has('uraian'))
                    is-invalid
                @endif" name="uraian" id="uraian" required value="{{date('d M Y')}}" disabled>
            </div>
            <div class="col-md-6 mb-3">
                <label for="nominal" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('nominal'))
                    is-invalid
                @endif" name="nominal" id="nominal" required data-thousands="." value="{{old('nominal')}}">
                </div>
                @if ($errors->has('nominal'))
                <div class="invalid-feedback">
                    {{$errors->first('nominal')}}
                </div>
                @endif
            </div>
        </div>
        <hr>
        <div class="row">
            @foreach ($investor as $i)
                @if ($i->persentase > 0)
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama</label>
                        <input type="text" class="form-control" name="nama" id="nama" value="{{$i->nama}}" disabled/>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nominal" class="form-label">Nominal ({{$i->persentase}}%)</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp</span>
                            <input type="text" class="form-control @if ($errors->has('nominal'))
                            is-invalid
                        @endif" name="nilai" required data-thousands="." value="{{old('nominal')}}" disabled id="nilai-{{$i->id}}" >
                        </div>
                        @if ($errors->has('nominal'))
                        <div class="invalid-feedback">
                            {{$errors->first('nominal')}}
                        </div>
                        @endif
                        <small id="infoText-{{$i->id}}" class="form-text text-danger"></small>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit" id="btnSubmit">Simpan</button>
            <a href="{{route('billing')}}" class="btn btn-secondary" type="button">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    $('#investor_modal_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih Investor Modal'
        });

        var nominal = new Cleave('#nominal', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        $('#nominal').on('keyup', function(){
            let val = $(this).val().replace(/\./g,'');
            let dataInvestor = {!! json_encode($investor) !!};

            dataInvestor.forEach(function(investor) {
                let persen = investor.persentase;
                let hasil = val * persen / 100;
                if (hasil > investor.modal) {
                    $('#nilai-'+investor.id).addClass('is-invalid');
                    $('#infoText-'+investor.id).text('Nilai melebihi modal investor');
                    // hide btnSubmit
                    $('#btnSubmit').hide();

                } else {
                    $('#nilai-'+investor.id).removeClass('is-invalid');
                    $('#infoText-'+investor.id).text('');
                    // show btnSubmit
                    $('#btnSubmit').show();
                }
                $('#nilai-'+investor.id).val(hasil.toLocaleString('id-ID'));

            });
        });
        // masukForm on submit, sweetalert confirm
        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
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
