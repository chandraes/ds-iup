<div class="modal fade" id="modalHistori" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="historiTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-primary" id="historiTitle">
                        <i class="fa fa-history me-2"></i>Histori Stok Barang
                    </h5>
                    <div class="text-muted small mt-1">
                        <i class="fa fa-info-circle me-1"></i>Keterangan:
                        <span id="historiKeterangan" class="fw-semibold text-dark"></span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="container mt-2">
                    <div class="row">
                    <div class="col-12 mb-3">
                        <label for="nm_barang_merk_retail" class="form-label">Nama Barang</label>
                        <input type="text" id="nm_barang_merk_retail" class="form-control" disabled
                            style="background-color: #e9ecef; opacity: 1;">
                    </div>
                </div>

                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-bordered align-middle mb-0" id="historiTable">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="text-center py-3" width="5%">No</th>
                                <th class="py-3" width="20%">
                                    <i class="fa fa-calendar-alt me-1"></i>Tanggal
                                </th>
                                <th class="text-center py-3" width="10%">Qty</th>
                                <th class="text-center py-3" width="10%">Satuan</th>
                                <th class="text-end py-3" width="20%">Harga Beli</th>
                                <th class="text-end py-3" width="20%">Harga Jual</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-secondary px-4 shadow-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times me-2"></i>Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Mengatur alignment kolom isi tabel agar sesuai dengan headernya */
    #historiTable tbody td:nth-child(1),
    /* Kolom No */
    #historiTable tbody td:nth-child(3),
    /* Kolom Qty */
    #historiTable tbody td:nth-child(4) {
        /* Kolom Satuan */
        text-align: center;
    }

    /* Mengatur kolom Harga agar Rata Kanan dan menggunakan font monospace (agar angka sejajar) */
    #historiTable tbody td:nth-child(5),
    /* Kolom Harga Beli */
    #historiTable tbody td:nth-child(6) {
        /* Kolom Harga Jual */
        text-align: right;
        font-family: 'Consolas', 'Monaco', monospace;
        /* Font angka pro */
        font-weight: 500;
    }

    /* Sedikit styling untuk tanggal agar fontnya tidak terlalu besar */
    #historiTable tbody td:nth-child(2) {
        font-size: 0.9rem;
    }
</style>
