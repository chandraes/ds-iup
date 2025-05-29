<div class="modal fade" id="modalKodeToko" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="kodeTokoTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="kodeTokoTitle">
                    Kode Toko
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <button class="btn btn-primary mb-3" id="addRowBtnKode">Tambah Data</button>
                <div class="row">
                    <table class="table table-bordered" id="kodeTokoTable" style="width: 100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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


    let tableKode;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $('#modalKodeToko').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#kodeTokoTable')) {
            tableKode = $('#kodeTokoTable').DataTable({
                ajax: '{{ route("db.kode-toko") }}',
                columns: [
                    {
                        data: null,
                        title: 'No',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    {
                        data: 'kode',
                        render: function (data, type, row) {
                            return `<span class="editable" data-id="${row.id}">${data}</span>`;
                        }
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: function (data, type, row) {
                            return `
                                <button class="btn btn-sm btn-warning edit-btn" data-id="${row.id}">Edit</button>
                                <button class="btn btn-sm btn-success save-edit-btn d-none" data-id="${row.id}">Simpan</button>
                                <button class="btn btn-sm btn-secondary cancel-edit-btn d-none" data-id="${row.id}">Batal</button>
                                <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}">Hapus</button>
                            `;
                        }
                    }
                ]
            });

            // Tambah Data
            $('#addRowBtnKode').on('click', function () {
                const newRow = tableKode.row.add({}).draw(false).node();
                $(newRow).addClass('new-row');
                $(newRow).html(`
                    <td></td>
                    <td><input type="text" class="form-control form-control-sm new-name" /></td>
                    <td><button class="btn btn-sm btn-success save-btn">Simpan</button></td>
                `);
            });

            // Simpan Data Baru
            $('#kodeTokoTable tbody').on('click', '.save-btn', function () {
                const row = $(this).closest('tr');
                const nama = row.find('.new-name').val();

                if (!nama.trim()) {
                    alert('Nama tidak boleh kosong');
                    return;
                }

                $.ajax({
                    url: '/db/kode-toko/store',
                    method: 'POST',
                    data: { kode: nama },
                    success: function () {
                        tableKode.ajax.reload();
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) { // Validation error
                            const errors = xhr.responseJSON.errors;
                            if (errors && errors.kode) {
                                alert(errors.kode[0]); // Show the first validation error for 'kode'
                            }
                        } else {
                            alert('Gagal simpan data');
                        }
                    }
                });
            });


            // Edit
            $('#kodeTokoTable tbody').on('click', '.edit-btn', function () {
                const row = $(this).closest('tr');
                const span = row.find('.editable');
                const currentText = span.text();
                const id = $(this).data('id');

                span.replaceWith(`<input type="text" class="form-control form-control-sm edit-input" value="${currentText}" data-id="${id}">`);
                row.find('.edit-btn').addClass('d-none');
                row.find('.save-edit-btn').removeClass('d-none');
                row.find('.cancel-edit-btn').removeClass('d-none');
            });

            // Simpan Edit
            $('#kodeTokoTable tbody').on('click', '.save-edit-btn', function () {
                const row = $(this).closest('tr');
                const input = row.find('.edit-input');
                const id = input.data('id');
                const newValue = input.val();

                $.ajax({
                    url: `/db/kode-toko/update/${id}`,
                    method: 'PATCH',
                    data: { kode: newValue },
                    success: function () {
                        tableKode.ajax.reload();
                    },
                    error: function (xhr) {
                        if (xhr.status === 422) { // Validation error
                            const errors = xhr.responseJSON.errors;
                            if (errors && errors.kode) {
                                alert(errors.kode[0]); // Show the first validation error for 'kode'
                            }
                        } else {
                            alert('Gagal simpan data');
                        }
                    }
                });
            });

            // Batal Edit
            $('#kodeTokoTable tbody').on('click', '.cancel-edit-btn', function () {
                const row = $(this).closest('tr');
                const input = row.find('.edit-input');
                const originalValue = input.val();
                const id = input.data('id');

                input.replaceWith(`<span class="editable" data-id="${id}">${originalValue}</span>`);
                row.find('.edit-btn').removeClass('d-none');
                row.find('.save-edit-btn').addClass('d-none');
                row.find('.cancel-edit-btn').addClass('d-none');
            });

            // Hapus Data
            $('#kodeTokoTable tbody').on('click', '.delete-btn', function () {
                const id = $(this).data('id');

                if (confirm('Yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: `/db/kode-toko/delete/${id}`,
                        method: 'DELETE',
                        success: function () {
                            tableKode.ajax.reload();
                        },
                        error: function () {
                            alert('Gagal hapus data');
                        }
                    });
                }
            });
        } else {
            tableKode.ajax.reload();
        }

        setTimeout(() => {
            tableKode.columns.adjust().draw();
        }, 200);
    });

    $('#modalkodeToko').on('hidden.bs.modal', function () {
        if ($.fn.DataTable.isDataTable('#kodeTokoTable')) {
            tableKode.destroy();
            $('#kodeTokoTable tbody').empty();
        }
    });
</script>
@endpush
