@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BIODATA SUPPLIER</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#createInvestor"><img
                                src="{{asset('images/supplier.svg')}}" width="30"> Tambah Supplier</a>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('db.supplier.create')
@include('db.supplier.edit')
<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">NO</th>
                <th class="text-center align-middle">KODE</th>
                <th class="text-center align-middle">NAMA<br>PERUSAHAAN</th>
                <th class="text-center align-middle">CONTACT<br>PERSON</th>
                <th class="text-center align-middle">NO HP</th>
                <th class="text-center align-middle">INFO REKENING</th>
                <th class="text-center align-middle">KOTA</th>
                <th class="text-center align-middle">STATUS</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->kode_supplier}}</td>
                <td class="text-center align-middle">{{$d->nama}}</td>
                <td class="text-center align-middle">{{$d->cp}}</td>
                <td class="text-center align-middle">{{$d->no_hp}}</td>
                <td class="text-start align-middle">
                    <div>
                        <ul>
                            <li>Atas Nama :  {{$d->nama_rek}}</li>
                            <li>No. Rek :  {{$d->no_rek}}</li>
                            <li>Bank :  {{$d->bank}}</li>
                        </ul>
                    </div>
                </td>
                <td class="text-center align-middle">{{$d->kota}}</td>
                <td class="text-center align-middle">
                    @if ($d->status == 1)
                    <span class="badge bg-success">Aktif</span>
                    @else
                    <span class="badge bg-danger">Tidak Aktif</span>
                    @endif
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal"
                            data-bs-target="#editInvestor" onclick="editInvestor({{$d}}, {{$d->id}})"><i
                                class="fa fa-edit"></i></button>
                        <form action="{{route('db.supplier.delete', $d)}}" method="post" id="deleteForm-{{$d->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>

                </td>
            </tr>
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

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    function editInvestor(data, id) {
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_cp').value = data.cp;
        document.getElementById('edit_no_hp').value = data.no_hp;
        document.getElementById('edit_kota').value = data.kota;
        document.getElementById('edit_alamat').value = data.alamat;
        document.getElementById('edit_status').value = data.status;
        document.getElementById('edit_nama_rek').value = data.nama_rek;
        document.getElementById('edit_no_rek').value = data.no_rek;
        document.getElementById('edit_bank').value = data.bank;
        // Populate other fields...
        document.getElementById('editForm').action = '/db/supplier/update/' + id;
    }

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        scrollY: "550px",
    });

    var no_rek = new Cleave('#no_rek', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    var no_hp = new Cleave('#no_hp', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    var edit_no_hp = new Cleave('#edit_no_hp', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    var edit_no_rek = new Cleave('#edit_no_rek', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    confirmAndSubmit('#createForm', "Apakah anda yakin?");
    confirmAndSubmit('#editForm', "Apakah anda yakin?");

</script>
@endpush
