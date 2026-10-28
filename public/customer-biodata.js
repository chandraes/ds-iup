(function () {
    'use strict';
    const config = JSON.parse(document.getElementById('customer-config').textContent);
    const endpoint = (url, id) => url.replace(':id', id).replace('%3Aid', id);
    const errorText = xhr => Object.values(xhr.responseJSON?.errors || {}).flat().join('\n') || xhr.responseJSON?.message || 'Permintaan gagal. Silakan coba lagi.';
    const moneyOptions = {numeral: true, numeralThousandsGroupStyle: 'thousand', numeralDecimalScale: 0, numeralPositiveOnly: true, numeralDecimalMark: ',', delimiter: '.'};
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content}});
    const table = $('#data').DataTable({
        processing: true, serverSide: true, scrollY: '65vh', scrollX: true, deferRender: true, scroller: {loadingIndicator: true}, searchDelay: 350,
        ajax: {url: config.dataUrl, data: d => {
            Object.assign(d, {kode_toko: $('#filterKodeToko').val(), area: $('#filterSalesArea').val(), kecamatan: $('#filterKecamatan').val(), status: $('#filterStatus').val(), kabupaten_kota: $('#filterKab').val(), status_wa: $('#filterStatusWa').val()});
        }},
        columns: [
            {data: 'full_kode', name: 'kode'}, {data: 'kode_toko', name: 'kode_toko.kode'}, {data: 'nama', name: 'nama'},
            {data: 'dokumen', orderable: false, searchable: false}, {data: 'cp', name: 'cp'}, {data: 'npwp', name: 'npwp'}, {data: 'nik', name: 'nik', defaultContent: '-'},
            {data: 'ktp', orderable: false, searchable: false}, {data: 'sales_area', name: 'karyawan.nama', defaultContent: '-'},
            {data: 'provinsi.nama_wilayah', name: 'provinsi.nama_wilayah', defaultContent: '-'}, {data: 'kabupaten_kota.nama_wilayah', name: 'kabupaten_kota.nama_wilayah', defaultContent: '-'},
            {data: 'kecamatan.nama_wilayah', name: 'kecamatan.nama_wilayah', defaultContent: '-'}, {data: 'alamat', name: 'alamat'},
            {data: 'pembayaran_raw', orderable: false, searchable: false}, {data: 'limit_plafon', name: 'plafon', searchable: false},
            {data: 'diskon', name: 'diskon_khusus', searchable: false}, {data: 'checklist_kunjungan', orderable: false, searchable: false},
            {data: 'notif', orderable: false, searchable: false}, {data: 'alasan', name: 'alasan', visible: false, defaultContent: '-'}, {data: 'aksi', orderable: false, searchable: false}
        ],
        language: {search: 'Cari konsumen:', searchPlaceholder: 'Nama, kode, atau kontak…', processing: 'Memuat konsumen…', zeroRecords: 'Tidak ada konsumen yang sesuai.', info: 'Menampilkan _START_–_END_ dari _TOTAL_ konsumen', infoEmpty: 'Belum ada konsumen'}
    });
    $(document).on('customer-documents-changed', () => table.ajax.reload(null, false));
    const filters = $('#filterKodeToko, #filterSalesArea, #filterKecamatan, #filterStatus, #filterKab, #filterStatusWa');
    filters.select2({theme: 'bootstrap-5', width: '100%'}).on('change', function () { table.column(18).visible($('#filterStatus').val() === '0'); table.draw(); });
    $('#resetFilter').on('click', () => { filters.val('').trigger('change.select2'); $('#filterStatus').val('1').trigger('change.select2'); $('#filterKecamatan').empty().append(new Option('Semua kecamatan', '')); table.column(18).visible(false); table.draw(); });
    const controllers = new Map();
    function clearOptions(element, label) { element.replaceChildren(new Option(label, '')); $(element).trigger('change.select2'); }
    async function loadOptions(element, url, params, selected, label) {
        controllers.get(element)?.abort();
        const controller = new AbortController(); controllers.set(element, controller);
        clearOptions(element, label); element.disabled = true;
        try {
            const response = await fetch(url + '?' + new URLSearchParams(params), {signal: controller.signal, headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Wilayah gagal dimuat.');
            if (controller.signal.aborted) return;
            for (const item of data.data) element.add(new Option(item.nama_wilayah, item.id));
            element.value = selected || ''; $(element).trigger('change.select2');
        } catch (error) { if (error.name !== 'AbortError') Swal.fire('Wilayah belum tersedia', error.message, 'error'); }
        finally { if (!controller.signal.aborted) element.disabled = false; }
    }
    function cancelOptions(element, label) { controllers.get(element)?.abort(); clearOptions(element, label); element.disabled = false; }
    window.filterKecamatan = function () {
        const select = document.getElementById('filterKecamatan'); const city = $('#filterKab').val();
        cancelOptions(select, 'Semua kecamatan');
        if (city) loadOptions(select, config.districtsUrl, {kabupaten_kota_id: city}, null, 'Semua kecamatan');
    };
    function confirmCustomerForm(form, title, description, prepare) {
        form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = form.querySelector('[type="submit"]');
            if (button.disabled || !form.reportValidity()) return;
            if (prepare) prepare();
            button.disabled = true;
            let submitted = false;
            try {
                const confirmation = await Swal.fire({title, text: description(form), icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal', focusCancel: true});
                if (!confirmation.isConfirmed) return;
                HTMLFormElement.prototype.submit.call(form);
                submitted = true;
            } finally {
                if (!submitted) button.disabled = false;
            }
        });
    }
    confirmCustomerForm(document.getElementById('diskonForm'), 'Simpan diskon khusus?', form => 'Diskon khusus akan diubah menjadi ' + form.elements.diskon_khusus.value + '%.');
    confirmCustomerForm(document.getElementById('uploadFotoForm'), 'Simpan foto KTP?', form => 'Foto KTP akan disimpan atau menggantikan foto sebelumnya. File: ' + (form.elements.upload_ktp.files[0]?.name || '-'));
    for (const formId of ['createForm', 'editForm']) {
        const form = document.getElementById(formId); const modal = form.closest('.modal');
        const province = form.elements.provinsi_id, city = form.elements.kabupaten_kota_id, district = form.elements.kecamatan_id;
        $(form).find('select').select2({theme: 'bootstrap-5', width: '100%', dropdownParent: $(modal)});
        let originalSales = '';
        const reasonBlock = form.querySelector('.customer-sales-reason');
        function salesState() {
            if (!reasonBlock) return;
            const changed = String(form.elements.karyawan_id.value) !== originalSales;
            reasonBlock.hidden = !changed;
            form.elements.sales_change_reason.disabled = !changed;
        }
        $(form.elements.karyawan_id).on('change', salesState);
        let cleave;
        if (form.querySelector('.customer-money')) cleave = new Cleave(form.querySelector('.customer-money'), moneyOptions);
        function paymentState() {
            const credit = form.elements.pembayaran.value === '2';
            form.querySelectorAll('.customer-credit-field').forEach(block => {
                block.hidden = !credit;
                block.querySelectorAll('input').forEach(input => { input.disabled = !credit; input.required = credit && input.type !== 'hidden'; });
            });
        }
        $(form.elements.pembayaran).on('change', paymentState); paymentState();
        $(province).on('change', async () => {
            cancelOptions(city, 'Pilih kabupaten / kota'); cancelOptions(district, 'Pilih kecamatan');
            if (province.value) await loadOptions(city, config.citiesUrl, {provinsi: province.value}, null, 'Pilih kabupaten / kota');
        });
        $(city).on('change', () => { cancelOptions(district, 'Pilih kecamatan'); if (city.value) loadOptions(district, config.districtsUrl, {kabupaten_kota_id: city.value}, null, 'Pilih kecamatan'); });
        confirmCustomerForm(form, formId === 'createForm' ? 'Simpan konsumen baru?' : 'Simpan perubahan biodata?', form => 'Data konsumen ' + form.elements.nama.value + ' akan disimpan.' + (formId === 'editForm' && String(form.elements.karyawan_id.value) !== String(form.elements._original_sales_id.value) ? ' Invoice yang belum lunas dan tidak void ikut dialihkan ke sales baru.' : ''), () => {
            if (cleave && !form.elements.plafon.disabled) form.elements.plafon.value = cleave.getRawValue();
        });
        form.populateCustomer = async data => {
            cancelOptions(city, 'Pilih kabupaten / kota'); cancelOptions(district, 'Pilih kecamatan');
            form.reset();
            originalSales = String(data._original_sales_id ?? data.karyawan_id ?? '');
            form.elements._original_sales_id && (form.elements._original_sales_id.value = originalSales);
            for (const [key, value] of Object.entries(data)) {
                const element = form.elements.namedItem(key);
                if (element && key !== '_token' && key !== '_method' && !['kabupaten_kota_id','kecamatan_id'].includes(key)) element.value = value ?? '';
            }
            if (data.karyawan_id && !Array.from(form.elements.karyawan_id.options).some(o => o.value === String(data.karyawan_id))) {
                form.elements.karyawan_id.add(new Option(data.karyawan?.nama || 'Sales sebelumnya', data.karyawan_id)); form.elements.karyawan_id.value = data.karyawan_id;
            }
            $(form).find('select').trigger('change.select2'); paymentState(); salesState();
            if (cleave) cleave.setRawValue(data.plafon || '');
            form.querySelector('[type="submit"]').disabled = false;
            if (data.provinsi_id) await loadOptions(city, config.citiesUrl, {provinsi: data.provinsi_id}, data.kabupaten_kota_id, 'Pilih kabupaten / kota');
            if (data.kabupaten_kota_id && city.value === String(data.kabupaten_kota_id)) await loadOptions(district, config.districtsUrl, {kabupaten_kota_id: data.kabupaten_kota_id}, data.kecamatan_id, 'Pilih kecamatan');
        };
    }
    $(document).on('click', '.customer-edit', function () {
        const data = JSON.parse(this.dataset.customer), form = document.getElementById('editForm');
        form.action = endpoint(config.updateUrl, data.id); form.populateCustomer({...data, _customer_id: data.id});
    });
    $(document).on('submit', '.customer-status-form', async function (event) {
        event.preventDefault();
        const form = this, active = form.dataset.active === '1', button = form.querySelector('[type="submit"]');
        if (button.disabled) return;
        button.disabled = true;
        let submitted = false;
        try {
            const result = await Swal.fire({title: active ? 'Nonaktifkan konsumen?' : 'Aktifkan kembali konsumen?', text: active ? 'Riwayat transaksi tetap tersimpan. Masukkan alasan penonaktifan.' : 'Konsumen kembali tersedia untuk transaksi.', input: active ? 'text' : undefined, inputValidator: active ? value => !value?.trim() ? 'Alasan wajib diisi.' : undefined : undefined, showCancelButton: true, confirmButtonText: active ? 'Nonaktifkan' : 'Aktifkan', cancelButtonText: 'Batal', focusCancel: true});
            if (!result.isConfirmed) return;
            if (active) {
                let input = form.querySelector('[name="alasan"]');
                if (!input) { input = document.createElement('input'); input.type = 'hidden'; input.name = 'alasan'; form.append(input); }
                input.value = result.value;
            }
            HTMLFormElement.prototype.submit.call(form);
            submitted = true;
        } finally { if (!submitted) button.disabled = false; }
    });
    window.uploadFoto = id => { document.getElementById('uploadFotoForm').action = endpoint(config.ktpUrl, id); };
    window.editDiskon = (id, value) => { document.getElementById('diskonForm').reset(); $('#edit_diskon').val(value); document.getElementById('diskonForm').action = '/db/konsumen/diskon-khusus/' + id; };
    async function toggle(url, id, element) {
        const desired = element.checked; element.disabled = true;
        const confirmation = await Swal.fire({title: 'Ubah status konsumen?', text: desired ? 'Aktifkan pengaturan ini?' : 'Nonaktifkan pengaturan ini?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, ubah', cancelButtonText: 'Batal'});
        if (!confirmation.isConfirmed) { element.checked = !desired; element.disabled = false; return; }
        try { await $.post(endpoint(url, id), {status: desired ? 1 : 0}); }
        catch (xhr) { element.checked = !desired; Swal.fire('Status gagal diperbarui', errorText(xhr), 'error'); }
        finally { element.disabled = false; }
    }
    window.toggleChecklist = (id, element) => toggle(config.checklistUrl, id, element);
    window.toggleNotif = (id, element) => toggle(config.notificationUrl, id, element);
    let salesHistory;
    let salesBusy = false;
    let salesCustomerId;
    let salesCurrentId;
    let salesCurrentName;
    const salesChangeForm = document.getElementById('salesChangeForm');
    $('#salesChangeEmployee').select2({theme: 'bootstrap-5', width: '100%', dropdownParent: $('#salesHistoryModal')});
    function updateSalesSaveState() {
        $('#salesChangeSave').prop('disabled', salesBusy || !salesChangeForm.elements.karyawan_id.value || salesChangeForm.elements.karyawan_id.value === salesCurrentId);
    }
    $('#salesChangeEmployee').on('change', updateSalesSaveState);
    $(salesChangeForm).on('submit', async function (event) {
        event.preventDefault();
        const button = $('#salesChangeSave');
        if (button.prop('disabled') || !this.reportValidity()) return;
        salesBusy = true;
        const newSalesId = this.elements.karyawan_id.value;
        const newSalesName = this.elements.karyawan_id.selectedOptions[0].textContent;
        const payload = $(this).serialize();
        button.prop('disabled', true);
        try {
            const confirmation = await Swal.fire({title: 'Ganti sales penanggung jawab?', text: $('#salesHistoryCustomer').text() + ': ' + salesCurrentName + ' → ' + newSalesName + '. Invoice yang belum lunas dan tidak void ikut dialihkan ke sales baru.', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, ganti sales', cancelButtonText: 'Batal', focusCancel: true});
            if (!confirmation.isConfirmed) return;
            const response = await $.ajax({url: endpoint(config.salesUpdateUrl, salesCustomerId), method: 'PATCH', data: payload});
            salesCurrentId = newSalesId; salesCurrentName = newSalesName;
            this.elements._original_sales_id.value = newSalesId;
            $('#salesCurrentName').text(newSalesName);
            this.elements.sales_change_reason.value = '';
            salesHistory.ajax.reload(null, false); table.ajax.reload(null, false);
            Swal.fire('Sales diperbarui', response.message, 'success');
        } catch (xhr) { Swal.fire('Sales belum tersimpan', errorText(xhr), 'error'); }
        finally { salesBusy = false; updateSalesSaveState(); }
    });
    $(document).on('click', '.customer-sales-history', function () {
        if (salesBusy) return;
        salesCustomerId = this.dataset.id;
        salesCurrentId = this.dataset.salesId || '';
        salesCurrentName = this.dataset.salesName || 'Belum ditentukan';
        salesChangeForm.reset();
        salesChangeForm.elements._original_sales_id.value = salesCurrentId;
        $('#salesCurrentName').text(salesCurrentName);
        $('#salesChangeEmployee').val(salesCurrentId).trigger('change');
        $('#salesHistoryCustomer').text(this.dataset.name);
        if (salesHistory) salesHistory.destroy();
        $('#salesHistoryTable tbody').empty();
        $('#salesHistoryModal').modal('show');
        salesHistory = $('#salesHistoryTable').DataTable({
            processing: true, serverSide: true, ajax: endpoint(config.salesHistoryUrl, this.dataset.id),
            order: [[0, 'desc']], pageLength: 10, lengthChange: false,
            columns: [
                {data: 'created_at', name: 'created_at'},
                {data: 'sales_lama', name: 'sales_lama', defaultContent: '-'},
                {data: 'sales_baru', name: 'sales_baru'},
                {data: 'updated_by_name', name: 'updated_by_name'},
                {data: 'keterangan', name: 'keterangan', defaultContent: '-'}
            ],
            language: {emptyTable: 'Belum ada pergantian sales yang tercatat.', zeroRecords: 'Tidak ada histori yang sesuai.', search: 'Cari histori:'}
        });
    });
    let history;
    let currentLimit = 0;
    const limit = new Cleave('#input_plafon_baru', moneyOptions);
    $(document).on('click', '.customer-plafon', function () {
        const id = this.dataset.id; $('#plafon_konsumen_id').val(id); $('#plafonKonsumenName').text(this.dataset.name); currentLimit = Number(this.dataset.limit); limit.setRawValue(currentLimit); $('#input_keterangan_plafon').val('');
        $('#modalPlafon').modal('show');
        if (history) history.destroy();
        history = $('#tableHistoriPlafon').DataTable({processing: true, serverSide: true, ajax: endpoint(config.historyUrl, id), order: [[0,'desc']], pageLength: 5, lengthChange: false,
            columns: [{data: 'created_at', name: 'created_at'}, {data: 'plafon_lama', name: 'nominal_lama'}, {data: 'plafon_baru', name: 'nominal_baru'}, {data: 'user', searchable: false, orderable: false}, {data: 'keterangan', name: 'keterangan', defaultContent: '-'}]});
    });
    $('#formUpdatePlafon').on('submit', async function (event) {
        event.preventDefault();
        const button = $('#btnSavePlafon');
        if (button.prop('disabled')) return;
        const newLimit = limit.getRawValue();
        $('#canonical_plafon_baru').val(newLimit);
        button.prop('disabled', true);
        try {
            const rupiah = value => 'Rp ' + Number(value).toLocaleString('id-ID');
            const confirmation = await Swal.fire({
                title: 'Simpan perubahan plafon?',
                text: $('#plafonKonsumenName').text() + ': ' + rupiah(currentLimit) + ' → ' + rupiah(newLimit),
                icon: 'question', showCancelButton: true,
                confirmButtonText: 'Ya, simpan', cancelButtonText: 'Batal', focusCancel: true
            });
            if (!confirmation.isConfirmed) return;
            const response = await $.post(endpoint(config.limitUrl, $('#plafon_konsumen_id').val()), $(this).serialize());
            currentLimit = Number(newLimit);
            Swal.fire('Plafon', response.message, 'success');
            history.ajax.reload(null, false); table.ajax.reload(null, false);
        }
        catch (xhr) { Swal.fire('Plafon belum tersimpan', errorText(xhr), 'error'); }
        finally { button.prop('disabled', false); }
    });
    if (config.hasErrors && config.old._customer_form) {
        const edit = config.old._customer_form === 'edit', form = document.getElementById(edit ? 'editForm' : 'createForm');
        if (edit) form.action = endpoint(config.updateUrl, config.old._customer_id);
        form.populateCustomer(config.old); $(form.closest('.modal')).modal('show');
    }
})();
