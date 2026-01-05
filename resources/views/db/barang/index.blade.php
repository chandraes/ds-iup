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
    @include('db.barang.grosir')
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


<div class="container-fluid mt-3 table-responsive ">
    <div class="row justify-content-center mb-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center"
                 data-bs-toggle="collapse" href="#filterCollapse" role="button" aria-expanded="true" style="cursor: pointer;">
                <h6 class="mb-0 fw-bold text-secondary"><i class="fa fa-filter me-2 text-primary"></i>Filter Data Barang</h6>
                <i class="fa fa-chevron-down text-muted" style="font-size: 0.8rem;"></i>
            </div>

            <div class="collapse show" id="filterCollapse">
                <div class="card-body bg-light bg-opacity-25">
                    <form id="filterForm">
                        <div class="row g-3">

                            <div class="col-md-8 col-lg-9">
                                <label class="form-label fw-bold small text-uppercase text-muted">Nama Barang</label>
                                <select class="form-select" name="barang_nama" id="filter_barang_nama"></select>
                            </div>

                            <div class="col-md-4 col-lg-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Status Barang</label>
                                <select class="form-select" name="status" id="filter_status">
                                    <option value="all" selected>Semua Status</option>
                                    <option value="1">Aktif</option>
                                    <option value="0">Non Aktif</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <hr class="my-2 text-muted opacity-25">
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Kategori Perusahaan</label>
                                <select name="unit" id="filter_unit" class="form-select">
                                    <option value="">Semua Perusahaan</option>
                                    @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ request('unit')==$unit->id ? 'selected' : '' }}>
                                        {{ $unit->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Bidang</label>
                                <select name="type" class="form-select" id="filter_type"></select>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">Kelompok Barang</label>
                                <select name="kategori" id="filter_kategori" class="form-select"></select>
                            </div>

                            <div class="col-md-6 col-lg-3">
                                <label class="form-label fw-bold small text-uppercase text-muted">PPN/Non PPN</label>
                                <select class="form-select" name="jenis" id="filter_jenis">
                                    <option value="">Semua Jenis</option>
                                    <option value="1">Barang PPN</option>
                                    <option value="2">Barang Non PPN</option>
                                </select>
                            </div>

                            <div class="col-12 text-end mt-3">
                                <button type="button" id="resetFilter" class="btn btn-danger px-4 shadow-sm">
                                    <i class="fa fa-refresh me-1"></i> Reset Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
    <div class="table-container mt-4">
        <table class="table table-bordered" id="data" style="font-size: 12px">
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
                    <th class="text-center align-middle">Subpg</th>
                    <th class="text-center align-middle">Diskon</th>
                    <th class="text-center align-middle">Grosir</th>
                    <th class="text-center align-middle">Foto</th>
                    <th class="text-center align-middle">PPN</th>
                    <th class="text-center align-middle">NON PPN</th>
                    <th class="text-center align-middle">Status</th>
                    <th class="text-center align-middle">Action</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
            <tfoot>

            </tfoot>
        </table>
    </div>

</div>

@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.1.1/css/scroller.dataTables.min.css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
<link href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script src="https://cdn.datatables.net/scroller/2.1.1/js/dataTables.scroller.min.js"></script>
<script>
    $(document).on('submit', 'form[action*="upload-image"]', function() {
        const filterState = {
            unit: $('#filter_unit').val(),
            type: $('#filter_type').val(),
            kategori: $('#filter_kategori').val(),
            barang_nama: $('#filter_barang_nama').val(),
            jenis: $('#filter_jenis').val(),
            status: $('#filter_status').val()
        };
        if (filterState.status) {
            $('#filter_status').val(filterState.status).trigger('change');
        }
        sessionStorage.setItem('barang_filter_state', JSON.stringify(filterState));
    });


   $(document).on('submit', 'form[action*="update"]', function() {
        // Fungsi bantu untuk mendapatkan ID dan Teks dari Select2
        function getSelect2Data(selector) {
            const data = $(selector).select2('data');
            if (data && data.length > 0) {
                return {
                    id: data[0].id,
                    text: data[0].text
                };
            }
            return { id: null, text: null };
        }

        const typeData = getSelect2Data('#filter_type');
        const kategoriData = getSelect2Data('#filter_kategori');
        const barangNamaData = getSelect2Data('#filter_barang_nama');

        const filterState = {
            unit: $('#filter_unit').val(),
            jenis: $('#filter_jenis').val(),
            status: $('#filter_status').val(),
            // Data AJAX
            type: typeData.id,
            type_text: typeData.text,
            kategori: kategoriData.id,
            kategori_text: kategoriData.text,
            barang_nama: barangNamaData.id,
            barang_nama_text: barangNamaData.text,
        };
        if (filterState.status) {
            $('#filter_status').val(filterState.status).trigger('change');
        }
        sessionStorage.setItem('barang_filter_state', JSON.stringify(filterState));
    });

    $(document).on('click', '.toggle-status-btn', function(e) {
        e.preventDefault();

        let btn = $(this);
        let icon = btn.find('i');
        // Ambil elemen teks label (sibling dari tombol <a>)
        let label = btn.siblings('.status-label');

        let id = btn.data('id');
        let currentStatus = btn.data('status'); // 1 = aktif, 0 = non-aktif

        let actionText = currentStatus == 1 ? "menonaktifkan" : "mengaktifkan";
        let confirmColor = currentStatus == 1 ? '#d33' : '#3085d6';

        Swal.fire({
            title: 'Ubah Status?',
            text: `Apakah Anda yakin ingin ${actionText} barang ini?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, ubah!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('db/barang/toggle-status') }}/" + id,
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function() {
                        btn.css('opacity', '0.5');
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message,
                            timer: 1000,
                            showConfirmButton: false
                        });

                        // --- UPDATE VISUAL SECARA DINAMIS ---

                        if (response.new_status == true) {
                            // KONDISI: BARU SAJA DIAKTIFKAN

                            // 1. Update Icon
                            icon.removeClass('fa-toggle-off text-danger').addClass('fa-toggle-on text-success');

                            // 2. Update Label Teks & Warna
                            label.text('Aktif')
                                .removeClass('text-danger')
                                .addClass('text-success');

                            // 3. Update Data Attribute
                            btn.data('status', 1);

                        } else {
                            // KONDISI: BARU SAJA DINONAKTIFKAN

                            // 1. Update Icon
                            icon.removeClass('fa-toggle-on text-success').addClass('fa-toggle-off text-danger');

                            // 2. Update Label Teks & Warna
                            label.text('Non Aktif')
                                .removeClass('text-success')
                                .addClass('text-danger');

                            // 3. Update Data Attribute
                            btn.data('status', 0);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Terjadi kesalahan saat mengubah status.'
                        });
                    },
                    complete: function() {
                        btn.css('opacity', '1');
                    }
                });
            }
        });
    });
// Restore state setelah page load
$(document).ready(function() {
    const savedState = sessionStorage.getItem('barang_filter_state');

    // Fungsi bantu untuk restorasi Select2 AJAX
    // Fungsi ini membuat elemen <option> secara manual agar Select2 dapat menampilkan nilainya.
    function restoreSelect2Ajax(selector, value, text) {
        if (value && text) {
            // 1. Buat elemen <option> baru (dipilih secara default)
            const newOption = new Option(text, value, true, true);

            // 2. Tambahkan option ke elemen Select2
            $(selector).append(newOption).trigger('change');

            // Catatan: Karena Anda sudah memanggil table.draw() di event change Select2,
            // baris ini sudah cukup untuk mengatur nilai dan memicu filter.
        }
    }

    if (savedState) {
        const filterState = JSON.parse(savedState);

        // --- 1. Restorasi Filter Non-AJAX (unit, jenis) ---
        if (filterState.unit) {
            $('#filter_unit').val(filterState.unit).trigger('change');
        }
        if (filterState.jenis) {
            $('#filter_jenis').val(filterState.jenis).trigger('change');
        }

        // --- 2. Restorasi Filter AJAX (Penting: Lakukan secara berurutan) ---

        // A. Restorasi 'type'
        restoreSelect2Ajax('#filter_type', filterState.type, filterState.type_text);

        // B. Restorasi 'kategori'
        // Ini perlu disetel sebelum 'barang_nama' karena 'barang_nama' mungkin terpengaruh olehnya.
        restoreSelect2Ajax('#filter_kategori', filterState.kategori, filterState.kategori_text);

        // C. Restorasi 'barang_nama'
        restoreSelect2Ajax('#filter_barang_nama', filterState.barang_nama, filterState.barang_nama_text);

        // Clear session storage setelah restore
        sessionStorage.removeItem('barang_filter_state');

        // PENTING: Jika event 'change' di setiap Select2 tidak memanggil table.draw(),
        // Anda perlu memanggilnya di sini sekali setelah semua filter di-restore.
        // Berdasarkan kode Anda, table.draw() sudah ada di setiap 'change',
        // jadi ini opsional, tetapi bisa digunakan sebagai fallback.
        // table.draw();
    }
});
</script>
<script>
    $(document).ready(function() {

    let table = $('#data').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("db.barang.data") }}',
            data: function (d) {
                d.barang_nama = $('#filter_barang_nama').val();
                d.kategori = $('#filter_kategori').val();
                d.jenis = $('#filter_jenis').val();
                d.type = $('#filter_type').val();
                d.unit = $('#filter_unit').val();
                d.status = $('#filter_status').val();
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
            {
                data: null,
                name: 'no',
                className: 'text-center align-middle',
                orderable: false,
                searchable: false,
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'unit', name: 'unit', className: 'text-center align-middle text-wrap' },
            { data: 'type.nama', name: 'barang_type_id', className: 'text-center align-middle text-wrap', sortable: false},
            { data: 'kategori.nama', name: 'kategori.nama', className: 'text-center align-middle text-wrap' },
            { data: 'barang_nama.nama', name: 'barang_nama.nama', className: 'text-center align-middle text-wrap' },
            { data: 'kode', name: 'kode', className: 'text-center align-middle text-wrap' },
            { data: 'merk', name: 'merk', className: 'text-center align-middle text-wrap' },
            { data: 'satuan_view', name: 'satuan_view', className: 'text-center align-middle text-wrap' },
            { data: 'subpg_view', name: 'subpg_view', className: 'text-center align-middle text-wrap' },
            { data: 'diskon_view', name: 'diskon', className: 'text-center align-middle text-wrap' },
            { data: 'grosir_view', name: 'grosir_view', className: 'text-center align-middle text-wrap', sortable:false},
            {
                data: 'upload_barang',
                name: 'upload_barang',
                className: 'text-center align-middle text-wrap',

            },
            // if jenis is 1 the show checked, if jenis is 2 show unchecked
            {
                data: 'jenis',
                name: 'jenis',
                className: 'text-center align-middle text-wrap',
                render: function(data, type, row) {
                    return data == 1 ? '<i class="fa fa-check text-success"></i>' : '';
                }
            },
            {
                data: 'jenis',
                name: 'jenis',
                className: 'text-center align-middle text-wrap',
                render: function(data, type, row) {
                    return data == 2 ? '<i class="fa fa-check text-success"></i>' : '';
                }
            },
            {
                data: 'status',
                name: 'is_active',
                className: 'text-center align-middle',
                orderable: true,
                searchable: false
            },
            { data: 'aksi', name: 'aksi', orderable: false, searchable: false, className: 'text-center align-middle text-wrap' }
        ]
    });

    // const filters = $('#filter_barang_nama');

    //  filters.select2({
    //     theme: 'bootstrap-5',
    //     width: '100%'
    // }).on('change', function () {
    //     table.draw();
    // });
     $('#filter_unit').select2({
        placeholder: 'Pilih Perusahaan',
        width: '100%',
        allowClear: true,
        theme: 'bootstrap-5', // tambahkan theme di sini
    }).on('change', function () {
        table.draw(); // tambahkan event handler di sini
    });

    $('#filter_type').select2({
        placeholder: 'Pilih Bidang',
        minimumInputLength: 3,
        width: '100%',
        allowClear: true,
        theme: 'bootstrap-5', // tambahkan theme di sini
        ajax: {
            url: '{{ route("universal.search-barang-type") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                };
            },
            processResults: function(data) {
                return {
                    results: data.data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.nama
                        };
                    })
                };
            },
            cache: true
        }
    }).on('change', function () {
        table.draw(); // tambahkan event handler di sini
    });

    $('#filter_barang_nama').select2({
        placeholder: 'Pilih Barang Nama',
        minimumInputLength: 3,
        width: '100%',
        allowClear: true,
        theme: 'bootstrap-5', // tambahkan theme di sini
        ajax: {
            url: '{{ route("universal.search-barang-nama") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                    kategori: $('#filter_kategori').val()
                };
            },
            processResults: function(data) {
                return {
                    results: data.data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.nama
                        };
                    })
                };
            },
            cache: true
        }
    }).on('change', function () {
        table.draw(); // tambahkan event handler di sini
    });

    $('#filter_kategori').select2({
        placeholder: 'Pilih Kelompok Barang',
        minimumInputLength: 3,
        width: '100%',
        allowClear: true,
        theme: 'bootstrap-5', // tambahkan theme di sini
        ajax: {
            url: '{{ route("universal.search-barang-kategori") }}',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    search: params.term,
                };
            },
            processResults: function(data) {
                return {
                    results: data.data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.nama
                        };
                    })
                };
            },
            cache: true
        }
    }).on('change', function () {
         $('#filter_barang_nama').val(null).trigger('change');
        table.draw();
    });

    $('#filter_jenis').select2({
        theme: 'bootstrap-5',
        width: '100%'
    }).on('change', function () {
        table.draw();
    });

    $('#filter_status').select2({
        theme: 'bootstrap-5',
        width: '100%',
        minimumResultsForSearch: Infinity // Sembunyikan search box karena opsi cuma sedikit
    }).on('change', function () {
        table.draw(); // Reload tabel saat status berubah
    });

   $('#resetFilter').on('click', function () {
        $('#filter_barang_nama, #filter_kategori, #filter_jenis, #filter_unit, #filter_type').val(null).trigger('change');

        // Reset Status ke 'all'
        $('#filter_status').val('all').trigger('change');

    });
});

</script>
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

         $('#edit_subpg_id').select2({
        theme: 'bootstrap-5',
        width: '100%',
        dropdownParent: $('#editModal')
    });

    function uploadFoto(id) {
        document.getElementById('uploadFotoForm').action = `{{route('db.barang.upload-image', ':id')}}`.replace(':id', id);
    }

    function formatDateForInput(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '';
        const month = ('0' + (d.getMonth() + 1)).slice(-2);
        const day = ('0' + d.getDate()).slice(-2);
        return day + '-' + month + '-' + d.getFullYear();
        // return d.getFullYear() + '-' + month + '-' + day;
    }

    function setDiskon(id, diskon, diskon_mulai, diskon_selesai, nama_barang, kode, merk) {
        // reset diskon form
        document.getElementById('diskonForm').reset();
        document.getElementById('diskon').value = diskon;
        document.getElementById('nm_barang_merk_diskon').value = `${nama_barang}, ${kode}, ${merk}`;

        document.getElementById('diskon_mulai').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: diskon_mulai ? formatDateForInput(diskon_mulai) : ''
        });
        document.getElementById('diskon_selesai').flatpickr({
            dateFormat: "d-m-Y",
            defaultDate: diskon_selesai ? formatDateForInput(diskon_selesai) : ''
        });
        document.getElementById('diskonForm').action = `{{route('db.barang.diskon', ':id')}}`.replace(':id', id);

        // Bind AJAX submit
        $('#diskonForm').off('submit').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let url = form.attr('action');
            let data = form.serialize();

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                beforeSend: function() {
                    form.find('button[type="submit"]').prop('disabled', true);
                },
                success: function(response) {
                    // Tampilkan notifikasi sukses, reload datatable jika perlu

                    Swal.fire({
                        icon: response.status,
                        title: (response.status).toUpperCase(),
                        text: response.message || 'Diskon berhasil disimpan!'
                    });
                    $('#diskonModal').modal('hide');
                    $('#data').DataTable().ajax.reload(null, false); // reload datatable
                },
                error: function(xhr) {
                    let msg = 'Terjadi kesalahan.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                },
                complete: function() {
                    form.find('button[type="submit"]').prop('disabled', false);
                }
            });
        });
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

    function setGrosir(id, satuan, nama, kode, merk) {
        // reset grosir form
        document.getElementById('sat_barang').innerText = satuan.nama;
        document.getElementById('grosirBarangId').value = id;
        document.getElementById('nm_barang_grosir').value = `${nama}, ${kode}, ${merk}`;
        let grosT = document.getElementById('grosirTableBody');
        grosT.innerHTML = ''; // Clear existing rows
        // ajax request to get grosir data
        $.ajax({
            url: "{{ route('db.barang.get-grosir') }}",
            type: "GET",
            data: {
                barang_id: id
            },
            success: function(response) {
                if (response.status === 'success' && response.data.length > 0) {
                    // Populate the grosir table with the data
                    let grosirTableBody = document.getElementById('grosirTableBody');
                    grosirTableBody.innerHTML = ''; // Clear existing rows
                    response.data.forEach(function(grosir) {
                        let row = document.createElement('tr');
                        row.innerHTML = `
                            <td class="text-center">${grosirTableBody.children.length + 1}</td>
                            <td class="text-center">${grosir.qty} ${grosir.satuan.nama}</td>
                            <td class="text-center">${grosir.qty_grosir} ${grosir.barang.satuan.nama}</td>
                            <td class="text-center">${grosir.diskon} %</td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" onclick="deleteGrosir(${grosir.id})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        `;
                        grosirTableBody.appendChild(row);
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan saat mengambil data grosir.',
                });
            }
        });


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
