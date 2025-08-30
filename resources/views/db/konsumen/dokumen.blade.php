<div class="modal fade" id="dokumenModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="dokumenModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dokumenModalTitle">
                    Dokumen Konsumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <input type="text" class="form-control" id="dokumen_konsumen_nama" disabled>
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <div id="dokumenContent">
                            <form id="dokumenForm" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <input type="hidden" name="konsumen_id" id="dokumen_konsumen_id">
                                    <div class="col-md-4 mb-3">
                                        <label for="perusahaan" class="form-label">Perusahaan</label>
                                        <select name="barang_unit_id" id="dokumen_barang_unit_id" class="form-select">

                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                                        <input type="text" class="form-control" id="nama_dokumen" name="nama" required>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="file" class="form-label">Pilih File</label>
                                        <input type="file" class="form-control" id="file_dokumen" name="file"
                                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                        <div class="form-text">Format yang diterima: .pdf, .doc, .docx, .jpg, .jpeg,
                                            .png</div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="uploadDokumen()">Upload Dokumen</button>

                            </form>
                        </div>
                    </div>
                    <div class="col-lg-12 col-md-12 mb-3 mt-3">
                        <div id="dokumenList">
                            <table class="table table-bordered table-hover" id="dokumenTable">
                                <thead>
                                    <tr class="text-center">
                                        <th>No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Perusahaan</th>
                                        <th>File</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dokumenTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
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
    function uploadDokumen() {
       Swal.fire({
            title: 'Apakah data sudah benar?',
            text: "Pastikan data sudah benar sebelum disimpan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
            if (result.isConfirmed) {
                let form = document.getElementById('dokumenForm');
                let formData = new FormData(form);

                fetch("{{ route('db.konsumen.dokumen.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadDokumen(formData.get('konsumen_id'));

                        Swal.fire(
                            'Berhasil!',
                            'Dokumen berhasil disimpan.',
                            'success'
                        );
                        form.reset();

                    } else {
                        Swal.fire(
                            'Gagal!',
                            data.message || 'Terjadi kesalahan saat menyimpan dokumen.',
                            'error'
                        );
                    }

                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Gagal!',
                        'Terjadi kesalahan saat menyimpan dokumen.',
                        'error'
                    );
                });
            }
        });
    }

   function loadDokumen(konsumenId) {
        const id = konsumenId || document.getElementById('dokumen_konsumen_id').value;
        document.getElementById('dokumen_konsumen_id').value = id;
        document.getElementById('dokumenTableBody').innerHTML = `
            <tr>
                <td colspan="5" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `;

        $.ajax({
            url: '{{route('db.konsumen.dokumen')}}',
            type: 'GET',
            data: { konsumen_id: id },
            success: function(data) {
                // Destroy DataTable sebelum mengisi ulang data
                if ($.fn.DataTable.isDataTable('#dokumenTable')) {
                    $('#dokumenTable').DataTable().destroy();
                }

                let rows = '';
                if (data.status === 'success' && data.data.length > 0) {
                    data.data.forEach((doc, index) => {
                        rows += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${doc.nama}</td>
                                <td>${doc.barang_unit ? doc.barang_unit.nama : '-'}</td>
                                <td><a href="${doc.file_url}" target="_blank">Lihat File</a></td>
                                <td class="text-center">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="deleteDokumen(${doc.id}, ${id})">
                                        <i class="fa fa-trash"></i> Hapus
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    rows = `
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada dokumen.</td>
                        </tr>
                    `;
                }

                document.getElementById('dokumenTableBody').innerHTML = rows;

                // Inisialisasi ulang DataTable
                $('#dokumenTable').DataTable({
                    paging: true,
                    lengthChange: false,
                    pageLength: 5,
                    columnDefs: [
                        { orderable: false, targets: 4 }
                    ]
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: textStatus+' '+errorThrown
                });
            }
        });
    }

    function deleteDokumen(docId, konsumenId) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Dokumen yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/db/konsumen/dokumen/destroy/${docId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        loadDokumen(konsumenId);

                        Swal.fire(
                            'Terhapus!',
                            'Dokumen berhasil dihapus.',
                            'success'
                        );
                    } else {
                        Swal.fire(
                            'Gagal!',
                            data.message || 'Terjadi kesalahan saat menghapus dokumen.',
                            'error'
                        );
                    }
                }
                )
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Gagal!',
                        'Terjadi kesalahan saat menghapus dokumen.',
                        'error'
                    );
                });
            }
        });

    }
</script>
@endpush
