<div class="modal fade" id="diskonModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="diskonModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="diskonModalTitle">
                    Diskon Khusus Konsumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="diskonForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nominal" class="form-label">Nominal</label>
                            <div class="input-group mb-3">
                                <input type="number" class="form-control text-end" name="diskon_khusus" id="edit_diskon" required max="100" min="0" step="0.01"
                                    placeholder="Masukkan diskon khusus dalam persen">
                                <span class="input-group-text" id="basic-addon1">%</span>
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
