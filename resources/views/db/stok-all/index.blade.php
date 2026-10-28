@extends('layouts.app')

@section('content')
<div class="container-fluid stock-page">
    <header class="stock-header">
        <div><span class="stock-eyebrow">Inventori</span><h1>Stok & Harga Jual Barang</h1><p>Stok, valuasi, dan pengelolaan harga seluruh perusahaan. Gulir tabel untuk memuat baris berikutnya.</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-secondary" href="{{ route('home') }}"><i class="fa fa-arrow-left" aria-hidden="true"></i> Dashboard</a>
            @unless($assistant)<a class="btn btn-outline-secondary" href="{{ route('db') }}">Database</a>@endunless
            <a class="btn btn-outline-success" href="{{ route('db.stok-all', array_merge($filters, ['mode' => 'classic'])) }}">Tampilan lama</a>
        </div>
    </header>
    <section class="stock-panel" aria-label="Filter stok barang">
        <form method="get" action="{{ route('db.stok-all') }}" id="stock-filters">
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
                @unless($assistant)
                <div class="col-lg-2 col-md-6"><label class="form-label" for="filter_harga">Harga jual</label><select name="filter_harga" id="filter_harga" class="form-select"><option value="">Semua stok</option><option value="1" @selected(!empty($filters['filter_harga']))>Harga jual nol</option></select></div>
                @endunless
                <div class="col-lg-2 d-flex gap-2"><button class="btn btn-success" type="submit">Terapkan</button><a class="btn btn-outline-secondary" href="{{ route('db.stok-all') }}">Reset</a></div>
            </div>
        </form>
    </section>
    <section class="stock-panel mt-3" aria-label="Daftar stok barang">
        <div class="stock-table-heading"><div><h2>Daftar stok</h2><p>Nominal dalam rupiah. Harga ajuan aktif ikut digunakan pada valuasi dan margin.</p></div><button type="button" class="btn btn-outline-success" id="stock-reload"><i class="fa fa-refresh" aria-hidden="true"></i> Muat ulang data</button></div>
        <div id="stock-error" class="alert alert-danger" role="alert" hidden>Data stok gagal dimuat. Gunakan tombol Muat ulang data untuk mencoba kembali.</div>
        <table id="admin-stock-table" class="table table-bordered table-hover w-100 stock-table">
            <thead class="table-success"><tr>
                <th>No</th><th>Perusahaan</th><th>Bidang</th><th>Kelompok barang</th><th>Nama barang</th><th>Kode barang</th><th>Merk barang</th><th>Stok awal</th><th>Stok barang</th><th>Satuan barang</th><th>PPN</th><th>NON PPN</th><th>Harga DPP beli</th><th>Harga + PPN beli</th><th>Harga DPP jual</th><th>Kelipatan jual min.</th><th>Harga + PPN jual</th><th>Total beli + PPN</th><th>Total jual + PPN</th><th>Margin</th><th>Aksi</th>
            </tr></thead><tbody></tbody>
        </table>
        <div class="row g-3 mt-2 stock-totals"><div class="col-md-4"><small>Total harga beli + PPN</small><strong id="total-beli">—</strong></div><div class="col-md-4"><small>Total harga jual + PPN</small><strong id="total-jual">—</strong></div><div class="col-md-4"><small>Estimasi profit</small><strong id="total-profit">—</strong></div></div>
        <p class="stock-note">Total menghitung seluruh hasil filter dan pencarian, bukan hanya baris yang dimuat.</p>
        <p class="stock-note mb-0">Data dibaca saat baris dimuat. Jika stok atau daftar barang berubah selama halaman terbuka, muat ulang data untuk menyegarkan tabel dari awal.</p>
    </section>
</div>
@include('db.stok-all.modals')
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatable/scroller.bootstrap5.min.css') }}">
<link rel="stylesheet" href="{{ asset('stock-table.css').'?v='.filemtime(public_path('stock-table.css')) }}">
@endpush


@push('css')
<style>
#admin-stock-table_wrapper table{min-width:1900px}.stock-action{padding:0;border:0;background:none;color:#146c43;text-decoration:underline;text-underline-offset:3px}.stock-action:hover{color:#0a3622}.stock-action:focus-visible{outline:2px solid #28704f;outline-offset:3px}.stock-totals small,.stock-totals strong{display:block}.stock-totals strong{font-size:1.1rem;margin-top:.25rem}.stock-warning{background:#fff3cd!important}.stock-danger{background:#f8d7da!important}.stock-proposal{display:block;font-size:.78rem;margin-top:.3rem}.stock-amount{white-space:nowrap}
</style>
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/js/select2.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="{{ asset('stock-table.js').'?v='.filemtime(public_path('stock-table.js')) }}"></script>
<script>
window.adminStockConfig = @json($stockConfig);
</script>
<script src="{{ asset('admin-stock.js').'?v='.filemtime(public_path('admin-stock.js')) }}"></script>
@endpush
