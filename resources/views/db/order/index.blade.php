@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
 <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h2 class="fw-bold text-dark mb-0">
                <i class="fa fa-shopping-cart text-primary me-2"></i> ORDER
            </h2>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>


        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('home') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <img src="{{ asset('images/dashboard.svg') }}" alt="dashboard" width="20">
                <span>Dashboard</span>
            </a>
            <a href="{{ route('db') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <img src="{{ asset('images/database.svg') }}" alt="database" width="20">
                <span>Database</span>
            </a>
        </div>
    </div>

    @include('swal')
    @include('billing.form-beli.foto')

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="mb-0 fw-bold text-primary"><i class="fa fa-filter me-2"></i>Filter Pencarian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="#" id="filter-form">
                <div class="row g-3 align-items-end">
                    <div class="col-md-2">
                        <label for="unit" class="form-label fw-bold small">Perusahaan</label>
                        <select name="unit" id="filter_unit" class="form-select">
                            <option value="">Semua Perusahaan</option>
                            @foreach($selectUnit as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit')==$unit->id ? 'selected' : '' }}>
                                {{ $unit->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="bidang" class="form-label fw-bold small">Bidang</label>
                        <select name="bidang" id="filter_bidang" class="form-select">
                            <option value="">Semua Bidang</option>
                            @foreach($selectBidang as $bidang)
                            <option value="{{ $bidang->id }}" {{ request('bidang')==$bidang->id ? 'selected' : '' }}>
                                {{ $bidang->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="kategori" class="form-label fw-bold small">Kelompok Barang</label>
                        <select name="kategori" id="filter_kategori" class="form-select">
                            <option value="">Semua Kelompok</option>
                            @foreach($selectKategori as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori')==$kat->id ? 'selected' : '' }}>
                                {{ $kat->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="nama" class="form-label fw-bold small">Nama Barang</label>
                        <select class="form-select" name="barang_nama" id="filter_barang_nama">
                            <option value="">Cari Nama Barang...</option>
                            @foreach ($selectBarangNama as $bn)
                            <option value="{{ $bn->id }}" {{ request('barang_nama')==$bn->id ? 'selected' : '' }}>
                                {{ $bn->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fa fa-search me-1"></i> Terapkan
                            </button>
                            <a href="{{url()->current()}}" class="btn btn-light border w-100 text-danger">
                                <i class="fa fa-undo me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-container p-3">
                <table class="table table-hover table-bordered table-striped align-middle w-100" id="stok-datatable">
                    <thead class="table-success border-bottom border-2">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Perusahaan</th>
                            <th>Bidang</th>
                            <th>Kelompok</th>
                            <th>Nama Barang</th>
                            <th>Kode</th>
                            <th>Merk</th>
                            <th class="text-center">PPN</th>
                            <th class="text-center">Non PPN</th>
                            <th class="text-center">Stok</th>
                            <th class="text-center">Satuan</th>
                            <th class="text-center">Avg Jual</th>
                            <th class="text-center">Saran</th>
                            <th class="text-center">Order</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.1.1/css/scroller.dataTables.min.css">

<style>
    /* Membuat header tabel sedikit lebih tebal dan rapi */
    #stok-datatable thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 1rem;
        letter-spacing: 0.5px;
        vertical-align: middle !important;

    }

    /* Mengatur font size isi tabel agar pas */
    #stok-datatable tbody td {
        font-size: 0.9rem;
    }

    /* Style Select2 agar match dengan Bootstrap 5 input height */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.1.1/js/dataTables.scroller.min.js"></script>

<script>
    // Inisialisasi Select2
    const select2Config = { theme: 'bootstrap-5', width: '100%' };
    $('#filter_barang_nama').select2(select2Config);
    $('#filter_unit').select2(select2Config);
    $('#filter_bidang').select2(select2Config);
    $('#filter_kategori').select2(select2Config);

    // Image Modal Logic
    function viewImage(imageUrl) {
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        const zoomableImage = document.getElementById('zoomableImage');
        if(zoomableImage) {
            zoomableImage.src = imageUrl;
            imageModal.show();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const image = document.getElementById('zoomableImage');
        const slider = document.getElementById('zoomSlider');

        if (slider && image) {
            slider.addEventListener('input', function () {
                const scale = slider.value;
                image.style.transform = `scale(${scale})`;
            });
        }
    });

    // Datatables Configuration
    $(document).ready(function() {
        var table = $('#stok-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('db.order.data') }}",
                data: function(d) {
                    d.unit = $('#filter_unit').val();
                    d.bidang = $('#filter_bidang').val();
                    d.kategori = $('#filter_kategori').val();
                    d.barang_nama = $('#filter_barang_nama').val();
                }
            },
            scrollY: '65vh', // Sedikit dikurangi agar pas dengan card
            scrollX: true,
            scrollCollapse: true,
            stateSave: true,
            scroller: {
                loadingIndicator: true
            },
            deferRender: true,
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'unit.nama', name: 'unit.nama', className: 'text-wrap' },
                { data: 'type.nama', name: 'type.nama', className: 'text-wrap'},
                { data: 'kategori.nama', name: 'kategori.nama', className: 'text-wrap'},
                { data: 'barang_nama.nama', name: 'barang_nama.nama', className: 'fw-bold text-primary'},
                { data: 'kode', name: 'kode', className: 'font-monospace small'},
                { data: 'merk', name: 'merk'},
                {
                    data: 'jenis',
                    name: 'jenis',
                    className: 'text-center',
                    render: function(data, type, row) {
                        // Menggunakan Badge agar lebih menarik
                        return data == 1 ? '<span class="badge bg-success bg-opacity-10 text-success"><i class="fa fa-check"></i></span>' : '-';
                    }
                },
                {
                    data: 'jenis',
                    name: 'jenis',
                    className: 'text-center',
                    render: function(data, type, row) {
                        return data == 2 ? '<span class="badge bg-success bg-opacity-10 text-success"><i class="fa fa-check"></i></span>' : '-';
                    }
                },
                { data: 'stok_info', name: 'stok_info', className: 'text-center fw-bold', searchable:false},
                { data: 'satuan.nama', name: 'satuan.nama', className: 'text-center'},
                { data: 'avg_penjualan', name: 'avg_penjualan', className: 'text-center', searchable:false},
                {
                    data: 'saran_order',
                    name: 'saran_order',
                    className: 'text-center',
                    searchable:false,
                    render: function(data, type, row) {
                        // Highlight saran order jika ada angka signifikan
                        return data > 0 ? `<span class="fw-bold text-danger">${data}</span>` : data;
                    }
                },
                { data: 'order_qty', name: 'order_qty', searchable: false, className: 'text-center' },
            ],
        });

        // Trigger reload table saat filter berubah (Optional UX improvement)
        $('#filter-form select').on('change', function() {
            table.draw();
        });
    });
</script>
@endpush
