$(function () {
    const config = window.adminStockConfig;
    const {money, wrap, numeric, mark} = StockTableUI;
    const url = (template, id) => template.replace(':id', encodeURIComponent(id));
    const button = (label, action, id) => '<button type="button" class="stock-action" data-action="' + action + '" data-id="' + Number(id) + '" data-bs-toggle="modal" data-bs-target="' + (action === 'history' ? '#stock-history' : '#stock-editor') + '">' + label + '</button>';
    $('#unit').select2({theme: 'bootstrap-5', width: '100%', placeholder: '-- Semua Perusahaan --'});
    $('#type, #kategori, #filter_barang_nama').select2({theme: 'bootstrap-5', width: '100%'});
    const highlightMissingMinimum = (cell, value, row) => { if (Number(row.stok) > 0 && (row.min_jual === null || Number(row.min_jual) === 0)) cell.classList.add('stock-danger'); };
    const table = StockTableUI.create('#admin-stock-table', {
        widths: [2, 9, 6, 6, 9, 5, 5, 3, 3, 4, 3, 3, 5, 5, 5, 4, 5, 6, 6, 3, 3],
        ajax: {url: config.data, data: data => Object.assign(data, config.filters),
            error: () => { $('#stock-error').prop('hidden', false); $('#admin-stock-table_processing').hide(); }},
        columns: [
            {data: null, render: (value, type, row, meta) => meta.settings._iDisplayStart + meta.row + 1, className: 'text-center'},
            ...['perusahaan','bidang','kelompok','nama'].map(data => ({data, render: wrap, className: 'stock-wrap-cell'})),
            ...['kode','merk'].map(data => ({data, className: 'stock-wrap-cell', render: (value, type, row) => wrap(config.assistant ? (value || '—') : button(value || '—', 'history', row.barang_id))})),
            {data: 'stok_awal', className: 'text-end', render: (value, type, row) => config.superuser ? button(money(value), 'correct', row.id) : money(value)},
            {data: 'stok', className: 'text-end', render: (value, type, row) => !config.assistant && Number(value) > 0 ? button(money(value), 'loss', row.id) : money(value)},
            {data: 'satuan', render: wrap, className: 'stock-wrap-cell'},
            {data: 'jenis', render: value => mark(Number(value) === 1), className: 'text-center'},
            {data: 'jenis', render: value => mark(Number(value) === 2), className: 'text-center'},
            {data: 'harga_beli', render: numeric, className: 'text-end'},
            {data: 'harga_beli_ppn', render: numeric, className: 'text-end'},
            {data: 'harga', className: 'text-end', createdCell: highlightMissingMinimum, render: (value, type, row) => {
                let display = config.assistant ? (Number(value) === 0 ? button(money(value), 'propose', row.id) : money(value)) : button(money(value), 'price', row.id);
                if (row.submission_id !== null) display += '<span class="stock-proposal">Ajuan: ' + (config.assistant ? money(row.harga_ajuan) : button(money(row.harga_ajuan), 'approve', row.id)) + '</span>';
                return '<div class="stock-wrap">' + display + '</div>';
            }},
            {data: 'min_jual', className: 'text-end', createdCell: highlightMissingMinimum, render: (value, type, row) => '<div class="stock-wrap">' + (value === null ? '—' : money(value)) + (row.submission_id === null ? '' : '<span class="stock-proposal">Ajuan: ' + money(row.min_jual_ajuan) + '</span>') + '</div>'},
            {data: 'harga_jual_ppn', render: numeric, className: 'text-end'},
            {data: 'total_beli', render: numeric, className: 'text-end'},
            {data: 'total_jual', render: numeric, className: 'text-end'},
            {data: 'margin', className: 'text-end', render: value => value === null ? '—' : new Intl.NumberFormat('id-ID', {minimumFractionDigits: 2, maximumFractionDigits: 2}).format(Number(value)) + '%',
                createdCell: (cell, value) => { if (value === null) cell.classList.add('stock-warning'); else if (Number(value) < 10.01) cell.classList.add('stock-danger'); }},
            {data: 'id', className: 'text-center', render: (value, type, row) => !config.assistant && Number(row.stok) === 0 ? button('<i class="fa fa-eye-slash" aria-hidden="true"></i><span class="visually-hidden">Sembunyikan stok</span>', 'hide', value) : '—'}
        ],
        onDraw: result => {
            if (!result?.totals) return;
            ['beli','jual','profit'].forEach(key => $('#total-' + key).text('Rp ' + money(result.totals[key])));
        }
    });
    let current = null, detailRequest = null, saving = false;
    const titles = {price: 'Ubah harga jual', propose: 'Ajukan harga jual', approve: 'Setujui ajuan harga', correct: 'Koreksi stok', hide: 'Sembunyikan stok', loss: 'Ganti rugi barang hilang'};
    const field = (name, label, value, currency = false, required = true) => {
        const row = $('<div class="mb-3">');
        row.append($('<label class="form-label">').attr('for', 'stock-field-' + name).text(label));
        const input = $('<input class="form-control">').attr({id: 'stock-field-' + name, required});
        if (currency) {
            input.attr({type: 'text', inputmode: 'numeric', autocomplete: 'off'});
            const hidden = $('<input type="hidden">').attr('name', name);
            row.append(input, hidden);
            const cleave = new Cleave(input[0], {numeral: true, numeralDecimalScale: 0, numeralPositiveOnly: true, delimiter: '.', numeralDecimalMark: ',',
                onValueChanged: event => hidden.val(event.target.rawValue)});
            cleave.setRawValue(required || value !== '' ? String(value ?? 0) : ''); hidden.val(cleave.getRawValue());
        } else {
            input.attr({name, type: 'number', inputmode: 'numeric', min: name === 'stok' || name === 'stok_awal' ? 0 : 1, max: 2147483647, step: 1}).val(value ?? 1);
            row.append(input);
        }
        $('#stock-editor-fields').append(row);
    };
    async function jsonFetch(target, options = {}) {
        const response = await fetch(target, {headers: {'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'), ...(options.headers || {})}, ...options});
        const body = await response.json();
        if (!response.ok) throw new Error(Object.values(body.errors || {}).flat().join('\n') || body.message || 'Permintaan gagal.');
        return body;
    }
    $('#stock-editor').on('show.bs.modal', async function (event) {
        if (event.relatedTarget?.dataset.action === 'correct' && !config.superuser) {
            event.preventDefault();
            return;
        }
        detailRequest?.abort(); detailRequest = new AbortController(); const signal = detailRequest.signal;
        current = null; saving = false;
        $('#stock-editor-error').prop('hidden', true); $('#stock-editor-fields').empty(); $('#stock-save').prop('disabled', true);
        const action = event.relatedTarget.dataset.action, id = Number(event.relatedTarget.dataset.id);
        $('#stock-editor-title').text(titles[action]); $('#stock-editor-product').text('Memuat data terbaru…');
        try {
            const data = await jsonFetch(url(config.detail, id), {signal});
            if (signal.aborted) return;
            const permission = {price: 'edit_price', propose: 'propose', approve: 'approve', correct: 'correct', hide: 'hide', loss: 'loss'}[action];
            if (!data.permissions[permission] || (action === 'approve' && data.harga_ajuan === null)) throw new Error('Aksi tidak tersedia untuk data terbaru. Muat ulang tabel.');
            $('#stock-editor-product').text([data.nama, data.kode, data.merk].join(' · '));
            if (action === 'price') { field('harga', 'Harga jual DPP (Rp)', data.harga, true); field('min_jual', 'Minimum kelipatan jual (' + data.satuan + ')', data.min_jual); }
            if (action === 'propose' || action === 'approve') { field('harga_ajuan', 'Harga ajuan DPP (Rp)', data.harga_ajuan ?? data.harga, true); field('min_jual_ajuan', 'Minimum kelipatan jual (' + data.satuan + ')', data.min_jual_ajuan ?? data.min_jual); }
            if (action === 'correct') { field('stok_awal', 'Stok awal', data.stok_awal); field('stok', 'Stok saat ini', data.stok); field('harga_beli', 'Harga beli DPP baru (opsional, Rp)', '', true, false); $('#stock-editor-fields').append($('<p class="small text-secondary">').text('Kosongkan harga beli untuk mempertahankan nilai yang tersimpan.')); }
            if (action === 'hide') $('#stock-editor-fields').append($('<p>').text('Sembunyikan batch stok nol ini? Minimal satu batch barang harus tetap terlihat.'));
            if (action === 'loss') {
                $('#stock-editor-fields').append($('<p class="text-secondary">').text('Stok tersedia: ' + money(data.stok) + ' ' + data.satuan + '. Harga ganti rugi: Rp ' + money(data.loss_unit_price) + ' per satuan. Menggunakan Kas Besar PPN.'));
                field('jumlah_hilang', 'Jumlah hilang', 1);
                const employees = await jsonFetch(config.employees, {signal});
                if (signal.aborted) return;
                const employeeSelect = $('<select name="karyawan_id" id="loss-employee" class="form-select mb-3" required>').append($('<option value="">').text('Pilih pegawai aktif'));
                employees.forEach(employee => employeeSelect.append($('<option>').val(employee.id).text(employee.nama)));
                $('#stock-editor-fields').append($('<label for="loss-employee" class="form-label">').text('Pegawai'), employeeSelect,
                    $('<label for="loss-action" class="form-label">').text('Penyelesaian'), $('<select id="loss-action" name="aksi" class="form-select"><option value="1">Bayar langsung</option><option value="2">Kasbon ganti rugi</option></select>'));
            }
            current = {id, action, revision: data.revision}; $('#stock-save').prop('disabled', false);
        } catch (error) { if (error.name !== 'AbortError') $('#stock-editor-error').text(error.message).prop('hidden', false); }
    }).on('hidden.bs.modal', () => { detailRequest?.abort(); current = null; });
    $('#stock-edit-form').on('submit', async function (event) {
        event.preventDefault(); if (!current || saving) return;
        const operation = current;
        saving = true; $('#stock-save').prop('disabled', true);
        try {
            const confirmation = await Swal.fire({title: titles[operation.action] + '?', text: 'Pastikan data yang dimasukkan sudah benar.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal'});
            if (!confirmation.isConfirmed || current !== operation) return;
            const payload = new FormData(this); payload.set('revision', operation.revision);
            const result = await jsonFetch(url(config.actions[operation.action], operation.id), {method: 'POST', body: payload});
            table.ajax.reload(null, true);
            if (current === operation) {
                $(this).closest('.modal').find('[data-bs-dismiss="modal"]').first()[0].click();
                Swal.fire({icon: 'success', title: result.message, timer: 1800, showConfirmButton: false});
            }
        } catch (error) { if (current === operation) $('#stock-editor-error').text(error.message).prop('hidden', false); }
        finally { if (current === operation) { saving = false; $('#stock-save').prop('disabled', false); } }
    });
    let historyRequest = null, historyProduct = null, historyStart = 0;
    async function loadHistory() {
        historyRequest?.abort(); historyRequest = new AbortController();
        const signal = historyRequest.signal;
        $('#stock-history-more').prop('disabled', true); $('#stock-history-error').prop('hidden', true);
        try {
            const result = await jsonFetch(url(config.history, historyProduct) + '?start=' + historyStart, {signal});
            if (signal.aborted) return;
            $('#stock-history-product').text([result.nama, result.kode, result.merk].join(' · ')); $('#stock-history-note').text(result.keterangan || '—');
            result.data.forEach((row, index) => {
                const tr = $('<tr>'); [historyStart + index + 1, row.tanggal || '—', money(row.stok_awal), result.satuan, money(row.harga_beli), money(row.harga)].forEach(value => tr.append($('<td>').text(value))); $('#stock-history-body').append(tr);
            });
            historyStart += result.data.length; $('#stock-history-more').prop('hidden', !result.more);
        } catch (error) { if (error.name !== 'AbortError') $('#stock-history-error').text(error.message).prop('hidden', false); }
        finally { $('#stock-history-more').prop('disabled', false); }
    }
    $('#stock-history').on('show.bs.modal', event => {
        historyProduct = Number(event.relatedTarget.dataset.id); historyStart = 0;
        $('#stock-history-body').empty(); $('#stock-history-product').text('Memuat histori…'); $('#stock-history-note').empty(); $('#stock-history-more').prop('hidden', true); loadHistory();
    }).on('hidden.bs.modal', () => historyRequest?.abort());
    $('#stock-history-more').on('click', loadHistory);
});
