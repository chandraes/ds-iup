(() => {
    const config = window.customerInvoiceConfig;
    const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('#cicilForm input[name="_token"]').value;
    const endpoint = (url, id) => url.replace(':id', encodeURIComponent(id));
    const money = value => new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(Number(value || 0));
    const cell = value => '<div class="invoice-cell">' + (value === null || value === '' ? '—' : value) + '</div>';
    const numeric = value => cell(money(value));
    const components = row => '<div class="invoice-cell invoice-components">DPP: <strong>' + money(row.total) + '</strong><br>Diskon: <strong>' + money(row.diskon) + '</strong><br>PPN: <strong>' + money(row.ppn) + '</strong><br>Penyesuaian: <strong>' + money(row.add_fee) + '</strong></div>';
    const button = (label, action, id, style) => '<button type="button" class="btn btn-sm btn-' + style + '" data-invoice-action="' + action + '" data-id="' + Number(id) + '">' + label + '</button>';
    let rowHeight = 144;
    function layout(api) {
        api.columns.adjust();
        let next = rowHeight;
        $(api.table().body()).find('.invoice-cell').each(function () { next = Math.max(next, Math.ceil(this.getBoundingClientRect().height) + 18); });
        if (next > rowHeight) {
            rowHeight = next;
            document.querySelector('.invoice-table-ui').style.setProperty('--invoice-row-height', rowHeight + 'px');
        }
        api.scroller.measure(false);
    }
    $.fn.dataTable.ext.errMode = 'none';
    const table = $('#rekapTable').DataTable({
        processing: true, serverSide: true, deferRender: true, autoWidth: false,
        pageLength: 50, lengthChange: false, dom: 'frti', searchDelay: 450,
        scrollCollapse: true, scrollY: '60vh', scrollX: true,
        scroller: {displayBuffer: 3, serverWait: 350, rowHeight: 'auto'}, order: [[0, 'asc']],
        ajax: {
            url: config.data,
            data: data => Object.assign(data, config.filters),
            error: () => { $('#invoice-table-error').prop('hidden', false).show(); document.querySelector('#rekapTable_processing')?.setAttribute('style', 'display:none'); }
        },
        columns: [
            {data: 'tanggal_en', render: cell}, {data: 'sales', render: cell},
            {data: null, render: row => cell([row.kabupaten, row.kecamatan].filter(Boolean).join(', '))},
            {data: 'full_kode', render: cell},
            {data: null, render: row => cell([row.kode_toko, row.konsumen_nama].filter(Boolean).join(' '))},
            {data: 'plafon', className: 'invoice-numeric', render: numeric},
            {data: null, render: row => cell('<a href="' + endpoint(config.detail, row.id) + '">' + row.kode + '</a>')},
            {data: null, render: components},
            ...['grand_total', 'dp', 'dp_ppn'].map(data => ({data, className: 'invoice-numeric', render: numeric})),
            {data: null, className: 'invoice-numeric', render: row => cell(Number(row.cicilan_count) > 0 ? '<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#invoice-history-modal" data-id="' + Number(row.id) + '">' + money(row.cicilan) + '</button>' : '0')},
            {data: 'sisa_ppn', className: 'invoice-numeric', render: numeric, createdCell: (td, value, row) => td.classList.toggle('table-danger', !Number(row.ppn_dipungut))},
            {data: 'sisa_tagihan', className: 'invoice-numeric', render: numeric},
            {data: 'jatuh_tempo', render: cell},
            {data: null, orderable: false, searchable: false, render: row => '<div class="invoice-cell invoice-actions">' +
                (config.privileged ? '<a class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener" href="' + endpoint(config.print, row.id) + '"><i class="fa fa-file-text-o" aria-hidden="true"></i> Invoice</a>' : '') +
                button('<i class="fa fa-credit-card" aria-hidden="true"></i> Bayar', 'pay', row.id, 'success') +
                '<button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="' + (Number(row.kas_ppn) === 1 ? '#cicilanModal' : '#cicilanNonModal') + '" data-id="' + Number(row.id) + '"><i class="fa fa-money" aria-hidden="true"></i> Cicil</button>' +
                (config.privileged ? button('<i class="fa fa-ban" aria-hidden="true"></i> Void', 'void', row.id, 'outline-danger') : '') + '</div>'}
        ],
        columnDefs: [5,6,8,3,9,6,9,9,7,4,4,6,6,7,5,6].map((width, targets) => ({targets, width: width + '%'})),
        language: {search: 'Cari invoice:', searchPlaceholder: 'Nota, toko, kode, sales, daerah…', processing: 'Memuat invoice…', emptyTable: 'Tidak ada invoice tempo yang sesuai.', zeroRecords: 'Tidak ada invoice yang sesuai pencarian.', info: 'Menampilkan _START_–_END_ dari _TOTAL_ invoice', infoEmpty: 'Tidak ada invoice', infoFiltered: '(dari _MAX_ invoice)'},
        drawCallback: function () {
            const api = this.api(), result = api.ajax.json();
            if (result) {
                $('#invoice-table-error').prop('hidden', !result.error).toggle(!!result.error);
                if (result.totals) {
                    document.querySelectorAll('[data-invoice-total]').forEach(el => { el.textContent = money(result.totals[el.dataset.invoiceTotal]); });
                    document.getElementById('invoice-total-components').innerHTML = components(result.totals);
                }
            }
            layout(api);
        }
    });
    $('#rekapTable_filter input').attr('maxlength', 100);
    $('#rekapTable_wrapper .dataTables_scrollBody').attr({tabindex: 0, role: 'region', 'aria-label': 'Invoice tempo. Gulir untuk memuat data berikutnya.'});
    $('#invoice-table-retry').on('click', () => table.ajax.reload());
    let resizeFrame;
    window.addEventListener('resize', () => { cancelAnimationFrame(resizeFrame); resizeFrame = requestAnimationFrame(() => layout(table)); });
    document.fonts?.ready.then(() => layout(table));
    ['karyawan_id', 'kecamatan_id', 'kabupaten_id'].forEach(id => $('#' + id).select2({theme: 'bootstrap-5', width: '100%'}));

    async function json(url, signal) {
        const response = await fetch(url, {signal, headers: {'Accept': 'application/json'}});
        if (!response.ok) throw new Error(response.status === 404 ? 'Invoice sudah berubah atau tidak tersedia. Muat ulang tabel.' : 'Data belum dapat dimuat. Tutup modal dan coba kembali.');
        return response.json();
    }
    ['cicilanModal', 'cicilanNonModal'].forEach(modalId => {
        const modal = document.getElementById(modalId), isPpn = modalId === 'cicilanModal';
        const form = modal.querySelector('form'), suffix = isPpn ? '' : '_non';
        modal.classList.add('invoice-modern-modal');
        modal.querySelector('label[for="edit_konsumen_nama"]').textContent = 'Konsumen';
        modal.querySelector('.modal-title').textContent = 'Cicilan invoice';
        modal.querySelector('.modal-footer button[type="submit"]').className = 'btn btn-success';
        modal.querySelector('.modal-footer button[type="submit"]').innerHTML = '<i class="fa fa-check" aria-hidden="true"></i> Simpan cicilan';
        const amount = document.getElementById('edit_nominal' + suffix);
        // Existing Cleave instances and calculations remain supplied by the payment partials.
        const canonical = document.createElement('input');
        canonical.type = 'hidden'; canonical.name = 'nominal'; form.append(canonical); amount.removeAttribute('name'); amount.setAttribute('inputmode', 'numeric');
        const formatter = isPpn ? window.nominal : window.nominalNon;
        formatter.properties.numeralDecimalScale = 0;
        formatter.properties.numeralPositiveOnly = true;
        formatter.initNumeralFormatter();
        let request, ready = false;
        const status = document.createElement('p'); status.setAttribute('role', 'status'); status.className = 'text-secondary'; form.querySelector('.modal-body').prepend(status);
        const submit = form.querySelector('button[type="submit"]');
        const updateCanonical = () => { canonical.value = amount.value.replace(/\./g, ''); };
        amount.addEventListener('input', updateCanonical);
        form.addEventListener('submit', event => {
            updateCanonical();
            if (!ready || !canonical.value) { event.preventDefault(); event.stopImmediatePropagation(); }
        }, true);
        let confirming = false;
        $(form).off('submit').on('submit', async event => {
            event.preventDefault();
            updateCanonical();
            if (!ready || confirming || !/^[0-9]+$/.test(canonical.value) || Number(canonical.value) <= 0) return;
            const openedRequest = request, action = form.action, value = canonical.value;
            confirming = true;
            try {
                const result = await Swal.fire({titleText: 'Simpan cicilan?', text: document.getElementById('edit_nota' + suffix).value + ' · Rp ' + money(value), icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal', focusCancel: true});
                updateCanonical();
                if (!result.isConfirmed || !ready || request !== openedRequest || form.action !== action || canonical.value !== value) return;
                ready = false; submit.disabled = true;
                $('#spinner').show(); HTMLFormElement.prototype.submit.call(form);
            } finally { confirming = false; }
        });
        modal.addEventListener('show.bs.modal', async event => {
            request?.abort(); request = new AbortController(); const current = request;
            ready = false; submit.disabled = true; form.removeAttribute('action'); form.reset(); canonical.value = '';
            modal.querySelector('.modal-title').textContent = 'Cicilan invoice';
            status.className = 'text-secondary'; status.textContent = 'Memuat data invoice terbaru…';
            form.setAttribute('aria-busy', 'true');
            const id = event.relatedTarget?.dataset.id;
            if (!id) { status.textContent = 'Invoice tidak tersedia.'; return; }
            try {
                const data = await json(endpoint(config.paymentData, id), current.signal);
                if (current !== request || current.signal.aborted) return;
                if ((Number(data.kas_ppn) === 1) !== isPpn) throw new Error('Jenis kas invoice berubah. Tutup modal dan muat ulang tabel.');
                document.getElementById('edit_konsumen_nama' + suffix).value = [data.konsumen?.kode_toko?.kode, data.konsumen?.nama].filter(Boolean).join(' ');
                document.getElementById('edit_nota' + suffix).value = data.kode;
                modal.querySelector('.modal-title').textContent = 'Cicilan · ' + data.kode;
                document.getElementById('edit_sisa_tagihan' + suffix).value = money(data.sisa_tagihan);
                document.getElementById('edit_apa_ppn' + suffix).value = isPpn ? 1 : 0;
                if (isPpn) {
                    document.getElementById('edit_sisa_dpp').value = money(Number(data.sisa_tagihan) - Number(data.sisa_ppn));
                    document.getElementById('edit_sisa_ppn').value = money(data.sisa_ppn);
                    document.getElementById('edit_ppn_dipungut').value = data.ppn_dipungut;
                    document.getElementById('edit_ppn').value = 0;
                }
                (isPpn ? window.nominal : window.nominalNon)?.setRawValue('');
                form.action = endpoint(config.installment, data.id);
                ready = true; submit.disabled = false; status.textContent = '';
            } catch (error) {
                if (current !== request || current.signal.aborted) return;
                status.className = 'text-danger'; status.textContent = error.message;
            } finally { if (current === request && !current.signal.aborted) form.removeAttribute('aria-busy'); }
        });
        modal.addEventListener('hidden.bs.modal', () => { request?.abort(); ready = false; submit.disabled = true; form.removeAttribute('action'); });
    });

    const historyModal = document.getElementById('invoice-history-modal'), historyStatus = document.getElementById('invoice-history-status');
    const historyBody = document.getElementById('invoice-history-body'), more = document.getElementById('invoice-history-more');
    let historyRequest, historyId, historyStart = 0;
    async function loadHistory() {
        historyRequest?.abort(); historyRequest = new AbortController(); const current = historyRequest;
        historyStatus.textContent = 'Memuat histori cicilan…'; historyBody.setAttribute('aria-busy', 'true'); more.disabled = true;
        try {
            const data = await json(endpoint(config.history, historyId) + '?start=' + historyStart, current.signal);
            if (current !== historyRequest || current.signal.aborted) return;
            document.getElementById('invoice-history-title').textContent = 'Histori cicilan · ' + data.kode;
            historyModal.querySelectorAll('.invoice-history-ppn').forEach(el => { el.hidden = Number(data.kas_ppn) !== 1; });
            data.data.forEach(row => {
                const tr = document.createElement('tr');
                [row.tanggal, money(row.nominal), ...(Number(data.kas_ppn) === 1 ? [money(row.ppn)] : []), money(row.total)].forEach(value => { const td = document.createElement('td'); td.textContent = value; tr.append(td); });
                historyBody.append(tr);
            });
            historyStart = data.next_start; more.hidden = !data.more;
            historyStatus.textContent = historyBody.children.length ? '' : 'Belum ada cicilan.';
        } catch (error) { if (current === historyRequest && !current.signal.aborted) { historyStatus.textContent = error.message; more.hidden = false; more.textContent = 'Coba lagi'; } }
        finally { if (current === historyRequest && !current.signal.aborted) { more.disabled = false; historyBody.removeAttribute('aria-busy'); } }
    }
    historyModal.addEventListener('show.bs.modal', event => { historyRequest?.abort(); historyId = event.relatedTarget.dataset.id; historyStart = 0; historyBody.replaceChildren(); more.hidden = true; more.textContent = 'Muat berikutnya'; loadHistory(); });
    historyModal.addEventListener('hidden.bs.modal', () => historyRequest?.abort());
    more.addEventListener('click', loadHistory);

    const busyActions = new Set();
    $('#rekapTable').on('click', '[data-invoice-action]', async function () {
        const id = this.dataset.id, action = this.dataset.invoiceAction, key = id + ':' + action;
        if (busyActions.has(key)) return;
        if (action === 'void' && !config.privileged) return;
        busyActions.add(key); this.disabled = true;
        try {
            let settings;
            if (action === 'pay') {
                const current = await json(endpoint(config.paymentData, id));
                settings = {titleText: 'Lunasi invoice ' + current.kode + '?', text: 'Sisa tagihan: Rp ' + money(current.sisa_tagihan), icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, bayar', cancelButtonText: 'Batal', focusCancel: true};
            } else {
                settings = {title: 'Void invoice?', text: 'Masukkan password konfirmasi.', input: 'password', showCancelButton: true, confirmButtonText: 'Ya, void', cancelButtonText: 'Batal', showLoaderOnConfirm: true,
                    preConfirm: async password => {
                        try {
                            const response = await fetch(config.passwordCheck, {method: 'POST', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token}, body: JSON.stringify({password})});
                            const result = await response.json();
                            if (!response.ok || result.status !== 'success') throw new Error(result.message || 'Password tidak sesuai.');
                            return true;
                        } catch (error) { Swal.showValidationMessage(error.message); return false; }
                    }, allowOutsideClick: () => !Swal.isLoading()};
            }
            const confirmation = await Swal.fire(settings);
            if (!confirmation.isConfirmed) return;
            const form = document.createElement('form'); form.method = 'post'; form.action = endpoint(action === 'pay' ? config.pay : config.void, id);
            const input = document.createElement('input'); input.type = 'hidden'; input.name = '_token'; input.value = token; form.append(input); document.body.append(form);
            $('#spinner').show(); HTMLFormElement.prototype.submit.call(form);
        } catch (error) { await Swal.fire({icon: 'error', title: 'Tidak dapat melanjutkan', text: error.message}); }
        finally { busyActions.delete(key); this.disabled = false; }
    });
})();
