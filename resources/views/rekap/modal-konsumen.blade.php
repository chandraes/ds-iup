<div class="modal fade" id="kasKonsumenModal" tabindex="-1" aria-labelledby="konsumenTitle" aria-describedby="konsumenDescription" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content customer-report-modal">
            <div class="modal-header align-items-start">
                <div class="d-flex gap-3">
                    <span class="customer-report-icon"><i class="fa fa-users" aria-hidden="true"></i></span>
                    <div><p class="customer-report-eyebrow">REKAP KEUANGAN</p><h5 class="modal-title" id="konsumenTitle">Kas konsumen</h5></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('rekap.kas-konsumen') }}" method="get">
                <div class="modal-body">
                    <p id="konsumenDescription" class="text-secondary mb-4">Pilih konsumen untuk melihat riwayat transaksi dan saldo kasnya.</p>
                    <label for="konsumen_id" class="form-label fw-semibold">Konsumen <span class="text-danger" aria-hidden="true">*</span></label>
                    <select class="form-select" name="konsumen_id" id="konsumen_id" aria-describedby="konsumenHelp" required>
                        <option value=""></option>
                    </select>
                    <p id="konsumenHelp" class="form-text mt-2 mb-0">Pilih dari daftar atau cari berdasarkan kode toko dan nama konsumen. Gulir untuk melihat pilihan lainnya.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="customer-report-submit" disabled>Lihat rekap <i class="fa fa-arrow-right ms-2" aria-hidden="true"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<style>
#kasKonsumenModal .customer-report-modal { border: 0; border-radius: 18px; box-shadow: 0 24px 64px rgba(15, 23, 42, .18); }
#kasKonsumenModal .modal-header, #kasKonsumenModal .modal-body, #kasKonsumenModal .modal-footer { padding: 24px; }
#kasKonsumenModal .modal-header { border-bottom: 1px solid #e2e8f0; }
#kasKonsumenModal .modal-footer { background: #f8fafc; border-top: 1px solid #e2e8f0; border-radius: 0 0 18px 18px; gap: 8px; }
#kasKonsumenModal .modal-title { font-size: 1.35rem; font-weight: 700; color: #0f172a; }
#kasKonsumenModal .customer-report-eyebrow { color: #475569; font-size: .75rem; font-weight: 600; letter-spacing: .1em; margin: 0 0 4px; }
#kasKonsumenModal .customer-report-icon { display: grid; place-items: center; width: 48px; height: 48px; border-radius: 14px; color: #166534; background: #dcfce7; flex-shrink: 0; }
#kasKonsumenModal .btn { min-height: 44px; padding-inline: 18px; border-radius: 9px; }
#kasKonsumenModal .btn-close { padding: 12px; }
#kasKonsumenModal .select2-selection { min-height: 48px; border-radius: 9px; }
#kasKonsumenModal .select2-results__option { padding: 10px 14px; }
#kasKonsumenModal .select2-results__options { max-height: 220px; }
#kasKonsumenModal .select2-container--focus .select2-selection { border-color: #15803d; box-shadow: 0 0 0 3px rgba(21, 128, 61, .18); }
</style>
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script>
    (function () {
        const modal = $('#kasKonsumenModal');
        const select = $('#konsumen_id');
        modal.on('shown.bs.modal', function () {
            if (!select.data('select2')) {
                select.select2({
                    theme: 'bootstrap-5', width: '100%', placeholder: 'Pilih konsumen',
                    minimumInputLength: 0, dropdownParent: modal,
                    ajax: {
                        url: @json(route('rekap.konsumen-options')),
                        dataType: 'json', delay: 300,
                        data: function (params) { return {search: params.term || '', page: params.page || 1}; },
                        processResults: function (data) { return data; }
                    },
                    language: {
                        searching: function () { return 'Memuat konsumen…'; },
                        noResults: function () { return 'Konsumen tidak ditemukan.'; },
                        loadingMore: function () { return 'Memuat pilihan lainnya…'; },
                        errorLoading: function () { return 'Gagal memuat. Tutup dan buka kembali pilihan untuk mencoba lagi.'; }
                    }
                });
            }
            select.select2('open');
        });
        modal.on('hidden.bs.modal', function () {
            if (select.data('select2')) select.select2('close');
        });
        select.on('change', function () { $('#customer-report-submit').prop('disabled', !select.val()); });
        select.on('select2:open', function () {
            const search = modal.find('.select2-search__field')[0];
            if (search) search.focus();
        });
    })();
</script>
@endpush
