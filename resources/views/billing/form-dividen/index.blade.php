@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Deviden</u></h1>
        </div>
    </div>
    @include('swal')
    <form action="{{ route('billing.form-dividen.store') }}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-4 mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="text" class="form-control @error('tanggal') is-invalid @enderror" name="tanggal" id="tanggal" value="{{ date('d-m-Y') }}" readonly>
                @error('tanggal')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
                @enderror
            </div>
            <div class="col-md-4 mb-3">
                <label for="nominal" class="form-label">Nominal</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @error('nominal') is-invalid @enderror" name="nominal" id="nominal" required data-thousands=".">
                    @error('nominal')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                    @enderror
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="mb-3">
                    <label for="ppn_kas" class="form-label">Kas</label>
                    <select class="form-select" name="ppn_kas" id="ppn_kas" required>
                        <option value="" disabled selected>-- Pilih Kas Besar --</option>
                        <option value="1">Kas Besar PPN</option>
                        <option value="0" >Kas Besar NON PPN</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            @foreach ($persen as $i)
            <div class="col-md-12 mb-3 mt-2">
                <h2 class="text-center"><label for="nilai-{{ $i->id }}" class="form-label">{{ ucfirst($i->nama) }} ({{ $i->persentase }}%)</label></h2>
                <hr>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control nilai-persentase" name="nilai-{{ $i->id }}" id="nilai-{{ $i->id }}" disabled data-thousands=".">
                </div>
            </div>
            @if ($i->nama == 'pengelola')
            @foreach ($pengelola as $d)
            <div class="col-md-6 mb-3">
                <label for="nilai_pengelola_{{ $d->id }}" class="form-label">{{ $d->nama }} ({{ $d->persentase }}%)</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control nilai-pengelola" name="nilai_pengelola_{{ $d->id }}" disabled data-thousands=".">
                </div>
            </div>
            @endforeach
            @endif
            @if ($i->nama == 'investor')
            @foreach ($investor as $inv)
            <div class="col-md-6 mb-3">
                <label for="nilai_investor_{{ $inv->id }}" class="form-label">{{ $inv->nama }} ({{ $inv->persentase }}%)</label>
                <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control nilai-investor" name="nilai_investor_{{ $inv->id }}" disabled data-thousands=".">
                </div>
            </div>
            @endforeach
            @endif
            <br>
            <hr>
            <br>
            @endforeach
        </div>
        <hr>
        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-success" type="submit">Simpan</button>
            <a href="{{ route('billing') }}" class="btn btn-secondary" type="button">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/moment.min.js')}}"></script>
<script>
        $(function() {
            var nominal = new Cleave('#nominal', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const nominalInput = document.getElementById('nominal');
            const persen = @json($persen);  // Ambil data persentase dari backend
            const pengelola = @json($pengelola);
            const investor = @json($investor);

            nominalInput.addEventListener('input', function() {
                const nominal = parseFloat(this.value.replace(/\./g, '')) || 0;

                persen.forEach(item => {
                    const nilai = (nominal * item.persentase) / 100;
                    document.getElementById(`nilai-${item.id}`).value = formatRupiah(nilai);

                    if (item.nama === 'pengelola') {
                        pengelola.forEach(d => {
                            const nilaiPengelola = (nilai * d.persentase) / 100;
                            document.querySelector(`input[name="nilai_pengelola_${d.id}"]`).value = formatRupiah(nilaiPengelola);
                        });
                    }

                    if (item.nama === 'investor') {
                        investor.forEach(inv => {
                            const nilaiInvestor = (nilai * inv.persentase) / 100;
                            document.querySelector(`input[name="nilai_investor_${inv.id}"]`).value = formatRupiah(nilaiInvestor);
                        });
                    }
                });
            });

            function formatRupiah(value) {
                return value.toLocaleString('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).replace('Rp', '').trim();
            }
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
