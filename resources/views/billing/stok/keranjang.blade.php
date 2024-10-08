<div class="modal fade" id="keranjangModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="keranjangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered " role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keranjangTitle">
                    Jumlah <span id="titleJumlah"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="keranjangForm" action="{{route('billing.form-jual.keranjang.store')}}">
                @csrf

            <div class="modal-body">
                <input type="hidden" name="barang_stok_harga_id" id="barang_stok_harga_id">
                <input type="hidden" name="barang_ppn" id="barang_ppn">
                <div class="row">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control text-end" name="jumlah" id="jumlah" required data-thousands=".">
                        <span class="input-group-text" id="jumlah_satuan"></span>
                    </div>
                    {{-- <div class="col-md-12 mb-3">
                        <input type="text" class="form-control" name="jumlah" id="jumlah" aria-describedby="helpId"
                            placeholder="" />
                    </div> --}}
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
