@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KATEGORI INVENTARIS</u></h1>
        </div>
    </div>
    @include('swal')
    @include('db.kategori-inventaris.create-kategori')
    @include('db.kategori-inventaris.create')
    @include('db.kategori-inventaris.edit')
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
                                src="{{asset('images/kategori.svg')}}" alt="dokumen" width="30"> Tambah Kategori</a>
                    </td>
                    <td>
                        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#createCustomer">
                            <img src=" {{asset('images/kategori-inventaris.svg')}}" alt="dokumen" width="30"> Tambah Jenis</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container mt-5 table-responsive ">
    <table class="table table-bordered" id="dataTable">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle" style="width: 15px">No</th>
                <th class="text-center align-middle">Kategori</th>
                <th class="text-center align-middle">Jenis</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                @foreach ($d->jenis as $index => $j)
                    <tr>
                        @if ($index === 0)
                            <td class="text-center align-middle" rowspan="{{ count($d->jenis) }}">{{ $loop->parent->iteration }}</td>
                            <td class="text-center align-middle" rowspan="{{ count($d->jenis) }}">{{ $d->nama }}</td>
                        @endif
                        <td class="text-center align-middle">{{ $j->nama }}</td>
                        <td class="text-center align-middle">
                            <div class="d-flex justify-content-center">
                                <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#editProject" onclick="editProject({{ $j }}, {{ $j->id }})">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <form action="{{ route('db.jenis-inventaris.delete', $j->id) }}" method="post" id="deleteForm{{ $j->id }}" class="delete-form" data-id="{{ $j->id }}">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>

        </tfoot>
    </table>
</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>


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

    function editProject(data, id) {
        document.getElementById('edit_kategori_id').value = data.kategori_id;
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('editForm').action = '/db/kategori-inventaris/jenis/update/' + id;
    };


    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

    } );

    confirmAndSubmit('#masukForm', 'Apakah Data yang anda masukan sudah benar?');
    confirmAndSubmit('#editForm', 'Apakah Data yang anda masukan sudah benar?');


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
