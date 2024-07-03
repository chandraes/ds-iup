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
                <label for="kepada" class="form-label">Kepada:</label>
                <input type="text" name="kepada" class="form-control" id="kepada" value="{{ old('kepada') }}" required>
            </div>
            <div class="col-md-12 mb-3">
                <label for="alamat" class="form-label">Alamat:</label>
                <input type="text" name="alamat" class="form-control" id="alamat" value="{{ old('alamat') }}" required>
            </div>
            <div class="col-md-12 mb-3">
                <label for="telepon" class="form-label">Telepon:</label>
                <input type="text" name="telepon" class="form-control" id="telepon" value="{{ old('telepon') }}" required>
            </div>
            {{-- add select option --}}
            <div class="col-md-12 mb-3">
                <label for="apa_ppn" class="form-label">Apakah Menggunakan PPN:</label>
                <select name="apa_ppn" id="apa_ppn" class="form-select" required onchange="checkPPN()">
                    <option value="1" {{ old('apa_ppn') == '1' ? 'selected' : '' }}>Dengan PPN</option>
                    <option value="0" {{ old('apa_ppn') == '2' ? 'selected' : '' }}>Tanpa PPN</option>
                </select>
            </div>

            <div class="col-md-12 mb-3 text-end">
                <button type="button" class="btn btn-success" id="addRowButton"><i class="fa fa-plus"></i> Tambah Item</button>
            </div>

            <div id="bahanContainer">
                <table class="table table-bordered table-hover" id="tableBahan">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">KATEGORI</th>
                            <th class="text-center align-middle">NAMA BARANG</th>
                            <th class="text-center align-middle">Qty</th>
                            <th class="text-center align-middle">HARGA SATUAN</th>
                            <th class="text-center align-middle">TOTAL</th>
                            <th class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="bahanTable">
                        @if(old('kategori'))
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
                        @endif
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="3" class="text-end align-middle">Grand Total:</td>
                            <td colspan="2"></td>
                            <td class="text-center align-middle" id="grandTotal">0</td>
                            <td></td>
                        </tr>
                        <tr id="ppnRow">
                            <td colspan="3" class="text-end align-middle">PPN ({{ $ppn }}%):</td>
                            <td colspan="2"></td>
                            <td class="text-center align-middle" id="ppnTotal">0</td>
                            <td></td>
                        </tr>
                        <tr id="totalKeseluruhanRow">
                            <td colspan="3" class="text-end align-middle">Grand Total + PPN:</td>
                            <td colspan="2"></td>
                            <td class="text-center align-middle" id="totalKeseluruhan">0</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="7" class="text-center align-middle" id="terbilangTotal"></td>
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
   // Memanggil fungsi saat halaman dimuat

    // Tambahkan event listener untuk memanggil fungsi setiap kali nilai select diubah


    const addRowButton = document.getElementById('addRowButton');
    const addNoteButton = document.getElementById('addNoteButton');
    const bahanTable = document.getElementById('bahanTable');
    const noteTable = document.getElementById('noteTable');
    const saveButton = document.getElementById('saveButton');
    let bahanCounter = 0;
    let noteCounter = 0;

    const dataTable = $('#tableBahan').DataTable({
        paging: false,
        info: false,
        searching: false,
        scrollY: '450px',
        scrollCollapse: true
    });

    const noteTableInstance = $('#tableNote').DataTable({
        paging: false,
        info: false,
        searching: false
    });

    addRowButton.addEventListener('click', function () {
        bahanCounter++;

        const row = document.createElement('tr');
        row.classList.add('bahan-row');
        row.innerHTML = `
            <td class="text-center align-middle">${bahanCounter}</td>
            <td><input type="text" name="kategori[]" class="form-control kategori" required></td>
            <td><input type="text" name="nama_barang[]" class="form-control nama_barang" required></td>
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
            dataTable.row($(this).closest('tr')).remove().draw();
            updateTotals();

            let rowIndex = 1;
            document.querySelectorAll('.bahan-row').forEach((row) => {
                row.cells[0].textContent = rowIndex++;
            });

            bahanCounter--;
            if (bahanCounter === 0) {
                saveButton.hidden = true;
            }
        });

        saveButton.hidden = false;
    });

    addNoteButton.addEventListener('click', function () {
        noteCounter++;

        const row = document.createElement('tr');
        row.classList.add('note-row');
        row.innerHTML = `
            <td class="text-center align-middle">${noteCounter}</td>
            <td><input type="text" name="catatan[]" class="form-control catatan" required></td>
            <td class="text-center align-middle"><button type="button" class="btn btn-danger remove-note w-100">Hapus</button></td>
        `;
        noteTableInstance.row.add(row).draw();

        row.querySelector('.remove-note').addEventListener('click', function () {
            noteTableInstance.row($(this).closest('tr')).remove().draw();

            let noteIndex = 1;
            document.querySelectorAll('.note-row').forEach((row) => {
                row.cells[0].textContent = noteIndex++;
            });

            noteCounter--;
        });

        saveButton.hidden = false;
    });
});

</script>
@endpush
