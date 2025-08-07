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
                    {{-- <td><a href="#" data-bs-toggle="modal" data-bs-target="#modalSalesArea"><img
                                src="{{asset('images/area.svg')}}" width="30"> Sales Area</a>
                    </td> --}}
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
@include('db.konsumen.kode-toko')
@include('db.konsumen.upload-foto')
@include('db.konsumen.diskon-khusus')

<div class="container-fluid mt-5 table-responsive">
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filterKodeToko" class="form-label">Kode Toko</label>
            <select id="filterKodeToko" name="kode_toko" class="form-select">
                <option value="" selected>-- Semua Kode Toko --</option>
                @foreach ($kode_toko as $k)
                <option value="{{ $k->id }}">
                    {{ $k->kode }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterSalesArea" class="form-label">Sales Area</label>

            <select id="filterSalesArea" name="area" class="form-select">
                <option value="" selected>-- Semua Sales Area --</option>
                @foreach ($sales_area as $salesArea)
                <option value="{{ $salesArea->id }}">
                    {{ $salesArea->nama }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="filterKecamatan" class="form-label">Kecamatan</label>
            <select id="filterKecamatan" name="kecamatan" class="form-select">
                <option value="">-- Semua Kecamatan --</option>
                @foreach ($kecamatan_filter as $kec)
                <option value="{{ $kec->id }}">
                    {{ $kec->nama_wilayah }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="filterKecamatan" class="form-label">Status</label>
            <select id="filterStatus" name="status" class="form-select">
                <option value="1">Aktif</option>
                <option value="0">Non
                    Aktif</option>
            </select>
        </div>
        <div class="col-md-1 mt-4">
            <div class="row">
                <button type="button" id="resetFilter" class="btn btn-secondary mt-2">Reset</button>
            </div>

        </div>
    </div>
    <table id="data" class="table table-bordered table-hover" style="font-size: 0.8rem;">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle">KODE</th>
                <th class="text-center align-middle">KODE TOKO</th>
                <th class="text-center align-middle">NAMA</th>
                <th class="text-center align-middle">CP</th>
                <th class="text-center align-middle">NPWP</th>
                <th class="text-center align-middle">NIK</th>
                <th class="text-center align-middle">KTP</th>
                <th class="text-center align-middle">Sales Area</th>
                <th class="text-center align-middle">Provinsi</th>
                <th class="text-center align-middle">Kab/Kota</th>
                <th class="text-center align-middle">Kecamatan</th>
                <th class="text-center align-middle">Alamat</th>
                <th class="text-center align-middle">Sistem<br>Pembayaran</th>
                <th class="text-center align-middle">Limit<br>Plafon</th>
                <th class="text-center align-middle">Diskon Khusus (%)</th>
                <th class="text-center align-middle">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
    <link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.1.1/css/scroller.dataTables.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
    <script src="{{asset('assets/js/dt5.min.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
    <script src="https://cdn.datatables.net/scroller/2.1.1/js/dataTables.scroller.min.js"></script>
<script>
    $(document).ready(function() {


    let table = $('#data').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("db.konsumen.data") }}',
            data: function (d) {
                d.kode_toko = $('#filterKodeToko').val();
                d.area = $('#filterSalesArea').val();
                d.kecamatan = $('#filterKecamatan').val();
                d.status = $('#filterStatus').val();
            }
        },
        scrollY: '70vh', // tinggi area scroll, bisa disesuaikan
        scrollX: true,
        stateSave: true,
        scroller: {
            loadingIndicator: true
        },
        deferRender: true,
        columns: [
            { data: 'full_kode', name: 'kode', className: 'text-center' },
            { data: 'kode_toko', name: 'kode_toko.kode', className: 'text-center' },
            { data: 'nama', name: 'nama', className: 'text-wrap' },
            { data: 'cp', name: 'cp', searchable: false, },
            { data: 'npwp', name: 'npwp' },
            { data: 'nik', name: 'nik' },
            { data: 'ktp', name: 'ktp', searchable: false, orderable: false },
            { data: 'karyawan.nama', name: 'karyawan.nama', className: 'text-wrap', searchable: false },
            { data: 'provinsi.nama_wilayah', name: 'provinsi.nama_wilayah', className: 'text-wrap' },
            { data: 'kabupaten_kota.nama_wilayah', name: 'kabupaten_kota.nama_wilayah', className: 'text-wrap' },
            { data: 'kecamatan.nama_wilayah', name: 'kecamatan.nama_wilayah', className: 'text-wrap' },
            { data: 'alamat', name: 'alamat', className: 'text-wrap', searchable: false },
            { data: 'pembayaran_raw', name: 'pembayaran_raw', className: 'text-wrap', searchable: false },
            { data: 'limit_plafon', name: 'plafon', className: 'text-end', searchable: false },
            { data: 'diskon', name: 'diskon_khusus', className: 'text-end', searchable: false },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false }
        ]
    });

    const filters = $('#filterKodeToko, #filterSalesArea, #filterKecamatan, #filterStatus');

     filters.select2({
        theme: 'bootstrap-5',
        width: '100%'
    }).on('change', function () {
        table.draw();
    });

   $('#resetFilter').on('click', function () {
        $('#filterKodeToko, #filterSalesArea, #filterKecamatan, #filterStatus')
            .val('')
            .trigger('change');

        $('#filterStatus').val('1').trigger('change'); // jika ingin set default ke "Aktif"
    });
});

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


        $('#filterSalesArea').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });



        $('#filterKecamatan').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

   function editInvestor(data, id) {

        document.getElementById('editForm').reset();

        $('#edit_kabupaten_kota_id').empty();
        $('#edit_kecamatan_id').empty();
        document.getElementById('edit_nama').value = data.nama;
        $('#edit_kode_toko_id').val(data.kode_toko_id || '').trigger('change');
        document.getElementById('edit_no_hp').value = data.no_hp;
        document.getElementById('edit_no_kantor').value = data.no_kantor;
        document.getElementById('edit_cp').value = data.cp;
        document.getElementById('edit_npwp').value = data.npwp;
        document.getElementById('edit_nik').value = data.nik;
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

    function editDiskon(id, diskon) {
        document.getElementById('diskonForm').reset();
        document.getElementById('edit_diskon').value = diskon;
        document.getElementById('diskonForm').action = '/db/konsumen/diskon-khusus/' + id;

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

     function uploadFoto(id) {
        document.getElementById('uploadFotoForm').action = `{{route('db.konsumen.upload-ktp', ':id')}}`.replace(':id', id);
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

 $('#edit_subpg_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
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

        $('#diskonForm').submit(function(e){
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
