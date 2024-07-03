<div class="modal fade" id="modalTransaksi" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="transaksiTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transaksiTitle">Form Transaksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" name="selectTransaksi" id="selectTransaksi">
                    <option value="keluar">Transaksi Dana Keluar</option>
                    <option value="masuk">Transaksi Dana Masuk</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="funTransaksi()">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
