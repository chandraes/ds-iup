@extends('layouts.app')
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center mb-4">
        <div class="col-md-12 text-center">
            <h1 class="text-primary fw-bold text-uppercase tracking-wider"><u>Edit Invoice Konsumen</u></h1>
            <h3 class="text-secondary fw-semibold mt-2">{{$data->uraian}} <span class="text-muted">({{$data->kode}})</span></h3>

            <div class="alert alert-warning d-inline-block mt-3 shadow-sm border-start border-warning border-4 text-start" role="alert">
                <i class="fa fa-exclamation-triangle me-2 text-warning fs-5"></i>
                <strong>Mode Batasan Ketat:</strong> Anda hanya diperbolehkan **mengurangi kuantitas** atau **menghapus** item barang. Menambah kuantitas atau menyisipkan barang baru tidak diizinkan.
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
        <a href="{{route('billing.invoice-konsumen.detail', ['invoice' => $data->id])}}" class="btn btn-outline-secondary btn-sm px-3 shadow-sm rounded-pill">
            <i class="fa fa-arrow-left me-2"></i> Kembali ke Detail
        </a>
        <span class="badge bg-dark px-3 py-2 rounded-pill shadow-sm fs-6">
            Sistem: {{$data->sistem_pembayaran == 1 ? 'Cash' : ($data->sistem_pembayaran == 2 ? 'Tempo' : 'Titipan')}}
        </span>
    </div>

    <div class="card shadow border-0 rounded-3 overflow-hidden mx-2">
        <div class="card-header bg-gradient bg-primary text-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-bold"><i class="fa fa-file-text me-2"></i> Ringkasan Invoice</h5>
                </div>
                <div class="col-md-6 text-md-end text-start mt-2 mt-md-0">
                    <span class="text-white-50">Konsumen:</span>
                    <strong class="text-white ms-1">{{$data->konsumen ? $data->konsumen->nama : 'Umum'}}</strong>
                </div>
            </div>
        </div>

        <div class="card-body p-4 bg-light">
            <form action="{{ route('billing.invoice-konsumen.update', $data->id) }}" method="POST" id="formEditInvoice">
                @csrf

                <div class="table-responsive shadow-sm rounded-3 bg-white">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-primary text-center text-uppercase fs-7 tracking-wider">
                            <tr>
                                <th width="4%" class="py-3">No</th>
                                <th class="text-start py-3">Nama Barang/Merek</th>
                                <th width="12%" class="py-3">Qty Edit</th>
                                <th width="8%" class="py-3">Satuan</th>
                                <th class="text-end py-3">Harga Satuan {{$data->kas_ppn ? '(DPP)' : ''}}</th>
                                <th class="text-end py-3">Diskon {{$data->kas_ppn ? '(DPP)' : ''}}</th>
                                @if ($data->kas_ppn == 1)
                                    <th class="text-end py-3">Harga Diskon (DPP)</th>
                                @endif
                                <th class="text-end py-3">Harga Diskon (PPN)</th>
                                <th class="text-end py-3">Total Harga</th>
                                <th width="8%" class="py-3">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tableBodyInvoice" class="border-top-0">
                            @foreach ($data->invoice_detail as $d)
                            {{-- Unit-price diambil dari total harga akhir dibagi kuantitas awal untuk hitungan dinamis --}}
                            <tr id="row-{{$d->id}}" class="transition-all invoice-row" data-unit-price="{{ $d->jumlah > 0 ? ($d->total / $d->jumlah) : 0 }}">
                                <td class="text-center fw-bold text-muted">{{$loop->iteration}}</td>

                                <td>
                                    <div class="fw-bold text-dark">{{$d->stok->barang_nama->nama}}</div>
                                    <div class="text-muted small" style="font-size: 12px;">
                                        Kode: <span class="badge bg-light text-dark border">{{$d->barang->kode}}</span>
                                        <span class="mx-1">|</span> Merek: <strong>{{$d->barang->merk}}</strong>
                                    </div>
                                    <input type="hidden" name="detail_id[]" value="{{$d->id}}">
                                </td>

                                <td>
                                    <div class="input-group input-group-sm">
                                        <input type="text"
                                               class="form-control text-center fw-bold bg-light-focus text-primary cleave-qty"
                                               name="qty[{{$d->id}}]"
                                               value="{{$d->jumlah}}"
                                               data-max="{{$d->jumlah}}"
                                               required>
                                    </div>
                                    <div class="text-center mt-1 text-uppercase text-muted" style="font-size: 10px; font-weight: 600;">
                                        Maks: <span class="text-danger fw-bold">{{$d->jumlah}}</span>
                                        @if ($d->is_grosir == 1)
                                            <br><span class="text-info">Grosir: ({{$d->nf_jumlah_grosir}})</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded">
                                        {{$d->barang->satuan ? $d->barang->satuan->nama : '-'}}
                                    </span>
                                    @if ($d->is_grosir == 1)
                                        <br>
                                        <span class="badge bg-info-subtle text-info mt-1" style="font-size: 10px;">
                                            ({{$d->satuan_grosir ? $d->satuan_grosir->nama : '-'}})
                                        </span>
                                    @endif
                                </td>

                                <td class="text-end font-monospace text-secondary">
                                    {{$d->nf_harga_satuan}}
                                </td>

                                <td class="text-end font-monospace text-danger">
                                    {{$d->nf_diskon}}
                                </td>

                                @if ($data->kas_ppn == 1)
                                <td class="text-end font-monospace text-dark fw-semibold">
                                    Rp {{number_format($d->harga_satuan - $d->diskon, 0, ',','.')}}
                                </td>
                                @endif

                                <td class="text-end font-monospace text-info fw-semibold">
                                    Rp {{number_format($d->harga_satuan - $d->diskon + $d->ppn, 0, ',','.')}}
                                </td>

                                <td class="text-end font-monospace fw-bold text-primary text-row-total">
                                    {{$d->nf_total}}
                                </td>

                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="hapusBaris('row-{{$d->id}}')">
                                        <i class="fa fa-trash-o me-1"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                        <tfoot class="table-light border-top fw-bold font-monospace">
                            <tr>
                                <td colspan="{{$data->kas_ppn == 1 ? '8' : '7'}}" class="text-end py-2 text-secondary">TOTAL ITEMS :</td>
                                <td class="text-end text-dark py-2 fs-6" id="textSubtotal">Rp {{ number_format($data->invoice_detail->sum('total'), 0, ',', '.') }}</td>
                                <td></td>
                            </tr>

                            {{-- Menampilkan Nilai Uang Muka (DP) jika diisi --}}
                            @if($data->dp > 0)
                            <tr>
                                <td colspan="{{$data->kas_ppn == 1 ? '8' : '7'}}" class="text-end py-2 text-secondary">DP (DOWN PAYMENT) :</td>
                                <td class="text-end text-danger py-2" id="textDp" data-dp="{{ $data->dp }}">{{ $data->nf_dp }}</td>
                                <td></td>
                            </tr>
                            @endif

                            {{-- Menampilkan Total Cicilan yang sudah masuk (jika ada transaksi cicilan berlanjut) --}}
                            @if(isset($data->total_bayar) && $data->total_bayar > 0)
                            <tr>
                                <td colspan="{{$data->kas_ppn == 1 ? '8' : '7'}}" class="text-end py-2 text-secondary">TOTAL CICILAN TERBAYAR :</td>
                                <td class="text-end text-success py-2" id="textCicilan" data-cicilan="{{ $data->total_bayar }}">
                                    Rp {{ number_format($data->total_bayar, 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                            {{-- Kondisi alternatif jika variabel cicilan Anda menggunakan nama relasi bayar/nominal_bayar --}}
                            @elseif( $data->invoice_jual_cicil?->sum('nominal') > 0)
                            <tr>
                                <td colspan="{{$data->kas_ppn == 1 ? '8' : '7'}}" class="text-end py-2 text-secondary">TOTAL CICILAN TERBAYAR :</td>
                                <td class="text-end text-success py-2" id="textCicilan" data-cicilan="{{ ($data->invoice_jual_cicil->sum('nominal')+$data->invoice_jual_cicil->sum('ppn')) }}">
                                    Rp {{ number_format(($data->invoice_jual_cicil->sum('nominal')+$data->invoice_jual_cicil->sum('ppn')), 0, ',', '.') }}
                                </td>
                                <td></td>
                            </tr>
                            @endif

                            <tr class="table-primary-subtle fs-5 border-top border-dark border-1 text-nowrap">
                                <td colspan="{{$data->kas_ppn == 1 ? '8' : '7'}}" class="text-end py-3 text-primary fw-bold">SISA TAGIHAN BARU :</td>
                                <td class="text-end text-primary py-3 fw-xlarge fw-bold" id="textSisaTagihan" data-sisa-awal="{{ $data->sisa_tagihan }}">
                                    {{ $data->nf_sisa_tagihan }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow rounded-3 fw-bold">
                            <i class="fa fa-check-circle me-2"></i> Simpan Perubahan Invoice
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    .fs-7 { font-size: 0.8rem; }
    .tracking-wider { letter-spacing: 0.05em; }
    .font-monospace { font-family: var(--bs-font-monospace); }
    .bg-gradient { background-image: linear-gradient(180deg, rgba(255, 255, 255, 0.15), rgba(255, 255, 255, 0)); }
    .transition-all { transition: all 0.2s ease-in-out; }
    .table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.04); }
    .bg-light-focus:focus { background-color: #fff !important; border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
    .table-primary-subtle { background-color: #e6f0ff !important; }
    .fw-xlarge { font-size: 1.25rem; }
</style>
@endpush

@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cleave.js/1.6.0/cleave.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Cleave.js untuk input Qty
        $('.cleave-qty').each(function() {
            let inputElement = this;
            let maxAvailable = parseInt($(inputElement).data('max'));

            let cleaveInstance = new Cleave(inputElement, {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalScale: 0,
                numeralPositiveOnly: true
            });

            inputElement.cleaveInstance = cleaveInstance;

            inputElement.addEventListener('input', function() {
                let rawValue = parseInt(cleaveInstance.getRawValue()) || 0;

                // Batasi maksimum kuantitas awal
                if (rawValue > maxAvailable) {
                    cleaveInstance.setRawValue(maxAvailable);
                    $(inputElement).addClass('is-invalid');
                    setTimeout(() => { $(inputElement).removeClass('is-invalid'); }, 500);
                }

                // Minimal 1 barang
                if (rawValue === 0 && $(inputElement).val() !== '') {
                    cleaveInstance.setRawValue(1);
                }

                hitungUlangTotal();
            });
        });

        // Picu hitung awal saat halaman dimuat demi memastikan keakuratan data
        hitungUlangTotal();
    });

    // ENGINE KALKULATOR LIVE OTOMATIS (MENDUKUNG DP + CICILAN BERJALAN)
    function hitungUlangTotal() {
        let subtotal = 0;

        $('.invoice-row').each(function() {
            let row = $(this);
            let unitPrice = parseFloat(row.data('unit-price')) || 0;
            let inputEl = row.find('.cleave-qty')[0];

            let qty = 0;
            if (inputEl && inputEl.cleaveInstance) {
                qty = parseInt(inputEl.cleaveInstance.getRawValue()) || 0;
            } else if (inputEl) {
                qty = parseInt(inputEl.value.replace(/,/g, '').replace(/\./g, '')) || 0;
            }

            let rowTotal = qty * unitPrice;
            subtotal += rowTotal;

            row.find('.text-row-total').text('Rp ' + formatRupiah(rowTotal));
        });

        // 1. Perbarui teks TOTAL ITEMS
        $('#textSubtotal').text('Rp ' + formatRupiah(subtotal));

        // 2. Ambil nilai Pengurang DP
        let dp = 0;
        if ($('#textDp').length > 0) {
            dp = parseFloat($('#textDp').data('dp')) || 0;
        }

        // 3. Ambil nilai Pengurang Cicilan Masuk
        let cicilan = 0;
        if ($('#textCicilan').length > 0) {
            cicilan = parseFloat($('#textCicilan').data('cicilan')) || 0;
        }

        // 4. Perbarui SISA TAGIHAN BARU (Subtotal Baru - DP - Cicilan Terbayar)
        let sisaTagihan = subtotal - dp - cicilan;
        if (sisaTagihan < 0) sisaTagihan = 0;

        $('#textSisaTagihan').text('Rp ' + formatRupiah(sisaTagihan));
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function hapusBaris(rowId) {
        Swal.fire({
            title: 'Hapus Item Barang?',
            text: "Barang akan dikeluarkan penuh dari tagihan setelah Anda menekan Simpan Perubahan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus Baris!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#' + rowId).fadeOut(300, function() {
                    $(this).remove();

                    // Susun ulang urutan penomoran tabel
                    $('#tableBodyInvoice tr').each(function(index) {
                        $(this).find('td:first').text(index + 1);
                    });

                    hitungUlangTotal();
                });
            }
        });
    }

    $('#formEditInvoice').submit(function(e){
        e.preventDefault();

        if($('#tableBodyInvoice tr').length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Transaksi Gagal',
                text: 'Isi Invoice tidak boleh kosong sama sekali! Minimal harus menyisakan 1 barang.'
            });
            return false;
        }

        Swal.fire({
            title: 'Konfirmasi Perubahan',
            text: "Kuantitas yang dikurangi/dihapus akan langsung dikembalikan menuju stok gudang kluster terbaru.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan Perubahan!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Sinkronisasi Data...',
                    text: 'Sedang menyesuaikan stok gudang terbaru & total tagihan...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                // Unmask nilai ribuan sebelum dikirim ke backend Laravel
                $('.cleave-qty').each(function() {
                    let rawVal = this.value.replace(/,/g, '').replace(/\./g, '');
                    this.value = rawVal;
                });

                this.submit();
            }
        });
    });
</script>
@endpush
