(function () {
    const tableElement = document.getElementById('rekapTable');
    if (tableElement && window.jQuery && jQuery.fn.DataTable) {
        jQuery(tableElement).DataTable({
            paging: false, ordering: false, autoWidth: false, scrollCollapse: true, scrollY: '520px', scrollX: true,
            language: {search: 'Cari pegawai:', searchPlaceholder: 'Nama, NIK, atau jabatan…',
                emptyTable: 'Tidak ada pegawai dalam rekap ini.', zeroRecords: 'Tidak ada pegawai yang sesuai pencarian.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ pegawai', infoEmpty: 'Tidak ada pegawai yang ditampilkan', infoFiltered: '(dari _MAX_ pegawai)'},
            initComplete: function () { this.api().columns.adjust(); }
        });
        jQuery('#rekapTable_wrapper .dataTables_scrollBody').attr({tabindex: 0, role: 'region', 'aria-label': 'Rincian gaji pegawai. Gulir ke samping untuk melihat seluruh komponen.'});
    }
    const commissionModal = document.getElementById('payroll-commission');
    if (commissionModal) {
        const content = document.getElementById('payroll-commission-content');
        const errorMessage = document.getElementById('payroll-commission-error');
        const retry = document.getElementById('payroll-commission-retry');
        let url;
        let request;
        async function loadCommission() {
            request?.abort();
            request = new AbortController();
            const current = request;
            content.textContent = 'Memuat rincian komisi…';
            content.setAttribute('aria-busy', 'true');
            errorMessage.hidden = true;
            retry.hidden = true;
            try {
                const response = await fetch(url, {signal: current.signal, headers: {'Accept': 'application/json'}});
                if (!response.ok) {
                    const failure = await response.json().catch(() => ({}));
                    throw new Error(failure.errors ? Object.values(failure.errors).flat().join(' ') : 'Rincian belum dapat dimuat. Periksa koneksi dan coba kembali.');
                }
                const data = await response.json();
                if (current.signal.aborted) return;
                document.getElementById('payroll-commission-title').textContent = data.title;
                content.innerHTML = data.html;
            } catch (error) {
                if (current.signal.aborted) return;
                content.textContent = '';
                errorMessage.textContent = error.message;
                errorMessage.hidden = false;
                retry.hidden = false;
            } finally {
                if (!current.signal.aborted) content.removeAttribute('aria-busy');
            }
        }
        commissionModal.addEventListener('show.bs.modal', function (event) {
            url = event.relatedTarget.dataset.url;
            document.getElementById('payroll-commission-title').textContent = 'Rincian komisi';
            loadCommission();
        });
        commissionModal.addEventListener('hide.bs.modal', () => request?.abort());
        retry.addEventListener('click', loadCommission);
    }
    const form = document.getElementById('lanjutForm');
    if (!form) return;
    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (button.disabled) return;
        button.disabled = true;
        try {
            const result = await Swal.fire({
                title: 'Proses gaji ' + form.dataset.period + '?',
                text: 'Total gaji dibayar Rp ' + Number(form.dataset.total).toLocaleString('id-ID') + '. Dana dicatat keluar dari Kas Besar PPN dan potongan kasbon diperbarui.',
                icon: 'question', showCancelButton: true, confirmButtonColor: '#17694f', cancelButtonColor: '#586a78',
                confirmButtonText: 'Ya, proses gaji', cancelButtonText: 'Periksa kembali'
            });
            if (result.isConfirmed) {
                button.textContent = 'Memproses…';
                jQuery('#spinner').show();
                HTMLFormElement.prototype.submit.call(form);
            } else {
                button.disabled = false;
            }
        } catch (error) {
            button.disabled = false;
            throw error;
        }
    });
})();
