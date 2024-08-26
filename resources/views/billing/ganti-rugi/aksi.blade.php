<div class="modal fade" id="bayarModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="bayarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bayarModalLabel">
                    Aksi Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="pembayaranForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jenis" class="form-label">Pembayaran</label>
                            <select class="form-select" name="jenis" id="jenis" onchange="checkJenis()" required>
                                <option value="" disabled selected>-- Pilih salah satu --</option>
                                <option value="0">Lunas</option>
                                <option value="1">Cicil</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="divNominal" hidden>
                            <label for="nominal" class="form-label">Nominal</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="nominal" id="nominal">
                              </div>
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
@push('js')
    <script>
        var nominal = new Cleave('#nominal', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        function checkJenis()
        {
            var jenis = $('#jenis').val();
            if(jenis == 1){
                $('#divNominal').removeAttr('hidden');
                $('#nominal').attr('required', true);
            }else{
                $('#divNominal').attr('hidden', true);
                $('#nominal').removeAttr('required');
            }
        }
    </script>
@endpush
