@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM PURCHASE ORDER</u></h1>
        </div>
    </div>
    @include('swal')
</div>
<div class="container mt-5">
    <form action="{{route('po.form.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-12 mb-3">
                <label for="supplier_id" class="form-label">Kepada:</label>
                <select name="supplier_id" id="supplier_id" class="form-select" required onchange="checkSupplier()">
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    @foreach($supplier as $s)
                        <option value="{{ $s->id }}" @if (old('supplier_id') == $s->id) selected @endif>{{ $s->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12 mb-3">
                <label for="alamat" class="form-label">Alamat:</label>
                <input type="text" name="alamat" class="form-control" id="alamat" value="{{old('alamat')}}" required readonly>
            </div>
            <div class="col-md-12 mb-3">
                <label for="telepon" class="form-label">Telepon:</label>
                <input type="text" name="telepon" class="form-control" id="telepon" value="{{old('telepon')}}" required readonly>
            </div>
            {{-- add select option --}}
            <div class="col-md-12 mb-3">
                <label for="apa_ppn" class="form-label">Apakah Menggunakan PPN:</label>
                <select name="apa_ppn" id="apa_ppn" class="form-select" required onchange="checkPPN()">
                    <option value="1" {{ old('apa_ppn') == '1' ? 'selected' : '' }}>Dengan PPN</option>
                    <option value="0" {{ old('apa_ppn') == '2' ? 'selected' : '' }}>Tanpa PPN</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="unit_id" class="form-label">Unit:</label>
                <select name="unit_id" id="unit_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    @foreach($unit as $u)
                        <option value="{{ $u->id }}">{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="type_id" class="form-label">Type:</label>
                <select name="type_id" id="type_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    <!-- Options akan diisi melalui AJAX -->
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="kategori_id" class="form-label">Kategori:</label>
                <select name="kategori_id" id="kategori_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    <!-- Options akan diisi melalui AJAX -->
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="barang_id" class="form-label">Nama Barang:</label>
                <select name="barang_id" id="barang_id" class="form-select" required>
                    <option value="" selected disabled>-- Pilih Salah Satu --</option>
                    <!-- Options akan diisi melalui AJAX -->
                </select>
            </div>
            <div class="col-md-12 mb-3 text-end">
                <button type="button" class="btn btn-success" id="addBarangButton"><i class="fa fa-plus"></i> Tambah Item</button>
            </div>

            <div id="bahanContainer">
                <table class="table table-bordered table-hover" id="tableBahan">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">UNIT</th>
                            <th class="text-center align-middle">TYPE</th>
                            <th class="text-center align-middle">KATEGORI</th>
                            <th class="text-center align-middle">NAMA BARANG</th>
                            <th class="text-center align-middle">Qty</th>
                            <th class="text-center align-middle">HARGA SATUAN</th>
                            <th class="text-center align-middle">TOTAL</th>
                            <th class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bahanTable">
                        {{-- @if(old('kategori'))
                            @foreach(old('kategori') as $index => $kategori)
                                <tr class="bahan-row">
                                    <td class="text-center align-middle">{{ $index + 1 }}</td>
                                    <td><input type="text" name="kategori[]" class="form-control kategori" value="{{ $kategori }}" required></td>
                                    <td><input type="text" name="nama_barang[]" class="form-control nama_barang" value="{{ old('nama_barang')[$index] }}" required></td>
                                    <td><input type="text" name="jumlah[]" class="form-control persentase" value="{{ old('jumlah')[$index] }}" required></td>
                                    <td><input type="text" name="harga_satuan[]" class="form-control harga" value="{{ old('harga_satuan')[$index] }}" required></td>
                                    <td><input type="text" name="total[]" class="form-control total" value="{{ old('jumlah')[$index] * old('harga_satuan')[$index] }}" readonly></td>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-danger remove-bahan w-100">Hapus</button></td>
                                </tr>
                            @endforeach
                        @endif --}}
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="7" class="text-end align-middle">Grand Total:</td>
                            <td class="text-end align-middle" id="grandTotal">0</td>
                            <td></td>
                        </tr>
                        <tr id="ppnRow">
                            <td colspan="7" class="text-end align-middle">PPN ({{ $ppn }}%):</td>
                            <td class="text-end align-middle" id="ppnTotal">0</td>
                            <td></td>
                        </tr>
                        <tr id="totalKeseluruhanRow">
                            <td colspan="7" class="text-end align-middle">Grand Total + PPN:</td>
                            <td class="text-end align-middle" id="totalKeseluruhan">0</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="9" class="text-center align-middle" id="terbilangTotal"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md-12 m-3 text-end">
                <button type="button" class="btn btn-info" id="addNoteButton"><i class="fa fa-plus"></i> Tambah Catatan</button>
            </div>
            <div id="noteContainer" class="col-md-12 mt-2">
                <table class="table table-bordered table-hover" id="tableNote">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Catatan</th>
                            <th class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="noteTable">
                        @if(old('catatan'))
                            @foreach(old('catatan') as $index => $catatan)
                                <tr class="note-row">
                                    <td class="text-center align-middle">{{ $index + 1 }}</td>
                                    <td><input type="text" name="catatan[]" class="form-control catatan" value="{{ $catatan }}" required></td>
                                    <td class="text-center align-middle"><button type="button" class="btn btn-danger remove-note w-100">Hapus</button></td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="col-md-12 mt-2">
                <button type="submit" class="btn btn-primary d-block w-100" id="saveButton" hidden>Simpan</button>
            </div>
            <div class="col-md-12 mt-2">
                <a href="{{ route('po') }}" class="btn btn-secondary d-block w-100">Kembali</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('js')
<!-- DataTables JS -->
<script src="{{asset('assets/js/angka_terbilang.js')}}"></script>
<script>
    confirmAndSubmit('#masukForm', 'Pastikan data yang diinput sudah benar, yakin ingin menyimpan data ini?');

    function checkSupplier()
    {
        const supplierId = document.getElementById('supplier_id').value;
        const alamat = document.getElementById('alamat');
        const telepon = document.getElementById('telepon');

        if (supplierId == '') {
            alamat.value = '';
            telepon.value = '';
        } else {
            const supplier = @json($supplier);
            const selectedSupplier = supplier.find(s => s.id == supplierId);

            alamat.value = selectedSupplier.alamat;
            telepon.value = selectedSupplier.no_hp;
        }
    }

    function checkPPN() {
        const apaPPN = document.getElementById('apa_ppn').value;
        const ppnRow = document.getElementById('ppnRow');
        const tkr = document.getElementById('totalKeseluruhanRow');

        if (apaPPN == '0') {
            ppnRow.style.display = 'none';
            tkr.style.display = 'none';
        } else {
            ppnRow.style.display = '';
            tkr.style.display = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const unitDropdown = document.getElementById('unit_id');
        const typeDropdown = document.getElementById('type_id');
        const kategoriDropdown = document.getElementById('kategori_id');
        const barangDropdown = document.getElementById('barang_id');
        const addBarangButton = document.getElementById('addBarangButton');
        const bahanTable = document.getElementById('bahanTable');
        const dataTable = $('#tableBahan').DataTable({
            paging: false,
            info: false,
            searching: false,
            scrollY: '450px',
            scrollCollapse: true
        });

        let selectedBarang = [];

        unitDropdown.addEventListener('change', function () {
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
            const typeId = this.value;
            fetch(`{{ url('/po/get-kategori') }}/${typeId}`)
                .then(response => response.json())
                .then(data => {
                    kategoriDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                    data.forEach(kategori => {
                        kategoriDropdown.innerHTML += `<option value="${kategori.id}">${kategori.nama}</option>`;
                    });
                });
        });

        kategoriDropdown.addEventListener('change', function () {
            const typeId = typeDropdown.value;
            const kategoriId = this.value;
            fetch(`{{ url('/po/get-barang') }}/${typeId}/${kategoriId}`)
                .then(response => response.json())
                .then(data => {
                    barangDropdown.innerHTML = '<option value="" selected disabled>-- Pilih Salah Satu --</option>';
                    data.forEach(barang => {
                        barangDropdown.innerHTML += `<option value="${barang.id}">${barang.nama}</option>`;
                    });
                });
        });

        addBarangButton.addEventListener('click', function () {
            const unitText = unitDropdown.options[unitDropdown.selectedIndex].text;
            const typeText = typeDropdown.options[typeDropdown.selectedIndex].text;
            const kategoriText = kategoriDropdown.options[kategoriDropdown.selectedIndex].text;
            const barangId = barangDropdown.value;
            const barangText = barangDropdown.options[barangDropdown.selectedIndex].text;

            if (selectedBarang.includes(barangId)) {
                alert('Barang sudah dipilih!');
                return;
            }

            selectedBarang.push(barangId);
            const row = document.createElement('tr');
            row.classList.add('bahan-row');
            row.innerHTML = `
                <td class="text-center align-middle">${selectedBarang.length}</td>
                <td><input type="text" name="unit[]" class="form-control" required readonly value="${unitText}"></td>
                <td><input type="text" name="type[]" class="form-control" required readonly value="${typeText}"></td>
                <td><input type="text" name="kategori[]" class="form-control" required readonly value="${kategoriText}"></td>
                <td>
                    <input type="text" name="barang[]" class="form-control" required readonly value="${barangText}">
                    <input type="hidden" name="barang_id[]" class="form-control" required readonly value="${barangId}">
                </td>
                <td><input type="text" name="jumlah[]" class="form-control persentase" required></td>
                <td><input type="text" name="harga_satuan[]" class="form-control harga" required></td>
                <td><input type="text" name="total[]" class="form-control total" readonly></td>
                <td class="text-center align-middle"><button type="button" class="btn btn-danger remove-bahan w-100">Hapus</button></td>
            `;
            dataTable.row.add(row).draw();

            new Cleave(row.querySelector('.persentase'), {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            new Cleave(row.querySelector('.harga'), {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            const jumlahInput = row.querySelector('.persentase');
            const hargaSatuanInput = row.querySelector('.harga');
            const totalInput = row.querySelector('.total');

            const updateTotals = () => {
                const jumlahRaw = jumlahInput.value.replace(/\./g, '').replace(',', '.');
                const jumlah = parseFloat(jumlahRaw) || 0;
                const hargaSatuanRaw = hargaSatuanInput.value.replace(/\./g, '').replace(',', '.');
                const hargaSatuan = parseFloat(hargaSatuanRaw) || 0;
                const total = jumlah * hargaSatuan;

                totalInput.value = total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                let grandTotalQty = 0;
                let grandTotal = 0;

                document.querySelectorAll('.persentase').forEach(input => {
                    const value = parseFloat(input.value.replace(/\./g, '').replace(',', '.')) || 0;
                    grandTotalQty += value;
                });

                document.querySelectorAll('.total').forEach(input => {
                    const value = parseFloat(input.value.replace(/\./g, '').replace(',', '.')) || 0;
                    grandTotal += value;
                });

                const apaPPN = document.getElementById('apa_ppn').value;

                const ppn = {{ $ppn }} / 100;
                let ppnTotal = 0;

                if (apaPPN == '1') {
                    ppnTotal = grandTotal * ppn;
                }

                const totalKeseluruhan = grandTotal + ppnTotal;

                document.getElementById('grandTotal').textContent = grandTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('ppnTotal').textContent = ppnTotal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                document.getElementById('totalKeseluruhan').textContent = totalKeseluruhan.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                const ter = angkaTerbilang(totalKeseluruhan);
                const terHurufBesar = ter.charAt(0).toUpperCase() + ter.slice(1); // Mengubah huruf depan menjadi besar

                document.getElementById('terbilangTotal').textContent = `Terbilang: #${terHurufBesar}#`;

                checkPPN();
            };

            jumlahInput.addEventListener('input', updateTotals);
            hargaSatuanInput.addEventListener('input', updateTotals);

            row.querySelector('.remove-bahan').addEventListener('click', function () {
                const barangIdToRemove = row.querySelector('.barang').value;
                selectedBarang = selectedBarang.filter(id => id !== barangIdToRemove);
                dataTable.row($(this).closest('tr')).remove().draw();
                updateTotals();

                let rowIndex = 1;
                document.querySelectorAll('.bahan-row').forEach((row) => {
                    row.cells[0].textContent = rowIndex++;
                });
            });
        });
    });

</script>
@endpush
