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
            <a href="{{route('billing.stok-bahan-jadi')}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i>
                Kembali</a>
        </div>
    </div>
    <div class="row">
        <form action="{{route('billing.stok-bahan-jadi.checkout.store')}}" method="post" id="storeForm">
            @csrf
            <div class="card">
                {{-- <img class="card-img-top" src="{{asset('images/cgm.png')}}" style="width: 200px" alt="Title" />
                --}}
                <div class="card-body">
                    <h4 class="card-title">
                        <strong>#INVOICE : {{$invoice}}</strong>
                    </h4>
                    <div class="row mt-3 mb-3">
                        <div class="col-md-6">
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
                                        @foreach ($konsumen as $k)
                                        <option value="{{$k->id}}">{{$k->nama}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row mt-2">
                                <label class="col-form-label col-md-3">Sistem Pembayaran :</label>
                                <div class="col-md-8">
                                    <input type="text" name="pembayaran" id="pembayaran" class="form-control" disabled>
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
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="text-center align-middle">Kategori</th>
                                    <th class="text-center align-middle">Jenis Barang</th>
                                    <th class="text-center align-middle">Qty</th>
                                    <th class="text-center align-middle">Satuan</th>
                                    <th class="text-center align-middle">Harga Satuan</th>
                                    <th class="text-center align-middle">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupedData as $group)
                                @php
                                $i = 0;
                                $total = 0;
                                $qty = 0;
                                @endphp
                                @foreach($group as $d)
                                <tr>
                                    @if($i++ == 0)
                                    <td class="text-center align-middle" rowspan="{{ count($group) }}">
                                        {{$loop->iteration}}
                                    </td>
                                    <td class="text-center align-middle" rowspan="{{ count($group) }}">
                                        {{$d->product_jadi->product->kategori->nama}}</td>
                                    @endif
                                    <td class="text-center align-middle">
                                        {{$d->product_jadi->product->nama}}
                                    </td>
                                    <td class="text-center align-middle">
                                        {{$d->jumlah}}
                                    </td>
                                    <td class="text-center align-middle">
                                        @if ($d->product_jadi->kemasan->packaging)
                                        {{$d->product_jadi->kemasan->packaging->nama}}
                                        @else
                                        {{$d->product_jadi->kemasan->nama}}
                                        @endif
                                    </td>
                                    <td class="text-end align-middle">
                                        {{$d->nf_harga}}
                                    </td>
                                    <td class="text-end align-middle">
                                        {{$d->nf_total}}
                                        @php
                                        $total += $d->total;
                                        $qty += $d->jumlah;
                                        @endphp
                                    </td>
                                </tr>
                                @endforeach
                                @endforeach
                            </tbody>
                            @php
                            $ppn = $total*$ppnVal;
                            $grandTotal = $total + $ppn;
                            $pph = 0;
                            @endphp
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end align-middle">TOTAL</th>
                                    <th class="text-center align-middle">{{number_format($qty, 0, ',','.')}}</th>
                                    <th></th>
                                    <th></th>
                                    <th class="text-end align-middle">{{number_format($total, 0, ',','.')}}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end align-middle">Ppn :</th>
                                    <th class="text-end align-middle">{{number_format(($ppn), 0, ',','.')}}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end align-middle">Pph :</th>
                                    <th class="text-end align-middle" id="pphTh">0</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end align-middle">Grand Total :</th>
                                    <th class="text-end align-middle" id="grandTotalTh">{{number_format(($grandTotal), 0, ',','.')}}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="row ">
                        <div class="col-md-12 text-end">
                            <button type="submit" class="btn btn-success"><i class="fa fa-credit-card"></i>
                                Lanjutkan Pembayaran</button>
                                {{-- <button type="button" class="btn btn-info text-white" onclick="javascript:window.print();"><i class="fa fa-print"></i> Print Invoice</button> --}}
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
        var total = {{$total}};
        var ppn = {{$ppn}};
        var pph = 0;

        if (apa_pph == 1) {
            pph = total * {{$pphVal}};
        }

        var gt = total + ppn - pph;
        var pphNf = pph.toLocaleString('id-ID');
        var gtVal = gt.toLocaleString('id-ID');
        document.getElementById('pphTh').innerText = pphNf;
        document.getElementById('grandTotalTh').innerText = gtVal;
    }

    function getKonsumenData()
    {
        var id = document.getElementById('konsumen_id').value;

        $.ajax({
            url: '{{route('billing.stok-bahan-jadi.checkout.konsumen')}}',
            type: 'GET',
            data: {
                id: id
            },
            success: function(data) {
                document.getElementById('pembayaran').value = data.sistem_pembayaran;
                document.getElementById('alamat').value = data.alamat;
            }
        });
    }

    $('#storeForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Semua data pada keranjang akan dihapus!",
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
