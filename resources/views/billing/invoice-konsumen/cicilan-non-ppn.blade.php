<div class="modal fade" id="cicilanModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="cicilanModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cicilanModalTitle">
                    Cicilan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="post" id="cicilForm">
                @csrf

                <input type="hidden" name="apa_ppn" id="edit_apa_ppn">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="edit_konsumen_nama" class="form-label">Kosumen</label>
                                <input type="text" class="form-control" name="edit_konsumen_nama"
                                    id="edit_konsumen_nama" disabled />
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="edit_nota" class="form-label">Nota</label>
                                <input type="text" class="form-control" name="edit_nota" id="edit_nota" disabled />
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="edit_sisa_tagihan" class="form-label">Total Tagihan</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                    <input type="text" class="form-control text-end" name="edit_sisa_tagihan"
                                        id="edit_sisa_tagihan" disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="edit_sisa_tagihan" class="form-label">Cicilan </label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                    <input type="text" class="form-control text-end" name="nominal" id="edit_nominal"
                                        required onkeyup="cekSisa()">
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="edit_total" class="form-label">Sisa Tagihan</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                    <input type="text" class="form-control text-end" name="total" id="edit_total" disabled>
                                </div>
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
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    var nominal = new Cleave('#edit_nominal', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.'
    });

    confirmAndSubmit('#cicilForm', "Apakah anda yakin?");

    function cekSisa(){
        var sisa = parseInt($('#edit_sisa_tagihan').val().replace(/\./g, '').replace(/\,/g, '.')) ?? 0;
        var nominal = parseInt($('#edit_nominal').val().replace(/\./g, '').replace(/\,/g, '.'));

        if (nominal > sisa) {
            alert('Nominal cicilan tidak boleh melebihi sisa tagihan');
            $('#edit_nominal').val('');
            $('#edit_total').val('');
            return;

        }

        var total = sisa - nominal;
        $('#edit_total').val(total.toLocaleString('id-ID'));
    }
</script>
@endpush
