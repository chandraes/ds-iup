@extends('layouts.app')
@section('content')
@include('billing.invoice-konsumen.cicil')
@include('billing.invoice-konsumen.cicil-non-new')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM JANJI BAYAR</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30"> Billing</a></td>
                    <td><a target="_blank" href="{{route('billing.invoice-konsumen.pdf-all', [
                                'expired' => request('expired'),
                                'apa_ppn' => request('apa_ppn'),
                                'karyawan_id' => request('karyawan_id'),
                                'kabupaten_id' => request('kabupaten_id'),
                                'kecamatan_id' => request('kecamatan_id'),
                            ])}}"><img src="{{asset('images/print.svg')}}" alt="dokumen" width="30"> Print</a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>
    <div class="row">
        <div class="row">
            <div class="col-md-2">
                <select name="expired" id="expired" class="form-select filter-dt">
                    <option value="">-- Semua Data --</option>
                    <option value="yes">Expired</option>
                    <option value="no">Belum Expired</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="apa_ppn" id="apa_ppn" class="form-select filter-dt">
                    <option value="">-- PPN & Non PPN --</option>
                    <option value="yes">PPN</option>
                    <option value="no">Non PPN</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="karyawan_id" id="karyawan_id" class="form-select filter-dt">
                    <option value="">-- Semua Sales --</option>
                    @foreach ($sales as $s)
                    <option value="{{$s->id}}">{{$s->nama}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="kabupaten_id" id="kabupaten_id" class="form-select filter-dt">
                    <option value="">-- Semua Kab/Kota --</option>
                    @foreach ($kabupaten as $kab)
                    <option value="{{$kab->id}}">{{$kab->nama_wilayah}}</option>
                    @endforeach
                </select>
            </div>
             <div class="col-md-3">
                <select name="kecamatan_id" id="kecamatan_id" class="form-select filter-dt">
                    <option value="">-- Semua Kecamatan --</option>
                    @foreach ($kecamatan as $k)
                    <option value="{{$k->id}}">{{$k->nama_wilayah}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2">
                <button type="button" id="resetFilter" class="btn btn-secondary mb-2"><i class="fa fa-repeat"></i> Reset Filter</button>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <div id="cartBanner" class="alert alert-info d-none justify-content-between align-items-center mt-3 shadow-sm animate__animated animate__fadeIn">
    <div class="align-middle">
        <i class="fa fa-shopping-cart fa-lg me-2"></i>
        Keranjang Janji Bayar: <span class="badge bg-dark mx-1" id="cartCount">0</span> Invoice Terpilih
        (Konsumen: <strong id="cartKonsumenName">-</strong>)
    </div>
    <div>
        <button type="button" id="btnEmptyCart" class="btn btn-sm btn-outline-danger me-2"><i class="fa fa-trash"></i> Kosongkan</button>
        <a href="{{ route('billing.form-janji-bayar.keranjang') }}" class="btn btn-sm btn-success px-3 fw-bold">
            <i class="fa fa-eye"></i> Buka & Proses Keranjang
        </a>
    </div>
</div>
        <table class="table table-hover table-bordered w-100" id="rekapTable" style="font-size: 0.8rem;">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    {{-- <th class="text-center align-middle">Sales</th> --}}
                    <th class="text-center align-middle">Daerah</th>
                    <th class="text-center align-middle">Kode</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Plafon</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Nilai</th>
                    <th class="text-center align-middle">Total Belanja</th>
                    <th class="text-center align-middle">DP</th>
                    <th class="text-center align-middle">DP PPN</th>
                    <th class="text-center align-middle">Cicilan</th>
                    <th class="text-center align-middle">Sisa PPN</th>
                    <th class="text-center align-middle">Sisa Tagihan</th>
                    <th class="text-center align-middle">Jatuh Tempo</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="6">Grand Total</th>
                    <th class="text-start text-nowrap align-middle">
                        <ul style="margin: 0; padding: 0; list-style: none;">
                            <li>DPP : <strong id="t_dpp">0</strong></li>
                            <li>Diskon : <strong id="t_diskon">0</strong></li>
                            <li>PPN : <strong id="t_ppn">0</strong></li>
                            <li>Penyesuaian : <strong id="t_add_fee">0</strong></li>
                        </ul>
                    </th>
                    <th class="text-end align-middle" id="t_grand_total">0</th>
                    <th class="text-end align-middle" id="t_dp">0</th>
                    <th class="text-end align-middle" id="t_dp_ppn">0</th>
                    <th class="text-end align-middle" id="t_cicilan">0</th>
                    <th class="text-end align-middle" id="t_sisa_ppn">0</th>
                    <th class="text-end align-middle" id="t_sisa_tagihan">0</th>
                    <th class="text-end align-middle" colspan="2"></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="modal fade" id="modalGlobalCicilan" tabindex="-1" aria-labelledby="modalGlobalCicilanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalGlobalCicilanLabel">Histori Cicilan Nota: <strong id="textModalNota"></strong></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-striped text-center" style="font-size: 0.9rem;">
                    <thead class="table-dark">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nominal + PPN</th>
                        </tr>
                    </thead>
                    <tbody id="contentModalCicilan"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
    /* CRUCIAL FIX UNTUK INFINITE SCROLL:
       Memaksa tinggi baris seragam agar kalkulasi matematika Scroller stabil dan data tidak hilang */
    #rekapTable tbody tr {
        height: 85px !important;
    }
    #rekapTable tbody td {
        vertical-align: middle !important;
        white-space: nowrap !important;
    }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>

<script>
    // Ambil data autentikasi user untuk pengecekan hak akses tombol void di client-side
    window.userRole = "{{ auth()->user()->role }}";
    window.csrfToken = "{{ csrf_token() }}";

    $(document).ready(function() {
        // Handler Klik Tambah Keranjang
        $(document).on('click', '.btn-add-cart', function(e) {
            e.preventDefault();
            let btn = $(this);
            let invoiceId = btn.data('id');

            $.ajax({
                url: "{{ route('billing.form-janji-bayar.tambah-keranjang') }}",
                type: "POST",
                data: { _token: window.csrfToken, invoice_jual_id: invoiceId },
                success: function(res) {
                    table.draw(false); // Draw ulang tabel tanpa memindahkan halaman/scroll page
                },
                error: function(xhr) {
                    // INTERAKTIF OPSI B: Jika terdeteksi konsumen yang dipilih berbeda
                    if (xhr.status === 400 && xhr.responseJSON.code === 'DIFFERENT_CONSUMER') {
                        Swal.fire({
                            title: 'Konsumen Berbeda!',
                            text: xhr.responseJSON.message + " Ingin mengosongkan keranjang lama Anda?",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Ya, Kosongkan & Tukar!',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Kosongkan keranjang lama lewat AJAX
                                $.ajax({
                                    url: "{{ route('billing.form-janji-bayar.kosongkan-keranjang') }}",
                                    type: "POST",
                                    data: { _token: window.csrfToken },
                                    success: function() {
                                        // Otomatis masukkan item baru yang diinginkan secara instan
                                        $.ajax({
                                            url: "{{ route('billing.form-janji-bayar.tambah-keranjang') }}",
                                            type: "POST",
                                            data: { _token: window.csrfToken, invoice_jual_id: invoiceId },
                                            success: function() {
                                                table.draw(false);
                                                Swal.fire('Berhasil!', 'Keranjang ditukar ke konsumen baru.', 'success');
                                            }
                                        });
                                    }
                                });
                            }
                        });
                    } else {
                        Swal.fire('Gagal', 'Terjadi masalah pada server.', 'error');
                    }
                }
            });
        });

        // Handler Klik Hapus dari Keranjang
        $(document).on('click', '.btn-remove-cart', function(e) {
            e.preventDefault();
            let invoiceId = $(this).data('id');

            $.ajax({
                url: "{{ route('billing.form-janji-bayar.hapus-keranjang') }}",
                type: "POST",
                data: { _token: window.csrfToken, invoice_jual_id: invoiceId },
                success: function() {
                    table.draw(false);
                }
            });
        });

        // Handler Klik Kosongkan Manual via Banner
        $('#btnEmptyCart').click(function() {
            Swal.fire({
                title: 'Kosongkan Keranjang?',
                text: 'Seluruh daftar invoice terpilih akan dibersihkan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Bersihkan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('billing.form-janji-bayar.kosongkan-keranjang') }}",
                        type: "POST",
                        data: { _token: window.csrfToken },
                        success: function() {
                            table.draw(false);
                        }
                    });
                }
            });
        });

        // Handler Tombol Proses Input Data Lanjut
        $('#btnCheckoutCart').click(function() {
            let namaKonsumen = $('#cartKonsumenName').text();
            $('#modalLabelKonsumen').text(namaKonsumen);
            $('#modalCheckout').modal('show');
        });

       $('#karyawan_id, #kecamatan_id, #kabupaten_id').select2({ theme: 'bootstrap-5', width: '100%' });

       var table = $('#rekapTable').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            scrollY: "55vh",
            scrollCollapse: true,
            saveState: true,
            scroller: {
                loadingIndicator: true,
                displayBuffer: 10
            },
            scrollX: true,

            // 1. AKTIFKAN SORTING DI SINI
            ordering: true,

            // Optional: Tentukan urutan default saat halaman pertama kali dibuka (misal: urut berdasarkan tanggal secara Descending)
            order: [[0, 'desc']],

            ajax: {
                url: "{{ route('billing.form-janji-bayar.data') }}",
                data: function (d) {
                    d.expired = $('#expired').val();
                    d.apa_ppn = $('#apa_ppn').val();
                    d.karyawan_id = $('#karyawan_id').val();
                    d.kabupaten_id = $('#kabupaten_id').val();
                    d.kecamatan_id = $('#kecamatan_id').val();
                }
            },
            columns: [
                {data: 'tanggal_en', name: 'created_at', className: 'text-center align-middle'},
                // {data: 'sales', name: 'sales', className: 'text-center align-middle'},

                // Daerah adalah gabungan string di PHP, matikan sorting-nya agar tidak SQL Error
                {data: 'daerah', name: 'daerah', className: 'text-start align-middle text-wrap', orderable: false},

                {data: 'konsumen_kode', name: 'konsumen_kode', className: 'text-center align-middle'},
                {data: 'konsumen_nama', name: 'konsumen_nama', className: 'text-start align-middle'},
                {data: 'konsumen.nf_plafon', name: 'konsumen_plafon', className: 'text-end align-middle'},
                {
                    data: 'nota_data',
                    name: 'kode', // Mengurutkan berdasarkan kolom 'kode' di database
                    className: 'text-start align-middle',
                    render: function(data) {
                        return `<a href="${data.url}">${data.kode}</a>`;
                    }
                },
                {
                    data: 'nilai_data',
                    name: 'total', // Mengurutkan berdasarkan kolom 'total' di database
                    className: 'text-start align-middle',
                    render: function(data) {
                        return `<div style="line-height: 1.2;">` +
                            `DPP: <strong>${data.dpp}</strong><br>` +
                            `Disk: <strong>${data.diskon}</strong><br>` +
                            `PPN: <strong>${data.ppn}</strong><br>` +
                            `Peny: <strong>${data.add_fee}</strong>` +
                            `</div>`;
                    }
                },
                {data: 'nf_grand_total', name: 'grand_total', className: 'text-end align-middle'},
                {data: 'nf_dp', name: 'dp', className: 'text-end align-middle'},
                {data: 'nf_dp_ppn', name: 'dp_ppn', className: 'text-end align-middle'},
                {
                    data: 'cicilan_data',
                    name: 'id',
                    className: 'text-end align-middle',
                    // Cicilan adalah hasil SUM query relasi, matikan sorting-nya
                    orderable: false,
                    render: function(data) {
                        if(data.history.length > 0) {
                            return `<a href="#" class="trigger-modal-cicilan" data-nota="${data.kode_nota}" data-history='${JSON.stringify(data.history)}'>${data.total_formatted}</a>`;
                        }
                        return '0';
                    }
                },
                {
                    data: 'action_data',
                    name: 'sisa_ppn',
                    className: 'text-end align-middle p-0',
                    render: function(data) {
                        let cls = data.ppn_dipungut ? '' : 'table-danger';
                        return `<span class="${cls} d-flex align-items-center justify-content-end w-100 h-100 px-2" style="min-height: 85px;">${data.nf_sisa_ppn}</span>`;
                    }
                },
                {data: 'nf_sisa_tagihan', name: 'sisa_tagihan', className: 'text-end align-middle'},
                {data: 'jatuh_tempo', name: 'jatuh_tempo', className: 'text-end align-middle'},
                {
                    data: 'action_data',
                    name: 'id',
                    orderable: false,
                    searchable: false,
                    className: 'text-center align-middle',
                    render: function(data) {
                        if (data.in_cart) {
                            return `<button type="button" class="btn btn-sm btn-danger btn-remove-cart py-1 px-3" data-id="${data.id}">
                                        <i class="fa fa-minus-circle"></i> Hapus
                                    </button>`;
                        } else {
                            return `<button type="button" class="btn btn-sm btn-success btn-add-cart py-1 px-3" data-id="${data.id}">
                                        <i class="fa fa-plus-circle"></i> + Keranjang
                                    </button>`;
                        }
                    }
                }
            ],
            drawCallback: function(settings) {
                var response = settings.json;
                if (response && response.totals) {
                    $('#t_dpp').text(response.totals.dpp);
                    $('#t_diskon').text(response.totals.diskon);
                    $('#t_ppn').text(response.totals.ppn);
                    $('#t_add_fee').text(response.totals.add_fee);
                    $('#t_grand_total').text(response.totals.grand_total);
                    $('#t_dp').text(response.totals.dp);
                    $('#t_dp_ppn').text(response.totals.dp_ppn);
                    $('#t_sisa_ppn').text(response.totals.sisa_ppn);
                    $('#t_sisa_tagihan').text(response.totals.sisa_tagihan);
                }

                if (response && response.cart_meta) {
                    if (response.cart_meta.count > 0) {
                        $('#cartBanner').removeClass('d-none').addClass('d-flex');
                        $('#cartCount').text(response.cart_meta.count);
                        $('#cartKonsumenName').text(response.cart_meta.konsumen_nama);
                    } else {
                        $('#cartBanner').removeClass('d-flex').addClass('d-none');
                    }
                }
            }
        });

        // Event handler klik link cicilan untuk menampilkan modal global dinamis
        $(document).on('click', '.trigger-modal-cicilan', function(e) {
            e.preventDefault();
            let nota = $(this).data('nota');
            let history = $(this).data('history'); // Berisi array objek hasil parse json otomatis jquery

            $('#textModalNota').text(nota);
            let htmlRows = '';

            history.forEach(function(item) {
                htmlRows += `<tr>
                                <td>${item.no}</td>
                                <td>${item.tanggal}</td>
                                <td class="text-end">Rp. ${item.nominal}</td>
                             </tr>`;
            });

            $('#contentModalCicilan').html(htmlRows);
            $('#modalGlobalCicilan').modal('show');
        });

        $('.filter-dt').change(function() {
            table.draw();
        });

        $('#resetFilter').click(function() {
            $('.filter-dt').val('').trigger('change');
            table.draw();
        });

        // Event Submit Bayar
        $(document).on('click', '.btn-submit-bayar', function(e){
            e.preventDefault();
            var form = $(this).closest('form');
            var nominal = form.data('nominal');

            Swal.fire({
                title: 'Apakah Anda Yakin? Sisa Tagihan Sebesar: Rp. ' + nominal,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                    $('#spinner').show();
                }
            });
        });

        // Event Submit Void
        $(document).on('click', '.btn-submit-void', function(e){
            e.preventDefault();
            var form = $(this).closest('form');

            Swal.fire({
                title: 'Apakah anda Yakin Ingin Melakukan Void? Masukkan Password Konfirmasi',
                input: 'password',
                inputAttributes: { autocapitalize: 'off' },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: '{{route('pengaturan.password-konfirmasi-cek')}}',
                            type: 'POST',
                            data: JSON.stringify({ password: password }),
                            contentType: 'application/json',
                            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                            success: function(data) {
                                if (data.status === 'success') {
                                    resolve();
                                } else {
                                    Swal.fire({ icon: 'error', title: 'Oops...', text: data.message });
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                Swal.fire({ icon: 'error', title: 'Oops...', text: textStatus });
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                    $('#spinner').show();
                }
            });
        });
    });
</script>
@endpush
