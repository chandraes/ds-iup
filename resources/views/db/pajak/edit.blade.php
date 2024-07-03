<div class="modal fade" id="editRekening" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="editInvestorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editInvestorTitle">Edit @isset($d) {{$d->untuk}} @endisset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="untuk" class="form-label">Untuk</label>
                            <input type="text" class="form-control" name="untuk" id="edit_untuk" aria-describedby="helpId"
                                placeholder="" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="persen" class="form-label">Persentase</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="persen" id="edit_persen" required>
                                <span class="input-group-text" id="basic-addon1">%</span>
                              </div>
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
