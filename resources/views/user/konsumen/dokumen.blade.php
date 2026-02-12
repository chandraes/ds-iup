<div class="modal fade" id="dokumenModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content shadow-lg border-0">
            {{-- Header Modal --}}
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-folder-open me-2"></i> Kelola Dokumen Konsumen
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-0">
                <div class="row g-0">

                    {{-- KOLOM KIRI: FORM UPLOAD --}}
                    <div class="col-lg-4 border-end bg-light p-4">
                        <div class="mb-3 border-bottom pb-2">
                            <h6 class="fw-bold text-dark"><i class="fa fa-upload me-1"></i> Upload Baru</h6>
                        </div>

                        <form id="dokumenForm" onsubmit="saveDokumen(event)" enctype="multipart/form-data">
                            @csrf
                            {{-- Hidden ID Konsumen --}}
                            <input type="hidden" name="konsumen_id" id="dokumen_konsumen_id">

                            {{-- Nama Konsumen (Readonly) --}}
                            <div class="mb-3">
                                <label class="small text-muted fw-bold">Nama Konsumen</label>
                                <input type="text" class="form-control bg-white fw-bold text-primary" id="dokumen_konsumen_nama" readonly>
                            </div>

                            {{-- Dropdown Unit (Diisi oleh AJAX Script Anda) --}}
                            <div class="mb-3">
                                <label for="dokumen_barang_unit_id" class="small text-muted fw-bold">Perusahaan / Unit</label>
                                <select name="barang_unit_id" id="dokumen_barang_unit_id" class="form-select">
                                    {{-- Option akan di-inject oleh JS --}}
                                </select>
                            </div>

                            {{-- Judul Dokumen --}}
                            <div class="mb-3">
                                <label for="dokumen_judul" class="small text-muted fw-bold">Judul Dokumen</label>
                                <input type="text" class="form-control" name="nama" id="dokumen_judul" placeholder="Contoh: KTP, NPWP" required>
                            </div>

                            {{-- File Upload --}}
                            <div class="mb-4">
                                <label for="dokumen_file" class="small text-muted fw-bold">Pilih File</label>
                                <input type="file" class="form-control" name="file" id="dokumen_file" required>
                                <small class="text-muted fst-italic" style="font-size: 11px;">Format: Gambar atau PDF</small>
                            </div>

                            {{-- Tombol Simpan --}}
                            <button type="submit" class="btn btn-primary w-100" id="btn-submit-dokumen">
                                <i class="fa fa-save me-1"></i> Simpan Dokumen
                            </button>
                        </form>
                    </div>

                    {{-- KOLOM KANAN: LIST DOKUMEN --}}
                    <div class="col-lg-8 p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark m-0">Daftar Dokumen</h6>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="refreshCurrentDokumen()">
                                <i class="fa fa-sync-alt me-1"></i> Refresh
                            </button>
                        </div>

                        {{-- Area Tabel --}}
                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-middle mb-0" id="dokumenTable" style="width:100%">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" width="5%">No</th>
                                        <th>Nama Dokumen</th>
                                        <th>Perusahaan</th>
                                        <th>File</th>
                                        <th class="text-center" width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="dokumenTableBody">
                                    {{-- Data akan diisi oleh AJAX Script Anda --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    // Variabel Global untuk menyimpan state saat ini agar Refresh berfungsi
    let globalKonsumenData = {
        id: null,
        nama: null,
        kode: null
    };

    // --- 1. FUNGSI UTAMA (Diadaptasi dari Script Anda) ---
    function dokumen(id, nama, kode)
    {
        // Simpan ke variabel global untuk fitur refresh
        globalKonsumenData = { id: id, nama: nama, kode: kode };

        // Set Nama di Input
        document.getElementById('dokumen_konsumen_nama').value = kode + ' ' + nama;
        document.getElementById('dokumen_konsumen_id').value = id; // Set Hidden ID

        // Reset Table & Tampilkan Loading
        if ($.fn.DataTable.isDataTable('#dokumenTable')) {
             $('#dokumenTable').DataTable().destroy();
        }

        document.getElementById('dokumenTableBody').innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 small text-muted">Memuat data...</div>
                </td>
            </tr>
        `;

        // AJAX 1: GET DOKUMEN LIST
        $.ajax({
            url: '{{route("user.konsumen.dokumen")}}', // Routing Anda
            type: 'GET',
            data: { konsumen_id: id },
            success: function(data) {
                if (data.status === 'success') {
                    let rows = '';
                    // Generate Rows
                    data.data.forEach((doc, index) => {
                        // Perbaikan: Pastikan URL file valid
                        let fileLink = doc.file_url;

                        rows += `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td class="fw-bold">${doc.nama}</td>
                                <td><span class="badge bg-info text-dark">${doc.barang_unit ? doc.barang_unit.nama : '-'}</span></td>
                                <td>
                                    <a href="${fileLink}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-eye"></i> Lihat
                                    </a>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteDokumen(${doc.id})">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    // Render ke Body
                    document.getElementById('dokumenTableBody').innerHTML = rows;

                    // Handle Empty State
                    if (data.data.length === 0) {
                        document.getElementById('dokumenTableBody').innerHTML = `
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fa fa-folder-open fa-2x mb-2"></i><br>
                                    Tidak ada dokumen.
                                </td>
                            </tr>
                        `;
                    } else {
                        // Init Datatable
                        $('#dokumenTable').DataTable({
                            paging: true,
                            lengthChange: false,
                            pageLength: 5,
                            retrieve: true,
                            language: { search: "", searchPlaceholder: "Cari dokumen..." },
                            columnDefs: [
                                { orderable: false, targets: [3, 4] }
                            ]
                        });
                    }

                } else {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: data.message });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({ icon: 'error', title: 'Oops...', text: textStatus+' '+errorThrown });
            }
        });

        // AJAX 2: GET UNIT (DROPDOWN)
        $('#dokumen_barang_unit_id').empty();
        $('#dokumen_barang_unit_id').append('<option value="" selected> -- Pilih Perusahaan -- </option>');

        $.ajax({
            url: '{{route("universal.get-unit")}}', // Routing Anda
            type: 'GET',
            success: function(data) {
                if (data.status === 'success') {
                    $.each(data.data, function(index, value){
                        $('#dokumen_barang_unit_id').append('<option value="'+value.id+'">'+value.nama+'</option>');
                    });

                    // Init Select2 pada Dropdown Unit
                    $('#dokumen_barang_unit_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('#dokumenModal'), // Penting agar Select2 jalan di Modal
                    });

                } else {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: data.message });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({ icon: 'error', title: 'Oops...', text: textStatus+' '+errorThrown });
            }
        });
    }

    // --- 2. FUNGSI REFRESH ---
    function refreshCurrentDokumen() {
        if (globalKonsumenData.id) {
            // Panggil ulang fungsi utama dengan data terakhir
            dokumen(globalKonsumenData.id, globalKonsumenData.nama, globalKonsumenData.kode);
        }
    }

    // --- 3. FUNGSI UPLOAD (AJAX POST) ---
    function saveDokumen(event) {
        event.preventDefault();

        let btn = $('#btn-submit-dokumen');
        let originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Loading...');

        let formData = new FormData(document.getElementById('dokumenForm'));

        $.ajax({
            url: '/user/konsumen/dokumen/store', // Sesuaikan route store Anda
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                // Reset Form (kecuali ID konsumen)
                $('#dokumen_judul').val('');
                $('#dokumen_file').val('');
                $('#dokumen_barang_unit_id').val('').trigger('change');

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Dokumen berhasil disimpan',
                    timer: 1500,
                    showConfirmButton: false
                });

                // Refresh Tabel
                refreshCurrentDokumen();
            },
            error: function(xhr) {
                let msg = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal upload.';
                Swal.fire('Gagal', msg, 'error');
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    }

    // --- 4. FUNGSI DELETE ---
    function deleteDokumen(docId) {
        Swal.fire({
            title: 'Hapus Dokumen?',
            text: "Data tidak bisa dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/user/konsumen/dokumen/destroy/" + docId, // Sesuaikan route destroy Anda
                    type: 'DELETE', // Gunakan method DELETE atau POST sesuai route Anda (biasanya DELETE)
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(response) {
                        Swal.fire('Terhapus!', 'Dokumen telah dihapus.', 'success');
                        refreshCurrentDokumen();
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Gagal menghapus data.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
