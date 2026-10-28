window.StockTableUI = (() => {
    const money = value => new Intl.NumberFormat('id-ID', {maximumFractionDigits: 0}).format(Number(value));
    const text = value => value === null || value === '' ? '—' : value;
    const wrap = value => '<div class="stock-wrap">' + text(value) + '</div>';
    const numeric = (value, type) => type === 'display' ? money(value) : value;
    const mark = value => value ? '<i class="fa fa-check" aria-label="Ya"></i>' : '<span aria-label="Tidak">—</span>';
    function create(selector, options) {
        $.fn.dataTable.ext.errMode = 'none';
        let rowHeight = 44;
        function syncLayout(api) {
            api.columns.adjust();
            let requiredHeight = rowHeight;
            $(api.table().body()).find('.stock-wrap').each(function () {
                requiredHeight = Math.max(requiredHeight, Math.ceil(this.getBoundingClientRect().height) + 18);
            });
            if (requiredHeight > rowHeight) {
                rowHeight = requiredHeight;
                api.table().node().style.setProperty('--stock-row-height', rowHeight + 'px');
            }
            api.scroller.measure(false);
        }
        const table = $(selector).DataTable({
            processing: true, serverSide: true, deferRender: true, ordering: false, autoWidth: false,
            searchDelay: 450, pageLength: 50, lengthChange: false, dom: 'frti', scrollX: true,
            scrollY: '60vh', scrollCollapse: true, scroller: {displayBuffer: 3, serverWait: 350, rowHeight: 'auto'},
            columnDefs: options.widths.map((width, targets) => ({targets, width: width + '%'})),
            ajax: options.ajax, columns: options.columns,
            language: {search: 'Cari barang:', searchPlaceholder: 'Nama, kode, merk, perusahaan…', processing: 'Memuat stok…',
                emptyTable: 'Belum ada stok barang yang sesuai filter.', zeroRecords: 'Tidak ada barang yang sesuai pencarian.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ baris stok', infoEmpty: 'Tidak ada stok yang ditampilkan', infoFiltered: '(dari _MAX_ baris stok)'},
            initComplete: function () { this.api().columns.adjust(); },
            drawCallback: function () {
                const api = this.api();
                $('#stock-error').prop('hidden', !api.ajax.json()?.error);
                syncLayout(api);
                options.onDraw?.(api.ajax.json());
            }
        });
        let resizeFrame;
        $(window).on('resize.stockTable', () => {
            cancelAnimationFrame(resizeFrame);
            resizeFrame = requestAnimationFrame(() => syncLayout(table));
        });
        document.fonts?.ready.then(() => syncLayout(table));
        $(selector + '_filter input').attr('maxlength', 100);
        $(selector + '_wrapper .dataTables_scrollBody').attr({tabindex: 0, role: 'region', 'aria-label': 'Daftar stok barang. Gulir untuk memuat baris berikutnya.'});
        $('#stock-reload').on('click', () => { table.scroller.toPosition(0); table.ajax.reload(null, true); });
        return table;
    }
    return {money, text, wrap, numeric, mark, create};
})();
