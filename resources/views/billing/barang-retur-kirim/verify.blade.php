@extends('layouts.app')

@section('content')
<div class="container-fluid">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3><i class="bi bi-box-arrow-in-down text-success"></i> Verifikasi Terima Retur</h3>
            <p class="text-muted mb-0">Invoice: <span class="fw-bold text-primary">RS-{{ sprintf('%04d',
                    $invoice->nomor) }}</span> | Supplier: <strong>{{ $invoice->barang_unit->nama ?? '-' }}</strong></p>
        </div>
        @if(($invoice->total_refund ?? 0) > 0)
            <div class="col-md-4">
                <div class="card border-warning bg-warning-subtle shadow-sm">
                    <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-warning-emphasis fw-bold" style="font-size: 0.85em;">
                                <i class="bi bi-cash-stack me-1"></i> Total Refund Diterima
                            </div>
                            <small class="text-muted" style="font-size: 0.75em;">Akumulasi Cair Uang</small>
                        </div>
                        <div class="fs-5 fw-bold text-dark">
                            Rp {{ number_format($invoice->total_refund, 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <a href="{{ route('billing.penyelesaian-retur.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="row">
        {{-- Panel Kiri: Informasi Barang yang Diretur (Referensi) --}}
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-light border-bottom py-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle"></i> Referensi Awal Retur</h6>
                </div>
                <div class="card-body p-2 table-responsive">
                    <table class="table table-sm table-striped table-hover align-middle mb-0"
                        style="font-size: 0.9rem;">
                        <thead class="table-light text-center">
                            <tr>
                                <th width="10%">No</th>
                                <th class="text-start">Nama Barang</th>
                                <th width="30%">Qty Awal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->details as $index => $detail)
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $detail->barang->barang_nama->nama }}</div>
                                    <small class="text-muted d-block" style="font-size: 0.75em;">
                                        {{ $detail->barang->kode }} | {{ $detail->barang->merk }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger-subtle text-danger border border-danger fs-7 mb-1">
                                        Awal: {{ $detail->qty }} {{ $detail->barang->satuan->nama ?? 'pcs' }}
                                    </span>
                                    <br>
                                    {{-- Indikator Sisa --}}
                                    @if($detail->sisa_qty > 0)
                                        <span class="badge bg-success-subtle text-success border border-success fs-7">
                                            Sisa: {{ $detail->sisa_qty }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white fs-7">
                                            <i class="bi bi-check2-all"></i> Tuntas
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Panel Kanan: Form Input Penerimaan (Dynamic Rows dengan Checkbox) --}}
        <div class="col-md-8 mb-4">
            <form action="{{ route('billing.penyelesaian-retur.verify.submit', $invoice->id) }}" method="POST"
                id="form-verify">
                @csrf
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-success"><i class="bi bi-check2-square"></i> Form Barang Diterima</h6>
                        {{-- Tambahkan ID btn-tambah-barang dan disable secara default --}}
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btn-tambah-barang" data-bs-toggle="modal" data-bs-target="#modalTambahBarang" disabled>
                            <i class="bi bi-plus-circle"></i> Tambah Barang Pengganti
                        </button>
                    </div>
                    <div class="card-body p-3 table-responsive">

                        {{-- TABEL 1: BARANG PENGGANTI (Disembunyikan default) --}}
                        <div id="container-pengganti" style="display: none;" class="mb-4">
                            <h6 class="fw-bold text-primary mb-2"><i class="bi bi-arrow-repeat"></i> List Barang Pengganti</h6>
                            <table class="table table-bordered table-sm align-middle" id="table-pengganti">
                                <thead class="table-primary text-center">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="35%">Nama Barang Pengganti</th>
                                        <th width="15%">Qty</th>
                                        <th width="35%">Catatan Item</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="tbody-pengganti">
                                    {{-- Baris barang pengganti masuk ke sini --}}
                                </tbody>
                            </table>
                        </div>

                        {{-- TABEL 2: BARANG ASLI BAWAAN RETUR --}}
                        <h6 class="fw-bold text-success mb-2"><i class="bi bi-box-seam"></i> List Barang Asli Retur</h6>
                        <table class="table table-bordered table-sm align-middle" id="table-penerimaan">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="5%">
                                        <input class="form-check-input border-secondary" type="checkbox" id="checkAll">
                                    </th>
                                    <th width="5%">No</th>
                                    <th width="30%">Barang Asli</th>
                                    <th width="15%">Qty Proses</th>
                                    <th width="20%">Tindakan</th>
                                    <th width="20%">Catatan Per Item</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-penerimaan">
                                {{-- Baris barang asli masuk ke sini --}}
                            </tbody>
                        </table>

                        {{-- CONTAINER INPUT NOMINAL GANTI UANG (GLOBAL - DENGAN DISPLAY HIDDEN DEFAULT) --}}
                        <div id="container-nominal-global" class="card border-warning mb-3" style="display: none;">
                            <div class="card-body bg-warning-subtle py-2 px-3 rounded">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold text-dark mb-0">
                                            <i class="bi bi-cash-stack text-warning-emphasis fs-5 me-1"></i> Total Nominal Refund / Ganti Uang
                                        </label>
                                        <small class="text-muted d-block" style="font-size: 0.78em;">
                                            * Terdapat item dengan tindakan "Ganti Uang", masukkan total nominal uang yang diterima dari supplier.
                                        </small>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text fw-bold border-warning bg-white">Rp</span>
                                            {{-- DIUBAH MENJADI TYPE TEXT UNTUK CLEAVE.JS --}}
                                            <input type="text" name="nominal_uang" id="input-nominal-global"
                                                class="form-control border-warning fw-bold text-dark fs-6"
                                                placeholder="0" disabled>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 mt-4">
                            <label class="form-label fw-bold">Catatan Penerimaan Umum (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2"
                                placeholder="Contoh: Dokumen SJ diserahkan ke admin..."></textarea>
                        </div>
                    </div>

                    {{-- Footer: Submit --}}
                    <div class="card-footer bg-light text-end py-3">
                        <button type="submit" class="btn btn-success" id="btn-simpan-terima">
                            <i class="bi bi-save"></i> Simpan Penerimaan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH BARANG --}}
<div class="modal fade" id="modalTambahBarang" tabindex="-1" aria-labelledby="modalTambahBarangLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahBarangLabel"><i class="bi bi-box"></i> Tambah Barang Kembali</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-bold">Pilih Barang Pengganti</label>
                    <select id="modal-barang-id" class="form-select select2-modal">
                        <option value="">-- Cari & Pilih Barang --</option>
                        @foreach($barangPengganti as $b)
                            @php
                                $satuan = $b->satuan?->nama ?? 'pcs';
                                $pajak = $b->jenis == 1 ? 'PPN' : 'Non-PPN';
                            @endphp
                            <option value="{{ $b->id }}"
                                    data-satuan="{{ $satuan }}"
                                    data-nama="{{ $b->barang_nama->nama }} ({{ $b->kode ?? '' }}) - {{ $b->merk ?? '' }}">
                                {{ $b->barang_nama->nama }} ({{ $b->kode ?? '' }}) - {{ $b->merk ?? '' }} | {{ $satuan }} | [{{ $pajak }}]
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Qty Diterima</label>
                    <div class="input-group">
                        <input type="number" id="modal-qty" class="form-control text-center" min="1" value="1">
                        <span class="input-group-text" id="modal-satuan">pcs</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-simpan-modal">
                    <i class="bi bi-plus"></i> Tambahkan ke Tabel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{-- CLEAVE JS LIBRARY --}}
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>

<script>
$(document).ready(function() {
    const detailInvoice = @json($invoice->details);

    // ==========================================
    // INISIALISASI CLEAVE JS NOMINAL
    // ==========================================
    let cleaveNominal = new Cleave('#input-nominal-global', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        delimiter: '.',
        numeralDecimalMark: ',',
        numeralDecimalScale: 0
    });

    function checkNominalUangState() {
        let adaGantiUang = false;

        // Cek semua item asli yang dicentang
        $('.check-item:checked').each(function() {
            let row = $(this).closest('tr');
            if (row.find('.select-tindakan').val() === 'hapus_ganti_uang') {
                adaGantiUang = true;
            }
        });

        // Tampilkan/Sembunyikan Box Input Global
        if (adaGantiUang) {
            $('#container-nominal-global').slideDown();
            $('#input-nominal-global').prop('disabled', false).prop('required', true);
        } else {
            $('#container-nominal-global').slideUp();
            $('#input-nominal-global').prop('disabled', true).prop('required', false);
            if (cleaveNominal) cleaveNominal.setRawValue('');
        }
    }

    // ==========================================
    // 1. FUNGSI NOMOR URUT & VALIDASI TOMBOL
    // ==========================================
    function updateRowNumbers() {
        $('#tbody-penerimaan tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }

    function updateRowNumbersPengganti() {
        $('#tbody-pengganti tr').each(function(index) {
            $(this).find('.row-number-pengganti').text(index + 1);
        });

        if($('#tbody-pengganti tr').length === 0) {
            $('#container-pengganti').hide();
        }
    }

    function checkTambahBarangState() {
        let bypassRule = false;

        if (bypassRule) {
            $('#btn-tambah-barang').prop('disabled', false);
            return;
        }

        let isAdaHapus = false;
        $('.check-item:checked').each(function() {
            let row = $(this).closest('tr');
            if (row.find('.select-tindakan').val() === 'hapus') {
                isAdaHapus = true;
            }
        });

        $('#btn-tambah-barang').prop('disabled', !isAdaHapus);
    }

    // ==========================================
    // 2. FUNGSI RENDER TABEL (ASLI & PENGGANTI)
    // ==========================================

    function tambahBarisAsli(barangId, namaBarang, qty, satuan) {
        let maxAttr = `max="${qty}"`;
        let htmlRow = `
            <tr>
                <td class="text-center align-middle">
                    <input class="form-check-input border-secondary check-item" type="checkbox">
                </td>
                <td class="text-center fw-bold text-muted align-middle row-number"></td>
                <td class="align-middle">
                    <span class="fw-bold fs-6 nama-item-asli">${namaBarang}</span>
                    <input type="hidden" name="barang_id[]" class="input-row" value="${barangId}" disabled>
                </td>
                <td class="text-center align-middle">
                    <div class="input-group input-group-sm">
                        <input type="number" name="qty_terima[]" class="form-control text-center input-row input-qty" min="1" value="${qty}" ${maxAttr} disabled required>
                        <span class="input-group-text">${satuan}</span>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 0.7em;">Max: ${qty} (Sisa)</div>
                </td>
                <td class="align-middle">
                    <select name="status_proses[]" class="form-select form-select-sm input-row select-tindakan" disabled>
                        <option value="terima">Terima (Masuk Stok)</option>
                        <option value="hapus">Hapus (Batal Retur - Tukar Barang)</option>
                        <option value="hapus_ganti_uang">Hapus (Batal Retur - Ganti Uang)</option>
                    </select>
                </td>
                <td class="align-middle">
                    <input type="text" name="catatan_item[]" class="form-control form-control-sm input-row input-catatan" placeholder="Opsional..." disabled>
                </td>
            </tr>
        `;
        $('#tbody-penerimaan').append(htmlRow);
        updateRowNumbers();
    }

    function tambahBarisPengganti(barangId, namaBarang, qty, satuan) {
        $('#container-pengganti').show();
        let htmlRow = `
            <tr class="table-info">
                <td class="text-center fw-bold text-muted row-number-pengganti"></td>
                <td>
                    <span class="fw-bold fs-6 nama-item-pengganti">${namaBarang}</span>
                    <input type="hidden" name="barang_id[]" value="${barangId}">
                    <input type="hidden" name="status_proses[]" value="terima">
                </td>
                <td class="text-center">
                    <div class="input-group input-group-sm">
                        <input type="number" name="qty_terima[]" class="form-control text-center input-qty-pengganti" min="1" value="${qty}" readonly>
                        <span class="input-group-text">${satuan}</span>
                    </div>
                </td>
                <td>
                    <input type="text" name="catatan_item[]" class="form-control form-control-sm" placeholder="Catatan opsional...">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-pengganti" title="Batal Tambah"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
        $('#tbody-pengganti').append(htmlRow);
        updateRowNumbersPengganti();
    }

    // ==========================================
    // 3. INISIALISASI DATA AWAL & EVENT LISTENER
    // ==========================================

    if(detailInvoice.length > 0) {
        detailInvoice.forEach(function(detail) {
            if (detail.sisa_qty > 0) {
                let nama = `${detail.barang.barang_nama.nama} (${detail.barang.kode ?? ''}) - ${detail.barang.merk ?? ''}`;
                let satuan = detail.barang.satuan ? detail.barang.satuan.nama : 'pcs';
                tambahBarisAsli(detail.barang_id, nama, detail.sisa_qty, satuan);
            }
        });
    }

    checkTambahBarangState();

    $('#tbody-penerimaan').on('change', '.check-item', function() {
        let isChecked = $(this).is(':checked');
        let row = $(this).closest('tr');

        row.find('.input-row').prop('disabled', !isChecked);

        row.removeClass('table-danger table-active table-warning');
        if(isChecked) {
            let val = row.find('.select-tindakan').val();
            if (val === 'hapus') row.addClass('table-danger');
            else if (val === 'hapus_ganti_uang') row.addClass('table-warning');
            else row.addClass('table-active');
        }

        checkTambahBarangState();
        checkNominalUangState();
    });

    $('#tbody-penerimaan').on('change', '.select-tindakan', function() {
        let val = $(this).val();
        let row = $(this).closest('tr');
        let isChecked = row.find('.check-item').is(':checked');

        row.removeClass('table-danger table-active table-warning');
        if(val === 'hapus') {
            if(isChecked) row.addClass('table-danger');
            row.find('.input-catatan').prop('required', true).attr('placeholder', 'Wajib alasan hapus...');
        } else if(val === 'hapus_ganti_uang') {
            if(isChecked) row.addClass('table-warning');
            row.find('.input-catatan').prop('required', true).attr('placeholder', 'Wajib info refund...');
        } else {
            if(isChecked) row.addClass('table-active');
            row.find('.input-catatan').prop('required', false).attr('placeholder', 'Opsional...');
        }

        checkTambahBarangState();
        checkNominalUangState();
    });

    $('#tbody-pengganti').on('click', '.btn-hapus-pengganti', function() {
        $(this).closest('tr').remove();
        updateRowNumbersPengganti();
    });

    $('#checkAll').on('change', function() {
        $('.check-item').prop('checked', $(this).is(':checked')).trigger('change');
        checkNominalUangState();
    });

    $('#tbody-penerimaan').on('input', '.input-qty', function() {
        let max = parseInt($(this).attr('max'));
        let val = parseInt($(this).val());
        if (max && val > max) $(this).val(max);
    });

    // ==========================================
    // 4. MODAL & SELECT2
    // ==========================================
    $('#modalTambahBarang').on('show.bs.modal', function () {
        $('#modal-barang-id option').prop('disabled', false);

        $('.check-item:checked').each(function() {
            let row = $(this).closest('tr');
            let tindakan = row.find('.select-tindakan').val();
            let idBarangAsli = row.find('input[name="barang_id[]"]').val();

            if (tindakan === 'hapus') {
                $(`#modal-barang-id option[value="${idBarangAsli}"]`).prop('disabled', true);
            }
        });

        if ($('#modal-barang-id').hasClass("select2-hidden-accessible")) {
            $('#modal-barang-id').trigger('change.select2');
        }
    });

    $('#modalTambahBarang').on('shown.bs.modal', function () {
        $('.select2-modal').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalTambahBarang'),
            placeholder: '-- Cari & Pilih Barang --',
            allowClear: true
        });
    });

    $('#modalTambahBarang').on('hidden.bs.modal', function () {
        $('#modal-barang-id').val('').trigger('change');
        $('#modal-qty').val(1);
    });

    $('#modal-barang-id').on('change', function() {
        let selected = $(this).find(':selected');
        $('#modal-satuan').text(selected.val() ? selected.data('satuan') : 'pcs');
    });

    $('#btn-simpan-modal').on('click', function() {
        let select = $('#modal-barang-id');
        let barangId = select.val();
        let qty = $('#modal-qty').val();

        if(!barangId || qty <= 0) {
            Swal.fire('Oops!', 'Pilih barang dan pastikan Qty > 0.', 'warning');
            return;
        }

        let nama = select.find(':selected').data('nama');
        let satuan = select.find(':selected').data('satuan');

        tambahBarisPengganti(barangId, nama, qty, satuan);
        $('#modalTambahBarang').modal('hide');
    });

    // ==========================================
    // 5. SWEETALERT REKAP KONFIRMASI SUBMIT
    // ==========================================
    $('#form-verify').on('submit', function(e) {
        e.preventDefault();
        let form = this;
        let countProses = 0;
        let requiresReplacement = false;
        let isGantiUangSelected = false;

        let htmlRekap = `
            <div class="mb-2 text-start" style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-bordered align-middle" style="font-size: 0.85em;">
                    <thead class="table-light position-sticky top-0 shadow-sm">
                        <tr>
                            <th width="30%" class="text-center">Tindakan</th>
                            <th width="50%">Nama Barang</th>
                            <th width="20%" class="text-center">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        $('.check-item:checked').each(function() {
            let row = $(this).closest('tr');
            let nama = row.find('.nama-item-asli').text();
            let qty = row.find('.input-qty').val();
            let tindakan = row.find('.select-tindakan').val();

            if(tindakan === 'terima') {
                htmlRekap += `
                    <tr>
                        <td class="text-center"><span class="badge bg-success w-100">MASUK STOK</span></td>
                        <td>${nama}</td>
                        <td class="text-center fw-bold">${qty}</td>
                    </tr>`;
            } else if (tindakan === 'hapus_ganti_uang') {
                isGantiUangSelected = true;
                htmlRekap += `
                    <tr class="table-warning">
                        <td class="text-center"><span class="badge bg-warning text-dark w-100"><i class="bi bi-cash"></i> REFUND</span></td>
                        <td><span class="text-dark fw-semibold">${nama}</span></td>
                        <td class="text-center fw-bold text-dark">${qty}</td>
                    </tr>`;
            } else {
                requiresReplacement = true;
                htmlRekap += `
                    <tr>
                        <td class="text-center"><span class="badge bg-danger w-100">TUKAR BARANG</span></td>
                        <td><span class="text-danger">${nama}</span></td>
                        <td class="text-center fw-bold text-danger">${qty}</td>
                    </tr>`;
            }
            countProses++;
        });

        let countPengganti = 0;
        $('#tbody-pengganti tr').each(function() {
            let nama = $(this).find('.nama-item-pengganti').text();
            let qty = $(this).find('.input-qty-pengganti').val();
            htmlRekap += `
                <tr class="table-info">
                    <td class="text-center"><span class="badge bg-primary w-100">PENGGANTI</span></td>
                    <td class="text-primary fw-bold">${nama}</td>
                    <td class="text-center fw-bold">${qty}</td>
                </tr>`;
            countProses++;
            countPengganti++;
        });

        htmlRekap += `</tbody></table></div>`;

        // Validasi
        if (countProses === 0) {
            Swal.fire('Peringatan', 'Anda belum mencentang item apapun!', 'warning');
            return;
        }

        if (requiresReplacement && countPengganti === 0) {
            Swal.fire('Tukar Barang Wajib!', 'Tambahkan minimal 1 Barang Pengganti.', 'error');
            return;
        }

        // AMBIL NILAI UNFORMATTED DARI CLEAVE JS
        let rawNominalVal = $('#input-nominal-global').val().replace(/\./g, '');
        let nominalUangVal = parseFloat(rawNominalVal) || 0;

        if (isGantiUangSelected && nominalUangVal <= 0) {
            Swal.fire('Nominal Wajib Diisi!', 'Anda memilih tindakan Ganti Uang, harap isi total nominal refund (> 0).', 'error');
            return;
        }

        // Tampilkan Banner Nominal Refund di atas Konfirmasi SweetAlert jika ada
        if (isGantiUangSelected) {
            let rpFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(nominalUangVal);
            htmlRekap += `
                <div class="alert alert-warning py-2 mt-2 mb-0 text-center fw-bold">
                    <i class="bi bi-cash-stack"></i> Total Nominal Refund: <span class="fs-6 text-dark">${rpFormatted}</span>
                </div>
            `;
        }

        Swal.fire({
            title: '<span class="fs-5 fw-bold">Konfirmasi Penerimaan</span>',
            html: htmlRekap + '<div class="text-center mt-3 text-dark">Data sudah benar dan ingin disimpan?</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Cek Kembali',
            width: '600px'
        }).then((result) => {
            if (result.isConfirmed) {
                // UNFORMAT NOMINAL SEBELUM SUBMIT AGAR DITERIMA DENGAN FORMAT NOMINAL MURNI DI CONTROLLER
                if (isGantiUangSelected && cleaveNominal) {
                    $('#input-nominal-global').val(cleaveNominal.getRawValue());
                }

                Swal.fire({ title: 'Memproses...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                form.submit();
            }
        });
    });
});
</script>
@endpush
