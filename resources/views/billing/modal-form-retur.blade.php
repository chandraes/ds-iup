<div class="modal fade" id="formReturModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="formReturModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="formReturModalTitle">
                    Form Barang Retur
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('billing.form-barang-retur')}}" method="get">
                <div class="modal-body">
                    <select name="tipe" id="tipe_mode" class="form-select select2" required>
                        <option value="">Pilih Salah Satu</option>
                        <option value="1">Dari Supplier</option>
                        <option value="2">Dari Konsumen</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
