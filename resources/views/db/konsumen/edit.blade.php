<div class="modal fade" id="editInvestor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="investorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investorTitle">Tambah Konsumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="nama" class="form-label">Nama Perusahaan</label>
                            <input type="text" class="form-control" name="nama" id="edit_nama" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="cp" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="cp" id="edit_cp" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control" name="no_hp" id="edit_no_hp" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="no_kantor" class="form-label">No Kantor</label>
                            <input type="text" class="form-control" name="no_kantor" id="edit_no_kantor" aria-describedby="helpId" value="{{old('no_kantor')}}"
                                placeholder="">
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control" name="npwp" id="edit_npwp" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="plafon" class="form-label">Limit Plafon</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="plafon" id="edit_plafon" required>
                              </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="tempo_hari" class="form-label">Tempo</label>
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="tempo_hari" id="edit_tempo_hari" required ">
                                <span class="input-group-text" id="basic-addon1">Hari</span>
                              </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="npwp" class="form-label">Sistem Pembayaran</label>
                            <select name="pembayaran" id="edit_pembayaran" required class="form-select">
                                <option value="" disabled selected>-- Pilih Sistem Pembayaran --</option>
                                <option value="1">Cash</option>
                                <option value="2">Tempo</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="kota" class="form-label">Kota</label>
                            <input type="text" class="form-control" name="kota" id="edit_kota" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-md-12 col-sm-12 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="edit_alamat" cols="30" rows="5" class="form-control"></textarea>
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
