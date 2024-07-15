@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="text-center mb-4">FORM PENJUALAN {{$barang_ppn == 1 ? 'PPN' : 'NON PPN' }}</h1>
    <div class="row justify-content-left mt-3 mb-3">
        <div class="col-5">
            <table>
                <tr>
                    <td>
                        <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#keranjangBelanja" >
                            <i class="fa fa-shopping-cart"> Keranjang </i> ({{$keranjang->count()}})
                        </a>
                        @include('billing.form-jual.keranjang')
                    </td>
                    <td>
                        <form action="{{route('billing.form-jual.keranjang.empty')}}" method="post" id="kosongKeranjang">
                            @csrf
                            <input type="hidden" name="barang_ppn" value="{{$barang_ppn}}">
                            <button class="btn btn-danger" type="submit">
                                <i class="fa fa-trash"> Kosongkan Keranjang </i>
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <form action="{{route('billing.form-jual.keranjang.store')}}" method="post" id="submitForm">
        @csrf
        <input type="hidden" name="barang_ppn" value="{{$barang_ppn}}">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="unit_id" class="form-label">Unit</label>
                <select name="unit_id" id="unit_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label for="type_id" class="form-label">Type</label>
                <select name="type_id" id="type_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    <!-- Options akan diisi melalui AJAX -->
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="kategori_id" class="form-label">Kategori</label>
                <select name="kategori_id" id="kategori_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    {{-- @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->nama }}</option>
                    @endforeach --}}
                </select>
            </div>
            <div class="col-md-6">
                <label for="barang_id" class="form-label">Nama Barang</label>
                <select name="barang_id" id="barang_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    <!-- Options akan diisi melalui AJAX -->
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="stok" class="form-label">Stok</label>
                <input type="text" name="stok" class="form-control" id="stok" disabled value="0">
            </div>
            <div class="col-md-3">
                <label for="jumlah" class="form-label">Jumlah</label>
                <input type="text" name="jumlah" class="form-control" id="jumlah" required>
            </div>
            <div class="col-md-3">
                <label for="harga_satuan" class="form-label">Harga Satuan</label>
                <input type="text" name="harga_satuan" class="form-control" id="harga_satuan" required readonly
                    value="0">
            </div>
            <div class="col-md-3">
                <label for="total" class="form-label">Total Harga</label>
                <input type="text" name="total" class="form-control" id="total" readonly>
            </div>
        </div>
        <div class="row mb-3">
            <div class="d-grid gap-3 mt-3">
                <button class="btn btn-primary">Masukan Keranjang</button>
                <a href="{{route('billing')}}" class="btn btn-secondary" type="button">Batal</a>
              </div>
        </div>
    </form>
    {{-- <div class="row mb-3">
        <div class="col-md-12">
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Unit</th>
                        <th class="text-center">Type</th>
                        <th class="text-center">Kategori</th>
                        <th class="text-center">Nama Barang</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-center">Harga Satuan</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="cartTable">
                    <!-- Keranjang barang akan ditampilkan di sini -->
                </tbody>
            </table>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
        </div>
    </div> --}}

</div>
@endsection
@push('js')
<script>
        new Cleave('#jumlah', {
            numeral: true,
            numeralDecimalMark: 'thousand',
            delimiter: '.',
        });

        confirmAndSubmit('#submitForm', "Apakah anda yakin ingin menyimpan data ini?");

        document.addEventListener('DOMContentLoaded', function () {
            const unitDropdown = document.getElementById('unit_id');
            const typeDropdown = document.getElementById('type_id');
            const kategoriDropdown = document.getElementById('kategori_id');
            const barangDropdown = document.getElementById('barang_id');
            const jumlahInput = document.getElementById('jumlah');
            const stokInput = document.getElementById('stok');
            const totalInput = document.getElementById('total');
            const hargaSatuanInput = document.getElementById('harga_satuan');
            const barangPpn = {{ $barang_ppn }};

            unitDropdown.addEventListener('change', function () {
                // reset type, kategori, and barang dropdown
                typeDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                kategoriDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                barangDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                totalInput.value = 0;
                stokInput.value = 0;
                hargaSatuanInput.value = 0;
                jumlahInput.value = 0;
                const unitId = this.value;
                fetch(`{{ url('/po/get-types') }}/${unitId}`)
                    .then(response => response.json())
                    .then(data => {
                        typeDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                        data.forEach(type => {
                            typeDropdown.innerHTML += `<option value="${type.id}">${type.nama}</option>`;
                        });
                    });
            });

            typeDropdown.addEventListener('change', function () {
                totalInput.value = 0;
                stokInput.value = 0;
                hargaSatuanInput.value = 0;
                jumlahInput.value = 0;
                const typeId = this.value;
                fetch(`{{ url('/po/get-kategori') }}/${typeId}`)
                    .then(response => response.json())
                    .then(data => {
                        kategoriDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                        barangDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                        data.forEach(kategori => {
                            kategoriDropdown.innerHTML += `<option value="${kategori.id}">${kategori.nama}</option>`;
                        });
                    });
            });

            kategoriDropdown.addEventListener('change', function () {
                totalInput.value = 0;
                stokInput.value = 0;
                hargaSatuanInput.value = 0;
                jumlahInput.value = 0;
                const typeId = typeDropdown.value;
                const kategoriId = this.value;
                fetch(`{{ url('/po/get-barang') }}/${typeId}/${kategoriId}`)
                    .then(response => response.json())
                    .then(data => {
                        barangDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                        // check if data is empty

                        data.forEach(barang => {
                            barangDropdown.innerHTML += `<option value="${barang.id}">${barang.nama}</option>`;
                        });
                    });
            });

            barangDropdown.addEventListener('change', function () {
                totalInput.value = 0;
                stokInput.value = 0;
                hargaSatuanInput.value = 0;
                jumlahInput.value = 0;
                const barangId = this.value;
                totalInput.value = 0;
                fetch(`{{ url('/billing/form-jual/get-stok') }}/${barangId}/${barangPpn}`)
                    .then(response => response.json())
                    .then(data => {

                        if (Object.keys(data).length === 0) {
                            alert('Barang tidak memiliki stok');
                            stokInput.value = 0;
                            hargaSatuanInput.value = 0;
                            jumlahInput.value = 0;
                            totalInput.value = 0;
                            return;
                        }
                        document.getElementById('stok').value = data.stok.toLocaleString('id-ID');
                        document.getElementById('harga_satuan').value = data.harga.toLocaleString('id-ID');
                    });
            });

            jumlahInput.addEventListener('keyup', function () {
                const jumlah = this.value.replace(/\./g, '');
                // check if jumlah more than stok
                const stok = document.getElementById('stok').value.replace(/\./g, '');

                if (parseInt(jumlah) > parseInt(stok)) {
                    alert('Jumlah melebihi stok barang');
                    const stokId = document.getElementById('stok');
                    this.value = stokId.value;
                    return;
                }

                const hargaSatuan = document.getElementById('harga_satuan').value.replace(/\./g, '');
                const total = jumlah * hargaSatuan;
                document.getElementById('total').value = total.toLocaleString('id-ID');
            });
        });
</script>
@endpush
