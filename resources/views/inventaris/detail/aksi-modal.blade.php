<div class="modal fade" id="aksiModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="aksiModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="aksiModalTitle">
                    Aksi {{$inventaris->nama}}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="aksiForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="pengurangan" id="pengurangan" disabled>
                        <h3>Informasi Pembelian</h3>
                        <div class="col-md-3 mb-3">
                            <label for="jenis_invetnaris" class="form-label">Jenis Inventaris</label>
                            <input type="text" class="form-control" name="jenis_invetnaris" id="jenis_invetnaris"
                                disabled value="{{$inventaris->nama}}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="jumlah_beli" class="form-label">Jumlah Beli</label>
                            <input type="text" class="form-control" name="jumlah_beli" id="jumlah_beli" disabled>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="jenis_invetnaris" class="form-label">Harga Satuan Beli</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="harga_satuan_beli" id="harga_satuan_beli"
                                    disabled>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="total_harga beli" class="form-label">Total Harga Beli</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="total_harga_beli" id="total_harga_beli"
                                    disabled>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mt-3">
                        <div class="col-md-3 mb-3">
                            <label for="uraian" class="form-label">Uraian</label>
                            <input type="text" class="form-control" name="uraian" id="uraian" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="jumlah" class="form-label">Jumlah Jual</label>
                            <input type="text" class="form-control" name="jumlah" id="jumlah" required onkeyup="calculateTotal()">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="jenis_invetnaris" class="form-label">Harga Satuan</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="harga_satuan" id="harga_satuan" onkeyup="calculateTotal()"
                                    required>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="jenis_invetnaris" class="form-label">Total</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="total_harga" id="total_harga"
                                    required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')
    <script>
         var jumlah = new Cleave('#jumlah', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        var harga_satuan = new Cleave('#harga_satuan', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });

        function calculateTotal()
        {
            var jumlahBeliInput = $('#jumlah_beli').val() || 0;
            var jumlahBeli = parseInt(jumlahBeliInput.replace(/\./g, ''), 10); // Assuming jumlah_beli is also formatted with Cleave.js

            var penguranganInput = $('#pengurangan').val() || 0;
            var pengurangan = parseInt(penguranganInput, 10); // Ensure this is correctly parsed

            // Adjust jumlahBeli by pengurangan if necessary
            jumlahBeli = jumlahBeli - pengurangan;

            var jumlahInput = $('#jumlah').val() || 0;
            var jumlah = parseInt(jumlahInput.replace(/\./g, ''), 10);

            var hargaSatuanInput = $('#harga_satuan').val() || 0;
            var hargaSatuan = parseInt(hargaSatuanInput.replace(/\./g, ''), 10);

            // Ensure jumlah does not exceed the adjusted jumlahBeli
            if (jumlah > jumlahBeli) {
                jumlah = jumlahBeli; // Set jumlah to adjusted jumlahBeli if it exceeds
                $('#jumlah').val(jumlah.toLocaleString('id-ID')); // Update the jumlah input field
            }

            var total = jumlah * hargaSatuan;
            $('#total_harga').val(total.toLocaleString('id-ID'));
        }

        confirmAndSubmit('#aksiForm', 'Apakah anda Yakin??')
    </script>
@endpush
