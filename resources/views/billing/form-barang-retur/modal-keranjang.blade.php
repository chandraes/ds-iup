<div class="modal fade" id="keranjangModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="keranjangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keranjangTitle">
                    <i class="bi bi-pencil-square me-2"></i> Input Jumlah Retur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form method="post" id="keranjangForm" action="{{route('billing.form-barang-retur.detail.store', $b->id)}}">
                @csrf

                <input type="hidden" name="barang_retur_id" value="{{ $b->id }}">
                <input type="hidden" name="barang_stok_harga_id" id="barang_stok_harga_id">
                <input type="hidden" name="detail_id" id="detail_id">

                <div class="modal-body">
                    <div class="row">

                        <div class="col-12 mb-3">
                            <label for="nm_barang_merk_retail" class="form-label">Nama Barang</label>
                            <input type="text" id="nm_barang_merk_retail" class="form-control" disabled
                                   style="background-color: #e9ecef; opacity: 1;">
                        </div>

                        <div class="col-12 mb-3">
                            <div class="alert alert-info d-flex justify-content-between align-items-center mb-0" role="alert">
                                <h6 class="mb-0">
                                    <i class="bi bi-info-circle-fill me-2"></i>
                                    Stok Tersedia:
                                </h6>
                                <span id="stok_tersedia" class="badge bg-primary rounded-pill" style="font-size: 1.05rem;">0</span>
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="jumlah" class="form-label fw-bold">Masukan Jumlah Retur</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control text-end" name="jumlah" id="jumlah"
                                    required data-thousands="." placeholder="0" style="font-size: 1.1rem;">
                                <span class="input-group-text" id="jumlah_satuan_group">
                                    <i class="bi bi-box-seam me-2"></i>
                                    <span id="jumlah_satuan"></span>
                                </span>
                            </div>
                        </div>

                        <div class="col-12">
                             <div class="row" id="rowGrosirRetail" hidden>
                                {{-- grosir --}}
                                <div class="col-md-12">
                                     <h6 class="mt-2 text-muted">Info Harga Grosir</h6>
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle">No</th>
                                                <th class="text-center align-middle">MIN Qty Grosir</th>
                                                <th class="text-center align-middle">Qty</th>
                                                <th class="text-center align-middle">Diskon</th>
                                            </tr>
                                        </thead>
                                        <tbody id="grosirTableBodyRetail"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i>
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
