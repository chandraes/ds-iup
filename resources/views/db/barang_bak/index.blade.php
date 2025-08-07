@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>KATEGORI KODE & MERK BARANG</u></h1>
        </div>
    </div>
    @include('swal')
    @include('db.barang.create')
    @include('db.barang.edit')
    @include('db.barang.keterangan')
    @include('db.barang.upload-foto')
    @include('db.barang.diskon')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('db')}}"><img
                                src="{{asset('images/database.svg')}}" alt="dokumen" width="30">
                            Database</a></td>
                    <td class="text-center align-middle">
                        <div class="row">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#createModal">
                                <img src=" {{asset('images/barang.svg')}}" alt="dokumen" width="30"> Tambah Barang</a>
                        </div>

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


<div class="container-fluid mt-3 table-responsive ">
    <form method="GET" action="{{ route('db.barang') }}">
        <div class="row">
            <div class="col-md-2">
                <label for="unit">Kategori Perusahaan</label>
                <select name="unit" id="unit" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ request('unit')==$unit->id ? 'selected' : '' }}>
                        {{ $unit->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="type">Bidang</label>
                <select name="type" id="type" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($selectType as $type)
                    <option value="{{ $type->id }}" {{ request('type')==$type->id ? 'selected' : '' }}>
                        {{ $type->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="kategori">Kelompok Barang</label>
                <select name="kategori" id="kategori" class="form-select">
                    <option value=""> ---------- </option>
                    @foreach($selectKategori as $kat)
                    <option value="{{ $kat->id }}" {{ request('kategori')==$kat->id ? 'selected' : '' }}>
                        {{ $kat->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="nama">Nama Barang</label>
                <select class="form-select" name="barang_nama" id="filter_barang_nama">
                    <option value=""> ---------- </option>
                    @foreach ($selectBarangNama as $bn)
                    <option value="{{ $bn->nama }}" {{ request('barang_nama')==$bn->nama ? 'selected' : '' }}>
                        {{ $bn->nama }}
                        @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="nama">PPN/Non PPN</label>
                <select class="form-select" name="jenis" id="filter_jenis">
                    <option value=""> ---------- </option>
                    <option value="1" {{ request('jenis')==1 ? 'selected' : '' }}>Barang PPN</option>
                    <option value="2" {{ request('jenis')==2 ? 'selected' : '' }}>Barang Non PPN</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="nama">
                    ---------------
                </label>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Apply Filter</button>
                    {{-- reset filter button --}}
                    <a href="{{ route('db.barang') }}" class="btn btn-danger">Reset Filter</a>
                </div>

            </div>
        </div>
        <div class="row mt-3">

        </div>
    </form>
    <div class="table-container mt-4">
        <table class="table table-bordered" id="dataTable" style="font-size: 12px">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 15px">No</th>
                    <th class="text-center align-middle">Perusahaan</th>
                    <th class="text-center align-middle">Bidang</th>
                    <th class="text-center align-middle">Kelompok<br>Barang</th>
                    <th class="text-center align-middle">Nama<br>Barang</th>

                    <th class="text-center align-middle">Kode<br>Barang</th>
                    <th class="text-center align-middle">Merk<br>Barang</th>
                    <th class="text-center align-middle">Satuan</th>
                    <th class="text-center align-middle">Diskon</th>
                    <th class="text-center align-middle">Foto</th>
                    {{-- <th class="text-center align-middle">Ket<br></th> --}}
                    <th class="text-center align-middle">PPN</th>
                    <th class="text-center align-middle">NON PPN</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $number = 1; @endphp
                @foreach ($units as $unit)
                @php $unitDisplayed = false; @endphp
                @foreach ($unit->types as $type)
                @php $typeDisplayed = false; @endphp
                @foreach ($type->groupedBarangs as $kategoriNama => $barangs)
                @php $kategoriDisplayed = false; @endphp
                @foreach ($barangs->groupBy('barang_nama.nama') as $namaBarang => $namaBarangs)
                @php $namaDisplayed = false; @endphp
                @foreach ($namaBarangs as $barang)
                <tr>
                    @if (!$unitDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $unit->unitRowspan }}">{{ $number++ }}</td>
                    <td class="text-center align-middle" rowspan="{{ $unit->unitRowspan }}">{{ $unit->nama }}</td>
                    @php $unitDisplayed = true; @endphp
                    @endif
                    @if (!$typeDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $type->typeRowspan }}">{{ $type->nama }}</td>
                    @php $typeDisplayed = true; @endphp
                    @endif
                    @if (!$kategoriDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $barang->kategoriRowspan }}">{{ $kategoriNama }}
                    </td>
                    @php $kategoriDisplayed = true; @endphp
                    @endif
                    @if (!$namaDisplayed)
                    <td class="text-center align-middle" rowspan="{{ $barang->namaRowspan }}">{{ $namaBarang }}</td>
                    @php $namaDisplayed = true; @endphp
                    @endif

                    <td class="text-center align-middle">{{ $barang->kode }}</td>
                    <td class="text-center align-middle">{{ $barang->merk }}</td>
                    <td class="text-center align-middle">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#keteranganModal"
                            onclick="showKeterangan({{ $barang }})">
                            {{ $barang->satuan ? $barang->satuan->nama : '' }}
                        </a>

                    </td>
                    <td class="text-center align-middle">
                        @if ($barang->diskon > 0)
                        Diskon {{ $barang->diskon }}% <br>
                        {{ $barang->diskon_mulai ? $barang->diskon_mulai : 'N/A' }} s/d
                        {{ $barang->diskon_selesai ? $barang->diskon_selesai : 'N/A' }} <br>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#diskonModal"
                            onclick="setDiskon({{ $barang->id }}, {{ $barang->diskon }}, {{ $barang->diskon_mulai ? $barang->diskon_mulai : 'null' }}, {{ $barang->diskon_selesai ? $barang->diskon_selesai : 'null' }})">
                            <i class="fa fa-edit"></i> Edit Diskon
                        </a>
                        @else
                        <a href="#" data-bs-toggle="modal" data-bs-target="#diskonModal" onclick="setDiskon({{ $barang->id }}, {{ $barang->diskon }}, {{ $barang->diskon_mulai ? $barang->diskon_mulai : 'null' }}, {{ $barang->diskon_selesai ? $barang->diskon_selesai : 'null' }})">
                            <i class="fa fa-plus"></i> Tambah Diskon
                        </a>
                        @endif
                    </td>
                    <td class="text-start align-middle">
                        @if ($barang->foto != null)
                        <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal{{ $barang->id }}">
                            <img src="{{ asset('storage/' . $barang->foto) }}" alt="Foto Barang" class="img-fluid" width="100">
                        </a>

                        <div class="modal fade" id="imageModal{{ $barang->id }}" tabindex="-1" aria-labelledby="imageModalLabel{{ $barang->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="imageModalLabel{{ $barang->id }}">Foto Barang</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <div class="image-container">
                                            <img id="zoomableImage{{ $barang->id }}" src="{{ asset('storage/' . $barang->foto) }}" alt="Foto Barang" class="img-fluid">
                                        </div>
                                        <input type="range" id="zoomSlider{{ $barang->id }}" class="form-range mt-3" min="1" max="3" step="0.1" value="1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const image = document.getElementById('zoomableImage{{ $barang->id }}');
                                const slider = document.getElementById('zoomSlider{{ $barang->id }}');

                                slider.addEventListener('input', function () {
                                    const scale = slider.value;
                                    image.style.transform = `scale(${scale})`;
                                });
                            });
                        </script>
                        <style>
                            .image-container {
                                overflow: hidden;
                                display: inline-block;
                            }

                            .image-container img {
                                transition: transform 0.2s ease;
                            }
                        </style>
                        <div class="row px-3 text-nowrap mt-1">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#uploadFotoModal" onclick="uploadFoto({{$barang->id}})" class="btn btn-success btn-sm">
                                <i class="fa fa-upload"></i> Edit
                            </button>
                        </div>
                        @else
                        <div class="row px-3 text-nowrap">
                            <button type="button" data-bs-toggle="modal" data-bs-target="#uploadFotoModal" onclick="uploadFoto({{$barang->id}})" class="btn btn-primary btn-sm">
                                <i class="fa fa-upload"></i> Upload
                            </button>
                        </div>
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        @if ($barang->jenis == 1)
                        <i class="fa fa-check"></i>
                        @endif

                    </td>
                    <td class="text-center align-middle">
                        @if ($barang->jenis == 2)
                        <i class="fa fa-check"></i>
                        @endif

                    </td>
                    <td class="text-center align-middle">
                        <a href="#" class="btn btn-warning m-2" data-bs-toggle="modal" data-bs-target="#editModal"
                            onclick="editFun({{ $barang }}, {{ $type->id }}, {{ $unit->id }})"><i
                                class="fa fa-edit"></i></a>
                        <form action="{{ route('db.barang.delete', $barang->id) }}" method="post"
                            class="d-inline delete-form" id="deleteForm{{ $barang->id }}" data-id="{{ $barang->id }}">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @endforeach
                @endforeach
                @endforeach
                @if (!$loop->last)
                <tr>
                    <td colspan="4" style="border: none; background-color:transparent; border-bottom-color:transparent">
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
            <tfoot>

            </tfoot>
        </table>
    </div>

</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $('#detail_type').select2({
        theme: 'classic',
        width: '100%',
        dropdownParent: $('#createModal'),
        placeholder: 'Select options',
        allowClear: true
    }).on('select2:open', function() {
        // Add "Select All" option if it doesn't exist
        if (!$('.select2-results__options .select-all').length) {
            $('.select2-results__options').prepend('<li class="select2-results__option select-all" role="option" aria-selected="false">Select All</li>');
        }
    });

    // Handle "Select All" click event
    $(document).on('click', '.select2-results__option.select-all', function() {
        var $select2 = $('#detail_type');
        var allSelected = $select2.find('option').length === $select2.val().length;

        if (allSelected) {
            $select2.val(null).trigger('change');
        } else {
            var allValues = $select2.find('option').map(function() {
                return $(this).val();
            }).get();
            $select2.val(allValues).trigger('change');
        }
    });

    $('#filter_barang_nama').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

      $('#kategori').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#type').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#barang_nama_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#createModal')
    });

    $('#edit_detail_type').select2({
            theme: 'classic',
            width: '100%',
            dropdownParent: $('#editModal')
        });

    function uploadFoto(id) {
        document.getElementById('uploadFotoForm').action = `{{route('db.barang.upload-image', ':id')}}`.replace(':id', id);
    }

    function setDiskon(id, diskon, diskon_mulai, diskon_selesai) {
        // reset diskon form
        document.getElementById('diskonForm').reset();
        document.getElementById('diskon').value = diskon;
        // Set diskon_mulai (input type="date" expects yyyy-mm-dd)
        if (diskon_mulai) {
            document.getElementById('diskon_mulai').value = diskon_mulai;
        } else {
            document.getElementById('diskon_mulai').value = '';
        }
        // Set diskon_selesai (input type="date" expects yyyy-mm-dd)
        if (diskon_selesai) {
            document.getElementById('diskon_selesai').value = diskon_selesai;
        } else {
            document.getElementById('diskon_selesai').value = '';
        }
        document.getElementById('diskonForm').action = `{{route('db.barang.diskon', ':id')}}`.replace(':id', id);
    }


    function editFun(data, type, unit)
    {

        document.getElementById('edit_barang_unit_id').value = unit;
        document.getElementById('edit_barang_type_id').value = type;

        document.getElementById('edit_jenis').value = data.jenis;
        document.getElementById('edit_barang_kategori_id').value = data.barang_kategori_id;
        document.getElementById('edit_kode').value = data.kode;
        document.getElementById('edit_merk').value = data.merk;
        document.getElementById('edit_keterangan').value = data.keterangan;
        document.getElementById('edit_satuan_id').value = data.satuan_id;
        document.getElementById('edit_subpg_id').value = data.subpg_id;

        if (data.foto != null) {
            document.getElementById('edit_foto_preview').hidden = false;
            document.getElementById('edit_foto_preview_img').src = "{{ asset('storage') }}/" + data.foto;
        } else {
            document.getElementById('edit_foto_preview').hidden = true;
        }


        let kategoriSelect = document.getElementById('edit_barang_kategori_id');
        kategoriSelect.onchange = () => {
            getNamaBarangEdit();
            setTimeout(() => {
                document.getElementById('edit_barang_nama_id').value = data.barang_nama_id;
            }, 500);
        };

        let unitSelect = document.getElementById('edit_barang_unit_id');
        unitSelect.onchange = () => {
            getTypeEdit();
            setTimeout(() => {
                document.getElementById('edit_barang_type_id').value = data.barang_type_id;
                $('#edit_detail_type').val(null).trigger('change');
                // Collect the IDs of the detail types to be selected
                let selectedDetailTypeIds = data.detail_types.map(detailType => detailType.barang_type_id);
                // Set the selected values
                $('#edit_detail_type').val(selectedDetailTypeIds).trigger('change');
            }, 500);

        };
        unitSelect.dispatchEvent(new Event('change'));
        kategoriSelect.dispatchEvent(new Event('change'));


        document.getElementById('editForm').action = `{{route('db.barang.update', ':id')}}`.replace(':id', data.id);
    }

    function showKeterangan(data) {
        document.getElementById('text_keterangan').value = "";
        if (data.keterangan != null) {
            document.getElementById('text_keterangan').value = data.keterangan;
        }

    }

    confirmAndSubmit("#editForm", "Apakah anda yakin untuk mengubah data ini?");
    confirmAndSubmit("#createForm", "Apakah anda yakin untuk menambah data ini?");
    confirmAndSubmit("#diskonForm", "Apakah data yang anda masukan sudah benar?");

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
</script>
@endpush
