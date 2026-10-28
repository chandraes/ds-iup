<div class="modal fade" id="dokumenModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="dokumenModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dokumenModalTitle">
                    Dokumen Konsumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <input type="text" class="form-control" id="dokumen_konsumen_nama" disabled>
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <div id="dokumenContent">
                            <form id="dokumenForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="konsumen_id" id="dokumen_konsumen_id">
                                    <div class="col-md-4 mb-3">
                                        <label for="perusahaan" class="form-label">Perusahaan</label>
                                        <select name="barang_unit_id" id="dokumen_barang_unit_id" class="form-select">

                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                                        <input type="text" class="form-control" id="nama_dokumen" name="nama" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="file" class="form-label">Pilih File</label>
                                        <input type="file" class="form-control" id="file_dokumen" name="file"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                        <div class="form-text">Format yang diterima: .pdf, .doc, .docx, .jpg, .jpeg,
                                            .png</div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">Upload Dokumen</button>

                            </form>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <div id="dokumenList">
                            <table class="table table-bordered table-hover" id="dokumenTable">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Perusahaan</th>
                                        <th>File</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dokumenTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@push('js')
<script id="customer-document-config" type="application/json">{!! json_encode([
    'listUrl' => route('db.konsumen.dokumen'),
    'storeUrl' => route('db.konsumen.dokumen.store'),
    'deleteUrl' => route('db.konsumen.dokumen.destroy', ':id'),
    'unitsUrl' => route('universal.get-unit'),
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
<script src="{{ asset('customer-documents.js').'?v='.filemtime(public_path('customer-documents.js')) }}"></script>
@endpush
