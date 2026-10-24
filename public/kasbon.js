(function () {
    const errors = document.getElementById('loan-errors');
    if (errors) errors.focus();
    const summary = document.getElementById('loan-summary');
    if (!summary) return;
    const amount = document.getElementById('loan-amount');
    if (amount && window.Cleave) {
        new Cleave(amount, {numeral: true, numeralThousandsGroupStyle: 'thousand', numeralDecimalScale: 0, numeralPositiveOnly: true, numeralDecimalMark: ',', delimiter: '.'});
    }
    const employee = document.getElementById('loan-employee');
    if (employee && window.jQuery && jQuery.fn.select2) {
        const select = jQuery(employee);
        if (select.data('select2')) select.select2('destroy');
        select.select2({width: '100%', dropdownParent: select.parent(), placeholder: 'Cari dan pilih pegawai'});
    }
    const month = document.getElementById('loan-month');
    const year = document.getElementById('loan-year');
    const money = value => 'Rp ' + Number(value).toLocaleString('id-ID');
    const count = document.getElementById('loan-count');
    const simulation = document.getElementById('loan-simulation-values');
    let installmentLimit = null;
    let installmentUnlimited = false;
    function simulate() {
        if (!simulation) return;
        const principal = Number(amount.value.replace(/\./g, ''));
        const months = Number(count.value);
        const help = document.getElementById('loan-simulation-help');
        const valid = /^\d{1,12}$/.test(amount.value.replace(/\./g, '')) && Number.isSafeInteger(principal) && principal > 0 && Number.isSafeInteger(months) && months > 0 && months <= principal;
        simulation.hidden = !valid;
        help.hidden = valid;
        help.textContent = months > principal && principal > 0 ? 'Jumlah cicilan tidak boleh melebihi nominal pinjaman dalam rupiah.' : 'Isi nominal pinjaman dan jumlah cicilan yang valid untuk melihat angsuran per bulan.';
        if (!valid) return;
        const monthly = Math.floor(principal / months);
        const last = principal - monthly * (months - 1);
        document.getElementById('loan-monthly-payment').textContent = money(monthly);
        document.getElementById('loan-payment-schedule').textContent = last === monthly
            ? `${months} kali angsuran × ${money(monthly)}. Total ${money(principal)}.`
            : `${months - 1} kali × ${money(monthly)}, lalu angsuran terakhir ${money(last)}. Total ${money(principal)}.`;
        const comparison = document.getElementById('loan-limit-comparison');
        comparison.className = '';
        if (installmentUnlimited) {
            comparison.textContent = 'Cicilan Sales tidak dibatasi berdasarkan penghasilan. Hanya satu cicilan aktif per pegawai.';
            return;
        }
        if (installmentLimit === null) {
            comparison.textContent = employee.value ? 'Perbandingan batas menunggu ringkasan pegawai tersedia.' : 'Pilih pegawai untuk membandingkan dengan batas angsuran bulanan.';
            return;
        }
        const exceeds = Math.max(monthly, last) > installmentLimit;
        comparison.className = exceeds ? 'is-over-limit' : 'is-within-limit';
        comparison.textContent = exceeds
            ? `Perhatian: ${monthly > installmentLimit ? 'angsuran bulanan' : 'angsuran terakhir'} melebihi batas ${money(installmentLimit)} per bulan. Kurangi nominal atau tambah jumlah cicilan.`
            : `Angsuran berada dalam batas ${money(installmentLimit)} per bulan.`;
    }
    amount?.addEventListener('input', simulate);
    count?.addEventListener('input', simulate);
    let request;
    async function refresh() {
        if (request) request.abort();
        request = new AbortController();
        const currentRequest = request;
        installmentLimit = null;
        installmentUnlimited = false;
        simulate();
        if (!employee.value) { summary.textContent = 'Pilih pegawai untuk melihat batas kasbon.'; return; }
        summary.textContent = 'Memuat batas kasbon…';
        const url = new URL(summary.dataset.url);
        url.search = new URLSearchParams({karyawan_id: employee.value, bulan: month?.value || summary.dataset.month, tahun: year?.value || summary.dataset.year});
        try {
            const response = await fetch(url, {signal: request.signal, headers: {'Accept': 'application/json'}});
            const data = await response.json();
            if (currentRequest.signal.aborted) return;
            if (!response.ok) throw new Error(data.message || 'Batas kasbon belum tersedia.');
            installmentUnlimited = data.is_sales === true && data.installment_limit === null;
            installmentLimit = data.installment_limit !== null && Number.isFinite(Number(data.installment_limit)) ? Number(data.installment_limit) : null;
            const rule = document.getElementById('loan-summary-rule');
            if (rule) rule.textContent = summary.dataset.type === 'cicilan'
                ? (data.is_sales ? 'Cicilan Sales tanpa batas nominal berbasis penghasilan. Satu cicilan aktif per pegawai.' : 'Batas angsuran: 30% × (penghasilan − kasbon potong gaji belum lunas). Satu cicilan aktif per pegawai.')
                : (data.is_sales ? 'Batas: (50% × komisi sampai tanggal proses) − angsuran periode ini − kasbon potong gaji belum lunas. ' : 'Batas: 50% × (penghasilan − angsuran berjalan), dikurangi kasbon potong gaji belum lunas. ') + 'Maksimal satu kali per pegawai per periode. ' + (data.ordinary_submitted ? 'Pegawai sudah mengajukan pada periode ini.' : 'Belum ada pengajuan pada periode ini.');
            simulate();
            summary.replaceChildren();
            const rows = [[data.is_sales ? 'Komisi sampai ' + data.commission_as_of : 'Penghasilan acuan', data.income], ['Kasbon potong gaji belum lunas', data.ordinary_due], ['Angsuran berjalan', data.installment_due]];
            rows.push(summary.dataset.type === 'cicilan' ? ['Batas angsuran bulanan', installmentUnlimited ? 'Tanpa batas berbasis penghasilan' : data.installment_limit] : ['Sisa batas pengajuan baru', data.ordinary_available]);
            for (const [label, value] of rows) {
                const block = document.createElement('div'); block.className = 'loan-summary-row';
                const title = document.createElement('span'); title.className = 'loan-summary-label'; title.textContent = label;
                const amount = document.createElement('strong'); amount.textContent = typeof value === 'string' ? value : money(value);
                block.append(title, amount); summary.append(block);
            }
        } catch (error) { if (error.name !== 'AbortError') summary.textContent = error.message; }
    }
    if (window.jQuery) jQuery(employee).on('change', refresh);
    else employee.addEventListener('change', refresh);
    month?.addEventListener('change', refresh); year?.addEventListener('change', refresh);
    refresh();
})();
