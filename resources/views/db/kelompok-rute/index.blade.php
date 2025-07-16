@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KELOMPOK RUTE PENGIRIMAN</u></h1>
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
                                src="{{asset('images/kelompok-rute.svg')}}" width="30"> Tambah Kelompok Rute</a>

                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('swal')
@include('db.kelompok-rute.create')
@include('db.kelompok-rute.edit')
<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">NO</th>
                <th class="text-center align-middle">NAMA KELOMPOK</th>
                <th class="text-center align-middle">KECAMATAN</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-start align-middle">{{$d->nama}}</td>
                <td class="text-start align-middle">
                    @if ($d->details->count() > 0)
                    @foreach ($d->details as $kecamatan)
                    {{$kecamatan->wilayah->nama_wilayah}}
                    @if (!$loop->last), @endif
                    @endforeach

                    @endif
                </td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal"
                            data-bs-target="#editInvestor" onclick="editInvestor({{$d}}, {{$d->id}})"><i
                                class="fa fa-edit"></i></button>
                          <form action="{{ route('db.kelompok-rute.delete', $d->id) }}" method="post"
                            class="d-inline delete-form" id="deleteForm{{ $d->id }}" data-id="{{ $d->id }}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i></button>
                        </form>
                    </div>

                </td>
            </tr>
            @endforeach
        </tbody>

    </table>
</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" /> --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>

    function editInvestor(data, id) {


        document.getElementById('edit_wilayah_id').innerHTML = '';

        data.details.forEach(function(detail) {
            var option = new Option(detail.wilayah.nama_wilayah, detail.wilayah.id, true, true);
            document.getElementById('edit_wilayah_id').append(option);
        });

        $('#edit_wilayah_id').select2({
            placeholder: 'Pilih Kecamatan',
            minimumInputLength: 3,
            width: '100%',
            allowClear: true,
            theme: 'bootstrap-5',
            dropdownParent: $('#editInvestor').parent(),
            ajax: {
                url: '{{ route("universal.search-kecamatan") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term, // search term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama_wilayah
                            };
                        })
                    };
                },
                cache: true
            }
        });
        $('#edit_wilayah_id').val(data.details.map(detail => detail.wilayah.id)).trigger('change');
        document.getElementById('editForm').reset();

        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('editInvestorTitle').innerText = 'Edit Kelompok Rute ' + data.nama;
        document.getElementById('editForm').action = '/db/kelompok-rute/update/' + id;
    }

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        scrollY: "550px",
    });

    $('#wilayah_id').select2({
            placeholder: 'Pilih Kecamatan',
            minimumInputLength: 3,
            width: '100%',
            allowClear: true,
            theme: 'bootstrap-5',
            dropdownParent: $('#createInvestor').parent(),
            ajax: {
                url: '{{ route("universal.search-kecamatan") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term, // search term
                    };
                },
                processResults: function(data) {
                    console.log(data);
                    return {
                        results: data.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.nama_wilayah
                            };
                        })
                    };
                },
                cache: true
            }
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
