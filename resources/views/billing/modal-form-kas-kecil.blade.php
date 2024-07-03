<div class="modal fade" id="formKecil" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="formSupplierTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formSupplierTitle">Form Kas Kecil</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" name="selectKecil" id="selectKecil">
                    <option value="masuk">Permintaan Dana</option>
                    <option value="keluar">Pengeluaran Dana</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="funKecil()">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
