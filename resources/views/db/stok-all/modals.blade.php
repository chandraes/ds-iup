<div class="modal fade" id="stock-editor" tabindex="-1" aria-labelledby="stock-editor-title" data-bs-backdrop="static"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h2 class="modal-title fs-5" id="stock-editor-title">Kelola stok</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <form id="stock-edit-form"><div class="modal-body"><p id="stock-editor-product" class="fw-semibold"></p><div id="stock-editor-error" role="alert" class="alert alert-danger" hidden></div><div id="stock-editor-fields"></div></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success" id="stock-save">Simpan</button></div></form>
</div></div></div>
<div class="modal fade" id="stock-history" tabindex="-1" aria-labelledby="stock-history-title"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h2 class="modal-title fs-5" id="stock-history-title">Histori stok barang</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body"><p id="stock-history-product" class="fw-semibold"></p><p id="stock-history-note" class="text-secondary"></p><div id="stock-history-error" class="alert alert-danger" role="alert" hidden></div>
    <div class="table-responsive"><table class="table table-bordered"><thead class="table-success"><tr><th>No</th><th>Tanggal</th><th>Qty awal</th><th>Satuan</th><th>Harga beli DPP</th><th>Harga jual DPP</th></tr></thead><tbody id="stock-history-body"></tbody></table></div>
    <button type="button" id="stock-history-more" class="btn btn-outline-success" hidden>Muat berikutnya</button></div>
</div></div></div>
