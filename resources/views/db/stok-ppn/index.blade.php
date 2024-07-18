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

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('db')}}"><img
                                src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
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
    <form method="GET" action="{{route('db.stok-ppn')}}">
        <div class="row">
            <div class="col-md-4">
                <label for="unit">Unit</label>
                <select name="unit" id="unit" class="form-control">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit')==$unit->id ? 'selected' : '' }}>
                        {{ $unit->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="type">Type</label>
                <select name="type" id="type" class="form-control">
                    <option value="">Semua Type</option>
                    @foreach($data as $type)
                    <option value="{{ $type->id }}" {{ request('type')==$type->id ? 'selected' : '' }}>
                        {{ $type->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="kategori">Kelompok Barang</label>
                <select name="kategori" id="kategori" class="form-control">
                    <option value="">Semua Kelompok</option>
                    @foreach($kategori as $kat)
                    <option value="{{ $kat->id }}" {{ request('kategori')==$kat->id ? 'selected' : '' }}>
                        {{ $kat->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary">Filter</button>
                {{-- reset filter button --}}
                <a href="{{route('db.stok-ppn')}}" class="btn btn-danger">Reset Filter</a>
            </div>
        </div>
    </form>
    <div class="table-container mt-4">
        <table class="table table-bordered" id="dataTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Unit</th>
                    <th class="text-center align-middle">Tipe</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle">Harga DPP<br>Beli Barang</th>
                    <th class="text-center align-middle">Harga+PPN<br>Beli Barang</th>
                    <th class="text-center align-middle" style="width: 20px">Harga DPP<br>Jual Barang</th>
                    <th class="text-center align-middle">Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Stok<br>Barang</th>
                    <th class="text-center align-middle">Total Harga+PPN<br>Beli Barang</th>
                    <th class="text-center align-middle">Total Harga+PPN<br>Jual Barang</th>
                    <th class="text-center align-middle">Margin<br>Profit</th>

                </tr>
            </thead>
            <tbody>
                @php $number = 1; $sumTotalHargaBeli = 0; $sumTotalHargaJual= 0; @endphp
                @foreach ($units as $unit)
                    @php $unitDisplayed = false; @endphp
                    @foreach ($unit->types as $type)
                        @php $typeDisplayed = false; @endphp
                        @foreach ($type->groupedBarangs as $kategoriNama => $barangs)
                            @php $kategoriDisplayed = false; @endphp
                            @foreach ($barangs->groupBy('barang_nama.nama') as $namaBarang => $namaBarangs)
                                @php $namaDisplayed = false; @endphp
                                @foreach ($namaBarangs as $barang)
                                    @php $stokDisplayed = false; @endphp
                                    @foreach ($barang->stok_harga as $stokHarga)
                                        @php
                                            $totalHargaBeli = ($stokHarga->harga_beli+($stokHarga->harga_beli*$ppnRate/100))*$stokHarga->stok;
                                            $totalHargaJual = ($stokHarga->harga+($stokHarga->harga*$ppnRate/100))*$stokHarga->stok;
                                            $sumTotalHargaJual += $totalHargaJual;
                                            $sumTotalHargaBeli += $totalHargaBeli;
                                            $margin = ($stokHarga->harga - $stokHarga->harga_beli)/$stokHarga->harga_beli*100;
                                        @endphp
                                        <tr>
                                            @if (!$unitDisplayed)
                                                <td class="text-center align-middle" rowspan="{{ $unit->unitRowspan }}">{{ $number++ }}</td>
                                                <td class="text-center align-middle" rowspan="{{ $unit->unitRowspan }}">{{ $unit->nama }}</td>
                                                @php $unitDisplayed = true; @endphp
                                            @endif
                                            @if (!$typeDisplayed)
                                                <td class="text-center align-middle" rowspan="{{ $type->typeRowspan }}">{{ $type->nama }}</td>
                                                @php $typeDisplayed = true; @endphp
                                            @endif
                                            @if (!$kategoriDisplayed)
                                                <td class="text-center align-middle" rowspan="{{ $barang->kategoriRowspan }}">{{ $kategoriNama }}</td>
                                                @php $kategoriDisplayed = true; @endphp
                                            @endif
                                            @if (!$namaDisplayed)
                                                <td class="text-center align-middle" rowspan="{{ $barang->namaRowspan }}">{{ $namaBarang }}</td>
                                                @php $namaDisplayed = true; @endphp
                                            @endif
                                            @if (!$stokDisplayed)
                                                <td class="text-center align-middle" rowspan="{{ $barang->stokPpnRowspan }}">{{ $barang->kode }}</td>
                                                <td class="text-center align-middle" rowspan="{{ $barang->stokPpnRowspan }}">{{ $barang->merk }}</td>
                                                @php $stokDisplayed = true; @endphp
                                            @endif
                                            <td class="text-end align-middle">{{ $stokHarga->nf_harga_beli }}</td>
                                            <td class="text-end align-middle">
                                                {{ number_format($stokHarga->harga_beli+($stokHarga->harga_beli*$ppnRate/100), 0, ',','.') }}
                                            </td>
                                            <td class="text-end align-middle">
                                                <div class="row mx-3">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal" onclick="editFun({{$stokHarga}})">{{ $stokHarga->nf_harga }}</a>
                                                </div>
                                            </td>
                                            <td class="text-end align-middle">
                                                {{ number_format($stokHarga->harga+($stokHarga->harga*$ppnRate/100), 0, ',','.') }}
                                            </td>
                                            <td class="text-center align-middle">{{ $stokHarga->nf_stok }}</td>
                                            <td class="text-end align-middle">
                                                {{ number_format($totalHargaBeli, 0, ',','.') }}
                                            </td>
                                            <td class="text-end align-middle">
                                                {{ number_format($totalHargaJual, 0, ',','.') }}
                                            </td>
                                            <td class="text-end align-middle @if ($margin < 10)
                                                'table-danger'
                                            @endif">
                                                {{ number_format($margin, 2) }}%
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="12" class="text-end align-middle">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($sumTotalHargaBeli, 0 ,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($sumTotalHargaJual, 0 ,',','.')}}</th>
                    <th class="text-end align-middle"></th>
                </tr>
                <tr>
                    <th colspan="12" class="text-end align-middle">Estimasi Profit</th>
                    <th class="text-end align-middle" colspan="2">{{number_format($sumTotalHargaJual-$sumTotalHargaBeli, 0 ,',','.')}}</th>
                    <th class="text-end align-middle"></th>
                </tr>
            </tfoot>
        </table>
    </div>

</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    function editFun(data)
    {

        document.getElementById('harga').value = data.nf_harga;

        // document.getElementById('harga').value = data.stok_ppn.nf_harga;
        document.getElementById('editForm').action = `{{route('db.stok-ppn.store', ':id')}}`.replace(':id', data.id);
    }

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
