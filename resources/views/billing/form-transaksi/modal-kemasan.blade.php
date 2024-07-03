<div class="modal fade" id="kemasanModal" tabindex="-1" role="dialog" aria-labelledby="bahanBakuTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bahanBakuTitle">
                    Pilih Jenis Pembelian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="mb-3">
                        <select class="form-select" name="kemasanSelect" id="kemasanSelect">
                            <option value="cash">Cash</option>
                            <option value="tempo">Dengan Tempo</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
                <button type="button" class="btn btn-primary" onclick="funKemasan()">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
    function funKemasan()
    {
        var kemasanSelect = document.getElementById('kemasanSelect').value;
        if(kemasanSelect == 'cash')
        {
            window.location.href = "{{route('billing.form-transaksi.kemasan')}}";
        }
        else if(kemasanSelect == 'tempo')
        {
            window.location.href = "{{route('billing.form-transaksi.kemasan.tempo')}}";
        }
    }
</script>
@endpush
