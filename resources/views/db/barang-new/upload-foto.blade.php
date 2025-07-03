<div class="modal fade" id="uploadFotoModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="uploadFotoModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadFotoModalTitle">
                    Upload Foto Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="uploadFotoForm" enctype="multipart/form-data">
                @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <input type="file" class="form-control" name="foto" id="edit_foto" aria-describedby="helpId"
                            placeholder="" accept="image/*">
                        <small class="text-danger">* Maksimal 500kb</small>
                        <small class="text-danger">* Format file jpg, jpeg, png</small>
                        <small class="text-danger">* Kosongkan jika tidak ingin mengubah foto</small>
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3 mt-3" id="edit_foto_preview" hidden>
                        <img src="" id="edit_foto_preview_img" class="img-fluid" alt="Preview Foto Barang" width="200">
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
