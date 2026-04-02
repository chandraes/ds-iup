@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3">
        <h2 class="mb-0 text-center text-md-start fw-bold">DETAIL INVOICE KONSUMEN</h2>
        <div class="d-flex gap-2">
            <a href="{{route('home')}}" class="btn btn-outline-secondary d-flex align-items-center">
                <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="20" class="me-2"> Dashboard
            </a>
            <a href="{{url()->previous()}}" class="btn btn-outline-primary d-flex align-items-center">
                <img src="{{asset('images/back.svg')}}" alt="kembali" width="20" class="me-2"> Kembali
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h4 class="text-uppercase fw-bold text-decoration-underline">{{$data->uraian}}</h4>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">Data Konsumen</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width: 35%">Konsumen</td>
                            <td style="width: 5%">:</td>
                            <td class="fw-semibold">
                                {{$data->konsumen ? $data->konsumen->kode_toko?->kode.' '.$data->konsumen->nama : $data->konsumen_temp->nama}}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sistem Pembayaran</td>
                            <td>:</td>
                            <td>{{$data->konsumen ? $data->konsumen->sistem_pembayaran : 'Cash'}}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tempo</td>
                            <td>:</td>
                            <td>{{$data->konsumen ? $data->konsumen->tempo_hari . ' Hari' : '-'}}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NPWP</td>
                            <td>:</td>
                            <td>{{$data->konsumen ? $data->konsumen->npwp : $data->konsumen_temp->npwp}}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat</td>
                            <td>:</td>
                            <td>{{$data->konsumen ? $data->konsumen->alamat : $data->konsumen_temp->alamat}}</td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">Data Invoice</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width: 35%">No. Invoice</td>
                            <td style="width: 5%">:</td>
                            <td><strong class="text-primary">{{$data->kode}}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tanggal</td>
                            <td>:</td>
                            <td>{{$tanggal}}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jam</td>
                            <td>:</td>
                            <td>{{$jam}}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">No WA</td>
                            <td>:</td>
                            <td>{{$data->konsumen ? $data->konsumen->no_hp : $data->konsumen_temp->no_hp}}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped table-hover align-middle" id="rekapTable" style="font-size: 0.875rem; width: 100%;">
                    <thead class="table-success text-center align-middle">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th class="text-center">NAMA BARANG/MEREK</th>
                            <th class="text-center">MEREK</th>
                            <th class="text-center" width="8%">QTY</th>
                            <th class="text-center" width="8%">SAT</th>
                            <th class="text-center">HARGA SATUAN</th>
                            <th class="text-center">HARGA DISKON</th>
                            <th class="text-center">TOTAL HARGA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data->invoice_detail as $d)
                        <tr>
                            <td class="text-center">{{$loop->iteration}}</td>
                            <td>
                                <span class="fw-semibold">{{$d->stok->barang_nama->nama}}</span><br>
                                <small class="text-muted">{{$d->stok->barang->kode}}</small>
                            </td>
                            <td>{{$d->stok->barang->merk}}</td>
                            <td class="text-center">
                                {{$d->nf_jumlah}}
                                @if ($d->is_grosir == 1)
                                    <br><small class="text-muted">({{$d->nf_jumlah_grosir}})</small>
                                @endif
                            </td>
                            <td class="text-center">
                                {{$d->stok->barang->satuan ? $d->stok->barang->satuan->nama : '-'}}
                                @if ($d->is_grosir == 1)
                                    <br><small class="text-muted">({{$d->satuan_grosir ? $d->satuan_grosir->nama : '-'}})</small>
                                @endif
                            </td>
                            <td class="text-end">
                                @if ($data->kas_ppn == 1)
                                    {{number_format($d->harga_satuan + floor($ppn / 100 * $d->harga_satuan), 0, ',','.')}}
                                @else
                                    {{$d->nf_harga_satuan}}
                                @endif
                            </td>
                            <td class="text-end">
                                {{number_format($d->harga_satuan - $d->diskon + $d->ppn, 0, ',','.')}}
                            </td>
                            <td class="text-end fw-semibold">
                                {{$d->nf_total}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-lg-5 col-md-7 col-sm-12">
                    <table class="table table-sm table-borderless text-end mb-2">
                        <tr>
                            <td class="fw-bold fs-6">Grand Total</td>
                            <td style="width: 5%">:</td>
                            <td class="fw-bold fs-6 text-primary">{{$data->nf_grand_total}}</td>
                        </tr>

                        @if ($data->konsumen && $data->konsumen->pembayaran == 2 && $data->lunas == 0)
                        <tr>
                            <td class="text-muted">DP</td>
                            <td>:</td>
                            <td>{{number_format($data->dp + $data->dp_ppn, 0,',','.')}}</td>
                        </tr>
                          <tr class="border-top">
                            <td class="fw-bold text-primary">Cicilan</td>
                            <td>:</td>
                            <td class="fw-bold text-primary">{{number_format($data->invoice_jual_cicil->sum('nominal')+$data->invoice_jual_cicil->sum('ppn'), 0,',','.')}}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="fw-bold text-danger">Sisa Tagihan</td>
                            <td>:</td>
                            <td class="fw-bold text-danger">{{$data->nf_sisa_tagihan}}</td>
                        </tr>
                        @endif
                    </table>

                    <div class="alert alert-light border text-center fst-italic fw-bold py-2 mt-3 mb-0" style="font-size: 0.9rem;">
                        # {{$terbilang}} Rupiah #
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<style>
    /* Custom tweak untuk header tabel */
    #rekapTable thead th {
        white-space: nowrap;
    }
</style>
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "info": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,

            "responsive": true
        });
    });
</script>
@endpush
