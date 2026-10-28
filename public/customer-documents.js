(function () {
    'use strict';
    const config = JSON.parse(document.getElementById('customer-document-config').textContent);
    const modal = $('#dokumenModal');
    const form = document.getElementById('dokumenForm');
    const unit = document.getElementById('dokumen_barang_unit_id');
    const body = document.getElementById('dokumenTableBody');
    const csrf = form.querySelector('[name="_token"]').value;
    let table, controller, currentCustomer, generation = 0, busy = false;
    const escape = value => { const node = document.createElement('span'); node.textContent = value ?? ''; return node.innerHTML; };
    const endpoint = (url, id) => url.replace(':id', id).replace('%3Aid', id);
    const errorText = error => Object.values(error.errors || {}).flat().join('\n') || error.message || 'Permintaan gagal. Silakan coba lagi.';
    async function json(url, options = {}) {
        const response = await fetch(url, {...options, headers: {Accept: 'application/json', 'X-CSRF-TOKEN': csrf, ...options.headers}});
        const data = await response.json();
        if (!response.ok || data.status === 'error') throw data;
        return data;
    }
    function lock(value) {
        busy = value;
        modal.find('button, input, select').prop('disabled', value);
        // Hidden identity and CSRF fields remain available for FormData.
        form.querySelectorAll('input[type="hidden"]').forEach(input => { input.disabled = false; });
        document.getElementById('dokumen_konsumen_nama').disabled = true;
    }
    function ensureTable() {
        if (table) return;
        body.replaceChildren();
        table = $('#dokumenTable').DataTable({
            data: [], pageLength: 5, lengthChange: false,
            columns: [
                {data: null, orderable: false, searchable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1},
                {data: 'nama', render: value => escape(value)},
                {data: 'barang_unit.nama', defaultContent: '-', render: value => escape(value || '-')},
                {data: 'file_url', orderable: false, searchable: false, render: value => {
                    const link = document.createElement('a'); link.href = value; link.target = '_blank'; link.rel = 'noopener'; link.textContent = 'Lihat File'; return link.outerHTML;
                }},
                {data: 'id', orderable: false, searchable: false, render: value => '<button type="button" class="btn btn-outline-danger btn-sm customer-delete-document" data-document-id="' + Number(value) + '"><i class="fa fa-trash" aria-hidden="true"></i> Hapus</button>'}
            ], language: {emptyTable: 'Tidak ada dokumen.', zeroRecords: 'Tidak ada dokumen yang sesuai.', search: 'Cari dokumen:'}
        });
    }
    async function loadDocuments(id, signal, version) {
        const data = await json(config.listUrl + '?' + new URLSearchParams({konsumen_id: id}), {signal});
        if (version !== generation || String(id) !== currentCustomer) return;
        table.clear().rows.add(data.data || []).draw();
    }
    async function openDocuments(id, name, code) {
        if (busy) return;
        currentCustomer = String(id); const version = ++generation;
        controller?.abort(); controller = new AbortController(); const signal = controller.signal;
        ensureTable(); table.clear().draw(); form.reset();
        form.elements.konsumen_id.value = currentCustomer;
        document.getElementById('dokumen_konsumen_nama').value = [code, name].filter(Boolean).join(' ');
        unit.replaceChildren(new Option('Pilih perusahaan (opsional)', ''));
        $(unit).select2({theme: 'bootstrap-5', width: '100%', dropdownParent: modal}).trigger('change.select2');
        lock(true);
        try {
            await Promise.all([
                loadDocuments(id, signal, version),
                json(config.unitsUrl, {signal}).then(data => {
                    if (version !== generation) return;
                    for (const item of data.data || []) unit.add(new Option(item.nama, item.id));
                    $(unit).trigger('change.select2');
                })
            ]);
        } catch (error) {
            if (error.name !== 'AbortError' && version === generation) Swal.fire('Dokumen belum tersedia', errorText(error), 'error');
        } finally { if (version === generation) lock(false); }
    }
    async function refreshAfterChange(id, message) {
        try {
            await loadDocuments(id, undefined, generation);
        } catch (error) {
            await Swal.fire('Perubahan berhasil disimpan', message + ' Daftar dokumen belum dapat dimuat ulang. Tutup dan buka kembali modal untuk memperbarui daftar.', 'warning');
            return;
        }
        await Swal.fire('Berhasil', message, 'success');
    }
    $(document).on('click', '[data-customer-document]', function () { openDocuments(this.dataset.customerDocument, this.dataset.customerName, this.dataset.customerCode); });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy || !form.reportValidity()) return;
        const id = currentCustomer, payload = new FormData(form);
        lock(true);
        try {
            const confirmation = await Swal.fire({title: 'Simpan dokumen konsumen?', text: payload.get('nama'), icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal', focusCancel: true});
            if (!confirmation.isConfirmed) return;
            await json(config.storeUrl, {method: 'POST', body: payload});
            $(document).trigger('customer-documents-changed');
            form.reset(); form.elements.konsumen_id.value = id; $(unit).val('').trigger('change.select2');
            await refreshAfterChange(id, 'Dokumen berhasil disimpan.');
        } catch (error) { Swal.fire('Dokumen belum tersimpan', errorText(error), 'error'); }
        finally { lock(false); }
    });
    $(document).on('click', '.customer-delete-document', async function () {
        if (busy) return;
        const id = currentCustomer, documentId = this.dataset.documentId;
        lock(true);
        try {
            const confirmation = await Swal.fire({title: 'Hapus dokumen?', text: 'Dokumen yang dihapus tidak dapat dikembalikan.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', focusCancel: true, confirmButtonColor: '#dc3545'});
            if (!confirmation.isConfirmed) return;
            await json(endpoint(config.deleteUrl, documentId), {method: 'DELETE'});
            $(document).trigger('customer-documents-changed');
            await refreshAfterChange(id, 'Dokumen berhasil dihapus.');
        } catch (error) { Swal.fire('Dokumen belum dihapus', errorText(error), 'error'); }
        finally { lock(false); }
    });
    modal.on('hide.bs.modal', event => { if (busy) { event.preventDefault(); return; } controller?.abort(); generation++; });
    modal.on('shown.bs.modal', () => { if (table) table.columns.adjust(); });
})();
