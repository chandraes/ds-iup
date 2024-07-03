<div class="modal fade" id="editWa" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
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
                        <div class="col-6 mb-3">
                            <label for="untuk" class="form-label">Untuk</label>
                            <input type="text" class="form-control" name="untuk" id="edit_untuk" aria-describedby="helpId"
                                placeholder="" disabled>
                        </div>
                        <input type="hidden" name="group_id" id="group_id">
                        <div class="col-6 mb-3">
                            <label for="nama_group" class="form-label">Nama Group</label>
                            <select class="form-select" name="nama_group" id="edit_nama_group" onchange="funSelect()" required>
                                <option value="">Select one</option>
                            </select>
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
