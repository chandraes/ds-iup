<div class="modal fade" id="modalBeli" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitleId">
                    Form Beli
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('billing.form-beli')}}" method="get">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <select class="form-select" name="tempo" id="tempo" required>
                                <option value="0">Cash</option>
                                <option value="1">Dengan Tempo</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <select class="form-select" name="kas_ppn" id="kas_ppn" required>
                                <option value="1">KAS PPN</option>
                                <option value="0">KAS NON PPN</option>
                            </select>
                        </div>
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
