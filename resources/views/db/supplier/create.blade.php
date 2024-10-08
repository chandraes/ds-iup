<div class="modal fade" id="createInvestor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="investorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investorTitle">Tambah Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('db.supplier.store')}}" method="post" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="nama" class="form-label">Nama Perusahaan</label>
                            <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="cp" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="cp" id="cp" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control" name="no_hp" id="no_hp" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="kota" class="form-label">KOTA</label>
                            <input type="text" class="form-control" name="kota" id="kota" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="pembayaran" class="form-label">Sistem Bayar</label>
                            <select class="form-select" name="pembayaran" id="pembayaran" required onchange="checkSistemBayar()">
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                <option value="1">Cash</option>
                                <option value="2">Tempo</option>
                            </select>
                        </div>
                        <div class="col-4 mb-3" id="divTempo" hidden>
                            <label for="pembayaran" class="form-label">Tempo</label>
                            <div class="input-group mb-3">
                                <input type="text" class="form-control" name="tempo_hari" id="tempo_hari">
                                <span class="input-group-text" id="basic-addon1">Hari</span>
                              </div>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="1" selected>Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" class="form-control" required></textarea>
                        </div>
                    </div>
                    <hr>
                    <h2>Informasi BANK</h2>
                    <hr>
                    <div class="row">
                        <div class="col-4 mb-3">
                            <label for="nama_rek" class="form-label">Atas Nama</label>
                            <input type="text" class="form-control" name="nama_rek" id="nama_rek"
                                aria-describedby="helpId" placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="no_rek" class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control" name="no_rek" id="no_rek" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-4 mb-3">
                            <label for="bank" class="form-label">BANK</label>
                            <input type="text" class="form-control" name="bank" id="bank" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')
    <script>
        function checkSistemBayar(){
            var pembayaran = $('#pembayaran').val();
            if(pembayaran == 2){
                $('#divTempo').removeAttr('hidden');
                $('#tempo_hari').attr('required', true);
            }else{
                $('#divTempo').attr('hidden', true);
                $('#tempo_hari').removeAttr('required');
            }
        }
    </script>
@endpush
