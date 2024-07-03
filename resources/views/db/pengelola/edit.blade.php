<div class="modal fade" id="editInvestor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="editInvestorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInvestorTitle">Edit Pengelola</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="nama" class="form-label">Nama</label>
                            <input type="text" class="form-control" name="nama" id="edit_nama" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="no_hp" class="form-label">No WA</label>
                            <input type="text" class="form-control" name="no_hp" id="edit_no_hp" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="persentase" class="form-label">Persentase</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="persentase" id="edit_persentase" required >
                                <span class="input-group-text" id="basic-addon1">%</span>
                              </div>
                        </div>
                    </div>
                    <hr>
                    <h2>Informasi BANK</h2>
                    <hr>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="nama_rek" class="form-label">Atas Nama</label>
                            <input type="text" class="form-control" name="nama_rek" id="edit_nama_rek"
                                aria-describedby="helpId" placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="no_rek" class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control" name="no_rek" id="edit_no_rek" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="bank" class="form-label">BANK</label>
                            <input type="text" class="form-control" name="bank" id="edit_bank" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
