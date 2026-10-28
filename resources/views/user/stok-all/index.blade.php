@extends('layouts.app')

@section('content')
<div class="container-fluid stock-page">
    <header class="stock-header">
        <div><span class="stock-eyebrow">Inventori</span><h1>Stok Barang All</h1><p>Stok dan harga jual seluruh perusahaan. Gulir tabel untuk memuat baris berikutnya.</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('home') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Dashboard</a>
            <a class="btn btn-outline-success" href="{{ route('user.stok-all', array_merge($filters, ['mode' => 'classic'])) }}">Tampilan lama</a>
        </div>
    </header>
    <section class="stock-panel" aria-label="Filter stok barang">
        <form method="get" action="{{ route('user.stok-all') }}" id="stock-filters">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6"><label class="form-label" for="unit">Perusahaan</label><select class="form-select" name="unit[]" id="unit" multiple>
                    @foreach($units as $unit)<option value="{{ $unit->id }}" @selected(in_array($unit->id, $filters['unit'] ?? []))>{{ $unit->nama }}</option>@endforeach
                </select></div>
                <div class="col-lg-2 col-md-6"><label class="form-label" for="type">Bidang</label><select class="form-select" name="type" id="type"><option value="">Semua bidang</option>
                    @foreach($selectType as $item)<option value="{{ $item->id }}" @selected(($filters['type'] ?? null) == $item->id)>{{ $item->nama }}</option>@endforeach
                </select></div>
                <div class="col-lg-2 col-md-6"><label class="form-label" for="kategori">Kelompok barang</label><select class="form-select" name="kategori" id="kategori"><option value="">Semua kelompok</option>
                    @foreach($selectKategori as $item)<option value="{{ $item->id }}" @selected(($filters['kategori'] ?? null) == $item->id)>{{ $item->nama }}</option>@endforeach
                </select></div>
                <div class="col-lg-3 col-md-6"><label class="form-label" for="filter_barang_nama">Nama barang</label><select class="form-select" name="barang_nama" id="filter_barang_nama"><option value="">Semua barang</option>
                    @foreach($selectBarangNama as $item)<option value="{{ $item->id }}" @selected(($filters['barang_nama'] ?? null) == $item->id)>{{ $item->nama }}</option>@endforeach
                </select></div>
                <div class="col-lg-2 d-flex gap-2"><button class="btn btn-success" type="submit">Terapkan</button><a class="btn btn-outline-secondary" href="{{ route('user.stok-all') }}">Reset</a></div>
            </div>
        </form>
    </section>
    <section class="stock-panel mt-3" aria-label="Daftar stok barang">
        <div class="stock-table-heading"><div><h2>Daftar stok</h2><p>Harga + PPN mengikuti tarif pajak dan harga ajuan seperti pada halaman admin. Nominal dalam rupiah.</p></div><button type="button" class="btn btn-outline-success" id="stock-reload"><i class="fa fa-refresh" aria-hidden="true"></i> Muat ulang data</button></div>
        <div id="stock-error" class="alert alert-danger" role="alert" hidden>Data stok gagal dimuat. Gunakan tombol Muat ulang data untuk mencoba kembali.</div>
        <table id="user-stock-table" class="table table-bordered table-hover w-100 stock-table">
            <thead class="table-success"><tr>
                <th>No</th><th>Perusahaan</th><th>Bidang</th><th>Kelompok barang</th><th>Nama barang</th><th>Kode barang</th><th>Merk barang</th><th>Stok awal</th><th>Stok barang</th><th>Satuan barang</th><th>PPN</th><th>NON PPN</th><th>Harga + PPN<br>Jual Barang</th>
            </tr></thead><tbody></tbody>
        </table>
        <p class="stock-note mb-0">Data dibaca saat baris dimuat. Jika stok atau daftar barang berubah selama halaman terbuka, muat ulang data untuk menyegarkan tabel dari awal.</p>
    </section>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatable/scroller.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('stock-table.css').'?v='.filemtime(public_path('stock-table.css')) }}">
@endpush

@push('js')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="{{ asset('stock-table.js').'?v='.filemtime(public_path('stock-table.js')) }}"></script>
<script>
$(function () {
    $('#unit').select2({theme: 'bootstrap-5', width: '100%', placeholder: '-- Semua Perusahaan --'});
    $('#type, #kategori, #filter_barang_nama').select2({theme: 'bootstrap-5', width: '100%'});
    const {text, wrap, numeric, mark} = StockTableUI;
    StockTableUI.create('#user-stock-table', {
        widths: [3, 14, 11, 10, 15, 8, 8, 5, 5, 7, 3, 3, 8],
        ajax: {
            url: @json(route('user.stok-all.data')),
            data: data => Object.assign(data, @json($filters)),
            error: () => { $('#stock-error').prop('hidden', false); $('#user-stock-table_processing').hide(); }
        },
        columns: [
            {data: null, render: (value, type, row, meta) => meta.settings._iDisplayStart + meta.row + 1, className: 'text-center'},
            {data: 'perusahaan', render: wrap, className: 'stock-wrap-cell'},
            {data: 'bidang', render: wrap, className: 'stock-wrap-cell'},
            {data: 'kelompok', render: wrap, className: 'stock-wrap-cell'},
            {data: 'nama', render: wrap, className: 'stock-wrap-cell'},
            {data: 'kode', render: wrap, className: 'stock-wrap-cell'},
            {data: 'merk', render: wrap, className: 'stock-wrap-cell'},
            {data: 'stok_awal', render: numeric, className: 'text-end'},
            {data: 'stok', render: numeric, className: 'text-end'},
            {data: 'satuan', render: wrap, className: 'stock-wrap-cell'},
            {data: 'jenis', render: value => mark(Number(value) === 1), className: 'text-center'},
            {data: 'jenis', render: value => mark(Number(value) === 2), className: 'text-center'},
            {data: 'harga_jual_ppn', render: numeric, className: 'text-end'}
        ],
    });
});
</script>
@endpush
