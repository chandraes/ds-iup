@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    {{-- ================================================================================== --}}
    {{-- BAGIAN HEADER & NAVIGASI (TIDAK DIUBAH) --}}
    {{-- ================================================================================== --}}
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1 class="fw-bold mb-0">
                OTORISASI PEMBELIAN <br> {{$user->name}}
            </h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>

                </tr>
            </table>
        </div>
    </div>
    {{-- ================================================================================== --}}

    {{-- MAIN CONTENT --}}
    <div class="row justify-content-center">
        @php
            $diskon = 0;
            $ppn = $data['jenis'] == 1 ? $keranjang->sum('total') * ($ppnRate/100) : 0;
            $total = $keranjang ? $keranjang->sum('total') : 0;
            $add_fee = 0;
            $dp = 0;
            $dpPPN = 0;
            $totalDp = 0;
            $sisaPPN = 0;
        @endphp

        <div class="col-md-12">

            <form action="{{route('billing.otorisasi-pembelian.keranjang.checkout')}}" method="post" id="beliBarang">
                @csrf
                <input type="hidden" name="asistenId" value="{{$user->id}}">
                <input type="hidden" name="kas_ppn" value="{{$data['jenis'] == 1 ? 1 : 0}}">
                <input type="hidden" name="jenis" value="{{$data['jenis']}}">
                <input type="hidden" name="tempo" value="{{$data['tempo']}}">

                {{-- CARD 1: INFORMASI SUPPLIER & REKENING --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-primary"><i class="fa fa-handshake-o me-2"></i>Informasi Supplier</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="supplier_id" class="form-label fw-bold">Pilih Supplier</label>
                                <select class="form-select" name="supplier_id" id="supplier_id" onchange="funSupplier()">
                                    <option value="">-- Silahkan Pilih Supplier --</option>
                                    @foreach ($supplier as $s)
                                    <option value="{{$s->id}}">{{$s->nama}}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Area Informasi Rekening --}}
                            <div class="col-12">
                                <div class="p-3 bg-light rounded border">
                                    <h6 class="text-muted mb-3 small text-uppercase fw-bold">Detail Rekening (Otomatis)</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small">Nama Rekening</label>
                                            <input type="text" class="form-control bg-white" name="nama_rek" id="nama_rek"
                                                value="{{old('nama_rek')}}" maxlength="15" required readonly>
                                            @if ($errors->has('nama_rek'))
                                            <div class="invalid-feedback d-block">{{$errors->first('nama_rek')}}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Bank</label>
                                            <input type="text" class="form-control bg-white" name="bank" id="bank"
                                                value="{{old('bank')}}" maxlength="10" required readonly>
                                            @if ($errors->has('bank'))
                                            <div class="invalid-feedback d-block">{{$errors->first('bank')}}</div>
                                            @endif
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small">Nomor Rekening</label>
                                            <input type="text" class="form-control bg-white" name="no_rek" id="no_rek"
                                                value="{{old('no_rek')}}" required readonly>
                                            @if ($errors->has('no_rek'))
                                            <div class="invalid-feedback d-block">{{$errors->first('no_rek')}}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($data['tempo'] == 1)
                            <div class="col-md-6">
                                <label for="tempo_hari" class="form-label fw-bold">Tempo Pembayaran</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="tempo_hari" id="tempo_hari" disabled>
                                    <span class="input-group-text">Hari</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="jatuh_tempo" class="form-label fw-bold">Tgl. Jatuh Tempo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control" name="jatuh_tempo" id="jatuh_tempo" readonly required>
                                </div>
                            </div>
                            @push('js')
                            <script>
                                if(document.getElementById('jatuh_tempo')){
                                    flatpickr("#jatuh_tempo", { dateFormat: "d-m-Y" });
                                }
                            </script>
                            @endpush
                            @endif
                        </div>
                    </div>
                </div>

                {{-- CARD 2: BIAYA & DISKON --}}
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-success"><i class="fa fa-tags me-2"></i>Rincian Biaya</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="uraian" class="form-label fw-bold">Uraian / Keterangan</label>
                                <input type="text" class="form-control" name="uraian" id="uraian"
                                    placeholder="Keterangan transaksi..." required maxlength="20"
                                    value="{{old('uraian')}}">
                            </div>

                            <div class="col-md-6">
                                <label for="diskon" class="form-label fw-bold">Nominal Diskon</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">Rp</span>
                                    <input type="text" class="form-control text-success fw-bold" name="diskon" id="diskon"
                                        required value="0" onkeyup="add_diskon()">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="add_fee" class="form-label fw-bold">Penyesuaian (+/-)</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">Rp</span>
                                    <input type="text" class="form-control text-danger fw-bold" name="add_fee" id="add_fee"
                                        data-thousands="." required value="0" onkeyup="add_diskon()">
                                </div>
                                @if ($errors->has('add_fee'))
                                    <small class="text-danger">{{$errors->first('add_fee')}}</small>
                                @endif
                            </div>

                            @if ($data['tempo'] == 1)
                            <div class="col-md-6">
                                <label for="dp" class="form-label fw-bold">Down Payment (DP)</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">Rp</span>
                                    <input type="text" class="form-control fw-bold" name="dp" id="dp"
                                        required value="0" onkeyup="add_dp()">
                                </div>
                            </div>
                            @if ($data['jenis'] == 1)
                            <div class="col-md-6">
                                <label for="dp_ppn" class="form-label fw-bold">Status PPN pada DP <span class="text-danger">*</span></label>
                                <select class="form-select" name="dp_ppn" id="dp_ppn" onchange="add_dp_ppn()" required>
                                    <option value="">-- Pilih Opsi --</option>
                                    <option value="1">DP Termasuk PPN</option>
                                    <option value="0">DP Tanpa PPN</option>
                                </select>
                            </div>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>
            </form>

            {{-- CARD 3: DAFTAR BARANG (KOLOM SESUAI PERMINTAAN) --}}
            <div class="card border-0 shadow-sm rounded-4 mb-5">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark"><i class="fa fa-shopping-cart me-2"></i>Daftar Barang</h5>
                    <span class="badge bg-secondary rounded-pill">{{ $keranjang->count() }} Item</span>
                </div>

                {{-- Table Responsive wajib ada karena kolomnya banyak (12 Kolom) --}}
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0 align-middle">
                        <thead class="table-success text-center align-middle small text-uppercase fw-bold">
                            <tr>
                                <th>No</th>
                                <th>Perusahaan</th>
                                <th>Bidang</th>
                                <th>Kategori Barang</th>
                                <th>Nama Barang</th>
                                <th>Kode</th>
                                <th>Merk</th>
                                <th>Banyak</th>
                                <th>Satuan</th>
                                <th>Harga Satuan</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($keranjang as $b)
                            <tr>
                                <td class="text-center">{{$loop->iteration}}</td>
                                <td class="text-center">{{$b->barang->type->unit->nama}}</td>
                                <td class="text-center">{{$b->barang->type->nama}}</td>
                                <td class="text-center">{{$b->barang->kategori->nama}}</td>
                                <td>{{$b->barang->barang_nama->nama}}</td>
                                <td class="text-center">{{$b->barang->kode}}</td>
                                <td class="text-center">{{$b->barang->merk}}</td>
                                <td class="text-center fw-bold">{{$b->nf_jumlah}}</td>
                                <td class="text-center">{{$b->barang->satuan ? $b->barang->satuan->nama : '-'}}</td>
                                <td class="text-end text-nowrap">{{$b->nf_harga}}</td>
                                <td class="text-end fw-bold text-nowrap">{{$b->nf_total}}</td>
                                <td class="text-center">
                                    <form action="{{ route('billing.form-beli.keranjang.delete', $b->id) }}" method="post"
                                        id="deleteForm{{ $b->id }}" class="d-inline">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-white border-top">
                            {{-- Karena ada 12 Kolom, Colspan untuk Label = 10, Kolom Angka = 1, Kolom Aksi = 1 --}}

                            {{-- SUBTOTAL --}}
                            <tr>
                                <td colspan="10" class="text-end text-muted small">Total DPP</td>
                                <td class="text-end fw-semibold text-nowrap" id="tdTotal">{{count($keranjang) > 0 ? number_format($keranjang->sum('total'), 0, ',','.') : ''}}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="10" class="text-end text-muted small">Diskon (-)</td>
                                <td class="text-end text-success text-nowrap" id="tdDiskon">{{number_format($diskon, 0, ',','.')}}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="10" class="text-end text-muted small">Penyesuaian (+/-)</td>
                                <td class="text-end text-nowrap" id="tdAddFee">0</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="10" class="text-end fw-bold">Total DPP (Setelah Diskon)</td>
                                <td class="text-end fw-bold text-nowrap" id="tdTotalSetelahDiskon">{{number_format($total-$diskon, 0, ',','.')}}</td>
                                <td></td>
                            </tr>

                            @if ($data['jenis'] == 1)
                            <tr>
                                <td colspan="10" class="text-end text-muted small">PPN ({{ $ppnRate }}%) (+)</td>
                                <td class="text-end text-nowrap" id="tdPpn">{{number_format($ppn, 0, ',','.')}}</td>
                                <td></td>
                            </tr>
                            @endif

                            {{-- GRAND TOTAL --}}
                            <tr class="table-primary border-top border-primary">
                                <td colspan="10" class="text-end fw-bold fs-5 text-primary">GRAND TOTAL</td>
                                <td class="text-end fw-bold fs-5 text-primary text-nowrap" id="grand_total">
                                    {{number_format($total + $add_fee + $ppn - $diskon, 0, ',','.')}}
                                </td>
                                <td></td>
                            </tr>

                            @if ($data['tempo'] == 1)
                            {{-- Spacer --}}
                            <tr><td colspan="12" class="p-0 border-0"></td></tr>

                            <tr>
                                <td colspan="10" class="text-end text-secondary small">Down Payment (DP)</td>
                                <td class="text-end text-danger text-nowrap" id="dpTd">{{number_format($dp, 0, ',','.')}}</td>
                                <td></td>
                            </tr>
                            @if ($data['jenis'] == 1)
                            <tr>
                                <td colspan="10" class="text-end text-secondary small">PPN pada DP</td>
                                <td class="text-end text-danger text-nowrap" id="dpPPNtd">{{number_format($dpPPN, 0, ',','.')}}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="10" class="text-end fw-bold text-secondary">Total DP</td>
                                <td class="text-end fw-bold text-danger text-nowrap" id="totalDpTd">{{number_format($totalDp, 0, ',','.')}}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="10" class="text-end text-secondary small">Sisa PPN</td>
                                <td class="text-end text-nowrap" id="sisaPPN">{{number_format($sisaPPN, 0, ',','.')}}</td>
                                <td></td>
                            </tr>
                            @endif

                            {{-- SISA TAGIHAN --}}
                            <tr class="table-warning border-top border-warning">
                                <td colspan="10" class="text-end fw-bold fs-5 text-dark">SISA TAGIHAN</td>
                                <td class="text-end fw-bold fs-5 text-dark text-nowrap" id="sisa">
                                    {{number_format($total + $add_fee + $ppn - $diskon - $sisaPPN, 0, ',','.')}}
                                </td>
                                <td></td>
                            </tr>
                            @endif
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- TOMBOL ACTION --}}
            <div class="d-grid gap-2 mb-5">
                <button type="submit" form="beliBarang" class="btn btn-primary btn-lg shadow fw-bold py-3 text-uppercase">
                    <i class="fa fa-save me-2"></i> Lanjutkan Transaksi
                </button>
            </div>

        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<style>
    /* Agar input readonly terlihat lebih jelas */
    input[readonly] {
        background-color: #e9ecef !important;
        cursor: default;
    }
    .table td, .table th {
        vertical-align: middle;
        white-space: nowrap; /* Opsional: Agar teks tidak turun ke bawah */
    }
</style>
@endpush

@push('js')
{{-- Load Cleave.js --}}
<script src="{{ asset('assets/plugins/cleave/cleave.min.js') }}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>

<script>
    $(document).ready(function() {
        // --- Perbaikan Logic Cleave.js ---
        // Cek element sebelum init agar tidak error
        if(document.getElementById('diskon')){
            var diskoTn = new Cleave('#diskon', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        }
        if(document.getElementById('add_fee')){
            var add_fee = new Cleave('#add_fee', {
                numeral: true,
                negative: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        }
        if(document.getElementById('dp')){
             var dp = new Cleave('#dp', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        }
    });

    function funSupplier() {
        var tempo = {{$data['tempo']}};
        var supplier_id = document.getElementById('supplier_id').value;

        $.ajax({
            url: "{{route('billing.form-beli.get-supplier')}}",
            type: "GET",
            data: { id: supplier_id },
            success: function(data) {
                document.getElementById('nama_rek').value = data.nama_rek;
                document.getElementById('bank').value = data.bank;
                document.getElementById('no_rek').value = data.no_rek;

                if (tempo !== 1) return;

                if (data.pembayaran === 1) {
                    document.getElementById('tempo_hari').value = null;
                    document.getElementById('jatuh_tempo').value = null;
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Supplier memiliki sistem pembayaran cash. Silahkan Isi Tanggal Jatuh Tempo',
                    });
                    return;
                }

                if (data.pembayaran === 2 && data.tempo_hari != null) {
                    document.getElementById('tempo_hari').value = data.tempo_hari;
                    var formattedDate = addDaysAndFormat(data.tempo_hari);
                    document.getElementById('jatuh_tempo').value = formattedDate;
                    if(document.getElementById('jatuh_tempo')._flatpickr){
                        document.getElementById('jatuh_tempo')._flatpickr.setDate(formattedDate);
                    }
                }
            }
        });
    }

    function addDaysAndFormat(days) {
        var currentDate = new Date();
        currentDate.setDate(currentDate.getDate() + days);
        var day = ("0" + currentDate.getDate()).slice(-2);
        var month = ("0" + (currentDate.getMonth() + 1)).slice(-2);
        var year = currentDate.getFullYear();
        return `${day}-${month}-${year}`;
    }

    function add_diskon() {
        var diskonT = document.getElementById('diskon').value ?? 0;
        var diskon = diskonT.replace(/\./g, '');
        var total = document.getElementById('tdTotal').textContent;
        total = total.replace(/\./g, '');
        var apa_ppn = {{$data['jenis'] == 1 ? 1 : 0}};

        if (apa_ppn == 1) {
            var ppn = document.getElementById('tdPpn').textContent;
            ppn = ppn.replace(/\./g, '');
        }

        var addFeeT = document.getElementById('add_fee').value;
        var addFee = addFeeT.replace(/\./g, '');
        var total_diskon = total - diskon;
        var gd = total_diskon + Number(ppn) + Number(addFee);

        var diskonFormatted = Number(diskon).toLocaleString('id-ID');
        var totalFormatted = total_diskon.toLocaleString('id-ID');
        var addFeeFormatted = Number(addFee).toLocaleString('id-ID');
        var gF = gd.toLocaleString('id-ID');

        document.getElementById('tdDiskon').textContent = diskonT;
        document.getElementById('tdTotalSetelahDiskon').textContent = totalFormatted;
        document.getElementById('tdAddFee').textContent = addFeeFormatted;
        document.getElementById('grand_total').textContent = gF;

        add_ppn();
        check_sisa();
    }

    function add_ppn() {
        var apa_ppn = {{$data['jenis'] == 1 ? 1 : 0}};
        var ppnRate = {!! $ppnRate !!} / 100;
        var addFee = Number(document.getElementById('add_fee').value.replace(/\./g, ''));

        if (apa_ppn === 1) {
            var gt = Number(document.getElementById('tdTotalSetelahDiskon').textContent.replace(/\./g, ''));
            var vPpn = Math.floor(gt * ppnRate);
            var totalap = gt + vPpn + addFee;
            var tF = totalap.toLocaleString('id-ID');
            var vF = vPpn.toLocaleString('id-ID');
            document.getElementById('grand_total').textContent = tF;
            document.getElementById('tdPpn').textContent = vF;
        } else {
            var gtWithoutPpn = Number(document.getElementById('tdTotalSetelahDiskon').textContent.replace(/\./g, ''));
            var totalWithoutPpn = gtWithoutPpn + addFee;
            totalWithoutPpn = totalWithoutPpn.toFixed(0);
            totalWithoutPpn = parseInt(totalWithoutPpn);
            var totalFormatted = totalWithoutPpn.toLocaleString('id-ID');
            document.getElementById('grand_total').textContent = totalFormatted;
        }
        check_sisa();
    }

    function add_dp(){
        var dpT = document.getElementById('dp').value;
        var dp = dpT.replace(/\./g, '');
        document.getElementById('dpTd').textContent = dpT;
        add_dp_ppn();
        check_sisa();
    }

    function add_dp_ppn(){
        var apa_dp_ppn = document.getElementById('dp_ppn') ? document.getElementById('dp_ppn').value || 0 : 0;
        if(apa_dp_ppn === '1') {
            var dp_ppn = document.getElementById('dp').value;
            var dp_ppn = dp_ppn.replace(/\./g, '');
            var ppn = {!! $ppnRate !!} / 100;
            var ppn_dp_num = Math.floor(dp_ppn * ppn);
            ppn_dp = ppn_dp_num.toLocaleString('id-ID');
            document.getElementById('dpPPNtd').textContent = ppn_dp;
            var ppn_total = document.getElementById('tdPpn').textContent;
            ppn_total = ppn_total.replace(/\./g, '');
            var sisa_ppn = ppn_total - ppn_dp_num;
            sisa_ppn = sisa_ppn.toFixed(0);
            var sisa_ppnF = sisa_ppn.toLocaleString('id-ID');
            document.getElementById('sisaPPN').textContent = sisa_ppnF;
        } else {
            if (document.getElementById('dpPPNtd')) document.getElementById('dpPPNtd').textContent = 0;
            if (document.getElementById('sisaPPN')) document.getElementById('sisaPPN').textContent = 0;
        }
        check_sisa();
    }

    function check_sisa(){
        var grand_total = document.getElementById('grand_total').textContent;
        grand_total = parseInt(grand_total.replace(/\./g, ''), 10);
        var dpElement = document.getElementById('dpTd');
        var dp = dpElement ? dpElement.textContent : '0';
        dp = parseInt(dp.replace(/\./g, ''), 10);
        var dpPPNtd = document.getElementById('dpPPNtd') ? document.getElementById('dpPPNtd').textContent : '0';
        dpPPNtd = parseInt(dpPPNtd.replace(/\./g, ''), 10);
        if (isNaN(dpPPNtd)) dpPPNtd = 0;

        var sisa = grand_total - dp - dpPPNtd;
        var sisaF = sisa.toLocaleString('id-ID');

        var tdPPN = document.getElementById('tdPpn') ? document.getElementById('tdPpn').textContent : '0';
        tdPPN = parseInt(tdPPN.replace(/\./g, ''), 10);
        if (isNaN(tdPPN)) tdPPN = 0;

        var sisaPPN = tdPPN - dpPPNtd;
        sisaPPN = Number(sisaPPN.toFixed(0));

        var sisaElement = document.getElementById('sisa');
        if (sisaElement) sisaElement.textContent = sisaF;

        if (sisaPPN == 0) {
            if (document.getElementById('sisaPPN')) document.getElementById('sisaPPN').textContent = 0;
        } else {
            if (document.getElementById('sisaPPN')) document.getElementById('sisaPPN').textContent = sisaPPN.toLocaleString('id-ID');
        }

        var totalDp = dp + dpPPNtd;
        totalDp = Number(totalDp.toFixed(0));
        if (document.getElementById('totalDpTd')) document.getElementById('totalDpTd').textContent = totalDp.toLocaleString('id-ID');
    }

     confirmAndSubmit('#beliBarang', 'Apakah anda Yakin?');
</script>
@endpush
