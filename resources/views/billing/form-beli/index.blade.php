@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="row justify-content-center">
                <div class="col-md-12 text-center">
                    <h1><u>FORM BELI</u></h1>
                </div>
            </div>
            @include('swal')

            <div class="flex-row justify-content-between mt-3">
                <div class="col-md-12">
                    <table class="table">
                        <tr class="text-center">
                            <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                        src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30">
                                    Dashboard</a></td>
                            @if (auth()->user()->role != 'asisten-admin')
                                <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                    src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                                Billing</a></td>
                            @endif

                        </tr>
                    </table>
                </div>
            </div>

            <h2 class="h5 mb-3 text-secondary"><i class="fa fa-list-alt me-1"></i> Transaksi Belum Selesai</h2>
            <div class="row">
                @if (!empty($data))
                @foreach ($data as $d)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100 border-start border-primary border-4">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                {{ $d->barang_unit?->nama ?? 'Supplier Tidak Dikenal' }} ({{$d->details_count}} Items)
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="card-text mb-1">
                                <span class="badge bg-info text-dark me-1">{{ $d->sistem_pembayaran_text }}</span>
                                <span class="badge bg-secondary">{{ $d->kas_ppn_text }}</span>
                            </p>
                             <p class="card-text mb-1">
                                @php
                                    $total = $d->kas_ppn == 1 ? floor($d->details_sum_total * ($ppnRate/100)) + $d->details_sum_total : $d->details_sum_total;
                                @endphp
                                Total Pembelian {{$d->kas_ppn == 1 ? '(Incl. PPN)' : ''}}: <strong>Rp {{ number_format($total, 0, ',', '.') }}</strong>
                            </p>
                            <div class="mt-3 d-flex justify-content-between align-items-center">
                                <form action="{{ route('billing.form-beli.delete', $d->id) }}" method="POST"
                                    class="d-flex gap-2 returDeleteForm" id="returDelete-{{ $d->id }}">
                                    @csrf
                                    <a class="btn btn-primary btn-sm"
                                        href="{{ route('billing.form-beli.detail', $d->id) }}">
                                        <i class="fa fa-play me-1"></i> Lanjutkan
                                    </a>

                                    <button type="submit" class="btn btn-danger btn-sm delete-btn"
                                        data-form-id="returDelete-{{ $d->id }}">
                                        <i class="fa fa-trash-alt me-1"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
                @else
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        <i class="fa fa-info-circle me-1"></i> Tidak ada transaksi pembelian yang belum selesai.
                    </div>
                </div>
                @endif
            </div>

            <hr class="my-4">

            <h2 class="h5 mb-4 text-primary"><i class="fa fa-plus-circle me-1"></i> Buat Transaksi Pembelian Baru</h2>
            <div class="card shadow">
                <div class="card-body">
                    <form action="{{ route('billing.form-beli.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="sistem_pembayaran" class="form-label fw-bold">
                                    <i class="fa fa-money-check-alt me-1"></i> Sistem Pembayaran
                                </label>
                                <select class="form-select" name="sistem_pembayaran" id="sistem_pembayaran" required>
                                    <option value="">Pilih Sistem Pembayaran</option>
                                    <option value="1">Cash</option>
                                    <option value="2">Tempo</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="kas_ppn" class="form-label fw-bold">
                                    Jenis Kas
                                </label>
                                <select class="form-select" name="kas_ppn" id="kas_ppn" required>
                                    <option value="">Pilih Jenis Kas</option>
                                    <option value="1">Kas PPN</option>
                                    <option value="0">Kas Non PPN</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="barang_unit_id" class="form-label fw-bold">
                                    <i class="fa fa-truck me-1"></i> Supplier
                                </label>
                                <select name="barang_unit_id" id="barang_unit_id" class="form-select select2" required>
                                    <option value="">Pilih Supplier</option>
                                    @foreach ($supplier as $k)
                                    <option value="{{ $k->id }}">
                                        {{ $k->nama }}</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih supplier yang akan menyediakan barang.</div>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fa fa-arrow-right me-1"></i> Lanjutkan
                                </button>
                                <a href="{{ route('billing') }}" class="btn btn-secondary">
                                    <i class="fa fa-undo me-1"></i> Kembali ke Billing
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endpush
@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('#barang_unit_id').select2({
            placeholder: 'Pilih Supplier',
            theme: 'bootstrap-5',
            width: '100%',
            allowClear: true
        });

        // --------------------------------------------------------
        // SWEETALERT CONFIRMATION FOR DELETE BUTTONS
        // --------------------------------------------------------
        $('.delete-btn').on('click', function(e) {
            e.preventDefault(); // Mencegah submit form default

            var formId = $(this).data('form-id');
            var $form = $('#' + formId);

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Anda akan menghapus transaksi pembelian yang belum selesai ini!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545', // Warna merah Bootstrap (danger)
                cancelButtonColor: '#6c757d', // Warna abu-abu Bootstrap (secondary)
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Jika user menekan tombol "Ya, Hapus!", submit form
                    $form.submit();
                }
            });
        });

        // --------------------------------------------------------
        // REMOVE OLD confirmAndSubmit FUNCTION (Not needed anymore)
        // --------------------------------------------------------
        // Kode confirmAndSubmit yang lama telah dihapus atau dinonaktifkan
    });

    // Kode ini tidak lagi diperlukan karena sudah diganti dengan SweetAlert di $(document).ready
    // function confirmAndSubmit(formSelector, message) { ... }

</script>
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
@endpush
