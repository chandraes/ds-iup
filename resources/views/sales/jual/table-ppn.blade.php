<div class="table-responsive">
    <h3>BARANG A : </h3>
    <table class="table table-bordered">
        <thead class="table-success">
            <tr>
                <th class="text-center align-middle">No</th>
                <th class="text-center align-middle">Nama Barang / Merek</th>
                <th class="text-center align-middle">Qty</th>
                <th class="text-center align-middle">Sat</th>
                <th class="text-center align-middle">Harga Satuan (DPP)</th>
                <th class="text-center align-middle">Diskon (DPP)</th>
                <th class="text-center align-middle">Harga Diskon (DPP)</th>
                <th class="text-center align-middle">Harga Diskon (PPN)</th>
                <th class="text-center align-middle">Total</th>
                <th class="text-center align-middle">Act</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($keranjang->where('barang_ppn', 1) as $b)
            <tr class="{{$b->stok_kurang == 1 ? 'table-danger' : ''}}">
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-start align-middle">
                    {{$b->stok->barang_nama->nama}},
                    <br>
                    {{$b->stok->barang->merk}}
                </td>
                <td class="text-center align-middle">
                    {{$b->nf_jumlah}}
                </td>
                <td class="text-center align-middle">
                    {{$b->barang->satuan ? $b->barang->satuan->nama
                    : '-'}}
                    </td>
                <td class="text-end align-middle">{{$b->nf_harga}}</td>
                <td class="text-end align-middle">{{$b->nf_diskon}}</td>
                    <td class="text-end align-middle">{{$b->harga_diskon_dpp}}</td>
                    <td class="text-end align-middle">{{$b->nf_harga_satuan_akhir}}</td>
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
            $gtPpn = $totalBarangPpn;
        @endphp
        <tfoot>
            <tr>
                <th colspan="8" class="text-end align-middle">Grand Total :</th>
                <th class="text-end align-middle" id="grandTotalTh">
                    {{number_format(($gtPpn), 0, ',','.')}}</th>
                    <td></td>
            </tr>
            <tr id="penyesuaianTr" hidden>
                <th colspan="8" class="text-end align-middle">Penyesuaian:</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="add_fee" id="add_fee" onkeyup="addCheck()"
                        value="0" />
                </th>
                <td></td>
            </tr>
            <tr
                hidden>
                <th colspan="8" class="text-end align-middle">Total Tagihan :</th>
                <th class="text-end align-middle" id="totalTagihanTh">
                    {{number_format(($gtPpn), 0, ',','.')}}</th>
                    <td></td>
            </tr>
            @if ($info->pembayaran != 1)
            <tr id="trJumlahDp">
                <th colspan="8" class="text-end align-middle">Masukan Nominal DP :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="jumlah_dp" id="jumlah_dp" value="0"
                        onkeyup="addDp()" />
                </th>
                <td></td>
            </tr>
            <tr id="trDp" hidden>
                <th colspan="8" class="text-end align-middle">DP :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="dp" id="dp" value="0" readonly/>
                </th>
                <td></td>
            </tr>
            <tr id="trDpPpn" hidden>
                <th colspan="8" class="text-end align-middle">DP PPn :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="dp_ppn" id="dp_ppn" value="0"
                        readonly />
                </th>
                <td></td>
            </tr>
            <tr id="trSisa" >
                <th colspan="8" class="text-end align-middle">Sisa Tagihan :</th>
                <th class="text-end align-middle" id="thSisa">
                    {{number_format(($gtPpn), 0, ',','.')}}
                </th>
                <td></td>
            </tr>
            @endif

        </tfoot>
    </table>
</div>

@push('js')
<script>
    $(document).ready(function() {
        new Cleave('#jumlah_dp', cleaveOptions);
        // new Cleave('#diskon_non_ppn', cleaveOptions); --- IGNORE ---

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
        const ppnValue = Math.floor(dpp * ppnRate / 100);
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
