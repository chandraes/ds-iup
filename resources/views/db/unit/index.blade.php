@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DIREKSI & STAFF</u></h1>
        </div>
    </div>
    @include('swal')
    @include('db.karyawan.create-jabatan')

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
                                src="{{asset('images/kategori.svg')}}" alt="dokumen" width="30"> Tambah Jabatan</a>
                    </td>
                    <td>
                        <a href="{{route('db.staff.create')}}" class="btn btn-outline-primary">
                            <img src=" {{asset('images/karyawan.svg')}}" alt="dokumen" width="30"> Tambah Staff</a>
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
                <th class="text-center align-middle">Nama</th>
                <th class="text-center align-middle">Panggilan</th>
                <th class="text-center align-middle">Jabatan</th>
                <th class="text-center align-middle">Informasi Bank</th>
                <th class="text-center align-middle">Status</th>
                <th class="text-center align-middle">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-start align-middle">{{$d->nama}}</td>
                    <td class="text-start align-middle">{{$d->nickname}}</td>
                    <td class="text-center align-middle">{{$d->jabatan->nama}}</td>
                    <td class="text-start align-middle">
                        <ul>
                            <li>Nama Rekening : {{$d->nama_rek}}</li>
                            <li>Nomor Rekening : {{$d->no_rek}}</li>
                            <li>Bank : {{$d->bank}}</li>
                        </ul>
                    </td>
                    <td class="text-center align-middle">
                        @if ($d->status == 1)
                        <h4><span class="badge bg-success text-white">Aktif</span></h4>
                        @elseif($d->status == 0)
                        <h4><span class="badge bg-danger text-white">Tidak Aktif</span></h4>
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        <a href="{{route('db.staff.edit', $d->id)}}" class="btn btn-warning"><i
                                class="fa fa-edit"></i></a>
                        <form action="{{route('db.staff.delete', $d->id)}}" method="post" class="d-inline delete-form" id="deleteForm{{$d->id}}" data-id="{{$d->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger" ><i
                                    class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
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

    $(document).ready(function() {
        $('#dataTable').DataTable({
            "paging": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

    } );


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
