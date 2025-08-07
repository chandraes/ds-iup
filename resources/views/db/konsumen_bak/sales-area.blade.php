<div class="modal fade" id="modalSalesArea" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="salesAreaTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salesAreaTitle">
                    Sales Area
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <button class="btn btn-primary mb-3" id="addRowBtn">Tambah Data</button>
                <div class="row">
                    <table class="table table-bordered" id="salesAreaTable" style="width: 100%">
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
    let table;

    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $('#modalSalesArea').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#salesAreaTable')) {
            table = $('#salesAreaTable').DataTable({
                ajax: '{{ route("db.sales-area") }}',
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
                        data: 'nama',
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
            $('#addRowBtn').on('click', function () {
                const newRow = table.row.add({}).draw(false).node();
                $(newRow).addClass('new-row');
                $(newRow).html(`
                    <td></td>
                    <td><input type="text" class="form-control form-control-sm new-name" /></td>
                    <td><button class="btn btn-sm btn-success save-btn">Simpan</button></td>
                `);
            });

            // Simpan Data Baru
            $('#salesAreaTable tbody').on('click', '.save-btn', function () {
                const row = $(this).closest('tr');
                const nama = row.find('.new-name').val();

                if (!nama.trim()) {
                    alert('Nama tidak boleh kosong');
                    return;
                }

                $.ajax({
                    url: '/db/sales-area/store',
                    method: 'POST',
                    data: { nama: nama },
                    success: function () {
                        table.ajax.reload();
                    },
                    error: function () {
                        alert('Gagal simpan data');
                    }
                });
            });

            // Edit
            $('#salesAreaTable tbody').on('click', '.edit-btn', function () {
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
            $('#salesAreaTable tbody').on('click', '.save-edit-btn', function () {
                const row = $(this).closest('tr');
                const input = row.find('.edit-input');
                const id = input.data('id');
                const newValue = input.val();

                $.ajax({
                    url: `/db/sales-area/update/${id}`,
                    method: 'PATCH',
                    data: { nama: newValue },
                    success: function () {
                        table.ajax.reload();
                    },
                    error: function () {
                        alert('Gagal update data');
                    }
                });
            });

            // Batal Edit
            $('#salesAreaTable tbody').on('click', '.cancel-edit-btn', function () {
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
            $('#salesAreaTable tbody').on('click', '.delete-btn', function () {
                const id = $(this).data('id');

                if (confirm('Yakin ingin menghapus data ini?')) {
                    $.ajax({
                        url: `/db/sales-area/delete/${id}`,
                        method: 'DELETE',
                        success: function () {
                            table.ajax.reload();
                        },
                        error: function () {
                            alert('Gagal hapus data');
                        }
                    });
                }
            });
        } else {
            table.ajax.reload();
        }

        setTimeout(() => {
            table.columns.adjust().draw();
        }, 200);
    });

    $('#modalSalesArea').on('hidden.bs.modal', function () {
        if ($.fn.DataTable.isDataTable('#salesAreaTable')) {
            table.destroy();
            $('#salesAreaTable tbody').empty();
        }
    });
</script>
@endpush
