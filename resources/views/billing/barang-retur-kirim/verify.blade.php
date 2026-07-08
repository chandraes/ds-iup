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
                    <div
                        class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-success"><i class="bi bi-check2-square"></i> Form Barang Diterima
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#modalTambahBarang">
                            <i class="bi bi-plus-circle"></i> Tambah Barang
                        </button>
                    </div>
                    <div class="card-body p-3 table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="table-penerimaan">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="5%">
                                        <input class="form-check-input border-secondary" type="checkbox" id="checkAll">
                                    </th>
                                    <th width="5%">No</th> {{-- KOLOM NOMOR BARU DI TABEL KANAN --}}
                                    <th width="30%">Barang (Asli/Pengganti)</th>
                                    <th width="15%">Qty Proses</th>
                                    <th width="20%">Tindakan</th>
                                    <th width="20%">Catatan Per Item</th>
                                    <th width="5%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-penerimaan">
                                {{-- Baris dinamis masuk ke sini --}}
                            </tbody>
                        </table>

                        <div class="mb-3 mt-4">
                            <label class="form-label fw-bold">Catatan Penerimaan (Opsional)</label>
                            <textarea name="catatan" class="form-control" rows="2"
                                placeholder="Contoh: Barang A dicicil penerimaannya, sisanya menyusul..."></textarea>
                        </div>
                    </div>
                    <div class="card-footer bg-light text-end py-3">
                        <div class="form-check form-check-inline me-4 text-start">
                            <input class="form-check-input" type="checkbox" id="checkSelesai" name="status_selesai"
                                value="1">
                            <label class="form-check-label fw-bold text-success" for="checkSelesai">
                                Tandai Selesai (Semua barang sudah beres)
                            </label>
                        </div>
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
                    {{-- Tambahkan class select2-modal di sini --}}
                    <select id="modal-barang-id" class="form-select select2-modal">
                        <option value="">-- Cari & Pilih Barang --</option>
                        @foreach($barangPengganti as $b)
                            <option value="{{ $b->id }}"
                                    data-satuan="{{ $b->satuan->nama ?? 'pcs' }}"
                                    data-nama="{{ $b->barang_nama->nama }} ({{ $b->kode ?? '' }}) - {{ $b->merk ?? '' }}">
                                {{ $b->barang_nama->nama }} ({{ $b->kode ?? '' }}) - {{ $b->merk ?? '' }}
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
<script>
$(document).ready(function() {
    const detailInvoice = @json($invoice->details);

    // Fungsi Otomatis untuk memperbarui nomor urut di tabel kanan
    function updateRowNumbers() {
        $('#tbody-penerimaan tr').each(function(index) {
            $(this).find('.row-number').text(index + 1);
        });
    }

    /**
     * Fungsi untuk menambah baris ke tabel kanan
     * @param {boolean} isManual - true jika barang berasal dari modal tambah barang
     */
    function tambahBaris(barangId, namaBarang, qty, satuan, maxQty = null, isManual = false) {
        let maxAttr = maxQty ? `max="${maxQty}"` : '';
        let infoMax = maxQty ? `<div class="text-muted mt-1" style="font-size: 0.7em;">Max: ${maxQty} (Sisa)</div>` : '';

        // LOGIC UX: Jika barang bawaan asli (isManual = false), jangan beri tombol hapus baris.
        // Jika barang baru dari modal (isManual = true), berikan tombol hapus baris.
        let tombolAksi = isManual
            ? `<button type="button" class="btn btn-sm btn-outline-danger btn-hapus-baris"><i class="bi bi-trash"></i></button>`
            : `<span class="text-muted small fst-italic">-</span>`;

        let htmlRow = `
            <tr>
                <td class="text-center">
                    <input class="form-check-input border-secondary check-item" type="checkbox">
                </td>
                <td class="text-center fw-bold text-muted row-number"></td>
                <td>
                    <span class="fw-bold fs-6">${namaBarang}</span>
                    <input type="hidden" name="barang_id[]" class="input-row" value="${barangId}" disabled>
                </td>
                <td class="text-center">
                    <div class="input-group input-group-sm">
                        <input type="number" name="qty_terima[]" class="form-control text-center input-row input-qty" min="1" value="${qty}" ${maxAttr} disabled required>
                        <span class="input-group-text">${satuan}</span>
                    </div>
                    ${infoMax}
                </td>
                <td>
                    <select name="status_proses[]" class="form-select form-select-sm input-row select-tindakan" disabled>
                        <option value="terima">Terima (Masuk Stok)</option>
                        <option value="hapus">Hapus (Batal Retur)</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="catatan_item[]" class="form-control form-control-sm input-row input-catatan" placeholder="Opsional..." disabled>
                </td>
                <td class="text-center">
                    ${tombolAksi}
                </td>
            </tr>
        `;
        $('#tbody-penerimaan').append(htmlRow);
        updateRowNumbers();
    }

    // 1. Load barang bawaan invoice secara otomatis (isManual = false)
    if(detailInvoice.length > 0) {
        detailInvoice.forEach(function(detail) {

            // HANYA BUAT BARIS JIKA BARANG MASIH MEMILIKI SISA > 0
            if (detail.sisa_qty > 0) {
                let nama = `${detail.barang.barang_nama.nama} (${detail.barang.kode ?? ''}) - ${detail.barang.merk ?? ''}`;
                let satuan = detail.barang.satuan ? detail.barang.satuan.nama : 'pcs';

                // PERHATIKAN: Parameter qty dan maxQty sekarang menggunakan 'detail.sisa_qty'
                tambahBaris(detail.barang_id, nama, detail.sisa_qty, satuan, detail.sisa_qty, false);
            }

        });
    }

    // 2. Eksekusi Modal Tambah Barang Pengganti (isManual = true)
    $('#btn-simpan-modal').on('click', function() {
        let select = $('#modal-barang-id');
        let barangId = select.val();
        let qty = $('#modal-qty').val();

        if(!barangId) {
            Swal.fire('Oops!', 'Pilih barang terlebih dahulu.', 'warning');
            return;
        }
        if(qty <= 0) {
            Swal.fire('Oops!', 'Qty harus lebih dari 0.', 'warning');
            return;
        }

        let nama = select.find(':selected').data('nama');
        let satuan = select.find(':selected').data('satuan');

        // Parameter terakhir dikirim true karena ini input manual dari modal
        tambahBaris(barangId, nama, qty, satuan, null, true);

        $('#modalTambahBarang').modal('hide');
    });

    // 3. Hapus Baris Manual dari Tabel (hanya akan merespon baris yang memiliki tombol .btn-hapus-baris)
    $('#tbody-penerimaan').on('click', '.btn-hapus-baris', function() {
        $(this).closest('tr').remove();
        updateRowNumbers();
    });

    // Inisialisasi Select2 pada Modal
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

    $('#tbody-penerimaan').on('change', '.check-item', function() {
        let isChecked = $(this).is(':checked');
        let row = $(this).closest('tr');
        row.find('.input-row').prop('disabled', !isChecked);
        if(isChecked) {
            row.addClass(row.find('.select-tindakan').val() === 'hapus' ? 'table-danger' : 'table-active');
        } else {
            row.removeClass('table-active table-danger');
        }
        if (!isChecked) $('#checkAll').prop('checked', false);
    });

    $('#tbody-penerimaan').on('change', '.select-tindakan', function() {
        let val = $(this).val();
        let row = $(this).closest('tr');
        let inputCatatan = row.find('.input-catatan');
        let isChecked = row.find('.check-item').is(':checked');

        if(val === 'hapus') {
            if(isChecked) row.addClass('table-danger').removeClass('table-active');
            inputCatatan.prop('required', true).attr('placeholder', 'Wajib diisi alasannya...');
        } else {
            if(isChecked) row.removeClass('table-danger').addClass('table-active');
            inputCatatan.prop('required', false).attr('placeholder', 'Opsional...');
        }
    });

    $('#tbody-penerimaan').on('input', '.input-qty', function() {
        let max = parseInt($(this).attr('max'));
        let val = parseInt($(this).val());
        if (max && val > max) $(this).val(max);
    });

    $('#checkAll').on('change', function() {
        $('.check-item').prop('checked', $(this).is(':checked')).trigger('change');
    });

    $('#form-verify').on('submit', function(e) {
        e.preventDefault();
        if ($('.check-item:checked').length === 0) {
            Swal.fire('Peringatan', 'Pilih (ceklis) minimal 1 barang yang akan diproses!', 'warning');
            return;
        }
        let form = this;
        Swal.fire({
            title: 'Proses Item Terpilih?',
            text: "Barang yang dicentang akan diproses sesuai tindakannya.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            confirmButtonText: 'Ya, Proses Sekarang!'
        }).then((result) => {
            if (result.isConfirmed) form.submit();
        });
    });
});
</script>
@endpush
