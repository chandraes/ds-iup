@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM BARANG RETUR<br>PILIH BARANG<br>{{$b->karyawan->nama}}
            <br>{{$b->konsumen ? $b->konsumen->kode_toko->kode.' '.$b->konsumen->nama : ''}}</u></h1>
        </div>
    </div>
    @include('swal')
     @include('billing.form-barang-retur.modal-keranjang')
     @include('billing.form-barang-retur.foto')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container-fluid mt-3 table-responsive ">
    <form method="GET" action="#" class="mt-3 mb-5">
        <div class="row">

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
    <div class="row my-3">
        <div class="col-md-6">
            <div class="row px-3">
                <a href="{{route('billing.form-barang-retur.detail.preview', $b->id)}}" class="btn btn-success @if ($keranjang->count() == 0) disabled
                @endif" role="button"><i class="fa fa-shopping-cart"></i> Keranjang {{$keranjang->count() == 0 ? '' :
                    '('.$keranjang->count().')'}}</a>
            </div>
        </div>
        <div class="col-md-6">
            <form action="{{route('billing.form-barang-retur.detail.empty', $b->id)}}" method="post"
                id="keranjangEmpty">
                @csrf
                <div class="row px-3">
                    <button class="btn btn-danger" @if ($keranjang->count() == 0) disabled
                        @endif><i class="fa fa-trash"></i> Kosongkan Keranjang</button>
                </div>
            </form>
        </div>
    </div>

    <center>
        <h2>PILIH BARANG</h2>
    </center>
    <div class="table-container mt-4">
        <table class="table table-bordered" id="stok-datatable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>

                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">PPN</th>
                    <th class="text-center align-middle">NON PPN</th>

                    <th class="text-center align-middle">ACT</th>

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

    $('#filter_kategori').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

      var nominal = new Cleave('#jumlah', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.',
        });

    confirmAndSubmit("#keranjangEmpty", "Apakah anda yakin untuk mengosongkan keranjang?");


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
            url: "{{ route('billing.form-barang-retur.detail.data', $b->id) }}",
                // Tambahkan fungsi 'data' untuk mengirim parameter filter
                data: function(d) {
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
                { data: 'kategori.nama', name: 'kategori.nama'},
                { data: 'barang_nama.nama', name: 'barang_nama.nama'},
                { data: 'kode', name: 'kode'},
                { data: 'merk', name: 'merk'},
                { data: 'stok_info', name: 'stok_harga_sum_stok', className: 'text-center', searchable:false},
                { data: 'satuan.nama', name: 'satuan.nama', className: 'text-center'},
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
                { data: 'action', name: 'action', orderable: false, searchable: false, width: '10%', className: 'text-center' }
            ],

        });

        $('#stok-datatable tbody').on('click', 'button.btn-modal-trigger', function() {
            var data = $(this).data('row');
            var qty = $(this).data('qty'); // data-qty (dari tombol)
            var detailId = $(this).data('detail-id'); // data-detail-id (dari tombol)

            // 2. Isi info barang (Nama, Stok, Satuan, ID Barang)
            document.getElementById('jumlah_satuan').innerText = data.satuan ? data.satuan.nama : '';
            document.getElementById('barang_id').value = data.id;
            document.getElementById('stok_tersedia').textContent = data.nf_stok;
            document.getElementById('nm_barang_merk_retail').value = data.barang_nama.nama + ', ' + data.kode + ', ' + data.merk;

            // 3. Kosongkan input
            document.getElementById('jumlah').value = '';
            document.getElementById('detail_id').value = '';

            // 4. INI LOGIKA ANDA SEBELUMNYA - SEKARANG BERFUNGSI KEMBALI
            if (qty > 0) {
                // Jika qty > 0 (mode edit), isi modal dengan data yang ada
                const formatter = new Intl.NumberFormat('id-ID');
                const formattedQty = formatter.format(qty);

                document.getElementById('jumlah').value = formattedQty;
                document.getElementById('detail_id').value = detailId;
            }

            // 5. Tampilkan Modal
            $('#keranjangModal').modal('show');
        });
    });
</script>
@endpush
