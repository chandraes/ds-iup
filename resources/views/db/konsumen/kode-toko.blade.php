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
(function () {
    'use strict';
    let tableKode;
    let busy = false;
    const modal = $('#modalKodeToko');
    const body = $('#kodeTokoTable tbody');
    const addButton = $('#addRowBtnKode');
    const routes = {
        list: @json(route('db.kode-toko')),
        store: @json(route('db.kode-toko.store')),
        update: @json(route('db.kode-toko.update', ':id')),
        destroy: @json(route('db.kode-toko.delete', ':id'))
    };
    const endpoint = (template, id) => template.replace(':id', String(id)).replace('%3Aid', String(id));
    const errorText = xhr => Object.values(xhr.responseJSON?.errors || {}).flat().join('\n') || xhr.responseJSON?.message || xhr.responseJSON?.error || 'Permintaan gagal. Silakan coba lagi.';
    function textSpan(value) {
        return $('<span>').addClass('editable').text(value ?? '')[0].outerHTML;
    }
    function button(label, classes) {
        return $('<button>', {type: 'button', class: 'btn btn-sm ' + classes}).text(label)[0].outerHTML;
    }
    function restoreRow(row) {
        tableKode.row(row).invalidate('data').draw(false);
    }
    function hasDraft() {
        return !!tableKode && tableKode.rows().data().toArray().some(row => row.isNew);
    }
    function syncAddButton() {
        addButton.prop('disabled', busy || hasDraft());
    }
    async function mutate(row, title, text, method, url, data) {
        if (busy) return;
        busy = true;
        row.find('button, input').prop('disabled', true);
        syncAddButton();
        try {
            const confirmation = await Swal.fire({title, text, icon: method === 'DELETE' ? 'warning' : 'question', showCancelButton: true, confirmButtonText: method === 'DELETE' ? 'Ya, hapus' : 'Ya, simpan', cancelButtonText: 'Batal', focusCancel: true, confirmButtonColor: method === 'DELETE' ? '#dc3545' : '#166534'});
            if (!confirmation.isConfirmed) return;
            await $.ajax({url, method, data, headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), 'Accept': 'application/json'}});
            tableKode.ajax.reload(() => syncAddButton(), false);
        } catch (xhr) {
            await Swal.fire('Data belum dapat disimpan', errorText(xhr), 'error');
        } finally {
            busy = false;
            row.find('button, input').prop('disabled', false);
            syncAddButton();
        }
    }
    modal.on('shown.bs.modal', function () {
        if (!tableKode) {
            tableKode = $('#kodeTokoTable').DataTable({
                ajax: routes.list,
                columns: [
                    {data: null, orderable: false, searchable: false, className: 'text-center', render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1},
                    {data: 'kode', defaultContent: '', render: function (data, type, row) {
                        if (type !== 'display') return data ?? '';
                        if (row.isNew) return $('<input>', {type: 'text', class: 'form-control form-control-sm new-name', maxlength: 255, 'aria-label': 'Kode toko baru'})[0].outerHTML;
                        return textSpan(data);
                    }},
                    {data: null, orderable: false, searchable: false, className: 'text-center', render: function (data, type, row) {
                        if (type !== 'display') return '';
                        if (row.isNew) return button('Simpan', 'btn-success save-btn') + ' ' + button('Batal', 'btn-secondary cancel-new-btn');
                        return button('Edit', 'btn-warning edit-btn') + ' ' + button('Simpan', 'btn-success save-edit-btn d-none') + ' ' + button('Batal', 'btn-secondary cancel-edit-btn d-none') + ' ' + button('Hapus', 'btn-danger delete-btn');
                    }}
                ],
                language: {emptyTable: 'Belum ada kode toko.', search: 'Cari kode toko:'},
                drawCallback: syncAddButton
            });
        } else if (!busy) {
            tableKode.ajax.reload(() => tableKode.columns.adjust(), false);
        }
        tableKode.columns.adjust();
    });
    addButton.on('click', function () {
        if (busy || !tableKode || hasDraft()) return;
        const row = tableKode.row.add({id: null, kode: '', isNew: true});
        tableKode.search('').order([]).page('last').draw(false);
        const index = row.index();
        tableKode.page(Math.floor(index / tableKode.page.len())).draw(false);
        $(row.node()).find('.new-name').trigger('focus');
        syncAddButton();
    });
    body.on('click', '.cancel-new-btn', function () {
        if (busy) return;
        tableKode.row($(this).closest('tr')).remove().draw(false);
        syncAddButton();
    });
    body.on('click', '.edit-btn', function () {
        if (busy) return;
        const row = $(this).closest('tr');
        const current = tableKode.row(row).data();
        row.find('.editable').replaceWith($('<input>', {type: 'text', class: 'form-control form-control-sm edit-input', maxlength: 255, 'aria-label': 'Kode toko'}).val(current.kode));
        row.find('.edit-btn, .delete-btn').addClass('d-none');
        row.find('.save-edit-btn, .cancel-edit-btn').removeClass('d-none');
    });
    body.on('click', '.cancel-edit-btn', function () {
        if (!busy) restoreRow($(this).closest('tr'));
    });
    body.on('click', '.save-btn, .save-edit-btn', function () {
        if (busy) return;
        const row = $(this).closest('tr');
        const data = tableKode.row(row).data();
        const value = String(row.find('input').val() || '').trim();
        if (!value) { Swal.fire('Kode toko belum diisi', 'Kode toko wajib diisi.', 'warning'); return; }
        mutate(row, data.isNew ? 'Simpan kode toko baru?' : 'Simpan perubahan kode toko?', value, data.isNew ? 'POST' : 'PATCH', data.isNew ? routes.store : endpoint(routes.update, Number(data.id)), {kode: value});
    });
    body.on('click', '.delete-btn', function () {
        if (busy) return;
        const row = $(this).closest('tr');
        const data = tableKode.row(row).data();
        mutate(row, 'Hapus kode toko?', 'Hapus kode ' + data.kode + '? Kode yang sedang digunakan tidak dapat dihapus.', 'DELETE', endpoint(routes.destroy, Number(data.id)));
    });
    modal.on('hidden.bs.modal', function () {
        if (tableKode && !busy) tableKode.ajax.reload(syncAddButton, false);
    });
})();
</script>
@endpush
