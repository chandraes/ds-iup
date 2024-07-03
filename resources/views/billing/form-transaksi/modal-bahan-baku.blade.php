<div class="modal fade" id="bahanBakuModal" tabindex="-1" role="dialog" aria-labelledby="bahanBakuTitle"
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
                        <select class="form-select" name="bahanBakuSelect" id="bahanBakuSelect">
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
                <button type="button" class="btn btn-primary" onclick="funBahan()">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
@push('js')
<script>
    function funBahan()
    {
        var bahanBakuSelect = document.getElementById('bahanBakuSelect').value;
        if(bahanBakuSelect == 'cash')
        {
            window.location.href = "{{route('billing.form-transaksi.bahan-baku.beli')}}";
        }
        else if(bahanBakuSelect == 'tempo')
        {
            window.location.href = "{{route('billing.form-transaksi.bahan-baku.beli-tempo')}}";
        }
    }
</script>
@endpush
