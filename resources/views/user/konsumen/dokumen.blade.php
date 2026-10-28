<div class="modal fade" id="dokumenModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content shadow-lg border-0">
            {{-- Header Modal --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-folder-open me-2"></i> Kelola Dokumen Konsumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="row g-0">

                    {{-- KOLOM KIRI: FORM UPLOAD --}}
                    <div class="col-lg-4 border-end bg-light p-4">
                        <div class="mb-3 border-bottom pb-2">
                            <h6 class="fw-bold text-dark"><i class="fa fa-upload me-1"></i> Upload Baru</h6>
                        </div>

                        <form id="dokumenForm" onsubmit="saveDokumen(event)" enctype="multipart/form-data">
                            @csrf
                            {{-- Hidden ID Konsumen --}}
                            <input type="hidden" name="konsumen_id" id="dokumen_konsumen_id">

                            {{-- Nama Konsumen (Readonly) --}}
                            <div class="mb-3">
                                <label class="small text-muted fw-bold">Nama Konsumen</label>
                                <input type="text" class="form-control bg-white fw-bold text-primary" id="dokumen_konsumen_nama" readonly>
                            </div>

                            {{-- Dropdown Unit (Diisi oleh AJAX Script Anda) --}}
                            <div class="mb-3">
                                <label for="dokumen_barang_unit_id" class="small text-muted fw-bold">Perusahaan / Unit</label>
                                <select name="barang_unit_id" id="dokumen_barang_unit_id" class="form-select">
                                    {{-- Option akan di-inject oleh JS --}}
                                </select>
                            </div>

                            {{-- Judul Dokumen --}}
                            <div class="mb-3">
                                <label for="dokumen_judul" class="small text-muted fw-bold">Judul Dokumen</label>
                                <input type="text" class="form-control" name="nama" id="dokumen_judul" placeholder="Contoh: KTP, NPWP" required>
                            </div>

                            {{-- File Upload --}}
                            <div class="mb-4">
                                <label for="dokumen_file" class="small text-muted fw-bold">Pilih File</label>
                                <input type="file" class="form-control" name="file" id="dokumen_file" required>
                                <small class="text-muted fst-italic" style="font-size: 11px;">Format: Gambar atau PDF</small>
                            </div>

                            {{-- Tombol Simpan --}}
                            <button type="submit" class="btn btn-primary w-100" id="btn-submit-dokumen">
                                <i class="fa fa-save me-1"></i> Simpan Dokumen
                            </button>
                        </form>
                    </div>

                    {{-- KOLOM KANAN: LIST DOKUMEN --}}
                    <div class="col-lg-8 p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark m-0">Daftar Dokumen</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshCurrentDokumen()">
                                <i class="fa fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>

                        {{-- Area Tabel --}}
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle mb-0" id="dokumenTable" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Perusahaan</th>
                                        <th>File</th>
                                        <th class="text-center" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dokumenTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script id="customer-document-config" type="application/json">{!! json_encode([
    'listUrl' => route('user.konsumen.dokumen'),
    'storeUrl' => route('user.konsumen.dokumen.store'),
    'deleteUrl' => route('.konsumen.dokumen.destroy', ':id'),
    'unitsUrl' => route('universal.get-unit'),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('customer-documents.js').'?v='.filemtime(public_path('customer-documents.js')) }}"></script>
@endpush
