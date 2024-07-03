<div class="modal fade" id="formDeposit" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">Form Deposit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <select class="form-select" name="selectDeposit" id="selectDeposit">
                    <option value="masuk">Penambahan Deposit</option>
                    <option value="keluar">Pengembalian Deposit</option>
                    <option value="keluar-all">Pengembalian Deposit All</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="funDeposit()">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
