@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PENGGUNA</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pengaturan')}}"><img src="{{asset('images/pengaturan.svg')}}" alt="PENGATURAN"
                                width="30"> Pengaturan</a></td>
                    <td><a href="#"  data-bs-toggle="modal" data-bs-target="#createUser"><img src="{{asset('images/pengguna.svg')}}"
                                width="30"> Tambah User</a>
                        @include('pengaturan.pengguna.create')
                    </td>

                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container mt-5 table-responsive">
    @if ($errors->any())
    <div class="container">
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Whoops!</strong> There were some problems with your input.
            <ul>
                @foreach ($errors->all() as $error)
                <li><strong>{{ $error }}</strong></li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">No</td>
                <th class="text-center align-middle">Name</td>
                <th class="text-center align-middle">E-Mail</td>
                <th class="text-center align-middle">Username</td>
                <th class="text-center align-middle">Action</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-center align-middle">{{$d->name}}</td>
                    <td class="text-center align-middle">{{$d->email}}</td>
                    <td class="text-center align-middle">{{$d->username}}</td>
                    <td class="text-center align-middle">
                        <button class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#editUser-{{$d->id}}">Edit</button>
                        <form action="{{route('pengaturan.akun.delete', $d->id)}}" method="post" id="deleteForm-{{$d->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger m-2"><i
                                    class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @include('pengaturan.pengguna.edit')
                <script>
                     $('#deleteForm-{{$d->id}}').submit(function(e){
                            e.preventDefault();
                            Swal.fire({
                                title: 'Apakah data yakin untuk menghapus data ini?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Ya, hapus!'
                                }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#spinner').show();
                                    this.submit();
                                }
                            })
                        });
                </script>
            @endforeach
        </tbody>
    </table>
</div>

@include('pengaturan.pengguna.edit')
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>


    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        scrollY: "550px",
    });

    $('#createForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });


    $('#editForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        // Ketika modal ditutup, kosongkan formulir dan segarkan halaman
        $('#createUser').on('hidden.bs.modal', function () {
            // Hapus nilai dari input
            $('#createForm input').val('');
            // Hilangkan pesan kesalahan
            $('.invalid-feedback').hide();
            // Segarkan halaman
            location.reload();
        });

        $('#editUser').on('hidden.bs.modal', function () {
            // Hapus nilai dari input
            $('#editForm input').val('');
            // Hilangkan pesan kesalahan
            $('.invalid-feedback').hide();
            // Segarkan halaman
            location.reload();
        });

</script>
@endpush
