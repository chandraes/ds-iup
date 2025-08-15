<div class="modal fade" id="grosirModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="grosirModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="grosirModalLabel">
                    Jual Grosir

                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="grosirForm" action="{{route('sales.jual.keranjang.grosir.store')}}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="keranjang_jual_konsumen_id" value="{{ $id }}">
                    <input type="hidden" name="barang_stok_harga_id" id="barang_stok_harga_id_grosir">
                    <input type="hidden" name="barang_ppn" id="barang_ppn_grosir">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <input type="text" id="nm_barang_merk" class="form-control" disabled></input>
                                </div>

                                <div class="col-md-6">
                                    <label for="" class="form-label">Qty</label>
                                    <input type="text" class="form-control" name="jumlah_grosir" id="jumlah_grosir"
                                        aria-describedby="helpId" placeholder="masukan qty grosir" />
                                </div>
                                 <div class="col-md-6">
                                    <label for="satuan_grosir_id" class="form-label">Pilih Satuan Grosir</label>
                                    <select class="form-select" name="satuan_grosir_id" id="satuan_grosir_id">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row" id="rowGrosir" hidden>
                                {{-- grosir --}}
                                <div class="col-md-12">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th class="text-center align-middle">No</th>
                                                <th class="text-center align-middle">MIN Qty Grosir</th>
                                                <th class="text-center align-middle">Qty</th>
                                                <th class="text-center align-middle">Diskon</th>
                                            </tr>
                                        </thead>
                                        <tbody id="grosirTableBody"></tbody>
                                    </table>
                                </div>
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
