<div class="modal fade" id="actionModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="actionTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionTitle">
                    Aksi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="actionForm">
                @csrf
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="kas_ppn" value="1">
                    <div class="col-md-4 mb-3">
                        <label for="harga_beli_dpp_act" class="form-label">Harga Beli DPP</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control text-end" name="harga_beli_dpp_act"
                                id="harga_beli_dpp_act" required readonly>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="harga_beli_dpp_act" class="form-label">Stok</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control text-end" name="stok_act" id="stok_act" disabled>
                            <span class="input-group-text" id="stok_satuan"></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="karyawan_id" class="form-label">Staff / Direksi</label>
                        <select class="form-select" name="karyawan_id" id="karyawan_id" required>
                            <option value="" disabled selected>-- Pilih Salah Satu --</option>
                            @foreach ($karyawan as $k)
                            <option value="{{$k->id}}">{{$k->nama}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="harga_beli_dpp_act" class="form-label">Jumlah Hilang</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control text-end" name="jumlah_hilang" id="jumlah_hilang" required>
                            <span class="input-group-text" id="hilang_satuan"></span>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="karyawan_id" class="form-label">Pilih salah satu</label>
                        <select class="form-select" name="aksi" id="aksi" required>
                            <option value="1">Bayar Langsung</option>
                            <option value="2">Kasbon</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
        </div>
    </div>
</div>
