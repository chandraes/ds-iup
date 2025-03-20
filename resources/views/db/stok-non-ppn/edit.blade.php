<div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-12 col-md-12 mb-3 mt-3">
                            <label for="harga" class="form-label">HARGA</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="harga" id="harga" required data-thousands="." >
                            </div>
                        </div>
                        <div class="col-lg-12 col-md-12 mb-3">
                            <label for="stok" class="form-label">Minimum Kelipatan Jual</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control text-end" name="min_jual" id="min_jual" required data-thousands="." >
                                <span class="input-group-text" id="satuan_edit"></span>
                            </div>
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
@push('js')
<script>
    new Cleave('#harga', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.'
    });

    new Cleave('#min_jual', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        negative: false,
        numeralDecimalMark: ',',
        delimiter: '.'
    });
</script>
@endpush


