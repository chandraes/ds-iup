@extends('layouts.app')
@section('content')
<div class="container-fluid py-3" style="max-width:1500px">
    <header class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-success text-uppercase fw-bold mb-1">Rekap · {{ $cashLabel }}</div>
            <h1 class="h2 fw-bold mb-1">Rekap Lain-lain</h1>
            <p class="text-muted mb-0">Rincian penerimaan dan pengeluaran dari Form Lain-lain.</p>
        </div>
        <div class="d-flex gap-2"><a href="{{ route('rekap.lain-lain') }}" class="btn btn-outline-success">Ganti tipe
                kas</a><a href="{{ route('rekap') }}" class="btn btn-outline-secondary">Kembali ke rekap</a></div>
    </header>
    @if($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('rekap.lain-lain') }}" method="get"><input type="hidden" name="ppn_kas"
                    value="{{ $cashType }}">
                <div class="row g-3 align-items-end">
                    <div class="col-sm-6 col-lg-2"><label for="report-month" class="form-label fw-bold small">Bulan
                            transaksi</label><select id="report-month" name="bulan"
                            class="form-select">@foreach([1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember']
                            as $value=>$label)<option value="{{ $value }}" @selected($month===$value)>{{ $label }}
                            </option>@endforeach</select></div>
                    <div class="col-sm-6 col-lg-2"><label for="report-year"
                            class="form-label fw-bold small">Tahun</label><select id="report-year" name="tahun"
                            class="form-select">@foreach($years as $value)<option value="{{ $value }}"
                                @selected($year===(int)$value)>{{ $value }}</option>@endforeach</select></div>

                    <div class="col-lg-4 d-flex gap-2"><button class="btn btn-success px-4">Tampilkan</button><a
                            href="{{ route('rekap.lain-lain', ['ppn_kas'=>$cashType]) }}"
                            class="btn btn-outline-secondary">Bulan berjalan</a></div>
                </div>
            </form>
        </div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="small text-muted mb-2">Jumlah transaksi</div>
                    <div id="summary-count" class="fs-3 fw-bold">{{ number_format($totals->count,0,',','.') }}</div>
                    <div class="small text-muted mt-2">{{ $periodLabel }}</div>
                </div>
            </div>
        </div>
        @foreach(['masuk'=>'Total masuk','keluar'=>'Total keluar'] as $key=>$label)
        <div class="col-md-4">
            <div class="card border-0 rounded-4 h-100 bg-{{ $key === 'masuk' ? 'success' : 'danger' }} bg-opacity-10">
                <div class="card-body p-4">
                    <div class="small mb-2">{{ $label }}</div>
                    <div id="summary-{{ $key }}"
                        class="fs-3 fw-bold text-{{ $key === 'masuk' ? 'success' : 'danger' }} report-total">Rp {{
                        number_format($totals->$key,0,',','.') }}</div>
                    <div class="small text-muted mt-2">Seluruh hasil filter dan pencarian</div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <section class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 class="h5 fw-bold mb-0">Rincian Transaksi</h2><span
                    class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">{{ $cashLabel }} · {{
                    $periodLabel }}</span>
            </div>
            <div class="table-responsive">
                <table id="lain-report" class="table table-hover align-middle report-lain" style="width:100%">
                    <thead class="table-success">
                        <tr>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Uraian</th>
                            <th scope="col" class="text-end">Masuk</th>
                            <th scope="col" class="text-end">Keluar</th>
                            <th scope="col">Transfer ke Rekening</th>
                            <th scope="col">Bank</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <th colspan="2" scope="row">Grand Total</th>
                            <td data-total="masuk" class="text-end text-success text-nowrap">{{
                                number_format($totals->masuk,0,',','.') }}</td>
                            <td data-total="keluar" class="text-end text-danger text-nowrap">{{
                                number_format($totals->keluar,0,',','.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="small text-muted mt-3">Gulir tabel untuk memuat transaksi berikutnya. Nominal dalam rupiah; grand
                total mencakup seluruh hasil filter dan pencarian, termasuk transaksi yang belum tampil.</p>
            <p id="report-error" class="text-danger small" role="alert" hidden>Data belum dapat dimuat. Periksa koneksi
                dan coba kembali.</p><button type="button" id="report-retry" class="btn btn-outline-success" hidden>Muat
                ulang data</button>
        </div>
    </section>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('assets/css/dt.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatable/scroller.bootstrap5.min.css') }}">
<style>
    .report-lain {
        font-size: .9rem
    }

    .report-lain td,
    .report-lain th {
        padding: 1rem .75rem;
        font-variant-numeric: tabular-nums;
        white-space: nowrap
    }

    .report-lain tbody tr {
        height: 76px
    }

    .report-description {
        min-width: 200px;
        max-width: 480px;
        overflow: hidden;
        text-overflow: ellipsis
    }

    .report-total {
        overflow-wrap: anywhere
    }

    .dataTables_filter {
        margin-bottom: 1rem
    }

    .dataTables_filter input {
        max-width: 100%
    }

    .dataTables_info {
        font-size: .85rem;
        color: #586a78
    }

    .dataTables_scrollBody:focus-visible {
        outline: 3px solid #347cba;
        outline-offset: 3px
    }
</style>
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script>
    $(function () {
    const text = $.fn.dataTable.render.text();
    const money = value => new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(value);
    const amount = (value, type) => type === 'display' ? (Number(value) === 0 ? '—' : money(value)) : value;
    const table = $('#lain-report').DataTable({
        processing: true, serverSide: true, deferRender: true, ordering: false,
        searchDelay: 450, pageLength: 25, lengthChange: false, dom: 'frti', scrollX: true,
        scrollY: '460px', scrollCollapse: true, scroller: {displayBuffer: 3, serverWait: 350, rowHeight: 76},
        search: {search: @json($search)},
        initComplete: function () { this.api().columns.adjust(); },
        ajax: {
            url: @json(route('rekap.lain-lain')),
            data: data => { data.ppn_kas = @json($cashType); data.bulan = @json($month); data.tahun = @json($year); },
            error: () => { $('#report-error, #report-retry').prop('hidden', false); $('#lain-report_processing').hide(); }
        },
        columns: [
            {data: 'tanggal', render: text},
            {data: 'uraian', render: text, className: 'report-description', createdCell: (cell, value) => { cell.title = value || ''; }},
            {data: 'masuk', render: amount, className: 'text-end text-success text-nowrap'},
            {data: 'keluar', render: amount, className: 'text-end text-danger text-nowrap'},
            {data: 'nama_rek', render: (value, type, row) => type === 'display'
                ? text.display(value || '—') + '<div class="small text-muted">' + text.display(row.no_rek || '') + '</div>' : value},
            {data: 'bank', render: text, defaultContent: '—'}
        ],
        language: {search: 'Cari transaksi:', searchPlaceholder: 'Uraian, penerima, rekening, bank…', processing: 'Memuat transaksi…',
            emptyTable: 'Belum ada transaksi lain-lain pada periode ini.', zeroRecords: 'Tidak ada transaksi yang sesuai pencarian.',
            info: 'Menampilkan _START_–_END_ dari _TOTAL_ transaksi', infoEmpty: 'Tidak ada transaksi yang ditampilkan',
            infoFiltered: '(dari _MAX_ transaksi)', paginate: {previous: 'Sebelumnya', next: 'Berikutnya'}},
        drawCallback: function () {
            const totals = this.api().ajax.json()?.totals;
            if (!totals) return;
            $('#summary-count').text(money(totals.count));
            ['masuk', 'keluar'].forEach(key => {
                $('#summary-' + key).text('Rp ' + money(totals[key]));
                $('#lain-report_wrapper [data-total="' + key + '"]').text(money(totals[key]));
            });
            $('#report-error, #report-retry').prop('hidden', true);
        }
    });
    $('#lain-report_filter input').attr('maxlength', 100);
    $('#lain-report_wrapper .dataTables_scrollBody').attr({tabindex: 0, role: 'region', 'aria-label': 'Rincian transaksi lain-lain. Gulir untuk memuat transaksi berikutnya.'});
    $('#report-retry').on('click', () => table.ajax.reload());
});
</script>
@endpush
