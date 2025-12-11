<div class="modal fade" id="modAsAdm" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modAsAdmTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-primary" id="modAsAdmTitle">
                    <i class="fa fa-edit me-2"></i>Update Ajuan Harga Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <form action="#" method="post" id="asistenForm">
                    @csrf
                    <div class="card bg-light border-0 mb-4">
                        <div class="card-body position-relative">

                            <div class="">
                                <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Nama Barang</small>
                                <h5 class="fw-bold text-dark mb-1" id="viewNamaBarang">Menunggu Data...</h5>
                                <div class="d-flex gap-3 mt-2">
                                    <small class="text-muted"><i class="fa fa-barcode me-1"></i> <span id="viewKode">-</span></small>
                                    <small class="text-muted"><i class="fa fa-tag me-1"></i> <span id="viewMerk">-</span></small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="asistNamaBarang" id="asistNamaBarang">
                    <input type="hidden" name="asistK" id="asistKode">
                    <input type="hidden" name="asistM" id="asistMerk">

                    <div class="row g-3">
                        <div class="col-16">
                            <label for="harga" class="form-label fw-bold small text-secondary">HARGA JUAL</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white text-success border-end-0 fw-bold">Rp</span>
                                <input type="text" class="form-control border-start-0 ps-0 fw-bold text-dark"
                                       name="harga_ajuan" id="harga_ajuan" required data-thousands="." placeholder="0">
                            </div>
                        </div>

                        <div class="col-12">
                            <label for="min_jual" class="form-label fw-bold small text-secondary">MINIMUM KELIPATAN JUAL</label>
                            <div class="input-group">

                                <input type="text" class="form-control" name="min_jual_ajuan" id="min_jual_ajuan" required data-thousands="." placeholder="Contoh: 1, 12, 100">
                                <span class="input-group-text bg-light fw-bold" id="satuan_edit">Pcs</span>
                            </div>
                            <div class="form-text small text-muted">
                                *Jumlah minimum barang yang harus dibeli pelanggan.
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" form="asistenForm" class="btn btn-primary px-4 shadow-sm">
                    <i class="fa fa-save me-2"></i>Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
    new Cleave('#harga_ajuan', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.'
    });

    new Cleave('#min_jual_ajuan', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        negative: false,
        numeralDecimalMark: ',',
        delimiter: '.'
    });
</script>
@endpush
