@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KATEGORI MERK UNIT</u></h1>
        </div>
    </div>
    @include('swal')
    @include('db.unit.create-unit')
    @include('db.unit.create')
    @include('db.unit.edit')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#create-category"><img
                                src="{{asset('images/kategori.svg')}}" alt="dokumen" width="30"> Tambah Unit</a>
                    </td>
                    <td>
                        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#createModal">
                            <img src=" {{asset('images/unit.svg')}}" alt="dokumen" width="30"> Tambah Type</a>
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
<div class="container mt-5 table-responsive ">
    <div class="table-container">
        <table class="table table-bordered" id="dataTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Unit</th>
                    <th class="text-center align-middle">Tipe</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @foreach ($data as $d)
                @if ($d->types)
                @php
                $rowspan = $d->types->count();
                @endphp
                @foreach ($d->types as $t)
                <tr>
                    @if ($loop->first)
                    <td class="text-center align-middle" rowspan="{{$rowspan}}">{{$counter}}</td>
                    <td class="text-center align-middle" rowspan="{{$rowspan}}">{{$d->nama}}</td>
                    <td class="text-start align-middle">{{$t->nama}}</td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                            onclick="editFun({{$t}})"><i class="fa fa-edit"></i> Edit</a>
                        <form action="{{route('db.unit.type.delete', $t->id)}}" method="post" class="d-inline delete-form"
                            id="deleteForm{{$t->id}}" data-id="{{$t->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                    @php $counter++; @endphp
                    @else
                    <td class="text-start align-middle">{{$t->nama}}</td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                            onclick="editFun({{$t}})"><i class="fa fa-edit"></i> Edit</a>
                        <form action="{{route('db.unit.type.delete', $t->id)}}" method="post" class="d-inline delete-form"
                            id="deleteForm{{$t->id}}" data-id="{{$t->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                    @endif
                </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="border: none; background-color:transparent; border-bottom-color:transparent"></td>
                </tr>
                @endif

                @endforeach
            </tbody>

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
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_barang_unit_id').value = data.barang_unit_id;
        document.getElementById('editForm').action = `{{route('db.unit.type.update', ':id')}}`.replace(':id', data.id);
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
