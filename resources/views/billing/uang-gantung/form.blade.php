@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="row justify-content-center mb-4">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold text-uppercase text-decoration-underline">Form Uang Gantung</h2>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-md-5">
                    <form action="{{route('billing.uang-gantung.form.store')}}" method="post" id="masukForm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="ppn_kas" class="form-label fw-semibold">Kas</label>
                                <select class="form-select" name="ppn_kas" id="ppn_kas" required>
                                    <option value="" disabled selected>-- Pilih Kas Besar --</option>
                                    <option value="1" {{old('ppn_kas')==1 ? 'selected' : '' }}>Kas Besar PPN</option>
                                    <option value="0" {{old('ppn_kas')==1 ? 'selected' : '' }}>Kas Besar NON PPN
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal" class="form-label fw-semibold">Tanggal</label>
                                <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                                    name="tanggal" id="tanggal" value="{{date('Y-m-d')}}" max="{{date('Y-m-d')}}"
                                    required>
                                @error('tanggal')
                                <div class="invalid-feedback">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="keterangan" class="form-label fw-semibold">Pengirim</label>
                                <input type="text" class="form-control @error('keterangan') is-invalid @enderror"
                                    name="keterangan" id="keterangan" placeholder="Masukkan nama pengirim" required
                                    maxlength="20">
                                @error('keterangan')
                                <div class="invalid-feedback">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-4">
                                <label for="nominal" class="form-label fw-semibold">Nominal</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold" id="basic-addon1">Rp</span>
                                    <input type="text"
                                        class="form-control form-control-lg @error('nominal') is-invalid @enderror"
                                        name="nominal" id="nominal" placeholder="0" required>
                                </div>
                                @error('nominal')
                                <div class="invalid-feedback text-danger mt-1" style="font-size: 0.875em;">
                                    {{$message}}
                                </div>
                                @enderror
                            </div>
                        </div>

                        <hr class="text-muted my-4">

                        <div class="d-flex flex-column flex-md-row gap-2 justify-content-end">
                            <a href="{{route('billing')}}" class="btn btn-secondary px-4">Batal</a>
                            <button class="btn btn-primary px-4" type="submit">
                                <i class="fa fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
            // Inisialisasi Cleave.js untuk format mata uang
            var nominal = new Cleave('#nominal', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            // masukForm on submit, sweetalert confirm
            $('#masukForm').submit(function(e){
                e.preventDefault();
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "Data Uang Gantung akan disimpan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#198754', // Warna hijau success
                    cancelButtonColor: '#6c757d', // Warna abu-abu secondary
                    confirmButtonText: 'Ya, simpan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Jika Anda punya element spinner, pastikan ID-nya sesuai
                        $('#spinner').show();
                        this.submit();
                    }
                });
            });
        });
</script>
@endpush
