<div class="modal fade" id="keranjangBelanja" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="keranjangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keranjangTitle">Keranjang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            @php
            $diskon = 0;
            $ppn = 0;
            $dp = 0;
            $dpPPN = 0;
            $totalDp = 0;
            $sisa = 0;
            $sisaPPN = 0;
            $total = $keranjang ? $keranjang->sum('total') : 0;
            $add_fee = $keranjang ? $keranjang->sum('add_fee') : 0;
            @endphp
            <div class="modal-body">
                <form action="{{route('billing.form-transaksi.kemasan.keranjang-tempo.checkout')}}" method="post"
                    id="beliBarang">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="uraian" class="form-label">Uraian</label>
                                <input type="text" class="form-control" name="uraian" id="uraian"
                                    aria-describedby="helpId" placeholder="" required maxlength="20"
                                    value="{{old('uraian')}}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="uraian" class="form-label">Apakah menggunakan PPn?</label>
                                <select class="form-select" name="ppn" id="ppn" onchange="add_ppn()" required>
                                    <option value="">-- Pilih Salah Satu --</option>
                                    <option value="1">Dengan PPn</option>
                                    <option value="0">Tanpa PPn</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="diskon" class="form-label">Diskon</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                    <input type="text" class="form-control" name="diskon" id="diskon"
                                        aria-describedby="helpId" placeholder="" required value="0"
                                        onkeyup="add_diskon()" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="dp" class="form-label">DP</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">Rp</span>
                                    <input type="text" class="form-control" name="dp" id="dp" aria-describedby="helpId"
                                        placeholder="" required value="0" onkeyup="add_dp()">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="uraian" class="form-label">Apakah DP menggunakan PPn? <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="dp_ppn" id="dp_ppn" onchange="add_dp_ppn()" required>
                                    <option value="">-- Pilih Salah Satu --</option>
                                    <option value="1">Dengan PPn</option>
                                    <option value="0">Tanpa PPn</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="jatuh_tempo" class="form-label">Tgl Jatuh Tempo</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i
                                            class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control" name="jatuh_tempo" id="jatuh_tempo"
                                        aria-describedby="helpId" placeholder="" readonly required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="supplier_id" class="form-label">Supplier Bahan Baku</label>
                            <select class="form-select" name="supplier_id" id="supplier_id" onchange="funSupplier()"
                                required>
                                <option value="">-- Pilih Supplier --</option>
                                @foreach ($supplier as $s)
                                <option value="{{$s->id}}">{{$s->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="nama_rek" class="form-label">Nama Rekening</label>
                            <input type="text" class="form-control @if ($errors->has('nama_rek'))
                        is-invalid
                    @endif" name="nama_rek" id="nama_rek" value="{{old('nama_rek')}}" maxlength="15" required
                                value="{{old('nama_rek')}}" readonly>
                            @if ($errors->has('nama_rek'))
                            <div class="invalid-feedback">
                                {{$errors->first('nama_rek')}}
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="bank" class="form-label">Bank</label>
                            <input type="text" class="form-control @if ($errors->has('bank'))
                        is-invalid
                    @endif" name="bank" id="bank" value="{{old('bank')}}" maxlength="10" required
                                value="{{old('bank')}}" readonly>
                            @if ($errors->has('bank'))
                            <div class="invalid-feedback">
                                {{$errors->first('bank')}}
                            </div>
                            @endif
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="no_rek" class="form-label">Nomor Rekening</label>
                            <input type="text" class="form-control @if ($errors->has('no_rek'))
                        is-invalid
                    @endif" name="no_rek" id="no_rek" value="{{old('no_rek')}}" required value="{{old('no_rek')}}"
                                readonly>
                            @if ($errors->has('no_rek'))
                            <div class="invalid-feedback">
                                {{$errors->first('no_rek')}}
                            </div>
                            @endif
                        </div>
                    </div>
                </form>
                <table class="table table-bordered">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center align-middle">Nama Product</th>
                            <th class="text-center align-middle">Kemasan</th>
                            <th class="text-center align-middle">Banyak</th>
                            <th class="text-center align-middle">Satuan</th>
                            <th class="text-center align-middle">Harga Satuan</th>
                            <th class="text-center align-middle">Biaya Tambahan</th>
                            <th class="text-center align-middle">Total</th>
                            <th class="text-center align-middle">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keranjang as $b)
                        <tr>
                            <td class="text-center align-middle">{{$b->kemasan->product->nama}}</td>
                            <td class="text-center align-middle">{{$b->kemasan->nama}}</td>
                            <td class="text-center align-middle">{{$b->nf_jumlah}}</td>
                            <td class="text-center align-middle">{{$b->satuan->nama}}</td>
                            <td class="text-center align-middle">{{number_format($b->harga, 0, ',','.')}}</td>
                            <td class="text-center align-middle">{{number_format($b->add_fee, 0, ',','.')}}</td>
                            <td class="text-end align-middle">{{number_format($b->total + $b->add_fee, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle">
                                <form action="{{ route('billing.form-transaksi.kemasan.keranjang.delete', $b->id) }}"
                                    method="post" id="deleteForm{{ $b->id }}" class="delete-form"
                                    data-id="{{ $b->id }}">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td class="text-end align-middle" colspan="6">Total DPP</td>
                            <td class="text-end align-middle" id="tdTotal">{{count($keranjang) > 0 ?
                                number_format($keranjang->sum('total') + $keranjang->sum('add_fee'), 0, ',','.') :
                                ''}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">Diskon</td>
                            <td class="text-end align-middle" id="tdDiskon">
                                {{number_format($diskon, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">Total DPP Setelah Diskon</td>
                            <td class="text-end align-middle" id="tdTotalSetelahDiskon">
                                {{number_format($total-$diskon, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">PPN</td>
                            <td class="text-end align-middle" id="tdPpn">
                                0
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">Grand Total</td>
                            <td class="text-end align-middle" id="grand_total">
                                {{number_format($total + $add_fee + $ppn - $diskon, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">DP</td>
                            <td class="text-end align-middle" id="dpTd">
                                {{number_format($dp, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">DP PPN</td>
                            <td class="text-end align-middle" id="dpPPNtd">
                                {{number_format($dpPPN, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">Total DP</td>
                            <td class="text-end align-middle" id="totalDpTd">
                                {{number_format($totalDp, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">Sisa PPN</td>
                            <td class="text-end align-middle" id="sisaPPN">
                                {{number_format($sisaPPN, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>
                        <tr>
                            <td class="text-end align-middle" colspan="6">Sisa Tagihan</td>
                            <td class="text-end align-middle" id="sisa">
                                {{number_format($total + $add_fee + $ppn - $diskon - $sisaPPN, 0, ',','.')}}
                            </td>
                            <td class="text-center align-middle"></td>
                        </tr>

                    </tfoot>
                </table>

                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="submitBeli()">Beli Barang</button>

            </div>

            <div class="modal-footer">

            </div>
        </div>
    </div>
</div>
@push('css')
<link rel="stylesheet" href="{{asset('/assets/js/flatpickr/flatpickr.min.css')}}">
@endpush
@push('js')
<script src="{{asset('/assets/js/flatpickr/flatpickr.js')}}"></script>
<script>
    flatpickr("#jatuh_tempo", {
            dateFormat: "d-m-Y",
        });

        var dp = new Cleave('#dp', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

        function funSupplier()
        {
            var supplier_id = document.getElementById('supplier_id').value;
            $.ajax({
                url: "{{route('billing.form-transaksi.bahan-baku.get-supplier')}}",
                type: "GET",
                data: {
                    id: supplier_id
                },
                success: function(data){
                    document.getElementById('nama_rek').value = data.nama_rek;
                    document.getElementById('bank').value = data.bank;
                    document.getElementById('no_rek').value = data.no_rek;
                }
            });
        }
</script>
@endpush
