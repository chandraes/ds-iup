@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="max-width: 1300px;">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="{{route('billing.otorisasi-pembelian', ['asistenId'=>$b->user_id])}}"
                            class="text-decoration-none">Detail Beli</a></li>
                    <li class="breadcrumb-item active">Keranjang</li>
                </ol>
            </nav>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h1 class="h3 fw-bold text-dark mb-0">KONFIRMASI PESANAN</h1>
                    <p class="text-muted mb-0">Tinjau item dan detail pembayaran sebelum menyelesaikan transaksi.</p>
                </div>
                <a href="{{route('billing.otorisasi-pembelian', ['asistenId'=>$b->user_id])}}" class="btn btn-outline-secondary rounded-pill">
                    <i class="fa fa-arrow-left me-2"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    @php
    $diskon = 0;
    $apaPpn = $b->kas_ppn ? 1 : 0;
    $ppnRateVal = $ppnRate ?? 0;
    $total = $keranjang ? $keranjang->sum('total') : 0;
    $ppn = $apaPpn == 1 ? $total * ($ppnRateVal/100) : 0;
    $add_fee = 0;
    @endphp
    @if (auth()->user()->role != 'asisten-admin')
    <form action="{{route('billing.form-beli.detail.lanjutkan', $b->id)}}" method="post" id="storeForm">
        @csrf
        @endif
        <input type="hidden" name="keranjang_beli_id" value="{{ $b->id }}">

        {{-- HIDDEN INPUT BARU UNTUK MENYIMPAN NILAI PPN DP --}}
        @if ($b->sistem_pembayaran == 2 && $apaPpn == 1)
        <input type="hidden" id="dp_ppn_value" value="0">
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4 rounded-3">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fa fa-truck me-2"></i>Informasi Supplier</h5>
                    </div>
                    <div class="card-body bg-light-subtle">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="small text-muted text-uppercase fw-bold">Supplier</label>
                                <p class="fw-bold mb-0">{{$supplier->nama}}</p>
                                <input type="hidden" name="supplier_id" value="{{$supplier->id}}">
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Nama Rekening</label>
                                <p class="mb-0">{{$supplier->nama_rek}}</p>
                                <input type="hidden" name="nama_rek" value="{{$supplier->nama_rek}}">
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Nomor Rekening</label>
                                <p class="mb-0">{{$supplier->no_rek}}</p>
                                <input type="hidden" name="no_rek" value="{{$supplier->no_rek}}">
                            </div>
                            <div class="col-md-4">
                                <label class="small text-muted text-uppercase fw-bold">Bank</label>
                                <p class="mb-0"><span class="badge bg-secondary">{{$supplier->bank}}</span></p>
                                <input type="hidden" name="bank" value="{{$supplier->bank}}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-primary"><i class="fa fa-shopping-cart me-2"></i>Daftar Barang</h5>
                        <span class="badge bg-info text-dark">{{ count($keranjang) }} Item</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Barang</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Harga Satuan</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($keranjang->where('barang_ppn', 0) as $item)
                                    <tr class="{{$item->stok_kurang == 1 ? 'table-danger' : ''}}">
                                        <td class="ps-4">
                                            <div class="fw-bold">{{$item->barang->barang_nama?->nama}}</div>
                                            <small class="text-muted">{{$item->barang->merk}} |
                                                {{$item->barang->kode}}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill bg-outline-primary text-dark border px-3">
                                                {{$item->nf_qty}} {{ $item->barang->satuan ? $item->barang->satuan->nama
                                                : '-' }}
                                            </span>
                                        </td>
                                        <td class="text-end">{{$item->nf_harga}}</td>
                                        <td class="text-end fw-bold">{{$item->nf_total}}</td>
                                        <td class="text-center pe-4">
                                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle"
                                                onclick="deleteKeranjang({{$item->id}})" title="Hapus">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="sticky-top" style="top: 2rem; z-index: 10;">
                    @if (auth()->user()->role != 'asisten-admin')
                    <div class="card border-0 shadow-sm mb-4 rounded-3">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="uraian" class="form-label fw-bold small text-muted text-uppercase">Uraian
                                    Transaksi</label>
                                <textarea class="form-control" name="uraian" id="uraian" rows="2"
                                    placeholder="Contoh: Pembelian Stok Bulanan" required
                                    maxlength="20">{{old('uraian')}}</textarea>
                            </div>

                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="diskon"
                                        class="form-label fw-bold small text-muted text-uppercase">Diskon (Rp)</label>
                                    <input type="text" class="form-control" name="diskon" id="diskon" value="0"
                                        onkeyup="add_diskon()">
                                </div>
                                <div class="col-6">
                                    <label for="add_fee"
                                        class="form-label fw-bold small text-muted text-uppercase">Penyesuaian</label>
                                    <input type="text" class="form-control" name="add_fee" id="add_fee" value="0"
                                        onkeyup="add_diskon()">
                                </div>
                            </div>

                            @if ($b->sistem_pembayaran == 2)
                            <hr>
                            <div class="row g-2">
                                <div class="col-12 mb-2">
                                    <label for="dp" class="form-label fw-bold small text-uppercase">Jatuh Tempo</label>
                                    <div class="input-group">

                                        <input type="text" class="form-control text-end" name="jatuh_tempo"
                                            id="jatuh_tempo" value="{{$jatuhTempo}}" readonly>
                                        <span class="input-group-text "> <i class="fa fa-calendar"></i></span>
                                    </div>
                                </div>
                                <div class="col-12 mb-2">
                                    <label for="dp" class="form-label fw-bold small text-danger text-uppercase">Uang
                                        Muka (DP)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-danger text-white border-danger">Rp</span>
                                        <input type="text" class="form-control border-danger" name="dp" id="dp"
                                            value="0" onkeyup="add_dp()">
                                    </div>
                                </div>
                                @if ($apaPpn == 1)
                                <div class="col-12">
                                    <label for="dp_ppn"
                                        class="form-label fw-bold small text-muted text-uppercase">Gunakan PPN untuk
                                        DP?</label>
                                    <select class="form-select" name="dp_ppn" id="dp_ppn" onchange="add_dp_ppn()"
                                        required>
                                        <option value="0">Tanpa PPn</option>
                                        <option value="1">Dengan PPn</option>
                                    </select>
                                </div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif


                    <div class="card border-0 shadow-lg bg-primary text-white rounded-3">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-4">Ringkasan Pembayaran</h5>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Total DPP</span>
                                <span id="tdTotal">{{number_format($total, 0, ',','.')}}</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <span>Diskon</span>
                                <span id="tdDiskon">0</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2">
                                <small>DPP Setelah Diskon</small>
                                <small id="tdTotalSetelahDiskon">{{number_format($total, 0, ',','.')}}</small>
                            </div>

                            @if ($apaPpn == 1)
                            <div class="d-flex justify-content-between mb-2">
                                <span>PPN ({{$ppnRateVal}}%)</span>
                                <span id="tdPpn">{{number_format($ppn, 0, ',','.')}}</span>
                            </div>
                            @endif

                            <div class="d-flex justify-content-between mb-3">
                                <span>Penyesuaian</span>
                                <span id="tdAddFee">0</span>
                            </div>

                            <hr class="bg-white">

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold mb-0">GRAND TOTAL</h4>
                                <h4 class="fw-bold mb-0" id="grand_total">{{number_format($total + $ppn, 0, ',','.')}}
                                </h4>
                            </div>

                            @if ($b->sistem_pembayaran == 2)
                            <div class="bg-white text-dark rounded p-3 mb-4 shadow-sm">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>DP Terbayar:</span>
                                    <span class="fw-bold text-danger" id="totalDpTd">0</span>
                                </div>
                                @if ($apaPpn == 1)
                                <div class="d-flex justify-content-between small mb-1 border-bottom pb-1">
                                    <span>Sisa PPN:</span>
                                    {{-- Catatan: Elemen Sisa PPN di-update di add_dp_ppn() --}}
                                    <span id="sisaPPN">{{number_format($ppn, 0, ',','.')}}</span>
                                </div>
                                @endif
                                <div class="d-flex justify-content-between fw-bold mt-2">
                                    <span>Sisa Tagihan:</span>
                                    <span id="sisa">{{number_format($total + $ppn, 0, ',','.')}}</span>
                                </div>
                            </div>
                            @endif
                            @if (auth()->user()->role != 'asisten-admin')
                            <button type="submit"
                                class="btn btn-light btn-lg w-100 fw-bold text-primary shadow-sm mt-2">
                                LANJUTKAN <i class="fa fa-chevron-right ms-2"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="mt-3 text-end">
                        @include('wa-status')
                    </div>
                </div>
            </div>
        </div>
        @if (auth()->user()->role != 'asisten-admin')
    </form>
    @endif
</div>
@endsection

@push('css')
<style>
    body {
        background-color: #f8f9fa;
    }

    .card {
        transition: transform 0.2s ease;
    }

    .table thead th {
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
    }

    .bg-light-subtle {
        background-color: #f1f4f8 !important;
    }
</style>
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush

@push('js')
{{-- <script src="{{asset('assets/js/cleave.min.js')}}"></script> --}}
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    // Fungsionalitas SweetAlert TIDAK DIUBAH
    $('#storeForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Konfirmasi Lanjutkan Transaksi',
            text: "Pastikan semua detail sudah benar. Anda akan melanjutkan ke proses berikutnya.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan!'
            }).then((result) => {
            if (result.isConfirmed) {
                this.submit();
            }
        })
    });

    function deleteKeranjang(id)
    {
        Swal.fire({
            title: 'Hapus Item?',
            text: "Anda yakin ingin menghapus item ini dari keranjang?",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                $.ajax({
                    url: '{{route('billing.form-beli.detail.preview.delete')}}',
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        location.reload();
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan saat menghapus item.', 'error');
                    }
                });
            }
        });
    }
</script>
<script>
    // SKRIP CLEAVE & PERHITUNGAN (DIUBAH UNTUK MEMPERBAIKI BUG DP)

    // Inisialisasi Cleave
    var cleaveDiskon = new Cleave('#diskon', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.',
    });

    var cleaveAddFee = new Cleave('#add_fee', {
        numeral: true,
        negative: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.',
    });

    // Inisialisasi DP jika ada
    @if ($b->sistem_pembayaran == 2)
    var cleaveDp = new Cleave('#dp', {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.',
    });

     flatpickr("#jatuh_tempo", {
                    dateFormat: "d-m-Y",
                });
    @endif

    // Helper untuk membersihkan dan mengkonversi angka
    function cleanNumber(value) {
        // Hati-hati dengan value yang berupa textContent/value dari input. Gunakan regex yang aman.
        if (typeof value !== 'string') return 0;
        return Number(value.replace(/[^0-9,-]/g, '').replace(/\./g, '').replace(',', '.'));
    }

    // Helper untuk memformat angka
    function formatNumber(number) {
        // Gunakan toLocaleString untuk format ID
        return number.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    // PPN Rate dari PHP
    const apaPpn = {!! $apaPpn !!};
    const ppnRate = {!! $ppnRate !!} / 100;

    function add_diskon() {
        var diskon = cleanNumber(document.getElementById('diskon').value);
        var totalDpp = cleanNumber(document.getElementById('tdTotal').textContent);
        var addFee = cleanNumber(document.getElementById('add_fee').value);

        if (isNaN(totalDpp)) totalDpp = 0;

        var totalDppSetelahDiskon = totalDpp - diskon;

        // Update nilai di tabel
        document.getElementById('tdDiskon').textContent = formatNumber(diskon);
        document.getElementById('tdTotalSetelahDiskon').textContent = formatNumber(totalDppSetelahDiskon);
        document.getElementById('tdAddFee').textContent = formatNumber(addFee);

        // Lanjutkan ke PPN dan Sisa
        add_ppn();
    }

    function add_ppn() {
        var totalDppSetelahDiskon = cleanNumber(document.getElementById('tdTotalSetelahDiskon').textContent);
        var addFee = cleanNumber(document.getElementById('tdAddFee').textContent);
        var vPpn = 0;

        if (apaPpn === 1) {
            vPpn = Math.floor(totalDppSetelahDiskon * ppnRate);
            document.getElementById('tdPpn').textContent = formatNumber(vPpn);
        }

        var grandTotal = totalDppSetelahDiskon + vPpn + addFee;
        document.getElementById('grand_total').textContent = formatNumber(grandTotal);

        // Setelah Grand Total dihitung, cek DP/Sisa
        if (document.getElementById('dp')) {
            add_dp();
        } else {
            check_sisa(); // Jika tidak ada DP, langsung hitung sisa (walau sisa=grandtotal)
        }
    }

    function add_dp(){
        // FUNGSI INI HANYA MEMICU PERHITUNGAN DP PPN DAN SISA

        // BARIS DI BAWAH INI ADALAH SUMBER ERROR (Mencoba menulis ke elemen yang tidak ada)
        // document.getElementById('dpTd').textContent = formatNumber(dp);

        // Panggil DP PPN jika ada
        if (document.getElementById('dp_ppn')) {
            add_dp_ppn();
        } else {
            check_sisa();
        }
    }

    function add_dp_ppn(){
        var dp = 0;
        var ppn_dp_num = 0;

        // Hanya dijalankan jika PPN diaktifkan DAN form DP ada
        if (apaPpn === 1 && document.getElementById('dp')) {
            var apa_dp_ppn = document.getElementById('dp_ppn').value;
            dp = cleanNumber(document.getElementById('dp').value); // Baca dari input DP
            var ppn_total = cleanNumber(document.getElementById('tdPpn').textContent);

            if (apa_dp_ppn === '1') {
                ppn_dp_num = Math.floor(dp * ppnRate);
            }

            // --- FIX BAGIAN B: SIMPAN NILAI PPN DP KE HIDDEN INPUT BARU ---
            document.getElementById('dp_ppn_value').value = ppn_dp_num;

            var sisa_ppn = ppn_total - ppn_dp_num;
            sisa_ppn = (sisa_ppn < 0) ? 0 : sisa_ppn;

            // document.getElementById('dpPPNtd').textContent = formatNumber(ppn_dp_num); // ID ini juga tidak ada
            document.getElementById('sisaPPN').textContent = formatNumber(sisa_ppn);
        }
        check_sisa();
    }

    function check_sisa(){
        var grandTotal = cleanNumber(document.getElementById('grand_total').textContent);

        // --- FIX BAGIAN A: BACA NILAI DP DARI INPUT DAN HIDDEN INPUT ---
        var dpBase = cleanNumber(document.getElementById('dp')?.value || '0'); // Ambil nilai DP dasar dari input
        var dpPPN = cleanNumber(document.getElementById('dp_ppn_value')?.value || '0'); // Ambil nilai PPN DP dari hidden input

        // Hitung Total DP (Base DP + PPN DP)
        var totalDp = dpBase + dpPPN;

        if (document.getElementById('totalDpTd')) {
            document.getElementById('totalDpTd').textContent = formatNumber(totalDp); // <<<< INI YANG MENGUBAH TEXT 'DP Terbayar'
        }

        // Hitung Sisa Tagihan
        var sisaTagihan = grandTotal - totalDp;
        sisaTagihan = (sisaTagihan < 0) ? 0 : sisaTagihan;

        // Update Sisa Tagihan
        var sisaElement = document.getElementById('sisa');
        if (sisaElement) {
            sisaElement.textContent = formatNumber(sisaTagihan);
        }
    }

    // Panggil fungsi inisial saat halaman dimuat (untuk memastikan semua total terhitung dengan benar)
    document.addEventListener('DOMContentLoaded', function() {
        add_diskon(); // Memulai seluruh rangkaian perhitungan
    });
</script>
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
@endpush
