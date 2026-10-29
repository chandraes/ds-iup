@extends('layouts.app')
@section('content')
@include('billing.invoice-konsumen.cicil')
@include('billing.invoice-konsumen.cicil-non-new')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE KONSUMEN<br> {{isset($titipan) && $titipan==1 ? 'TITIPAN' : 'TEMPO'}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen"
                                width="30">
                            Billing</a></td>
                    <td><a target="_blank"
                            href="{{route('billing.invoice-konsumen.pdf-all', [
                                'expired' => request('expired'),
                                'apa_ppn' => request('apa_ppn'),
                                'karyawan_id' => request('karyawan_id'),
                                'kabupaten_id' => request('kabupaten_id'),
                                'kecamatan_id' => request('kecamatan_id'),
                                'konsumen_id' => request('konsumen_id'),
                            ])}}"><img
                                src="{{asset('images/print.svg')}}" alt="dokumen" width="30">
                            Print</a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>
    <div class="row">
        <form action="{{url()->current()}}" method="get">
            @if(request('konsumen_id'))<input type="hidden" name="konsumen_id" value="{{ request('konsumen_id') }}">@endif
            <div class="row">
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="expired" id="expired" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Data --</option>
                        <option value="yes" {{ request('expired')=='yes' ? 'selected' : '' }}>Expired</option>
                        <option value="no" {{ request()->has('expired') && request('expired') == 'no' ? 'selected' : ''
                            }}>Belum
                            Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="apa_ppn" id="apa_ppn" class="form-select" onchange="this.form.submit()">
                        <option value="">-- PPN & Non PPN --</option>
                        <option value="yes" {{ request('apa_ppn')=='yes' ? 'selected' : '' }}>PPN</option>
                        <option value="no" {{ request()->has('apa_ppn') && request('apa_ppn') == 'no' ? 'selected' : ''
                            }}>Non PPN</option>
                    </select>
                </div>
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="karyawan_id" id="karyawan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Sales --</option>
                        @foreach ($sales as $s)
                        <option value="{{$s->id}}" {{ request('karyawan_id')==$s->id ? 'selected' : '' }}>{{$s->nama}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="kabupaten_id" id="kabupaten_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kab/Kota --</option>
                        @foreach ($kabupaten as $kab)
                        <option value="{{$kab->id}}" {{ request('kabupaten_id')==$kab->id ? 'selected' : '' }}>{{$kab->nama_wilayah}}</option>
                        @endforeach
                    </select>
                </div>
                 <div class="col-md-3">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="kecamatan_id" id="kecamatan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kecamatan --</option>
                        @foreach ($kecamatan as $k)
                        <option value="{{$k->id}}" {{ request('kecamatan_id')==$k->id ? 'selected' : '' }}>{{$k->nama_wilayah}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </form>
        <div class="row mt-2">
        <div class="col-md-2">

                <a href="{{ url()->current() }}" class="btn btn-secondary mb-2"><i class="fa fa-repeat"></i> Reset Filter</a>
            </div>
        </div>
        </div>
    </div>

</div>
<div class="container-fluid invoice-table-ui">
    <div id="invoice-table-error" class="alert alert-danger" role="alert" hidden>Data invoice belum dapat dimuat. <button type="button" class="btn btn-sm btn-outline-danger" id="invoice-table-retry">Coba lagi</button></div>
    <p class="text-secondary small">Gulir untuk memuat invoice berikutnya. Total di bawah tabel mencakup seluruh hasil filter dan pencarian.</p>
    <table class="table table-hover table-bordered w-100" id="rekapTable">
        <thead class="table-success"><tr>
            <th>Tanggal</th><th>Sales</th><th>Daerah</th><th>Kode</th><th>Konsumen</th><th>Plafon</th><th>Nota</th><th>Nilai</th><th>Total Belanja</th><th>DP</th><th>DP PPN</th><th>Cicilan</th><th>Sisa PPN</th><th>Sisa Tagihan</th><th>Jatuh Tempo</th><th>ACT</th>
        </tr></thead>
        <tbody></tbody>
        <tfoot><tr><th colspan="7" class="text-end">Grand Total</th><th id="invoice-total-components">—</th>
            @foreach(['grand_total', 'dp', 'dp_ppn', 'cicilan', 'sisa_ppn', 'sisa_tagihan'] as $key)<th class="text-end" data-invoice-total="{{ $key }}">—</th>@endforeach
            <th colspan="2"></th>
        </tr></tfoot>
    </table>
</div>
<div class="modal fade invoice-modern-modal" id="invoice-history-modal" tabindex="-1" aria-labelledby="invoice-history-title" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
    <div class="modal-header"><h2 class="modal-title fs-5" id="invoice-history-title">Histori cicilan</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body"><p id="invoice-history-status" role="status"></p><table class="table table-bordered"><thead class="table-success"><tr><th>Tanggal</th><th>Nominal</th><th class="invoice-history-ppn">PPN</th><th>Total</th></tr></thead><tbody id="invoice-history-body"></tbody></table><button type="button" class="btn btn-outline-secondary" id="invoice-history-more" hidden>Muat berikutnya</button></div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button></div>
</div></div></div>
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/dt.min.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.dataTables.min.css">
<link rel="stylesheet" href="{{ asset('customer-invoice-table.css').'?v='.filemtime(public_path('customer-invoice-table.css')) }}">
@endpush
@push('js')
<script src="{{ asset('assets/plugins/select2/select2.full.min.js') }}"></script>
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>
<script>window.customerInvoiceConfig = @json($invoiceTableConfig);</script>
<script src="{{ asset('customer-invoice-table.js').'?v='.filemtime(public_path('customer-invoice-table.js')) }}"></script>
@endpush
