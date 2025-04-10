@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KATEGORI KELOMPOK BARANG</u></h1>
        </div>
    </div>
    @include('swal')
    @include('db.kategori-barang.create-kategori')
    @include('db.kategori-barang.create')
    @include('db.kategori-barang.edit')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td>
                    <td class="text-center align-middle"><a href="#" data-bs-toggle="modal"
                            data-bs-target="#create-category"><img src="{{asset('images/kategori.svg')}}" alt="dokumen"
                                width="30"> Tambah Kelompok</a>
                    </td>
                    <td>
                        <a href="#" class="btn btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#createModal">
                            <img src=" {{asset('images/kelompok-barang.svg')}}" alt="dokumen" width="30"> Tambah Nama
                            Barang</a>
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
    <div class="filter">
        <form action="{{route('db.barang-kategori')}}" method="get">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="filter_barang_nama" id="filter_barang_nama" onchange="this.form.submit()">
                        <option value="" disabled selected>-- Pilih Nama Barang --</option>
                        @foreach ($barangNama as $b)
                        <option value="{{ $b->id }}" {{ request('filter_barang_nama') == $b->id ? 'selected' : '' }}>
                            {{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <a type="submit" class="btn btn-primary" href="{{url()->current()}}">Reset Filter</a>
                </div>
            </div>
        </form>
    </div>
    {{-- <input type="text" id="searchInput" placeholder="Search for names.." title="Type in a name"> --}}
    <div class="table-container">
        <table class="table table-bordered" id="dataTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Kelompok Barang</th>
                    <th class="text-center align-middle">Nama Barang</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $counter = 1; @endphp
                @foreach ($data as $d)
                @if ($d->barang_nama)
                @php $isFirst = true; @endphp
                @foreach ($d->barang_nama as $t)
                <tr>
                    @if ($isFirst)
                    <td class="text-center align-middle" rowspan="{{ $d->barang_nama_count }}">{{ $counter }}</td>
                    <td class="text-center align-middle" rowspan="{{ $d->barang_nama_count }}">{{ $d->nama }}</td>
                    @php $isFirst = false; @endphp
                    @endif
                    <td class="text-start align-middle">{{ $t->nama }}</td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editModal"
                            onclick="editFun({{ $t }})"><i class="fa fa-edit"></i></a>
                        <form action="{{ route('db.barang-kategori.delete', $t->id) }}" method="post"
                            class="d-inline delete-form" id="deleteForm{{ $t->id }}" data-id="{{ $t->id }}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @php $counter++; @endphp
                @endif
                @endforeach
            </tbody>

        </table>
    </div>

</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/datatable/datatables.min.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $('#filter_barang_nama').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });
    
    function editFun(data)
    {
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_barang_kategori_id').value = data.barang_kategori_id;
        document.getElementById('editForm').action = `{{route('db.barang-kategori.nama-update', ':id')}}`.replace(':id', data.id);
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

    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');

        searchInput.addEventListener('keyup', function() {
            const filter = searchInput.value.toUpperCase();
            const table = document.getElementById("dataTable");
            const tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (let i = 0; i < tr.length; i++) {
                // Target the 'Nama Barang' and 'Kelompok Barang' columns
                let tdNamaBarang = tr[i].getElementsByTagName("td")[2];
                let tdKelompokBarang = tr[i].getElementsByTagName("td")[1];
                if (tdNamaBarang || tdKelompokBarang) {
                    let txtValueNamaBarang = tdNamaBarang.textContent || tdNamaBarang.innerText;
                    let txtValueKelompokBarang = tdKelompokBarang.textContent || tdKelompokBarang.innerText;
                    if (txtValueNamaBarang.toUpperCase().indexOf(filter) > -1 || txtValueKelompokBarang.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        });
    });
</script>
@endpush
