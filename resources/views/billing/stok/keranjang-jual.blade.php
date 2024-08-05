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
            <a href="{{route('billing.lihat-stok')}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i>
                Kembali</a>
        </div>
    </div>
    <div class="row">
        <form action="{{route('billing.form-jual.keranjang.checkout')}}" method="post" id="storeForm">
            @csrf
            <div class="card">

                <div class="card-body">
                    <h4 class="card-title">
                        {{-- <strong>#INVOICE : {{$invoice}}</strong> --}}
                    </h4>
                    <div class="row mt-3 mb-3">
                        <div class="col-md-8">
                            <div class="form-group row mb-2">
                                <label class="col-form-label col-md-3">Menggunakan PPh :</label>
                                <div class="col-md-8">
                                    <select class="form-select" name="apa_pph" id="apa_pph" required
                                        onchange="calculatePPh()">
                                        <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                        <option value="1">Ya</option>
                                        <option value="0">Tidak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-form-label col-md-3">Konsumen :</label>
                                <div class="col-md-8">
                                    <select class="form-select" name="konsumen_id" id="konsumen_id" required
                                        onchange="getKonsumenData()">
                                        <option value="" disabled selected>-- Pilih Konsumen --</option>
                                        <option value="*">INPUT MANUAL</option>
                                        @foreach ($konsumen as $k)
                                        <option value="{{$k->id}}">{{$k->nama}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row" id="konsumenRow" hidden>
                                <div class="form-group row mt-2">
                                    <label class="col-form-label col-md-3">Sistem Pembayaran :</label>
                                    <div class="col-md-8">
                                        <input type="text" name="pembayaran" id="pembayaran" class="form-control"
                                            disabled>
                                    </div>
                                </div>
                                <div class="form-group row mt-2">
                                    <label class="col-form-label col-md-3">Alamat :</label>
                                    <div class="col-md-8">
                                        <textarea name="alamat" id="alamat" class="form-control" disabled></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <hr>
                        <div class="row mt-4" id="konsumenTempRow" hidden>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="nama" class="form-label">Nama Konsumen</label>
                                    <input type="text" class="form-control" name="nama" id="nama" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="no_hp" class="form-label">No HP</label>
                                    <input type="text" class="form-control" name="no_hp" id="no_hp" placeholder="Kosongkan jika tidak ada"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="npwp" class="form-label">NPWP</label>
                                    <input type="text" class="form-control" name="npwp" id="npwp" placeholder="Kosongkan jika tidak ada"/>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat</label>
                                    <input type="text" class="form-control" name="alamat" id="alamat" placeholder="Kosongkan jika tidak ada"/>
                                </div>
                            </div>
                        </div>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center align-middle">Unit</th>
                                    <th class="text-center align-middle">Type</th>
                                    <th class="text-center align-middle">Kategori Barang</th>
                                    <th class="text-center align-middle">Nama Barang</th>
                                    <th class="text-center align-middle">Kode</th>
                                    <th class="text-center align-middle">Merk</th>
                                    <th class="text-center align-middle">Banyak</th>
                                    <th class="text-center align-middle">Satuan</th>
                                    <th class="text-center align-middle">Harga Satuan</th>
                                    <th class="text-center align-middle">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($keranjang as $b)
                                <tr>
                                    <td class="text-center align-middle">{{$b->barang->type->unit->nama}}</td>
                                    <td class="text-center align-middle">{{$b->barang->type->nama}}</td>
                                    <td class="text-center align-middle">{{$b->barang->kategori->nama}}</td>
                                    <td class="text-center align-middle">{{$b->barang->barang_nama->nama}}</td>
                                    <td class="text-center align-middle">{{$b->barang->kode}}</td>
                                    <td class="text-center align-middle">{{$b->barang->merk}}</td>
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
                                    <th colspan="9" class="text-end align-middle">TOTAL</th>
                                    <th class="text-end align-middle">{{number_format($keranjang->sum('total'), 0,
                                        ',','.')}}</th>
                                </tr>
                                <tr>
                                    <th colspan="9" class="text-end align-middle">Ppn :</th>
                                    <th class="text-end align-middle">{{number_format(($nominalPpn), 0, ',','.')}}</th>
                                </tr>
                                <tr>
                                    <th colspan="9" class="text-end align-middle">Pph :</th>
                                    <th class="text-end align-middle" id="pphTh">0</th>
                                </tr>
                                <tr>
                                    <th colspan="9" class="text-end align-middle">Grand Total :</th>
                                    <th class="text-end align-middle" id="grandTotalTh">
                                        {{number_format(($total+$nominalPpn), 0, ',','.')}}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row ">
                        <div class="col-md-12 text-end">
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
    </div>
</div>
@endsection
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    function calculatePPh()
    {
        var apa_pph = document.getElementById('apa_pph').value;
        var total = {{$total ?? 0}};
        var ppn = {{$nominalPpn}};
        var pph = 0;

        if (apa_pph == 1) {
            pph = total * {{$pphVal / 100}};
        }
        console.log(pph);
        var gt = total + ppn - pph;
        var pphNf = pph.toLocaleString('id-ID');
        var gtVal = gt.toLocaleString('id-ID');
        document.getElementById('pphTh').innerText = pphNf;
        document.getElementById('grandTotalTh').innerText = gtVal;
    }

    function getKonsumenData()
    {
        var id = document.getElementById('konsumen_id').value;

        if (id != '*') {

            document.getElementById('konsumenRow').hidden = false;
            document.getElementById('konsumenTempRow').hidden = true;
            document.getElementById('nama').required = false;

            $.ajax({
                url: '{{route('billing.form-jual.keranjang.get-konsumen')}}',
                type: 'GET',
                data: {
                    id: id
                },
                success: function(data) {
                    document.getElementById('pembayaran').value = data.sistem_pembayaran;
                    document.getElementById('alamat').value = data.alamat;
                }
            });
            return;
        }

        document.getElementById('konsumenRow').hidden = true;
        document.getElementById('konsumenTempRow').hidden = false;

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
