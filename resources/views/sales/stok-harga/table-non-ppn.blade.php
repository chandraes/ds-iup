<div class="table-responsive">
    <h3>BARANG B : </h3>
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
            @foreach ($keranjang->where('barang_ppn', 0) as $b)
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
            $totalBarangNonPpn = $keranjang->where('barang_ppn', 0)->sum('total');
            $gtNonPpn = $totalBarangNonPpn;
        @endphp
        <tfoot>
            <tr>
                <th colspan="7" class="text-end align-middle">DPP :</th>
                <th class="text-end align-middle" id="dppTh_non_ppn">{{number_format($totalBarangNonPpn, 0,
                    ',','.')}}</th>
                <td></td>
            </tr>
            <tr id="trDiskon_non_ppn">
                <th colspan="7" class="text-end align-middle">Diskon :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="diskon_non_ppn" id="diskon_non_ppn" value="0" readonly
                        onkeyup="addDiskonNonPpn()" />
                </th>
                <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">DPP Setelah Diskon :</th>
                <th class="text-end align-middle" id="thDppDiskon_non_ppn">{{number_format($totalBarangNonPpn, 0,
                    ',','.')}}</th>
                    <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">Grand Total :</th>
                <th class="text-end align-middle" id="grandTotalTh_non_ppn">
                    {{number_format(($gtNonPpn), 0, ',','.')}}</th>
                    <td></td>
            </tr>
            <tr id="penyesuaianTr_non_ppn" hidden>
                <th colspan="7" class="text-end align-middle">Penyesuaian:</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="add_fee_non_ppn" id="add_fee_non_ppn" onkeyup="addCheckNonPpn()"
                        value="0" />
                </th>
                <td></td>
            </tr>
            <tr>
                <th colspan="7" class="text-end align-middle">Total Tagihan :</th>
                <th class="text-end align-middle" id="totalTagihanTh_non_ppn">
                    {{number_format(($gtNonPpn), 0, ',','.')}}</th>
                    <td></td>
            </tr>
            <tr id="trJumlahDp_non_ppn" hidden>
                <th colspan="7" class="text-end align-middle">Masukan Nominal DP :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="jumlah_dp_non_ppn" id="jumlah_dp_non_ppn" value="0"
                        onkeyup="addDpNonPpn()" />
                </th>
                <td></td>
            </tr>
            <tr id="trDp_non_ppn" hidden>
                <th colspan="7" class="text-end align-middle">DP :</th>
                <th class="text-end align-middle">
                    <input type="text" class="form-control text-end" name="dp_non_ppn" id="dp_non_ppn" value="0" readonly/>
                </th>
                <td></td>
            </tr>
            <tr id="trSisa_non_ppn" hidden>
                <th colspan="7" class="text-end align-middle">Sisa Tagihan :</th>
                <th class="text-end align-middle" id="thSisa_non_ppn">
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
        new Cleave('#add_fee_non_ppn', cleaveOptions);
        new Cleave('#diskon_non_ppn', cleaveOptions);

    });

    function updateTotalTagihanNonPpn(total) {
        document.getElementById('totalTagihanTh_non_ppn').innerText = formatNumber(total);
        checkSisaNonPpn();
    }

    // Hitung Sisa Tagihan
    function checkSisaNonPpn() {
        const dp = parseNumber(document.getElementById('dp_non_ppn').value || '0');
        const grandTotal = parseNumber(document.getElementById('totalTagihanTh_non_ppn').innerText);

        const sisa = grandTotal - dp;
        document.getElementById('thSisa_non_ppn').innerText = formatNumber(sisa);
    }

    // Tambahkan Diskon
    function addDiskonNonPpn() {
        const dpp = parseNumber(document.getElementById('dppTh_non_ppn').innerText);
        const diskon = parseNumber(document.getElementById('diskon_non_ppn').value || '0');
        const dppDiskon = dpp - diskon;

        document.getElementById('thDppDiskon_non_ppn').innerText = formatNumber(dppDiskon);
        calculateTotalTagihanNonPpn();
    }

    function calculateTotalTagihanNonPpn() {
        var gt = document.getElementById('grandTotalTh_non_ppn').innerText;
        var add_fee = document.getElementById('add_fee_non_ppn').value;
        gt = gt.replace(/\./g, '');
        add_fee = add_fee.replace(/\./g, '');

        var addFeeNumber = parseFloat(add_fee);
        var gtNumber = parseFloat(gt);

        totahTagihan = gtNumber + addFeeNumber;
        var totahTagihanNf = totahTagihan.toLocaleString('id-ID');
        document.getElementById('totalTagihanTh_non_ppn').innerText = totahTagihanNf;

        checkSisaNonPpn();
    }

    function addCheckNonPpn() {
        const addFee = parseNumber(document.getElementById('add_fee_non_ppn').value);
        const limitPenyesuaian = parseFloat({{ $penyesuaian }});
        const limitNegatif = limitPenyesuaian * -1;
        const grandTotal = parseNumber(document.getElementById('grandTotalTh_non_ppn').innerText);

        if (addFee > limitPenyesuaian || addFee < limitNegatif) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Penyesuaian tidak boleh melebihi batas limit!',
            });
            document.getElementById('add_fee_non_ppn').value = 0;
            return;
        }

        if (addFee > grandTotal) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Additional Fee tidak boleh melebihi Grand Total!',
            });
            document.getElementById('add_fee_non_ppn').value = 0;
            return;
        }

        updateTotalTagihanNonPpn(grandTotal + addFee);
    }


    function addDpNonPpn(){
        console.log('add dp');
        var jumlah_dp = document.getElementById('jumlah_dp_non_ppn').value;

        var dp = document.getElementById('dp_non_ppn');

        dp = jumlah_dp;

        document.getElementById('dp_non_ppn').value = dp;
        // var dp = jumlah_dp;
        var gt = document.getElementById('totalTagihanTh_non_ppn').innerText;
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
            document.getElementById('dp_non_ppn').value = 0;
            return;
        }

        checkSisaNonPpn();

    }

</script>


@endpush
