@extends('layouts.app')
@section('content')
@php
    $date = \Carbon\Carbon::parse(request('tanggal'))->locale('id');
    $salesTotal = $data['data']->sum('grand_total');
    $voidTotal = $data['void']->sum('grand_total');
    $number = 1;
@endphp
<div class="container-fluid sales-turnover">
    <header class="turnover-header">
        <div><div class="turnover-eyebrow">Statistik · Detail omset</div><h1>{{ $karyawan->nama }}</h1><p>Rincian omset {{ $date->translatedFormat('d F Y') }} <span aria-hidden="true">·</span> Periode {{ \Carbon\Carbon::create($backYear,$backMonth,1)->locale('id')->translatedFormat('F Y') }}</p></div>
        <a href="{{ route('statistik.omset-harian-sales',['month'=>$backMonth,'year'=>$backYear]) }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali ke omset</a>
    </header>
    <div class="turnover-stats">
        <div class="turnover-stat is-highlight"><span>Omset bersih hari ini</span><strong>Rp {{ number_format($salesTotal-$voidTotal,0,',','.') }}</strong><small>Nilai penjualan dikurangi koreksi pembatalan</small></div>
        <div class="turnover-stat"><span>Penjualan</span><strong>Rp {{ number_format($salesTotal,0,',','.') }}</strong><small>{{ $data['data']->count() }} invoice terbit pada tanggal ini</small></div>
        <div class="turnover-stat"><span>Koreksi pembatalan</span><strong class="turnover-negative">Rp {{ number_format($voidTotal,0,',','.') }}</strong><small>{{ $data['void']->count() }} invoice mengurangi omset tanggal ini</small></div>
    </div>
    <section class="turnover-panel">
        <div class="turnover-panel-heading"><div><h2>Rincian invoice</h2><p>Cari nama toko, wilayah, atau nomor invoice. Klik nomor invoice untuk melihat rinciannya.</p></div><span class="turnover-badge">{{ $date->translatedFormat('d F Y') }}</span></div>
        @if($data['data']->isEmpty() && $data['void']->isEmpty())
            <p class="turnover-empty">Tidak ada transaksi omset untuk sales pada tanggal ini.</p>
        @else
        <div class="table-responsive"><table id="turnover-detail" class="table table-hover turnover-table" style="width:100%">
            <thead class="table-success"><tr><th scope="col">No</th><th scope="col">Toko</th><th scope="col">Kabupaten / Kota</th><th scope="col">Kecamatan</th><th scope="col">Invoice</th><th scope="col">Pencatatan</th><th scope="col" class="text-end">Nominal (Rp)</th></tr></thead>
            <tbody>
                @foreach(['data'=>'Penjualan','void'=>'Koreksi void'] as $group=>$label)
                    @foreach($data[$group] as $invoice)
                    <tr>
                        <td>{{ $number++ }}</td>
                        <td><strong>{{ $invoice->konsumen?->nama ?? 'Konsumen tidak tersedia' }}</strong><div class="small text-muted">{{ $invoice->konsumen?->kode_toko?->kode ?? '—' }}</div></td>
                        <td>{{ $invoice->konsumen?->kabupaten_kota?->nama_wilayah ?? '—' }}</td>
                        <td>{{ $invoice->konsumen?->kecamatan?->nama_wilayah ?? '—' }}</td>
                        <td><a href="{{ route('rekap.invoice-penjualan.detail',['invoice'=>$invoice->id]) }}">{{ $invoice->kode }}</a></td>
                        <td><span class="turnover-entry {{ $group === 'void' ? 'is-void' : '' }}">{{ $label }}</span></td>
                        <td class="text-end {{ $group === 'void' ? 'turnover-negative' : '' }}" data-order="{{ $group === 'void' ? -$invoice->grand_total : $invoice->grand_total }}">{{ $group === 'void' ? '−' : '' }}{{ number_format($invoice->grand_total,0,',','.') }}</td>
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot><tr><th scope="row" colspan="6">Total omset hari ini</th><td class="text-end fw-bold">{{ number_format($salesTotal-$voidTotal,0,',','.') }}</td></tr></tfoot>
        </table></div>
        @endif
        <p class="turnover-footnote">Penjualan mengikuti tanggal invoice dibuat. Koreksi void mengikuti tanggal pembaruan invoice yang dibatalkan. Total mencakup seluruh transaksi pada tanggal ini.</p>
    </section>
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="{{ asset('sales-turnover.css').'?v='.filemtime(public_path('sales-turnover.css')) }}" rel="stylesheet">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script>
$(function () {
    if (!document.getElementById('turnover-detail')) return;
    $('#turnover-detail').DataTable({
        paging:false, ordering:true, order:[], scrollCollapse:true, scrollY:'520px', scrollX:true,
        language:{search:'Cari invoice:',searchPlaceholder:'Toko, wilayah, atau invoice…',zeroRecords:'Tidak ada invoice yang sesuai pencarian.',info:'Menampilkan _START_–_END_ dari _TOTAL_ pencatatan',infoEmpty:'Tidak ada pencatatan yang ditampilkan',infoFiltered:'(dari _MAX_ pencatatan)'},
        initComplete:function(){this.api().columns.adjust();}
    });
});
</script>
@endpush
