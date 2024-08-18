@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>STOK & HARGA JUAL BARANG PPN</u></h1>
        </div>
    </div>
    @include('swal')
    @include('db.stok-ppn.edit')
    @include('db.stok-ppn.action')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('db')}}"><img
                                src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td class="text-center align-middle">
                        <form action="{{route('db.stok-ppn.download')}}" method="get" target="_blank">
                            <input type="hidden" name="unit" value="{{request('unit')}}">
                            <input type="hidden" name="type" value="{{request('type')}}">
                            <input type="hidden" name="kategori" value="{{request('kategori')}}">
                            <input type="hidden" name="barang_nama" value="{{request('barang_nama')}}">
                            <div class="row">
                                <button type="submit" class="btn"><img src="{{asset('images/print.svg')}}" alt="dokumen"
                                        width="30">
                                    PDF</button>
                            </div>

                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<style>
    .table-container {
        max-height: 500px;
        overflow-y: auto;
    }

    thead th {
        position: sticky;
        top: 0;
        background: white;
        z-index: 1;
    }
</style>
<div class="container-fluid mt-3 table-responsive ">
    <form method="GET" action="{{route('db.stok-ppn')}}" class="mt-3 mb-5">
        <div class="row">
            <div class="col-md-2">
                <label for="unit">Unit</label>
                <select name="unit" id="unit" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit')==$unit->id ? 'selected' : '' }}>
                        {{ $unit->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type">Type</label>
                <select name="type" id="type" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($selectType as $type)
                    <option value="{{ $type->id }}" {{ request('type')==$type->id ? 'selected' : '' }}>
                        {{ $type->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="kategori">Kelompok Barang</label>
                <select name="kategori" id="kategori" class="form-select">
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
            <div class="col-md-2">
                <label for="nama">
                    ---------------
                </label>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    {{-- reset filter button --}}
                    <a href="{{route('db.stok-ppn')}}" class="btn btn-danger">Reset Filter</a>
                </div>

            </div>

        </div>

    </form>
    <div class="table-container mt-4">
        <table class="table table-bordered" id="dataTable" >
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Unit</th>
                    <th class="text-center align-middle">Tipe</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Satuan<br>Barang</th>
                    <th class="text-center align-middle">Harga DPP<br>Beli Barang</th>
                    <th class="text-center align-middle">Harga+PPN<br>Beli Barang</th>
                    <th class="text-center align-middle" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Total Harga+PPN<br>Beli Barang</th>
                    <th class="text-center align-middle">Total Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Margin<br>Profit</th>
                    {{-- <th class="text-center align-middle">ACT</th> --}}

                </tr>
            </thead>
            @php
            $number = 1;
            $sumTotalHargaBeli = 0;
            $sumTotalHargaJual = 0;
            @endphp

            <tbody>
                @foreach ($data as $unitId => $types)
                @php $unitDisplayed = false; @endphp
                @foreach ($types as $typeId => $categories)
                @php $typeDisplayed = false; @endphp
                @foreach ($categories as $kategoriId => $barangs)
                @php $kategoriDisplayed = false; @endphp
                @foreach ($barangs as $namaId => $items)
                @php $namaDisplayed = false; @endphp
                @foreach ($items as $barangId => $stokHargas)
                @php $barangDisplayed = false; @endphp
                @foreach ($stokHargas as $stokHarga)
                @if ($stokHarga->stok > 0)
                <tr>
                    @if (!$unitDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->unitRowspan }}">{{ $number++ }}</td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->unitRowspan }}">{{
                        $stokHarga->unit->nama }}</td>
                    @php $unitDisplayed = true; @endphp
                    @endif
                    @if (!$typeDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->typeRowspan }}">{{
                        $stokHarga->type->nama }}</td>
                    @php $typeDisplayed = true; @endphp
                    @endif
                    @if (!$kategoriDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->kategoriRowspan }}">{{
                        $stokHarga->kategori->nama }}</td>
                    @php $kategoriDisplayed = true; @endphp
                    @endif
                    @if (!$namaDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->namaRowspan }}">
                        {{$stokHarga->barang_nama->nama }}</a>
                        </td>
                    @php $namaDisplayed = true; @endphp
                    @endif
                    @if (!$barangDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">{{
                        $stokHarga->barang->kode }}</td>
                    <td class="text-center align-middle" rowspan="{{ $stokHarga->barangRowspan }}">{{
                        $stokHarga->barang->merk }}</td>
                    @php $barangDisplayed = true; @endphp
                    @endif
                    <td class="text-center align-middle"><a href="#"data-bs-toggle="modal" data-bs-target="#actionModal" onclick="actionFun({{$stokHarga}})">{{ $stokHarga->nf_stok }}</a></td>
                    <td class="text-center align-middle">{{ $stokHarga->barang->satuan ?
                        $stokHarga->barang->satuan->nama : '-' }}</td>
                    @php
                    $totalHargaBeli = ($stokHarga->harga_beli + ($stokHarga->harga_beli * $ppnRate / 100)) *
                    $stokHarga->stok;
                    $totalHargaJual = ($stokHarga->harga + ($stokHarga->harga * $ppnRate / 100)) * $stokHarga->stok;
                    $sumTotalHargaJual += $totalHargaJual;
                    $sumTotalHargaBeli += $totalHargaBeli;
                    if ($stokHarga->harga_beli == 0) {
                    $margin = '-';
                    } else {
                    $margin = ($stokHarga->harga - $stokHarga->harga_beli) / $stokHarga->harga_beli * 100;

                    }

                    @endphp
                    <td class="text-center align-middle">{{ $stokHarga->nf_harga_beli }}</td>
                    <td class="text-center align-middle">{{ number_format(($stokHarga->harga_beli +
                        ($stokHarga->harga_beli * $ppnRate / 100)), 0, ',', '.') }}</td>
                    <td class="text-end align-middle">
                        <div class="row mx-3">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#editModal"
                                onclick="editFun({{$stokHarga}})">{{ $stokHarga->nf_harga }}</a>
                        </div>
                    </td>
                    <td class="text-end align-middle">
                        {{ number_format($stokHarga->harga+($stokHarga->harga*$ppnRate/100), 0, ',','.') }}
                    </td>

                    <td class="text-center align-middle">{{ number_format($totalHargaBeli, 0, ',','.') }}</td>
                    <td class="text-center align-middle">{{ number_format($totalHargaJual, 0, ',','.') }}</td>
                    <td class="text-end align-middle @if ($margin == '-')
                    table-warning
                    @else
                    @if ($margin < 10.01) table-danger @endif
                    @endif">
                        @if ($margin == '-')
                        {{$margin}}
                        @else
                        {{number_format($margin, 2, '.',',')}}%
                        @endif
                    </td>
                    {{-- <td class="text-center align-middle">
                        <button class="btn btn-sm btn-warning">Hilang</button>
                    </td> --}}
                </tr>
                @endif
                @endforeach
                @endforeach
                @endforeach
                @endforeach
                @endforeach
                @if (!$loop->last)
                <tr>
                    <td colspan="4" style="border: none; background-color:transparent; border-bottom-color:transparent">
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="13" class="text-end align-middle">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($sumTotalHargaBeli, 0 ,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($sumTotalHargaJual, 0 ,',','.')}}</th>
                    <th class="text-end align-middle"></th>
                </tr>
                <tr>
                    <th colspan="13" class="text-end align-middle">Estimasi Profit</th>
                    <th class="text-end align-middle" colspan="2">{{number_format($sumTotalHargaJual-$sumTotalHargaBeli,
                        0 ,',','.')}}</th>
                    <th class="text-end align-middle"></th>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $('#filter_barang_nama').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#karyawan_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#actionModal')
    });

    function editFun(data)
    {

        document.getElementById('harga').value = data.nf_harga;

        // document.getElementById('harga').value = data.stok_ppn.nf_harga;
        document.getElementById('editForm').action = `{{route('db.stok-ppn.store', ':id')}}`.replace(':id', data.id);
    }

    function actionFun(data)
    {
        var harga_beli_ppn = data.harga_beli + (data.harga_beli * {{$ppnRate}} / 100);
        // make harga_beli_ppn to number format
        var formatted_harga_beli_ppn = harga_beli_ppn.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

        document.getElementById('harga_beli_dpp_act').value = formatted_harga_beli_ppn;
        document.getElementById('stok_act').value = data.nf_stok;
        document.getElementById('stok_satuan').innerHTML = data.barang.satuan ? data.barang.satuan.nama : '-';
        document.getElementById('hilang_satuan').innerHTML = data.barang.satuan ? data.barang.satuan.nama : '-';

        document.getElementById('actionForm').action = `{{route('db.stok-hilang', ':id')}}`.replace(':id', data.id);
    }
    confirmAndSubmit("#actionForm", "Apakah anda yakin?");
    confirmAndSubmit("#editForm", "Apakah anda yakin untuk mengubah data ini?");

    function toggleNamaJabatan(id) {

        // check if input is readonly
        if ($('#nama_jabatan-'+id).attr('readonly')) {
            // remove readonly
            $('#nama_jabatan-'+id).removeAttr('readonly');
            // show button
            $('#buttonJabatan-'+id).removeAttr('hidden');
        } else {
            // add readonly
            $('#nama_jabatan-'+id).attr('readonly', true);
            // hide button
            $('#buttonJabatan-'+id).attr('hidden', true);
        }
    }

    $('.delete-form').submit(function(e){
        e.preventDefault();
        var formId = $(this).data('id');
        Swal.fire({
            title: 'Apakah Anda Yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, simpan!'
        }).then((result) => {
            if (result.isConfirmed) {
                $(`#deleteForm${formId}`).unbind('submit').submit();
                $('#spinner').show();
            }
        });
    });
</script>
@endpush
