@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>BIODATA KONSUMEN</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('db')}}"><img src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalSalesArea"><img
                                src="{{asset('images/area.svg')}}" width="30"> Sales Area</a>
                    </td>
                    <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalKodeToko"><img
                        src="{{asset('images/kode-toko.svg')}}" width="30"> Kode Toko</a>
            </td>
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
@include('db.konsumen.sales-area')
@include('db.konsumen.kode-toko')

<div class="container-fluid mt-5 table-responsive">

    {{-- buat filter untuk sales area --}}
    <form method="GET" action="{{ url()->current() }}">
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="filterSalesArea" class="form-label">Kode Toko</label>

                <select id="filterKodeToko" name="kode_toko" class="form-select" onchange="this.form.submit()">
                    <option value="" selected>-- Semua Kode Toko --</option>
                    @foreach ($kode_toko as $k)
                    <option value="{{ $k->id }}" {{ request('kode_toko')==$k->id ? 'selected' : '' }}>
                        {{ $k->kode }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterSalesArea" class="form-label">Sales Area</label>

                <select id="filterSalesArea" name="area" class="form-select" onchange="this.form.submit()">
                    <option value="" selected>-- Semua Sales Area --</option>
                    @foreach ($sales_area as $salesArea)
                    <option value="{{ $salesArea->id }}" {{ request('area')==$salesArea->id ? 'selected' : '' }}>
                        {{ $salesArea->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="filterKecamatan" class="form-label">Kecamatan</label>
                    <select id="filterKecamatan" name="kecamatan" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kecamatan --</option>
                        @foreach ($kecamatan_filter as $kec)
                            <option value="{{ $kec->id }}" {{ request('kecamatan') == $kec->id ? 'selected' : '' }}>
                                {{ $kec->nama_wilayah }}
                            </option>
                        @endforeach
                    </select>
            </div>
            <div class="col-md-2">
                <label for="filterKecamatan" class="form-label">Status</label>
                    <select id="filterStatus" name="status" class="form-select" onchange="this.form.submit()">
                        <option value="1" {{ request()->has('status') && request('status') == 1 ? 'selected' : 'selected' }}>Aktif</option>
                        <option value="0" {{ request()->has('status') && request('status') == 0 ? 'selected' : '' }}>Non Aktif</option>
                    </select>
            </div>
            <div class="col-md-1 mt-4">
                <div class="row">
                    <a href="{{ url()->current() }}" class="btn btn-secondary mt-2">Reset</a>
                </div>

            </div>
        </div>
    </form>
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle">KODE</th>
                <th class="text-center align-middle">KODE <br>TOKO</th>
                <th class="text-center align-middle">NAMA</th>
                <th class="text-center align-middle">CP</th>
                <th class="text-center align-middle">NPWP</th>
                <th class="text-center align-middle">Sales Area</th>
                <th class="text-center align-middle">Provinsi</th>
                <th class="text-center align-middle">Kab/Kota</th>
                <th class="text-center align-middle">Kecamatan</th>
                <th class="text-center align-middle">Alamat</th>
                @if (request()->has('status') && request('status') == 0)
                <th class="text-center align-middle">Alasan<br>Nonaktif</th>
                @else
                <th class="text-center align-middle">Sistem<br>Pembayaran</th>
                <th class="text-center align-middle">Limit<br>Plafon</th>
                @endif

                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        @php
        $noHpCounts = $data->pluck('no_hp')->countBy();
        $namaCounts = $data->pluck('nama')->countBy();
        @endphp

        <tbody>
            @foreach ($data as $d)
            @php
            $isDuplicate = $noHpCounts[$d->no_hp] > 1;
            $isDuplicateNama = $namaCounts[$d->nama] > 1;
            @endphp
            <tr>

                <td class="text-center align-middle">{{$d->full_kode}}</td>
                <td class="text-center align-middle">{{$d->kode_toko ? $d->kode_toko->kode : ''}}</td>
                <td class="text-center align-middle {{$isDuplicateNama ? 'text-danger' : ''}}">{{$d->nama}}</td>
                <td class="text-start align-middle">
                    @php
                    $hasSpace = strpos($d->no_hp, ' ') !== false;
                    @endphp
                    <ul>
                        <li>CP : {{$d->cp}} <br></li>
                        <li class="{{ $isDuplicate || $hasSpace ? 'text-danger' : '' }}">No.HP : {{$d->no_hp}} <br></li>
                        <li>No.Kantor : {{$d->no_kantor}} <br></li>
                    </ul>
                </td>
                <td class="text-center align-middle">{{$d->npwp}}</td>
                <td class="text-center align-middle">{{$d->karyawan ? $d->karyawan->nama : ''}}</td>
                <td class="text-start align-middle">
                    {{$d->provinsi ? $d->provinsi->nama_wilayah : ''}}
                </td>
                <td class="text-start align-middle">
                    {{$d->kabupaten_kota ? $d->kabupaten_kota->nama_wilayah : ''}}
                </td>
                <td class="text-start align-middle">
                    {{$d->kecamatan ? $d->kecamatan->nama_wilayah : ''}}
                </td>
                <td class="text-start align-middle">
                    {{$d->alamat}}
                </td>
                @if (request()->has('status') && request('status') == 0)
                <td class="text-start align-middle">
                    {{$d->alasan}}
                </td>
                @else
                <td class="text-center align-middle">
                    {{$d->sistem_pembayaran}} <br>
                    @if ($d->pembayaran == 2)
                    ({{$d->tempo_hari}} Hari)
                    @endif
                </td>
                <td class="text-end align-middle">{{$d->nf_plafon}}</td>
                @endif

                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal"
                            data-bs-target="#editInvestor" onclick="editInvestor({{$d}}, {{$d->id}})"><i
                                class="fa fa-edit"></i></button>
                        <form action="{{route('db.konsumen.delete', $d)}}" method="post" id="deleteForm-{{$d->id}}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger m-2"><i class="fa fa-{{request()->has('status') && request('status') == 0 ? 'refresh' : 'power-off' }}"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            @if (request()->has('status') && request('status') == 0)
            <script>
                 $('#deleteForm-{{$d->id}}').submit(function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah data yakin untuk mengaktifkan data ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, aktifkan kembali!'
                        }).then((result) => {
                        if (result.isConfirmed) {
                            $('#spinner').show();
                            this.submit();
                        }
                    })
                });
            </script>
            @else
            <script>
                $('#deleteForm-{{$d->id}}').submit(function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah data yakin untuk menonaktifkan konsumen ini?',
                        icon: 'warning',
                        input: 'text',
                        inputPlaceholder: 'Masukkan alasan',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Anda harus memasukkan alasan!'
                            }
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!'
                        }).then((result) => {
                        if (result.isConfirmed) {
                            var status =1;
                            $('#deleteForm-{{$d->id}}').append($('<input>').attr({
                                type: 'hidden',
                                name: 'status',
                                value: status
                            }));
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'alasan',
                                value: result.value
                            }).appendTo('#deleteForm-{{$d->id}}');
                            $('#spinner').show();
                            this.submit();
                        }
                    })
                });
            </script>
            @endif
            @endforeach
        </tbody>
    </table>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
{{-- <script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script> --}}
<script>

</script>
<script>
    $('#provinsi_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#createInvestor'),
        });
        $('#kabupaten_kota_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#createInvestor'),
        });

        $('#kecamatan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#createInvestor'),
        });

        $('#edit_karyawan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editInvestor'),
        });

        $('#edit_kode_toko_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editInvestor'),
        });

        $('#filterSalesArea').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#filterKodeToko').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#filterKecamatan').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

   function editInvestor(data, id) {
        $('#edit_kabupaten_kota_id').empty();
        $('#edit_kecamatan_id').empty();
        document.getElementById('edit_nama').value = data.nama;
        document.getElementById('edit_kode_toko_id').value = data.kode_toko_id;
        document.getElementById('edit_no_hp').value = data.no_hp;
        document.getElementById('edit_no_kantor').value = data.no_kantor;
        document.getElementById('edit_cp').value = data.cp;
        document.getElementById('edit_npwp').value = data.npwp;
        document.getElementById('edit_pembayaran').value = data.pembayaran;
        document.getElementById('edit_plafon').value = data.nf_plafon;
        document.getElementById('edit_tempo_hari').value = data.tempo_hari;
        document.getElementById('edit_alamat').value = data.alamat;
        document.getElementById('edit_karyawan_id').value = data.karyawan_id;


        if (data.provinsi_id !== null) {
            document.getElementById('edit_provinsi_id').value = data.provinsi_id;
            getEditKabKota(data.kabupaten_kota_id, data.kecamatan_id);
        } else {
            getEditKabKota();
        }

        document.getElementById('editForm').action = '/db/konsumen/' + id + '/update';

        $('#edit_provinsi_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editInvestor'),
        });


        $('#edit_karyawan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#editInvestor'),
        });

    }

    function getEditKabKota(selectedKabupatenKotaId, selectedKecamatanId) {
        var provinsi = document.getElementById('edit_provinsi_id').value;
        // console.log(provinsi);
        $('#edit_kabupaten_kota_id').empty();
        $('#edit_kabupaten_kota_id').append('<option value="" selected> -- Pilih Kabupaten / Kota -- </option>');
        $('#edit_kecamatan_id').empty();
        $('#edit_kecamatan_id').append('<option value="" selected> -- Pilih Kecamatan -- </option>');
        // ajax request to get-kab-kota
        $.ajax({
            url: '{{route('get-kab-kota')}}',
            type: 'GET',
            data: {
                provinsi: provinsi
            },
            success: function(data) {
                if (data.status === 'success') {
                    $.each(data.data, function(index, value){
                        var isSelected = value.id_wilayah == '116000' ? 'selected' : '';
                        $('#edit_kabupaten_kota_id').append('<option value="'+value.id+'" '+isSelected+'>'+value.nama_wilayah+'</option>');
                    });

                    $('#edit_kabupaten_kota_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('#editInvestor'),
                    });

                    if ($('#edit_kabupaten_kota_id').val()) {
                        getEditKecamatan();
                    }

                    if (selectedKabupatenKotaId) {
                        $('#edit_kabupaten_kota_id').val(selectedKabupatenKotaId).trigger('change');
                        getEditKecamatan(selectedKecamatanId);
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: textStatus+' '+errorThrown
                });
            }
        });
    }

    function getEditKecamatan(selectedKecamatanId) {
        var kab = document.getElementById('edit_kabupaten_kota_id').value;
        $('#edit_kecamatan_id').empty();
        $('#edit_kecamatan_id').append('<option value="" selected> -- Pilih Kecamatan -- </option>');
        // ajax request to get-kab-kota
        $.ajax({
            url: '{{route('get-kecamatan')}}',
            type: 'GET',
            data: {
                kab: kab
            },
            success: function(data) {
                if (data.status === 'success') {
                    $.each(data.data, function(index, value){
                        $('#edit_kecamatan_id').append('<option value="'+value.id+'">'+value.nama_wilayah+'</option>');
                    });

                    $('#edit_kecamatan_id').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        dropdownParent: $('#editInvestor'),
                    });

                    if (selectedKecamatanId) {
                        $('#edit_kecamatan_id').val(selectedKecamatanId).trigger('change');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: textStatus
                });
            }
        });
    }

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        stateSave: true,
        scrollY: "550px",
        scrollX: true,
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

        $('#modalSalesArea').on('shown.bs.modal', function () {
            table.columns.adjust().draw();
        });

        $('#modalSalesArea').on('hidden.bs.modal', function () {
            if ($.fn.DataTable.isDataTable('#salesAreaTable')) {
                table.destroy();
                $('#salesAreaTable tbody').empty(); // bersihkan isi table
            }

            // 🔄 Refresh halaman
            location.reload();
        });
</script>
@endpush
