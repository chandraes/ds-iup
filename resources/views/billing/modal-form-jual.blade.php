<div class="modal fade" id="modalJual" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalJual" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalJual">
                    Form Jual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('billing.form-jual')}}" method="get">
                <div class="modal-body">
                    <div class="col-md-12 mb-3">
                        <select class="form-select" name="barang_ppn" id="barang_ppn" required>
                            <option value="1">Barang PPN</option>
                            <option value="0">Barang NON PPN</option>
                        </select>
                    </div>
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
