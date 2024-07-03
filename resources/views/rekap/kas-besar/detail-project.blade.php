<div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="title-{{$d->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="title-{{$d->id}}">
                    Detail Project
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="kode_project" class="form-label">Customer</label>
                        <input type="text" class="form-control" id="kode_project"
                            value="{{$d->project->customer->singkatan}}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="kode_project" class="form-label">No. Kontrak</label>
                        <input type="text" class="form-control" id="kode_project" value="{{$d->project->nomor_kontrak}}"
                            readonly>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label for="nama_project" class="form-label">Nama Project</label>
                        <input type="text" class="form-control" id="nama_project" value="{{$d->project->nama}}"
                            readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_mulai" class="form-label
                                                ">Tanggal Mulai</label>
                        <input type="text" class="form-control" id="tanggal_mulai"
                            value="{{$d->project->id_tanggal_mulai}}" readonly>
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_selesai" class="form-label">Jatuh Tempo</label>
                        <input type="text" class="form-control" id="tanggal_selesai"
                            value="{{$d->project->id_jatuh_tempo}}" readonly>
                    </div>
                </div>
                <hr>
                <div class="row mt-3">
                    <div class="col-md-3">
                        <label for="nilai_kontrak" class="form-label">Nilai DPP</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp</span>
                            <input type="text" class="form-control" value="{{$d->project->nf_nilai}}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="nilai_kontrak" class="form-label">Nilai PPN</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp</span>
                            <input type="text" class="form-control"
                                value="{{$d->project->invoice_tagihan->nf_nilai_ppn}}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="nilai_kontrak" class="form-label">Nilai PPh</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp</span>
                            <input type="text" class="form-control"
                                value="{{$d->project->invoice_tagihan->nf_nilai_pph}}" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="nilai_kontrak" class="form-label">Total Tagihan</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">Rp</span>
                            <input type="text" class="form-control"
                                value="{{$d->project->invoice_tagihan->nf_total_tagihan}}" readonly>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
