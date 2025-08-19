<div class="modal fade" id="grosirModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="grosirModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="grosirModalTitle">
                    Grosir Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <input type="text" class="form-control" id="nm_barang_grosir"
                                    placeholder="Nama Barang Grosir" disabled>
                            </div>

                             <div class="col-md-2">
                                <input type="hidden" id="grosirBarangId" name="grosirBarangId" value="">
                                    <label for="grosirBarangJumlah">Min Qty</label>
                                        <input type="text" class="form-control" name="qty" id="qty" required
                                            data-thousands=".">
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="grosirBarangNama">Satuan Grosir</label>
                                    <select class="form-select" id="grosirSatuan" name="grosirSatuan" required>
                                        <option value="" disabled selected>
                                            Pilih Satuan Grosir
                                        </option>
                                        @foreach ($satuan as $s)
                                        <option value="{{ $s->id }}">
                                            {{ $s->nama }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input type="hidden" id="grosirBarangId" name="grosirBarangId" value="">
                                <div class="form-group">
                                    <label for="grosirBarangJumlah">Qty Satuan Retail</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="qty_grosir" id="jumlah" required
                                            data-thousands=".">
                                        <span class="input-group-text" id="sat_barang"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <input type="hidden" id="grosirBarangId" name="grosirBarangId" value="">
                                <div class="form-group">
                                    <label for="grosirBarangJumlah">Diskon Grosir</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="diskon" id="diskon_grosir"
                                            required data-thousands="." max="100" min="0.01" step="0.01">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-sm btn-block mt-4" id="addRowBtnGrosir" onclick="grosirStore()">
                                   <i class="fa fa-plus me-2"></i> Tambah Grosir
                                </button>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <table class="table table-bordered" id="grosirTable" style="width: 100%">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle">No</th>
                                        <th class="text-center align-middle">Min Qty Grosir</th>
                                        <th class="text-center align-middle">Qty Retail</th>
                                        <th class="text-center align-middle">Diskon</th>
                                        <th class="text-center align-middle">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="grosirTableBody"></tbody>
                            </table>
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
@push('js')
<script>
    $(document).ready(function() {
        // Initialize the grosir table
        let barangId = document.getElementById('grosirBarangId').value;
        if (barangId) {
            loadGrosirTable(barangId);
        }
    });

    function grosirStore() {
        let barangId = document.getElementById('grosirBarangId').value;
        let grosirSatuan = document.getElementById('grosirSatuan').value;
        let qtyGrosir = document.getElementById('jumlah').value;
        let diskonGrosir = document.getElementById('diskon_grosir').value;
        let qty = document.getElementById('qty').value;

        if (!barangId || !grosirSatuan || !qtyGrosir || !diskonGrosir) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Semua field harus diisi!'
            });
            return;
        }

        $.ajax({
            url: "{{ route('db.barang.store-grosir') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                barang_id: barangId,
                satuan_id: grosirSatuan,
                qty: qty,
                qty_grosir: qtyGrosir,
                diskon: diskonGrosir
            },
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message
                    });
                    // Clear the form fields
                    document.getElementById('grosirSatuan').value = '';
                    document.getElementById('jumlah').value = '';
                    document.getElementById('diskon_grosir').value = '';
                    document.getElementById('qty').value = '';
                    // Reload the grosir table
                    loadGrosirTable(barangId);

                    // Refresh DataTable if initialized
                    if ($.fn.DataTable.isDataTable('#data')) {
                        $('#data').DataTable().ajax.reload(null, false);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON.message || 'Terjadi kesalahan saat menyimpan data.'
                });
            }
        });
    }

    function loadGrosirTable(barangId) {
        // clear existing rows
        document.querySelector('#grosirTable tbody').innerHTML = '';
        // Fetch grosir data for the given barangId
        $.ajax({
            url: "{{ route('db.barang.get-grosir') }}",
            type: "GET",
            data: {
                barang_id: barangId
            },
            success: function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    let grosirTableBody = document.getElementById('grosirTableBody');
                    grosirTableBody.innerHTML = ''; // Clear existing rows
                    response.data.forEach(function(grosir) {
                        console.log(grosir);
                        let row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-center">${grosirTableBody.children.length + 1}</td>
                            <td class="text-center">${grosir.qty} ${grosir.satuan.nama} </td>
                            <td class="text-center">${grosir.qty_grosir} ${grosir.barang.satuan.nama} </td>
                            <td class="text-center">${grosir.diskon} %</td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" onclick="deleteGrosir(${grosir.id})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        `;
                        grosirTableBody.appendChild(row);
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON.message || 'Terjadi kesalahan saat memuat data grosir.'
                });
            }

        });
    }

    function deleteGrosir(grosirId) {
        Swal.fire({
            title: 'Konfirmasi',
            text: "Apakah Anda yakin ingin menghapus grosir ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('db.barang.delete-grosir') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: grosirId
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            // Reload the grosir table
                            let barangId = document.getElementById('grosirBarangId').value;
                            loadGrosirTable(barangId);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message || 'Terjadi kesalahan saat menghapus grosir.'
                        });
                    }
                });
            }
        });
    }

</script>
@endpush
