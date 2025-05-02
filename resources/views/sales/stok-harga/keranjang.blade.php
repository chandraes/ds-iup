@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Invoice Penjualan</u></h1>
        </div>
    </div>
    <div class="row mb-3 d-flex">
        <div class="col-md-6">
            <a href="{{route('sales.stok')}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i>
                Kembali</a>
        </div>
    </div>
    <div class="row">
        <form action="{{route('sales.stok.keranjang.checkout')}}" method="post" id="storeForm">
            @csrf
            <div class="card">

                <div class="card-body bg-white">
                    <h4 class="card-title">
                        {{-- <strong>#INVOICE : {{$invoice}}</strong> --}}
                    </h4>
                    <div class="row mt-3 mb-3">
                        <div class="col-md-12 my-3">
                            <div class="row" id="konsumenRow">
                                <div class="row invoice-info">
                                    <div class="col-md-6 invoice-col">
                                        <table style="width: 90%">
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Konsumen</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <select class="form-select" name="konsumen_id" id="konsumen_id" required
                                                        onchange="getKonsumenData()">
                                                        <option value="" disabled selected>-- Pilih Konsumen --</option>
                                                        {{-- <option value="*">INPUT MANUAL</option> --}}
                                                        @foreach ($konsumen as $k)
                                                        <option value="{{$k->id}}">{{$k->nama}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr id="namaTr" hidden style="height:50px">
                                                <td class="text-start align-middle">Nama</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <input type="text" name="nama" id="nama"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Sistem Pembayaran</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <select type="text" name="pembayaran" id="pembayaran" onchange="checkPembayaran()"
                                                        class="form-select" required>

                                                    </select>
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Tempo</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="tempo_hari"
                                                            id="tempo_hari" disabled>
                                                        <span class="input-group-text" id="basic-addon1">Hari</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">NPWP</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <input type="text" name="npwp" id="npwp" class="form-control"
                                                        disabled>
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Alamat</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <textarea name="alamat" id="alamat" class="form-control"
                                                        disabled></textarea>
                                                </td>
                                            </tr>

                                        </table>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-md-6 invoice-col" >
                                        <div class="row d-flex justify-content-end">
                                            <table style="width: 90%">
                                                {{-- <tr style="height:50px">
                                                    <td class="text-start align-middle">Invoice</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="kode" id="kode" class="form-control"
                                                            disabled value="{{$kode}}">
                                                    </td>
                                                </tr> --}}
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">Tanggal</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="tanggal" id="tanggal" class="form-control"
                                                            value="{{$tanggal}}" disabled>
                                                    </td>
                                                </tr>
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">Jam</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="jam" id="jam" class="form-control"
                                                            value="{{$jam}}" disabled>
                                                    </td>
                                                </tr>
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">No WA</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="no_hp" id="no_hp" class="form-control"
                                                            disabled>
                                                    </td>
                                                </tr>
                                                @if ($ppnStore == 1)
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">PPn Disetor Oleh</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <select class="form-select" name="dipungut" id="dipungut" required
                                                        onchange="ppnPungut()">
                                                            <option value="" selected>-- Pilih Salah Satu --</option>
                                                            <option value="1">Sendiri</option>
                                                            <option value="0">Konsumen</option>
                                                    </select>
                                                    </td>
                                                </tr>
                                                @endif
                                            </table>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center align-middle">Kelompok Barang</th>
                                    <th class="text-center align-middle">Nama Barang</th>
                                    <th class="text-center align-middle">Kode Barang</th>
                                    <th class="text-center align-middle">Merk Barang</th>
                                    <th class="text-center align-middle">Banyak</th>
                                    <th class="text-center align-middle">Satuan</th>
                                    <th class="text-center align-middle">Harga Satuan</th>
                                    <th class="text-center align-middle">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($keranjang as $b)
                                <tr class="{{$b->stok_kurang == 1 ? 'table-danger' : ''}}">
                                    <td class="text-center align-middle">{{$b->stok->kategori->nama}}</td>
                                    <td class="text-center align-middle">{{$b->stok->barang_nama->nama}}</td>
                                    <td class="text-center align-middle">{{$b->stok->barang->kode}}</td>
                                    <td class="text-center align-middle">{{$b->stok->barang->merk}}</td>
                                    <td class="text-center align-middle">{{$b->nf_jumlah}}</td>
                                    <td class="text-center align-middle">{{$b->barang->satuan ? $b->barang->satuan->nama
                                        : '-'}}</td>
                                    <td class="text-center align-middle">{{$b->nf_harga}}</td>
                                    <td class="text-end align-middle">{{$b->nf_total}}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-end align-middle">DPP :</th>
                                    <th class="text-end align-middle" id="dppTh">{{number_format($keranjang->sum('total'), 0,
                                        ',','.')}}</th>
                                </tr>
                                <tr id="trDiskon">
                                    <th colspan="7" class="text-end align-middle">Diskon :</th>
                                    <th class="text-end align-middle">
                                        <input type="text" class="form-control text-end" name="diskon" id="diskon" value="0"
                                            onkeyup="addDiskon()" />
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end align-middle">DPP Setelah Diskon :</th>
                                    <th class="text-end align-middle" id="thDppDiskon">{{number_format($keranjang->sum('total'), 0,
                                        ',','.')}}</th>
                                </tr>
                                @if ($ppnStore == 1)
                                <tr>
                                    <th colspan="7" class="text-end align-middle">Ppn :</th>
                                    <th class="text-end align-middle" id="thPpn">{{number_format(($nominalPpn), 0,
                                        ',','.')}}</th>
                                </tr>
                                @endif

                                {{-- <tr>
                                    <th colspan="7" class="text-end align-middle">Pph :</th>
                                    <th class="text-end align-middle" id="pphTh">0</th>
                                </tr> --}}
                                <tr>
                                    <th colspan="7" class="text-end align-middle">Grand Total :</th>
                                    <th class="text-end align-middle" id="grandTotalTh">
                                        {{number_format(($total+$nominalPpn), 0, ',','.')}}</th>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end align-middle">Penyesuaian:</th>
                                    <th class="text-end align-middle">
                                        <input type="text" class="form-control text-end" name="add_fee" id="add_fee" onkeyup="addCheck()"
                                            value="0" />
                                    </th>
                                </tr>
                                <tr>
                                    <th colspan="7" class="text-end align-middle">Total Tagihan :</th>
                                    <th class="text-end align-middle" id="totalTagihanTh">
                                        {{number_format(($total+$nominalPpn), 0, ',','.')}}</th>
                                </tr>
                                <tr id="trJumlahDp" hidden>
                                    <th colspan="7" class="text-end align-middle">Masukan Nominal DP :</th>
                                    <th class="text-end align-middle">
                                        <input type="text" class="form-control text-end" name="jumlah_dp" id="jumlah_dp" value="0"
                                            onkeyup="addDp()" />
                                    </th>
                                </tr>
                                <tr id="trDp" hidden>
                                    <th colspan="7" class="text-end align-middle">DP :</th>
                                    <th class="text-end align-middle">
                                        <input type="text" class="form-control text-end" name="dp" id="dp" value="0" readonly/>
                                    </th>
                                </tr>
                                @if ($ppnStore == 1)
                                <tr id="trDpPpn" hidden>
                                    <th colspan="7" class="text-end align-middle">DP PPn :</th>
                                    <th class="text-end align-middle">
                                        <input type="text" class="form-control text-end" name="dp_ppn" id="dp_ppn" value="0"
                                            readonly />
                                    </th>
                                </tr>
                                @endif

                                <tr id="trSisa" hidden>
                                    <th colspan="7" class="text-end align-middle">Sisa Tagihan :</th>
                                    <th class="text-end align-middle" id="thSisa">
                                        0
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row ">
                        <div class="col-md-6"></div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-success"><i class="fa fa-credit-card"></i>
                                Lanjutkan Pembayaran</button>
                            {{-- <button type="button" class="btn btn-info text-white"
                                onclick="javascript:window.print();"><i class="fa fa-print"></i> Print Invoice</button>
                            --}}
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-md-6 text-end mt-2">
            @include('wa-status')
        </div>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
{{-- <script src="{{asset('assets/js/cleave.min.js')}}"></script> --}}
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>

    $('#konsumen_id').select2({
        width: '100%',
    });

    // Format input angka dengan Cleave.js
    const cleaveOptions = {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.',
        negative: true
    };
    new Cleave('#add_fee', cleaveOptions);
    new Cleave('#diskon', cleaveOptions);

    // Helper untuk menghapus format angka
    function parseNumber(value) {
        return parseFloat(value.replace(/\./g, '') || 0);
    }

    // Helper untuk memformat angka ke format Indonesia
    function formatNumber(value) {
        return value.toLocaleString('id-ID');
    }

    function ppnPungut() {
        var dipungut = document.getElementById('dipungut').value ?? 0;
        calculatePpn();
    }

     // Update Total Tagihan
     function updateTotalTagihan(total) {
        document.getElementById('totalTagihanTh').innerText = formatNumber(total);
        checkSisa();
    }

    // Hitung Sisa Tagihan
    function checkSisa() {
        const dp = parseNumber(document.getElementById('dp').value || '0');
        const grandTotal = parseNumber(document.getElementById('totalTagihanTh').innerText);
        const dpPpn = parseNumber(document.getElementById('dp_ppn')?.value || '0');
        const dipungut = document.getElementById('dipungut')?.value || 0;

        const sisa = dipungut == 0 ? grandTotal - dp : grandTotal - dp - dpPpn;
        document.getElementById('thSisa').innerText = formatNumber(sisa);
    }

    // Tambahkan Diskon
    function addDiskon() {
        const dpp = parseNumber(document.getElementById('dppTh').innerText);
        const diskon = parseNumber(document.getElementById('diskon').value || '0');
        const dppDiskon = dpp - diskon;

        document.getElementById('thDppDiskon').innerText = formatNumber(dppDiskon);
        calculatePpn();
    }

    function calculatePpn()
    {
        const dpp = parseNumber(document.getElementById('thDppDiskon').innerText);
        const ppnRate = {{ $ppn }};
        const ppnValue = Math.round(dpp * ppnRate / 100);
        const dipungut = document.getElementById('dipungut')?.value || 0;

        const grandTotal = dipungut == 0 ? dpp : dpp + ppnValue;

        if (document.getElementById('thPpn')) {
            document.getElementById('thPpn').innerText = formatNumber(ppnValue);
        }
        document.getElementById('grandTotalTh').innerText = formatNumber(grandTotal);

        checkSisa();
        calculateTotalTagihan();
    }

    function calculateTotalTagihan() {
        var gt = document.getElementById('grandTotalTh').innerText;
        var add_fee = document.getElementById('add_fee').value;
        gt = gt.replace(/\./g, '');
        add_fee = add_fee.replace(/\./g, '');

        var addFeeNumber = parseFloat(add_fee);
        var gtNumber = parseFloat(gt);

        totahTagihan = gtNumber + addFeeNumber;
        var totahTagihanNf = totahTagihan.toLocaleString('id-ID');
        document.getElementById('totalTagihanTh').innerText = totahTagihanNf;

        checkSisa();
    }

    function addCheck() {
        const addFee = parseNumber(document.getElementById('add_fee').value);
        const limitPenyesuaian = parseFloat({{ $penyesuaian }});
        const limitNegatif = limitPenyesuaian * -1;
        const grandTotal = parseNumber(document.getElementById('grandTotalTh').innerText);

        if (addFee > limitPenyesuaian || addFee < limitNegatif) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Penyesuaian tidak boleh melebihi batas limit!',
            });
            document.getElementById('add_fee').value = 0;
            return;
        }

        if (addFee > grandTotal) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Additional Fee tidak boleh melebihi Grand Total!',
            });
            document.getElementById('add_fee').value = 0;
            return;
        }

        updateTotalTagihan(grandTotal + addFee);
    }


    function addDp(){
        console.log('add dp');
        var jumlah_dp = document.getElementById('jumlah_dp').value;

        var dp = document.getElementById('dp');

        dp = jumlah_dp;

        document.getElementById('dp').value = dp;
        // var dp = jumlah_dp;
        var gt = document.getElementById('totalTagihanTh').innerText;
        gt = gt.replace(/\./g, '');
        dp = dp.replace(/\./g, '');
        var dpNumber = parseFloat(dp);
        var gtNumber = parseFloat(gt);

        if (dpNumber > gtNumber) {
            console.log('dp melebihi gt');
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'DP tidak boleh melebihi Total Tagihan!',
            });
            document.getElementById('dp').value = 0;
            document.getElementById('dp_ppn').value = 0;
            return;
        }

        var thPpnElement = document.getElementById('thPpn');
        var thPpn = thPpnElement ? thPpnElement.innerText : 0;
                        // remove . in ppnVal
        if (thPpn != '0') {
            var ppn = {{$ppn}};

            var dpPpn = Math.floor(dpNumber * ppn / 100);

            var dpPpnNf = dpPpn.toLocaleString('id-ID');

            document.getElementById('dp').value = (dpNumber - dpPpn).toLocaleString('id-ID');
            document.getElementById('dp_ppn').value = dpPpnNf;

        }

        checkSisa();

    }

    function checkPembayaran()
    {
        var pembayaran = document.getElementById('pembayaran').value;
        if (pembayaran == 2) {
            console.log('tempo');
            document.getElementById('trJumlahDp').hidden = false;
            document.getElementById('trDp').hidden = false;
            var dp = new Cleave('#dp', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.',
                negative: false
            });

            var ppnValElement = document.getElementById('thPpn');
            var ppnVal = ppnValElement ? ppnValElement.innerText : 0;
            // remove . in ppnVal
            if (ppnVal != '0') {
                document.getElementById('trDpPpn').hidden = false;
            }

            var sisa = document.getElementById('grandTotalTh').innerText;
            document.getElementById('trSisa').hidden = false;
            document.getElementById('thSisa').innerText = sisa;
        } else {
            document.getElementById('tempo_hari').value = '-';
            document.getElementById('trJumlahDp').hidden = true;
            document.getElementById('trDp').hidden = true;
            var tdDpPpnElement = document.getElementById('trDpPpn');
            if (tdDpPpnElement) {
                document.getElementById('trDpPpn').hidden = true;
            }
            // document.getElementById('trDpPpn').hidden = true;
            document.getElementById('trSisa').hidden = true;
        }
    }


    function getKonsumenData()
    {
        var id = document.getElementById('konsumen_id').value;

        if (id != '*') {
            document.getElementById('nama').required = false;
            document.getElementById('namaTr').hidden = true;
            document.getElementById('alamat').disabled = true;
            document.getElementById('npwp').disabled = true;
            document.getElementById('no_hp').disabled = true;
            $.ajax({
                url: '{{route('universal.get-konsumen')}}',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(data) {
                    document.getElementById('pembayaran').value = data.sistem_pembayaran;

                    document.getElementById('alamat').value = data.alamat;
                    document.getElementById('npwp').value = data.npwp;
                    document.getElementById('no_hp').value = data.no_hp;
                    if (data.pembayaran == 2) {
                        // empty pembayaran option
                        document.getElementById('pembayaran').innerHTML = '';
                        // add option dengan value 1 cash, 2 tempo, 3 titipan menggunakan array atau json lalu buat selected ke 2
                        var pembayaranText = ['Cash', 'Tempo', 'Titipan'];
                        var pembayaranValue = [1, 2, 3];

                        for (var i = 0; i < pembayaranText.length; i++) {
                            var option = document.createElement('option');
                            option.value = pembayaranValue[i];
                            option.text = pembayaranText[i];
                            document.getElementById('pembayaran').add(option);
                        }

                        document.getElementById('pembayaran').value = 2;

                        document.getElementById('tempo_hari').value = data.tempo_hari;
                        document.getElementById('trJumlahDp').hidden = false;
                        document.getElementById('trDp').hidden = false;
                        var dp = new Cleave('#dp', {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand',
                            numeralDecimalMark: ',',
                            delimiter: '.',
                            negative: false
                        });
                        var jumlah_dp = new Cleave('#jumlah_dp', {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand',
                            numeralDecimalMark: ',',
                            delimiter: '.',
                            negative: false
                        });
                        var ppnValElement = document.getElementById('thPpn');
                        var ppnVal = ppnValElement ? ppnValElement.innerText : 0;
                        // remove . in ppnVal
                        if (ppnVal != '0') {
                            document.getElementById('trDpPpn').hidden = false;
                        }

                        var sisa = document.getElementById('grandTotalTh').innerText;
                        document.getElementById('trSisa').hidden = false;
                        document.getElementById('thSisa').innerText = sisa;
                    } else {

                        document.getElementById('tempo_hari').value = '-';
                        document.getElementById('trJumlahDp').hidden = true;
                        document.getElementById('trDp').hidden = true;
                        var tdDpPpnElement = document.getElementById('trDpPpn');
                        if (tdDpPpnElement) {
                            document.getElementById('trDpPpn').hidden = true;
                        }
                        // document.getElementById('trDpPpn').hidden = true;
                        document.getElementById('trSisa').hidden = true;
                    }
                }
            });
            return;
        }

        document.getElementById('pembayaran').innerHTML = '';
        // add option dengan value 1 cash selected
        var pembayaranText = ['Cash'];
        var pembayaranValue = [1];

        for (var i = 0; i < pembayaranText.length; i++) {
            var option = document.createElement('option');
            option.value = pembayaranValue[i];
            option.text = pembayaranText[i];
            if (pembayaranValue[i] == 1) { // Jika value adalah 1, maka set sebagai selected
                option.selected = true;
            }
            document.getElementById('pembayaran').add(option);
        }


        document.getElementById('trDp').hidden = true;
        document.getElementById('trJumlahDp').hidden = true;
        document.getElementById('trDpPpn').hidden = true;
        document.getElementById('trSisa').hidden = true;
        document.getElementById('namaTr').hidden = false;
        document.getElementById('pembayaran').value = 'Cash';
        document.getElementById('tempo_hari').value = '-';
        document.getElementById('npwp').value = '';
        // remove disabled from alamat & npwp
        document.getElementById('alamat').disabled = false;
        document.getElementById('npwp').disabled = false;
        document.getElementById('no_hp').disabled = false;
        document.getElementById('no_hp').value = '';
        document.getElementById('no_hp').required = true;
        document.getElementById('alamat').value = '';
        // nama required
        document.getElementById('nama').required = true;

    }

    $('#storeForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Apakah anda yakin?',
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
</script>
@endpush
