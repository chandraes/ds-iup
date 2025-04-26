<div class="modal fade" id="eModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="eForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 mb-3 mt-3">
                            <label for="harga" class="form-label">Stok Awal</label>
                            <input type="text" class="form-control" name="stok_awal" id="e_stok_awal" required data-thousands="." >
                        </div>
                        <div class="col-lg-12 col-md-12 mb-3 mt-3">
                            <label for="harga" class="form-label">Stok</label>
                            <input type="text" class="form-control" name="stok" id="e_stok" required data-thousands="." >
                        </div>
                        <div class="col-lg-12 col-md-12 mb-3 mt-3">
                            <label for="harga" class="form-label">HARGA BELI</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="harga_beli" id="e_harga_beli" required data-thousands="." >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

