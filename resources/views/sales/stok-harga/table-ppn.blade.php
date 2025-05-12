<div class="table-responsive">
    <h3>BARANG PPN : </h3>
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
                <th class="text-center align-middle">Act</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($keranjang->where('barang_ppn', 1) as $b)
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
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-danger btn-sm" onclick="deleteKeranjang({{$b->id}})"><i
                            class="fa fa-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
        @php
            $totalBarangPpn = $keranjang->where('barang_ppn', 1)->sum('total');
            $ppnNominalBarangPpn = 0;
            $ppnNominalBarangPpn = floor($total * ($ppn/100));
            $gtPpn = $totalBarangPpn + $ppnNominalBarangPpn;
        @endphp
        <tfoot>
            <tr>
                <th colspan="7" class="text-end align-middle">DPP :</th>
                <th class="text-end align-middle" id="dppTh">{{number_format($totalBarangPpn, 0,
                    ',','.')}}</th>
                <td></td>
            </tr>
            <tr id="trDiskon">
                <th colspan="7" class="text-end align-middle">Diskon :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="diskon" id="diskon" value="0" readonly
                        onkeyup="addDiskon()" />
                </th>
                <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">DPP Setelah Diskon :</th>
                <th class="text-end align-middle" id="thDppDiskon">{{number_format($totalBarangPpn, 0,
                    ',','.')}}</th>
                    <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">Ppn :</th>
                <th class="text-end align-middle" id="thPpn">{{number_format(($ppnNominalBarangPpn), 0,
                    ',','.')}}</th>
                    <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">Grand Total :</th>
                <th class="text-end align-middle" id="grandTotalTh">
                    {{number_format(($gtPpn), 0, ',','.')}}</th>
                    <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">Penyesuaian:</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="add_fee" id="add_fee" onkeyup="addCheck()"
                        value="0" />
                </th>
                <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">Total Tagihan :</th>
                <th class="text-end align-middle" id="totalTagihanTh">
                    {{number_format(($gtPpn), 0, ',','.')}}</th>
                    <td></td>
            </tr>
            <tr id="trJumlahDp" hidden>
                <th colspan="7" class="text-end align-middle">Masukan Nominal DP :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="jumlah_dp" id="jumlah_dp" value="0"
                        onkeyup="addDp()" />
                </th>
                <td></td>
            </tr>
            <tr id="trDp" hidden>
                <th colspan="7" class="text-end align-middle">DP :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="dp" id="dp" value="0" readonly/>
                </th>
                <td></td>
            </tr>
            <tr id="trDpPpn" hidden>
                <th colspan="7" class="text-end align-middle">DP PPn :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="dp_ppn" id="dp_ppn" value="0"
                        readonly />
                </th>
                <td></td>
            </tr>
            <tr id="trSisa" hidden>
                <th colspan="7" class="text-end align-middle">Sisa Tagihan :</th>
                <th class="text-end align-middle" id="thSisa">
                    0
                </th>
                <td></td>
            </tr>
        </tfoot>
    </table>
</div>

@push('js')
<script>
    $(document).ready(function() {
        new Cleave('#add_fee', cleaveOptions);
        new Cleave('#diskon', cleaveOptions);

    });

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

</script>
@endpush
