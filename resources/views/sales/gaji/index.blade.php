@extends('layouts.app')
@section('content')
@php
    $statusLabels = ['stored' => 'Diproses', 'estimate' => 'Estimasi', 'not_recorded' => 'Tidak tercatat', 'upcoming' => 'Belum dimulai', 'unavailable' => 'Estimasi tidak tersedia'];
    $money = fn ($value) => number_format($value, 0, ',', '.');
@endphp
<div class="container-fluid payroll-ui sales-payroll">
    <header class="payroll-header"><div><div class="payroll-eyebrow">Sales · Penghasilan pribadi</div><h1>Gaji Saya</h1><p>{{ $employee->nama }} · Riwayat gaji dan estimasi pendapatan per periode.</p></div><a href="{{ route('home') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Dashboard</a></header>
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <section class="sales-payroll-panel">
        <div class="sales-payroll-heading"><div><h2>Periode {{ $year }}</h2><p>Seluruh komponen gaji tersedia pada tabel ini. Klik komisi atau BBM untuk melihat perhitungannya.</p></div>
            <form action="{{ route('sales.gaji') }}" method="get" class="d-flex align-items-end gap-2"><div><label for="payroll-year" class="form-label">Tahun</label><select id="payroll-year" name="year" class="form-select">@foreach($years as $value)<option value="{{ $value }}" @selected($value === $year)>{{ $value }}</option>@endforeach</select></div><button type="submit" class="btn btn-success">Tampilkan</button></form>
        </div>
        <div class="sales-payroll-context">
            <strong>{{ $selected['label'] }} {{ $year }} · {{ $isStored ? 'Snapshot final' : $statusLabels[$selected['status']] }}</strong>
            @if($selected['status'] === 'estimate')<p>Estimasi memakai data pegawai saat ini dan invoice lunas terbaru. {{ $attendanceMessage }}.@if(!$bbmAvailable) Total estimasi belum lengkap karena BBM belum tersedia.@endif</p>
            @elseif($selected['status'] === 'upcoming')<p>Periode belum dimulai. Estimasi tersedia setelah periode ini dimulai.</p>
            @elseif($selected['status'] === 'not_recorded')<p>Tidak tercatat pada rekap periode ini. Hubungi admin untuk pemeriksaan.</p>
            @elseif($selected['status'] === 'unavailable')<p>Estimasi tidak tersedia untuk pegawai yang tidak aktif atau bukan sales.</p>@endif
        </div>
        <p class="payroll-footnote mt-0">Semua nominal dalam rupiah. Gulir tabel ke samping untuk melihat seluruh komponen.</p>
        <div class="table-responsive sales-payroll-scroll" id="tabel-gaji" tabindex="0" role="region" aria-label="Komponen gaji Januari sampai Desember. Gulir ke samping untuk melihat seluruh kolom."><table class="table table-bordered table-hover sales-payroll-table"><thead class="table-success"><tr>
            <th rowspan="2" scope="col">Bulan / periode</th><th rowspan="2" scope="col">Status</th><th rowspan="2" scope="col">Gaji pokok</th><th colspan="4" scope="colgroup">Tunjangan</th><th rowspan="2" scope="col">Komisi</th><th rowspan="2" scope="col">Tunjangan BBM</th><th rowspan="2" scope="col">Pendapatan kotor</th><th colspan="2" scope="colgroup">Potongan BPJS</th><th rowspan="2" scope="col">Pendapatan Bersih</th><th rowspan="2" scope="col">Potongan Kasbon</th><th rowspan="2" scope="col">Potongan absensi</th><th rowspan="2" scope="col">Gaji dibayar / estimasi</th>
        </tr><tr><th scope="col">Jabatan</th><th scope="col">Keluarga</th><th scope="col">BPJS TK</th><th scope="col">BPJS Kesehatan</th><th scope="col">TK</th><th scope="col">Kesehatan</th></tr></thead><tbody>
            @foreach($months as $item)
                @php
                    $selectedItem = $item['month'] === $selectedMonth;
                    $amounts = $item['amounts'];
                    $itemBbmAvailable = $item['bbmAvailable'];
                    $commissionUrl = route('sales.gaji.rincian', ['year' => $year, 'month' => $item['month'], 'type' => 'komisi']);
                    $bbmUrl = route('sales.gaji.rincian', ['year' => $year, 'month' => $item['month'], 'type' => 'bbm']);
                    $estimate = $item['status'] === 'estimate';
                @endphp
                <tr @class(['sales-payroll-selected' => $selectedItem])>
                    <th scope="row" class="sales-payroll-month">{{ $item['label'] }}<small>{{ $item['from']->format('d/m') }}–{{ $item['to']->format('d/m/Y') }}</small></th>
                    <td><span @class(['sales-payroll-badge', 'is-final' => $item['status'] === 'stored'])>{{ $statusLabels[$item['status']] }}</span></td>
                    @foreach(['gaji_pokok','tunjangan_jabatan','tunjangan_keluarga','bpjs_tk','bpjs_k'] as $component)<td class="text-end">{{ $amounts ? $money($amounts[$component] ?? 0) : '—' }}</td>@endforeach
                    <td class="text-end">@if($amounts || $estimate)<button type="button" class="payroll-commission-link" data-bs-toggle="modal" data-bs-target="#payroll-commission" data-url="{{ $commissionUrl }}" aria-label="Lihat rincian komisi {{ $item['label'] }} {{ $year }}">{{ $amounts ? $money($amounts['komisi']) : 'Lihat komisi' }}</button>@else—@endif</td>
                    <td>@if($item['status'] === 'stored')<button type="button" class="payroll-commission-link" data-bs-toggle="modal" data-bs-target="#payroll-commission" data-url="{{ $bbmUrl }}" aria-label="Lihat rincian BBM {{ $item['label'] }} {{ $year }}">{{ $money($amounts['tunjangan_bbm']) }}</button>@elseif($estimate)
                        @if(!$item['attendanceEnabled'])<span class="sales-payroll-note">Integrasi absensi nonaktif</span>
                        @elseif(!$itemBbmAvailable)<span class="sales-payroll-note">Snapshot absensi belum tersedia</span>
                        @else<button type="button" class="payroll-commission-link" data-bs-toggle="modal" data-bs-target="#payroll-commission" data-url="{{ $bbmUrl }}" aria-label="Lihat rincian BBM {{ $item['label'] }} {{ $year }}">{{ $money($amounts['tunjangan_bbm']) }}</button>@endif
                    @else—@endif</td>
                    <td class="text-end">@if($amounts && $itemBbmAvailable){{ $money($amounts['pendapatan_kotor']) }}@elseif($amounts)<span class="sales-payroll-note">Estimasi belum lengkap</span>@else—@endif</td>
                    @foreach(['potongan_bpjs_tk','potongan_bpjs_kesehatan'] as $component)<td class="text-end">{{ $amounts ? $money($amounts[$component] ?? 0) : '—' }}</td>@endforeach
                    <td class="text-end">@if($amounts && $itemBbmAvailable){{ $money($amounts['pendapatan_bersih']) }}@elseif($amounts)<span class="sales-payroll-note">Estimasi belum lengkap</span>@else—@endif</td>
                    <td class="text-end">@if($amounts){{ $money($amounts['potongan_kasbon'] ?? 0) }}@else—@endif</td>
                    <td class="text-end">{{ $amounts ? $money($amounts['potongan_absensi'] ?? 0) : '—' }}</td>
                    <td class="text-end">@if($amounts && $itemBbmAvailable){{ $money($amounts['sisa_gaji_dibayar']) }}@elseif($amounts)<span class="sales-payroll-note">Estimasi belum lengkap</span>@else—@endif</td>
                </tr>
            @endforeach
        </tbody></table></div>
        <p class="payroll-footnote">Estimasi seluruh periode yang sudah dimulai dan belum diproses dihitung otomatis saat halaman dibuka, dan dapat berubah sampai gaji diproses. Snapshot final mengikuti data yang tersimpan. Pada rekap lama, gaji dibayar mengikuti pendapatan bersih yang tercatat jika nilai pembayaran terpisah belum tersedia.</p>
    </section>
</div>
@include('rekap.gaji.commission-detail')
@endsection
@push('css')
<link rel="stylesheet" href="{{ asset('payroll-ui.css').'?v='.filemtime(public_path('payroll-ui.css')) }}">
<link rel="stylesheet" href="{{ asset('sales-payroll.css').'?v='.filemtime(public_path('sales-payroll.css')) }}">
@endpush

@push('js')<script src="{{ asset('payroll-ui.js').'?v='.filemtime(public_path('payroll-ui.js')) }}"></script>@endpush
