<div class="modal fade" id="keranjangModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="keranjangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keranjangTitle">
                    Jumlah <span id="titleJumlah"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="keranjangForm" action="{{route('billing.stok-bahan-jadi.keranjang.store')}}">
                @csrf

            <div class="modal-body">
                <input type="hidden" name="product_jadi_id" id="product_jadi_id">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <input type="text" class="form-control" name="jumlah" id="jumlah" aria-describedby="helpId"
                            placeholder="" />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
        </div>
    </div>
</div>
