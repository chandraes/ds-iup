@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1>ORDER</h1>
        </div>
    </div>
    @include('swal')
     @include('billing.form-beli.foto')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                     <td><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container-fluid mt-3  ">
    <form method="GET" action="#" class="mt-3 mb-5">
        <div class="row">
            <div class="col-md-2">
                <label for="unit">Perusahaan</label>
                <select name="unit" id="filter_unit" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($selectUnit as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit')==$unit->id ? 'selected' : '' }}>
                        {{ $unit->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="bidang">Bidang</label>
                <select name="bidang" id="filter_bidang" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($selectBidang as $bidang)
                    <option value="{{ $bidang->id }}" {{ request('bidang')==$bidang->id ? 'selected' : '' }}>
                        {{ $bidang->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="kategori">Kelompok Barang</label>
                <select name="kategori" id="filter_kategori" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($selectKategori as $kat)
                    <option value="{{ $kat->id }}" {{ request('kategori')==$kat->id ? 'selected' : '' }}>
                        {{ $kat->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="nama">Nama Barang</label>
                <select class="form-select" name="barang_nama" id="filter_barang_nama">
                    <option value=""> ---------- </option>
                    @foreach ($selectBarangNama as $bn)
                    <option value="{{ $bn->id }}" {{ request('barang_nama')==$bn->id ? 'selected' : '' }}>
                        {{ $bn->nama }}
                        @endforeach
                </select>
            </div>
            <div class="col-md-4">

                <div class="btn-group form-control mt-3">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    {{-- reset filter button --}}
                    <a href="{{url()->current()}}" class="btn btn-danger">Reset Filter</a>
                </div>

            </div>

        </div>

    </form>

    <div class="table-container mt-4">
        <table class="table table-bordered" id="stok-datatable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Perusahaan</th>
                    <th class="text-center align-middle">Bidang</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                       <th class="text-center align-middle">PPN</th>
                    <th class="text-center align-middle">NON PPN</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">AVG<br>Penjualan</th>
                    <th class="text-center align-middle">Saran<br>Order</th>
                    <th class="text-center align-middle">Order</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

    </div>
    <br>
    <hr>
    <br>


</div>

@endsection
@push('css')

<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.1.1/css/scroller.dataTables.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.1.1/js/dataTables.scroller.min.js"></script>
<script>
    $('#filter_barang_nama').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

     $('#filter_unit').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

     $('#filter_bidang').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filter_kategori').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });


    function viewImage(imageUrl) {
        const imageModal = new bootstrap.Modal(document.getElementById('imageModal'));
        const zoomableImage = document.getElementById('zoomableImage');
        zoomableImage.src = imageUrl;
        imageModal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const image = document.getElementById('zoomableImage');
        const slider = document.getElementById('zoomSlider');

        slider.addEventListener('input', function () {
            const scale = slider.value;
            image.style.transform = `scale(${scale})`;
        });
    });

    $(document).ready(function() {
        var table = $('#stok-datatable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
            url: "{{ route('db.order.data') }}",
                // Tambahkan fungsi 'data' untuk mengirim parameter filter
                data: function(d) {
                    d.unit = $('#filter_unit').val();
                    d.bidang = $('#filter_bidang').val();
                    d.kategori = $('#filter_kategori').val();
                    d.barang_nama = $('#filter_barang_nama').val();
                }
            },
            scrollY: '70vh', // tinggi area scroll, bisa disesuaikan
            stateSave: true,
            scroller: {
                loadingIndicator: true
            },
            deferRender: true,
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' , className: 'text-center' },
                { data: 'unit.nama', name: 'unit.nama', className: 'text-wrap' },
                { data: 'type.nama', name: 'type.nama', className: 'text-wrap'},
                { data: 'kategori.nama', name: 'kategori.nama', className: 'text-wrap'},
                { data: 'barang_nama.nama', name: 'barang_nama.nama'},
                { data: 'kode', name: 'kode'},
                { data: 'merk', name: 'merk'},
                 {
                    data: 'jenis',
                    name: 'jenis',
                    className: 'text-center align-middle text-wrap',
                    render: function(data, type, row) {
                        return data == 1 ? '<i class="fa fa-check text-success"></i>' : '';
                    }
                },
                {
                    data: 'jenis',
                    name: 'jenis',
                    className: 'text-center align-middle text-wrap',
                    render: function(data, type, row) {
                        return data == 2 ? '<i class="fa fa-check text-success"></i>' : '';
                    }
                },
                { data: 'stok_info', name: 'stok_info', className: 'text-center', searchable:false},
                { data: 'satuan.nama', name: 'satuan.nama', className: 'text-center'},
                { data: 'avg_penjualan', name: 'avg_penjualan', className: 'text-center', searchable:false},
                { data: 'saran_order', name: 'saran_order', className: 'text-center', searchable:false},
                { data: 'order_qty', name: 'order_qty', searchable: false, className: 'text-center' },
            ],

        });
    });
</script>
@endpush
