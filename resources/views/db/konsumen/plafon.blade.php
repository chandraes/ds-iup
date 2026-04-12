<div class="modal fade" id="modalPlafon" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Limit Plafon: <span id="plafonKonsumenName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formUpdatePlafon" class="mb-4">
                    @csrf
                    <input type="hidden" id="plafon_konsumen_id" name="konsumen_id">
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Ubah Nominal Limit Plafon</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text">Rp</span>
                                <input type="text" class="form-control" id="input_plafon_baru" name="plafon" required>
                            </div>
                        </div>
                        {{-- <div class="col-md-5">
                            <label class="form-label fw-bold">Keterangan (Opsional)</label>
                            <input type="text" class="form-control form-control-sm" id="input_keterangan_plafon" name="keterangan" placeholder="Alasan perubahan...">
                        </div> --}}
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100" id="btnSavePlafon">Update</button>
                        </div>
                    </div>
                </form>

                <hr>

                <h6 class="fw-bold mb-3"><i class="fa fa-history"></i> Histori Perubahan</h6>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100" id="tableHistoriPlafon" style="font-size: 0.8rem;">
                        <thead class="table-secondary">
                            <tr>
                                <th class="text-center">Tanggal</th>
                                <th class="text-center">Plafon Lama</th>
                                <th class="text-center">Plafon Baru</th>
                                <th class="text-center">Diubah Oleh</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
