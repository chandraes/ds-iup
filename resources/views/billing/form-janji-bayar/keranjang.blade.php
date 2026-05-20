@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('billing.form-janji-bayar') }}">Janji Bayar</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Keranjang Belanja</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800"><i class="fa fa-shopping-cart text-success"></i> Keranjang Janji Bayar</h1>
        </div>
        <a href="{{ route('billing.form-janji-bayar') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left"></i> Kembali Pilih Invoice
        </a>
    </div>

  @include('swal')


    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="m-0 fw-bold"><i class="fa fa-list"></i> Daftar Invoice Terpilih (Konsumen: {{ $konsumen->kode_toko?->kode.' '.$konsumen->nama }})</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center px-3" width="5%">No</th>
                                    <th>Kode Nota</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Sisa Tagihan</th>
                                    <th class="text-center" width="15%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($cartItems as $index => $item)
                                    <tr id="row-cart-{{ $item->invoice_jual_id }}">
                                        <td class="text-center px-3">{{ $index + 1 }}</td>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $item->invoice_jual->kode }}</span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($item->invoice_jual->created_at)->format('d-m-Y') }}</td>
                                        <td class="text-end fw-bold text-danger">
                                            Rp {{ number_format($item->invoice_jual->sisa_tagihan, 0, ',', '.') }}
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-item" data-id="{{ $item->invoice_jual_id }}" data-nominal="{{ $item->invoice_jual->sisa_tagihan }}">
                                                <i class="fa fa-trash"></i> Hapus
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 border-top border-success border-4">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-success"><i class="fa fa-calculator"></i> Ringkasan & Eksekusi Data</h6>
                </div>
                <form action="{{ route('billing.form-janji-bayar.checkout') }}" method="POST" class="card-body">
                    @csrf

                    <div class="mb-3 p-3 bg-light rounded">
                        <small class="text-muted d-block">Konsumen Terpilih:</small>
                        <strong class="text-dark d-block mb-2 h6">{{ $konsumen->kode_toko?->kode.' '.$konsumen->nama }}</strong>

                        <small class="text-muted d-block">Total Sisa Tagihan Keranjang:</small>
                        <strong class="text-danger h4 fw-black" id="textTotalSisa">Rp {{ number_format($totalSisaTagihan, 0, ',', '.') }}</strong>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Unik Dokumen</label>
                        <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode') }}" placeholder="Masukkan nomor dokumen unik" required>
                        @error('kode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Metode Pembayaran</label>
                        <select name="metode" id="selectMetode" class="form-select @error('metode') is-invalid @enderror" required>
                            <option value="">-- Pilih Metode Pembayaran --</option>
                            @foreach($metodeBayars as $mb)
                                <option value="{{ $mb->slug }}" data-maxhari="{{ $mb->max_hari }}" {{ old('metode') == $mb->slug ? 'selected' : '' }}>
                                    {{ $mb->nama }} (Maks +{{ $mb->max_hari }} Hari)
                                </option>
                            @endforeach
                        </select>
                        @error('metode')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                  <div class="mb-3">
                        <label class="form-label fw-bold">Nominal Janji Bayar</label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="nominal" id="inputNominal" class="form-control nominal-cleave bg-light @error('nominal') is-invalid @enderror" value="{{ old('nominal', $totalSisaTagihan) }}" placeholder="0" readonly required>
                        </div>
                        <div class="form-text text-muted small"><i class="fa fa-info-circle"></i> Nominal otomatis disesuaikan dengan total sisa tagihan di keranjang dan tidak dapat diubah.</div>
                        @error('nominal')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Tanggal Jatuh Tempo</label>
                        <input type="date" name="jatuh_tempo" id="inputJatuhTempo" class="form-control @error('jatuh_tempo') is-invalid @enderror" value="{{ old('jatuh_tempo') }}" disabled required>
                        @error('jatuh_tempo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-bold shadow-sm" id="btnSubmitForm">
                        <i class="fa fa-check-circle"></i> Eksekusi & Simpan Janji Bayar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {

       let todayStr = new Date().toISOString().split('T')[0];
        $('#inputJatuhTempo').attr('min', todayStr);

        // 2. Logika Interaksi antar Input (Metode -> Jatuh Tempo)
        $('#selectMetode').change(function() {
            let selectedOption = $(this).find(':selected');
            let maxHari = selectedOption.data('maxhari');
            let valueMetode = $(this).val();

            // Cek apakah user sudah memilih metode pembayaran (bukan opsi kosong)
            if (valueMetode !== "") {
                // Buka proteksi disabled pada input tanggal
                $('#inputJatuhTempo').removeAttr('disabled');

                if (maxHari !== undefined) {
                    let maxDate = new Date();
                    maxDate.setDate(maxDate.getDate() + parseInt(maxHari));
                    let maxDateStr = maxDate.toISOString().split('T')[0];

                    // Pasang batasan maksimal tanggal kalender yang bisa diklik user
                    $('#inputJatuhTempo').attr('max', maxDateStr);

                    // Reset tanggal jika tanggal yang terpilih sebelumnya melampaui batas baru
                    if($('#inputJatuhTempo').val() > maxDateStr){
                        $('#inputJatuhTempo').val('');
                    }
                } else {
                    $('#inputJatuhTempo').removeAttr('max');
                }
            } else {
                // Jika user mengembalikan pilihan ke "-- Pilih Metode --", kunci kembali dan bersihkan nilainya
                $('#inputJatuhTempo').attr('disabled', true).val('').removeAttr('max');
            }
        });

        // 3. Trigger change saat halaman reload jika ada nilai 'old' input dari Laravel (Gagal validasi server)
        if($('#selectMetode').val()) {
            $('#selectMetode').trigger('change');
        }
        // Simpan nilai totalSisaTagihan awal dari PHP ke variabel JavaScript global
        let totalSisaTagihan = {{ $totalSisaTagihan }};

        // 1. INISIALISASI CLEAVE.JS UNTUK INPUT NOMINAL
        let nominalCleave = new Cleave('#inputNominal', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            delimiter: '.',
            numeralDecimalMark: ',',
            numeralDecimalScale: 0
        });

        // Fungsi Helper untuk merubah angka biasa ke format mata uang Rupiah (untuk teks statis)
        function formatRupiah(angka) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);
        }

        // HAPUS ITEM TERTENTU DARI KERANJANG VIA AJAX
        $('.btn-delete-item').click(function(e) {
            e.preventDefault();
            let btn = $(this);
            let invoiceId = btn.data('id');
            let nominalItem = parseFloat(btn.data('nominal')) || 0;

            Swal.fire({
                title: 'Hapus Item?',
                text: "Invoice ini akan dikeluarkan dari keranjang belanja.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('billing.form-janji-bayar.hapus-keranjang') }}",
                        type: "POST",
                        data: {
                            _token: "{{ csrf_token() }}",
                            invoice_jual_id: invoiceId
                        },
                        success: function(res) {
                            totalSisaTagihan -= nominalItem;
                            $('#textTotalSisa').text(formatRupiah(totalSisaTagihan));

                            // PERUBAHAN: Paksa nilai nominal untuk selalu mengikuti totalSisaTagihan yang baru
                            nominalCleave.setRawValue(totalSisaTagihan);

                            $(`#row-cart-${invoiceId}`).fadeOut(400, function() {
                                $(this).remove();
                                if ($('tbody tr').length === 0) {
                                    window.location.href = "{{ route('billing.form-janji-bayar') }}";
                                }
                            });

                            Swal.fire('Terhapus!', 'Item berhasil dikeluarkan.', 'success');
                        }
                    });
                }
            });
        });

        // SWEETALERT CONFIRMATION SEBELUM SUBMIT FORM
        $('form').submit(function(e) {
            let form = this;
            e.preventDefault(); // Hentikan submit form default secara paksa

            // Ambil angka murni tanpa titik pemisah dari Cleave.js
            let inputNominalRaw = parseFloat(nominalCleave.getRawValue()) || 0;

            // 1. Validasi Client-side: Cek apakah nominal kurang dari total tagihan
            if (inputNominalRaw !== totalSisaTagihan) {
                Swal.fire({
                    icon: 'error',
                    title: 'Nominal Tidak Sesuai!',
                    text: 'Nominal janji bayar wajib sama persis dengan total sisa tagihan (' + formatRupiah(totalSisaTagihan) + ').',
                    confirmButtonColor: '#3085d6'
                });
                return false;
            }

            // 2. Jika validasi lolos, tampilkan konfirmasi SweetAlert sebelum submit
            Swal.fire({
                title: 'Konfirmasi Simpan Data',
                text: "Apakah Anda yakin data yang diinput sudah benar dan siap diproses?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754', // Warna hijau success
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Eksekusi & Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Masukkan nilai raw integer tanpa format titik tepat sebelum dikirim ke backend
                    $('#inputNominal').val(inputNominalRaw);

                    // Tampilkan elemen loading spinner global jika ada aplikasi Anda
                    if($('#spinner').length) {
                        $('#spinner').show();
                    }

                    // Lanjutkan pengiriman form secara native
                    form.submit();
                }
            });
        });
    });
</script>
@endpush
