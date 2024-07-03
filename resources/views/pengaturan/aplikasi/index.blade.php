@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>PENGATURAN APLIKASI</u></h1>
        </div>
    </div>
    @include('swal')
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
                    <th class="text-center align-middle">Nama Perusahaan</th>
                    <th class="text-center align-middle">Singkatan</th>
                    <th class="text-center align-middle">Nama Direktur</th>
                    <th class="text-center align-middle">Alamat</th>
                    <th class="text-center align-middle">Kode Pos</th>
                    <th class="text-center align-middle">Logo</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$loop->iteration}}</td>
                    <td class="text-start align-middle">{{$d->nama}}</td>
                    <td class="text-center align-middle">{{$d->singkatan}}</td>
                    <td class="text-center align-middle">{{$d->nama_direktur}}</td>
                    <td class="text-start align-middle">
                        <p>{{Str::limit($d->alamat, 40)}}</p>
                    </td>
                    <td class="text-center align-middle">{{$d->kode_pos}}</td>
                    <td class="text-center align-middle">
                    <img
                        src="{{asset('uploads/logo/'.$d->logo)}}"
                        class="img-fluid rounded-top"
                        alt="" width="70"
                    />
                    </td>
                    <td class="text-center align-middle">
                        <a href="{{route('pengaturan.aplikasi.edit', $d->id)}}" class="btn btn-warning btn-sm"><i class="fa fa-pencil"></i> Edit</a>
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
