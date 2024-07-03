@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>PENGATURAN BATASAN</u></h1>
        </div>
    </div>
    @include('swal')
    @include('pengaturan.batasan.edit')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-4">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pengaturan')}}"><img src="{{asset('images/pengaturan.svg')}}" alt="dokumen"
                                width="30"> Pengaturan</a></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">PENGGUNAAN</th>
                    <th class="text-center align-middle">NILAI</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-center align-middle">{{$d->untuk}}</td>
                    <td class="text-end align-middle">
                        <div class="row px-5">
                            <button class="btn btn-outline-dark" data-bs-toggle="modal"
                                data-bs-target="#editModal" onclick="edit({{$d}}, {{$d->id}})">{{$d->nf_nilai}}</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('js')
<script>
   function edit(data, id)
   {
        document.getElementById('nilai').value = data.nf_nilai;
        // Populate other fields...
        document.getElementById('editForm').action = '/pengaturan/batasan/update/' + id;

   }

   confirmAndSubmit('#editForm', "Apakah anda yakin?");
</script>
@endpush
