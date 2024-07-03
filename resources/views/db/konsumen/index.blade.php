@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BIODATA KONSUMEN</u></h1>
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
                                src="{{asset('images/customer.svg')}}" width="30"> Tambah Konsumen</a>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('swal')
@include('db.konsumen.create')
@include('db.konsumen.edit')
<div class="container-fluid mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">NO</th>
                <th class="text-center align-middle">KODE</th>
                <th class="text-center align-middle">NAMA</th>
                <th class="text-center align-middle">CP</th>
                <th class="text-center align-middle">NPWP</th>
                <th class="text-center align-middle">KOTA</th>
                <th class="text-center align-middle">Sistem<br>Pembayaran</th>
                <th class="text-center align-middle">Limit<br>Plafon</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{$d->full_kode}}</td>
                <td class="text-center align-middle">{{$d->nama}}</td>
                <td class="text-start align-middle">
                    <ul>
                        <li>CP : {{$d->cp}}</li>
                        <li>No. HP : {{$d->no_hp}}</li>
                        <li>No. Kantor : {{$d->no_kantor}}</li>
                    </ul>
                </td>
                <td class="text-center align-middle">{{$d->npwp}}</td>
                <td class="text-center align-middle">
                    {{$d->kota}}
                </td>
                <td class="text-center align-middle">
                    {{$d->sistem_pembayaran}} <br>
                    @if ($d->pembayaran == 2)
                    ({{$d->tempo_hari}} Hari)
                    @endif
                </td>
                <td class="text-end align-middle">{{$d->nf_plafon}}</td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal"
                            data-bs-target="#editInvestor" onclick="editInvestor({{$d}}, {{$d->id}})"><i
                                class="fa fa-edit"></i></button>
                        <form action="{{route('db.konsumen.delete', $d)}}" method="post" id="deleteForm-{{$d->id}}">
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
        document.getElementById('edit_no_hp').value = data.no_hp;
        document.getElementById('edit_no_kantor').value = data.no_kantor;
        document.getElementById('edit_cp').value = data.cp;
        document.getElementById('edit_npwp').value = data.npwp;
        document.getElementById('edit_pembayaran').value = data.pembayaran;
        document.getElementById('edit_plafon').value = data.nf_plafon;
        document.getElementById('edit_tempo_hari').value = data.tempo_hari;
        document.getElementById('edit_alamat').value = data.alamat;

        document.getElementById('editForm').action = '/db/konsumen/' + id + '/update';
    }

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        scrollY: "550px",
    });

    var no_hp = new Cleave('#no_hp', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

     var no_kantor = new Cleave('#no_kantor', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    var edit_no_kantor = new Cleave('#edit_no_kantor', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });


    var plafon = new Cleave('#plafon', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.'
    });

    var edit_plafon = new Cleave('#edit_plafon', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.'
    });

    var edit_no_hp = new Cleave('#edit_no_hp', {
        delimiter: '-',
        blocks: [4, 4, 8]
    });

    var npwp = new Cleave('#npwp', {
        delimiters: ['.', '.', '.', '-','.','.'],
        blocks: [2, 3, 3, 1, 3, 3],
    });

    var edit_npwp = new Cleave('#edit_npwp', {
        delimiters: ['.', '.', '.', '-','.','.'],
        blocks: [2, 3, 3, 1, 3, 3],
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

</script>
@endpush
