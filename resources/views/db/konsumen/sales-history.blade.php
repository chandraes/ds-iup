<div class="modal fade customer-modal" id="salesHistoryModal" tabindex="-1" aria-labelledby="salesHistoryTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div><p class="customer-eyebrow">PENANGGUNG JAWAB TOKO</p><h2 class="modal-title" id="salesHistoryTitle">Histori pergantian sales</h2><p id="salesHistoryCustomer" class="text-secondary mb-0 mt-2"></p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body">
            <form id="salesChangeForm" class="border rounded-3 p-3 mb-4" method="post">
                @csrf
                @method('patch')
                <input type="hidden" name="_original_sales_id" value="">
                <h3 class="h6 fw-bold mb-2">Ganti sales penanggung jawab</h3>
                <p class="small text-secondary">Sales saat ini: <strong id="salesCurrentName"></strong></p>
                <div class="row g-3">
                    <div class="col-md-5"><label for="salesChangeEmployee" class="form-label">Sales baru *</label><select id="salesChangeEmployee" name="karyawan_id" class="form-select" required><option value="">Pilih sales baru</option>@foreach($sales_area as $sales)<option value="{{ $sales->id }}">{{ $sales->nama }}</option>@endforeach</select></div>
                    <div class="col-md-7"><label for="salesChangeReason" class="form-label">Alasan <span class="fw-normal text-secondary">(opsional)</span></label><input id="salesChangeReason" name="sales_change_reason" class="form-control" maxlength="500" placeholder="Alasan pergantian sales"></div>
                </div>
                <div class="mt-3"><button id="salesChangeSave" type="submit" class="btn btn-success" disabled><i class="fa fa-check me-2" aria-hidden="true"></i>Simpan pergantian sales</button></div>
            </form>
            <h3 class="h6 fw-bold">Riwayat perubahan</h3><p class="text-secondary small">Riwayat dimulai dari pergantian sales setelah fitur histori diterapkan.</p><div class="table-responsive">
            <table id="salesHistoryTable" class="table table-bordered table-hover w-100"><thead class="table-success"><tr><th>Waktu</th><th>Sales sebelumnya</th><th>Sales baru</th><th>Diubah oleh</th><th>Alasan</th></tr></thead><tbody></tbody></table>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-light border" data-bs-dismiss="modal">Tutup</button></div>
    </div></div>
</div>
