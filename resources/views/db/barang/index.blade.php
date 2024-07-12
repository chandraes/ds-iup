@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KATEGORI BARANG</u></h1>
        </div>
    </div>
    @include('swal')
    @include('db.barang.create-kategori')
    @include('db.barang.create')
    @include('db.barang.edit')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td class="text-center align-middle">
                    <td class="text-center align-middle"><a href="#" data-bs-toggle="modal" data-bs-target="#create-category"><img
                                src="{{asset('images/kategori.svg')}}" alt="dokumen" width="30"> Tambah Kategori</a>
                    </td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#createModal">
                            <img src=" {{asset('images/barang.svg')}}" alt="dokumen" width="30"> Tambah Barang</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<style>
    .table-container {
        height: 500px;
        overflow-y: auto;
    }
    thead th {
        position: sticky;
        top: 0;
        background: white;
        z-index: 1;
    }
</style>
<div class="container mt-3 table-responsive ">
    <form method="GET" action="{{ route('db.barang') }}">
        <div class="row">
            <div class="col-md-4">
                <label for="unit">Unit</label>
                <select name="unit" id="unit" class="form-control">
                    <option value="">Semua Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit') == $unit->id ? 'selected' : '' }}>
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
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>
                            {{ $type->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="kategori">Kategori</label>
                <select name="kategori" id="kategori" class="form-control">
                    <option value="">Semua Kategori</option>
                    @foreach($kategori as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>
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
                <a href="{{ route('db.barang') }}" class="btn btn-danger">Reset Filter</a>
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
                    <th class="text-center align-middle">Kategori</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>
                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $number = 1; @endphp
                @foreach ($units as $unit)
                    @php $unitDisplayed = false; @endphp
                    @foreach ($unit->types as $typeIndex => $type)
                        @php $typeDisplayed = false; @endphp
                        @foreach ($type->groupedBarangs as $kategoriNama => $barangs)
                            @php $kategoriDisplayed = false; @endphp
                            @foreach ($barangs as $barangIndex => $barang)
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
                                        <td class="text-center align-middle" rowspan="{{ $barangs->kategoriRowspan }}">{{ $kategoriNama }}</td>
                                        @php $kategoriDisplayed = true; @endphp
                                    @endif
                                    <td class="text-start align-middle">{{ $barang->nama }}</td>
                                    <td class="text-center align-middle">{{ $barang->kode }}</td>
                                    <td class="text-center align-middle">{{ $barang->merk }}</td>
                                    <td class="text-center align-middle">
                                        <a href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                                            onclick="editFun({{ $barang }}, {{$type->id}}, {{$unit->id}})"><i class="fa fa-edit"></i></a>
                                        <form action="{{route('db.barang.delete', $barang->id)}}" method="post" class="d-inline delete-form"
                                            id="deleteForm{{$barang->id}}" data-id="{{$barang->id}}">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>

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
    function editFun(data, type, unit)
    {

        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_barang_unit_id').value = unit;
        document.getElementById('edit_barang_type_id').value = type;
        document.getElementById('edit_barang_kategori_id').value = data.barang_kategori_id;
        document.getElementById('edit_kode').value = data.kode;
        document.getElementById('edit_merk').value = data.merk;

        document.getElementById('editForm').action = `{{route('db.barang.update', ':id')}}`.replace(':id', data.id);
    }

    confirmAndSubmit("#editForm", "Apakah anda yakin untuk mengubah data ini?");
    confirmAndSubmit("#createForm", "Apakah anda yakin untuk menambah data ini?");

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