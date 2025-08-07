<div class="modal fade" id="diskonModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="diskonModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="diskonModalTitle">
                    Upload Foto Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="diskonForm">
                @csrf
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <label for="diskon" class="form-label">Diskon</label>
                        <input type="number" class="form-control" id="diskon" name="diskon" placeholder="Masukkan diskon"
                            required step="0.01" min="0" max="100">
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <label for="diskon_mulai" class="form-label">Tanggal Mulai Diskon</label>
                        <input type="date" class="form-control" id="diskon_mulai" name="diskon_mulai"
                            placeholder="Masukkan tanggal mulai diskon" required>
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <label for="diskon_selesai" class="form-label">Tanggal Selesai Diskon</label>
                        <input type="date" class="form-control" id="diskon_selesai" name="diskon_selesai"
                            placeholder="Masukkan tanggal selesai diskon" required>
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
