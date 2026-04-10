@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="mb-0 font-weight-bold text-dark">Checklist Kunjungan Sales</h2>
            <p class="text-muted mb-0">Checklist dan rekapan kunjungan konsumen</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{route('home')}}" class="btn btn-light shadow-sm me-2">
                <i class="fas fa-home text-primary"></i> Dashboard
            </a>
            @if (auth()->user()->role !== 'sales')
           <button onclick="downloadPDF()" class="btn btn-primary shadow-sm">
                <i class="fas fa-print"></i> Cetak PDF
            </button>
            @endif

        </div>
    </div>

    <div class="row row-cols-1 row-cols-md-3 row-cols-xl-5 mb-4">
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-store fa-lg text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Total Toko</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-total">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Dikunjungi (Bln Ini)</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-visited">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-times-circle fa-lg text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Tdk Dikunjungi (Bln Ini)</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-not-visited">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-exclamation-circle fa-lg text-warning"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Blm Dikunjungi (Bln Ini)</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-empty">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-chart-pie fa-lg text-info"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.75rem;">Persentase Kunjungan (Bln Ini)</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-percent">0%</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-secondary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-bullseye fa-lg text-secondary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.70rem;">Rata2 Wajib Kunjungan / Hari</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-avg-wajib">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-walking fa-lg text-primary"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.70rem;">Rata2 Kunjungan Real / Hari</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-avg-real">0</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card border-0 shadow-sm rounded-3 h-100 kpi-card">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 p-3 rounded-3 me-3">
                        <i class="fas fa-route fa-lg text-danger"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-0 font-weight-bold text-uppercase" style="font-size: 0.70rem;">Target Rata2 Kunjungan / Sisa Hari</p>
                        <h4 class="mb-0 font-weight-bold text-dark" id="kpi-avg-target">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <form id="filterForm">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filterTahun" class="form-label text-muted" style="font-size:0.85rem;">Tahun</label>
                        <select id="filterTahun" name="tahun" class="form-select border-0 bg-light">
                            @foreach ($pilihan_tahun as $th)
                                <option value="{{ $th }}" {{ $tahun_aktif == $th ? 'selected' : '' }}>{{ $th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filterStatusKunjungan" class="form-label text-muted" style="font-size:0.85rem;">Status Bulan Ini</label>
                        <select id="filterStatusKunjungan" name="status_kunjungan" class="form-select border-0 bg-light">
                            <option value="" selected>-- Semua --</option>
                            <option value="visited">Dikunjungi</option>
                            <option value="not_visited">Tidak Dikunjungi</option>
                            <option value="empty">Belum Dikunjungi</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filterKodeToko" class="form-label text-muted" style="font-size:0.85rem;">Kode Toko</label>
                        <select id="filterKodeToko" name="kode_toko" class="form-select border-0 bg-light">
                            <option value="" selected>-- Semua --</option>
                            @foreach ($kode_toko as $k)
                            <option value="{{ $k->id }}">{{ $k->kode }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if (auth()->user()->role !== 'sales')
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filterSalesArea" class="form-label text-muted" style="font-size:0.85rem;">Sales Area</label>
                        <select id="filterSalesArea" name="area" class="form-select border-0 bg-light">
                            <option value="" selected>-- Semua --</option>
                            @foreach ($sales_area as $salesArea)
                            <option value="{{ $salesArea->id }}">{{ $salesArea->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filterKecamatan" class="form-label text-muted" style="font-size:0.85rem;">Kecamatan</label>
                        <select id="filterKecamatan" name="kecamatan" class="form-select border-0 bg-light">
                            <option value="">-- Semua --</option>
                            @foreach ($kecamatan_filter as $kec)
                                <option value="{{ $kec->id }}">{{ $kec->nama_wilayah }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1 mb-2 mb-md-0">
                        <a href="{{ url()->current() }}" class="btn btn-outline-secondary w-100" title="Reset Filter">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0 mx-2 my-3">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100 m-0" id="data">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle border-bottom-0 text-muted" style="font-size:0.85rem; letter-spacing:1px;">KODE</th>
                            <th class="text-center align-middle border-bottom-0 text-muted" style="font-size:0.85rem; letter-spacing:1px;">NAMA TOKO</th>
                            <th class="text-center align-middle border-bottom-0 text-muted" style="font-size:0.85rem; letter-spacing:1px;">KECAMATAN</th>
                            <th class="text-center align-middle border-bottom-0 text-muted" style="font-size:0.85rem; letter-spacing:1px;">AREA</th>
                            @foreach ($months as $key => $month)
                            <th class="text-center align-middle border-bottom-0 text-muted" style="font-size:0.85rem;">{{ strtoupper(substr($month, 0, 3)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 font-weight-bold text-dark"><i class="fas fa-chart-bar text-primary me-2"></i> Rekapitulasi Kunjungan Per Bulan</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover w-100 m-0 text-center" id="table-rekap-bulanan">
                    <thead class="bg-light">
                        <tr>
                            <th class="align-middle text-start text-muted" style="font-size:0.85rem; letter-spacing:1px; min-width: 180px;">KETERANGAN</th>
                            @foreach ($months as $key => $month)
                            <th class="align-middle text-muted" style="font-size:0.85rem; width: 6%;">{{ strtoupper(substr($month, 0, 3)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-start font-weight-bold text-success align-middle">
                                <i class="fas fa-check-circle me-1"></i> Dikunjungi
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="align-middle font-weight-bold" id="rekap-visit-{{ $i }}">0</td>
                            @endfor
                        </tr>
                        <tr>
                            <td class="text-start font-weight-bold text-danger align-middle">
                                <i class="fas fa-times-circle me-1"></i> Tidak Dikunjungi
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="align-middle font-weight-bold" id="rekap-notvisit-{{ $i }}">0</td>
                            @endfor
                        </tr>
                        <tr>
                            <td class="text-start font-weight-bold text-warning align-middle">
                                <i class="fas fa-exclamation-circle me-1"></i> Belum Dikunjungi
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="align-middle font-weight-bold text-secondary" id="rekap-empty-{{ $i }}">0</td>
                            @endfor
                        </tr>
                        {{-- <tr class="bg-light">
                            <td class="text-start font-weight-bold text-secondary align-middle">
                                <i class="fas fa-bullseye me-1"></i> Rata-rata Wajib / Hari
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="align-middle font-weight-bold text-secondary" id="rekap-avg-wajib-{{ $i }}">0</td>
                            @endfor
                        </tr>
                        <tr class="bg-light">
                            <td class="text-start font-weight-bold text-primary align-middle">
                                <i class="fas fa-walking me-1"></i> Rata-rata Real / Hari
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="align-middle font-weight-bold text-primary" id="rekap-avg-real-{{ $i }}">0</td>
                            @endfor
                        </tr>
                        <tr class="bg-light">
                            <td class="text-start font-weight-bold text-danger align-middle">
                                <i class="fas fa-route me-1"></i> Target / Sisa Hari
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="align-middle font-weight-bold text-danger" id="rekap-avg-target-{{ $i }}">0</td>
                            @endfor
                        </tr> --}}
                        <tr class="bg-light">
                            <td class="text-start font-weight-bold text-info align-middle">
                                <i class="fas fa-percent me-1"></i> Persentase Capaian
                            </td>
                            @for($i = 1; $i <= 12; $i++)
                                <td class="align-middle font-weight-bold text-info" id="rekap-percent-{{ $i }}">0%</td>
                            @endfor
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@include('swal')
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    body { background-color: #f8f9fa; }
    .kpi-card { transition: transform 0.3s ease; }
    .kpi-card:hover { transform: translateY(-5px); }

    .col-nama-toko {
        max-width: 250px;
        min-width: 200px;
        white-space: normal !important;
        word-wrap: break-word;
        font-weight: 500;
    }
    .col-bulan { min-width: 45px; }

    .checklist-cell {
        transition: all 0.2s ease-in-out;
        border-radius: 4px;
        margin: 2px;
        border: 1px solid transparent;
    }
    .checklist-cell:hover {
        transform: scale(1.1);
        border: 1px solid #ddd;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        z-index: 10;
        position: relative;
    }
    .checklist-cell.bg-danger {
        background-color: #ff0000 !important;
        border-color: #ff0000 !important;
    }
    .checklist-cell.bg-danger:hover { background-color: #ffcccc !important; }

    table.dataTable tbody tr:hover { background-color: #f1f5f9; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });

    $(document).ready(function() {
        $('#filterTahun, #filterStatusKunjungan, #filterSalesArea, #filterKodeToko, #filterKecamatan').select2({
            theme: 'bootstrap-5', width: '100%'
        });

        let serverMonth = {{ date('n') }};
        let serverYear = {{ date('Y') }};

        let userRole = "{{ auth()->user()->role ?? '' }}";

        var table = $('#data').DataTable({
            processing: true,
            serverSide: true,
            deferRender: true,
            scroller: { loadingIndicator: true },
            scrollY: "550px",
            scrollCollapse: true,
            stateSave: true,
            scrollX: true,
            language: {
                processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
            },
            ajax: {
                url: "{{ route('checklist-sales') }}",
                data: function (d) {
                    d.kode_toko = $('#filterKodeToko').val();
                    d.area = $('#filterSalesArea').val();
                    d.kecamatan = $('#filterKecamatan').val();
                    d.tahun = $('#filterTahun').val();
                    d.status_kunjungan = $('#filterStatusKunjungan').val();
                }
            },
            columns: [
                {data: 'full_kode', name: 'kode', className: 'text-center align-middle text-muted', width: '8%'},
                {data: 'nama_toko', name: 'nama', className: 'text-start align-middle col-nama-toko text-dark'},
                {data: 'nama_kecamatan', name: 'kecamatan_id', className: 'text-start align-middle text-muted', width: '12%'},
                {data: 'sales_area', name: 'karyawan_id', className: 'text-center align-middle', width: '10%'},

                @for($i = 1; $i <= 12; $i++)
                {
                    data: 'bulan_{{ $i }}', name: 'bulan_{{ $i }}', orderable: false, searchable: false, className: 'text-center align-middle p-1 col-bulan',
                    render: function(data, type, row) {
                        let content = '';
                        let bgClass = 'bg-white';
                        let pointer = data.can_edit ? 'cursor: pointer;' : 'cursor: not-allowed; opacity: 0.6;';

                        if (data.status === 'visited') {
                            content = '<i class="fas fa-check text-success"></i>';
                        } else if (data.status === 'not_visited') {
                            bgClass = 'bg-danger';
                        }

                        return `<div class="w-100 h-100 d-flex align-items-center justify-content-center checklist-cell ${bgClass}"
                                     style="min-height: 35px; ${pointer}"
                                     data-konsumen-id="${data.konsumen_id}" data-bulan="${data.bulan}" data-tahun="${data.tahun}" data-status="${data.status}" data-can-edit="${data.can_edit}">
                                     ${content}
                                </div>`;
                    }
                },
                @endfor
            ]
        });

        // UPDATE TABEL BAWAH DAN KARTU ATAS SETIAP KALI AJAX SELESAI MELOAD
        table.on('xhr.dt', function (e, settings, json, xhr) {
            if (json && json.summary) {

                // Ambil data untuk spesifik bulan berjalan saat ini untuk KPI Card Atas
                let currentMonthData = json.summary.monthly[serverMonth];

                // Update KPI Bulan Berjalan (Atas)
                $('#kpi-total').fadeOut(150, function() { $(this).text(json.summary.total_konsumen).fadeIn(150); });
                $('#kpi-visited').fadeOut(150, function() { $(this).text(currentMonthData.visited).fadeIn(150); });
                $('#kpi-not-visited').fadeOut(150, function() { $(this).text(currentMonthData.not_visited).fadeIn(150); });
                $('#kpi-empty').fadeOut(150, function() { $(this).text(currentMonthData.empty).fadeIn(150); });
                $('#kpi-percent').fadeOut(150, function() { $(this).text(currentMonthData.percentage + '%').fadeIn(150); });

               $('#kpi-avg-wajib').fadeOut(150, function() { $(this).text(currentMonthData.avg_wajib).fadeIn(150); });
                $('#kpi-avg-real').fadeOut(150, function() { $(this).text(currentMonthData.avg_real).fadeIn(150); });
                $('#kpi-avg-target').fadeOut(150, function() { $(this).text(currentMonthData.avg_target).fadeIn(150); });

                // Update Tabel Rekap Bulanan (Bawah)
                for (let i = 1; i <= 12; i++) {
                    let dataBulan = json.summary.monthly[i];

                    $('#rekap-visit-' + i).text(dataBulan.visited);
                    $('#rekap-notvisit-' + i).text(dataBulan.not_visited);
                    $('#rekap-empty-' + i).text(dataBulan.empty); // Rekap Belum Dikunjungi Baru

                    $('#rekap-avg-wajib-' + i).text(dataBulan.avg_wajib);
                    $('#rekap-avg-real-' + i).text(dataBulan.avg_real);

                    // Khusus Target Sisa hari, jika 0 tampilkan "-" supaya tabel lebih bersih untuk bulan lalu
                    let textTarget = dataBulan.avg_target == 0 ? '-' : dataBulan.avg_target;
                    $('#rekap-avg-target-' + i).text(textTarget);

                    let cellPercent = $('#rekap-percent-' + i);
                    cellPercent.text(dataBulan.percentage + '%');

                    // Pewarnaan dinamis untuk persentase
                    cellPercent.removeClass('text-danger text-warning text-success text-info');
                    if (dataBulan.percentage < 50 && dataBulan.percentage > 0) cellPercent.addClass('text-danger');
                    else if (dataBulan.percentage >= 50 && dataBulan.percentage < 100) cellPercent.addClass('text-warning');
                    else if (dataBulan.percentage === 100) cellPercent.addClass('text-success');
                    else cellPercent.addClass('text-info');
                }
            }
        });

        $('#filterKodeToko, #filterSalesArea, #filterKecamatan, #filterTahun, #filterStatusKunjungan').on('change', function() {
            table.ajax.reload();
        });

        // LOGIKA KLIK CHECKLIST
        $('#data tbody').on('click', '.checklist-cell', function() {
            let cell = $(this);
            let canEdit = cell.data('can-edit');
            let konsumenId = cell.data('konsumen-id');
            let bulan = parseInt(cell.data('bulan'));
            let tahun = parseInt(cell.data('tahun'));
            let status = cell.data('status');

            if (userRole !== 'su' && userRole !== 'admin' && userRole !== 'user') {

                return;
            }

            if (!canEdit) {
                if (userRole !== 'su' && userRole !== 'admin') {

                    return; // Hentikan proses jika bukan su/admin
                }
                let errorMsg = (tahun < serverYear || (tahun === serverYear && bulan < serverMonth))
                    ? 'Waktu pengisian bulan ke-' + bulan + ' sudah terlewat.'
                    : (tahun > serverYear || (tahun === serverYear && bulan > serverMonth))
                        ? 'Belum waktunya! Bulan ke-' + bulan + ' belum berjalan.'
                        : 'Anda tidak memiliki izin mengubah data.';

                Swal.fire({ icon: 'warning', title: 'Akses Ditolak', text: errorMsg, confirmButtonColor: '#3085d6' });
                return;
            }

            if (status === 'empty') {
                Swal.fire({
                    title: 'Status Kunjungan',
                    text: "Bulan ke-" + bulan + " Tahun " + tahun,
                    icon: 'question', showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-check"></i> Dikunjungi',
                    cancelButtonText: '<i class="fas fa-times"></i> Tidak Dikunjungi',
                    confirmButtonColor: '#28a745', cancelButtonColor: '#dc3545',
                }).then((result) => {
                    if (result.isConfirmed) simpanStatus(konsumenId, bulan, tahun, 'visited', cell);
                    else if (result.dismiss === Swal.DismissReason.cancel) simpanStatus(konsumenId, bulan, tahun, 'not_visited', cell);
                });
            } else {

                if (userRole !== 'su' && userRole !== 'admin') {

                    return; // Hentikan proses jika bukan su/admin
                }

                Swal.fire({
                    title: 'Batalkan Status',
                    icon: 'warning',
                    text: 'Masukkan password konfirmasi:',
                    input: 'password', inputAttributes: { autocapitalize: 'off' },
                    showCancelButton: true, confirmButtonText: 'Batalkan', showLoaderOnConfirm: true,
                    preConfirm: (password) => {
                        if (!password) { Swal.showValidationMessage('Wajib diisi!'); return false; }
                        return $.ajax({
                            url: "{{ route('checklist-sales.uncheck') }}", type: 'POST',
                            data: { konsumen_id: konsumenId, bulan: bulan, tahun: tahun, password: password }
                        }).catch(err => { Swal.showValidationMessage(err.responseJSON.message || 'Error server.'); });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed && result.value.success) {
                        cell.data('status', 'empty').removeClass('bg-danger').addClass('bg-white').html('');
                        table.ajax.reload(null, false);
                        Swal.fire('Berhasil!', result.value.message, 'success');
                    }
                });
            }
        });

        function simpanStatus(konsumenId, bulan, tahun, status, cellElement) {
            $.ajax({
                url: "{{ route('checklist-sales.update') }}", type: 'POST',
                data: { konsumen_id: konsumenId, bulan: bulan, tahun: tahun, status: status },
                success: function(response) {
                    if(response.success) {
                        cellElement.data('status', status);
                        if (status === 'visited') {
                            cellElement.removeClass('bg-danger').addClass('bg-white').html('<i class="fas fa-check text-success"></i>');
                        } else {
                            cellElement.removeClass('bg-white').addClass('bg-danger').html('');
                        }
                        table.ajax.reload(null, false);
                    }
                },
                error: function(error) { Swal.fire('Error', error.responseJSON.message || 'Gagal.', 'error'); }
            });
        }
    });

    function downloadPDF() {
        var params = new URLSearchParams();
        if ($('#filterKodeToko').val()) params.append('kode_toko', $('#filterKodeToko').val());
        if ($('#filterSalesArea').val()) params.append('area', $('#filterSalesArea').val());
        if ($('#filterKecamatan').val()) params.append('kecamatan', $('#filterKecamatan').val());
        if ($('#filterTahun').val()) params.append('tahun', $('#filterTahun').val());
        if ($('#filterStatusKunjungan').val()) params.append('status_kunjungan', $('#filterStatusKunjungan').val());

        window.open("{{ route('checklist-sales.download') }}?" + params.toString(), '_blank');
    }
</script>
@endpush
