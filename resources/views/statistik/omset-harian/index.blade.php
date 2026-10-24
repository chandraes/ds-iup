@extends('layouts.app')
@section('content')
<div class="container-fluid sales-turnover">
    <header class="turnover-header">
        <div><div class="turnover-eyebrow">Statistik · Penjualan</div><h1>Omset harian sales</h1><p>Periode {{ $dataBulan[$selectedMonth] }} {{ $selectedYear }} <span aria-hidden="true">·</span> {{ $periodStart->copy()->locale('id')->translatedFormat('d F Y') }}–{{ $periodEnd->copy()->locale('id')->translatedFormat('d F Y') }}</p></div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('statistik') }}" class="btn btn-outline-secondary"><i class="fa fa-arrow-left" aria-hidden="true"></i> Kembali ke statistik</a>
            <a href="{{ route('home') }}" class="btn btn-outline-secondary"><i class="fa fa-home" aria-hidden="true"></i> Dashboard</a>
        </div>
    </header>
    <section class="turnover-filter" aria-label="Filter periode omset">
        <div><h2>Periode</h2><p>Dimulai tanggal 26 bulan sebelumnya hingga tanggal 25 bulan yang dipilih.</p></div>
        <form action="{{ route('statistik.omset-harian-sales') }}" method="get">
            <div><label for="month">Bulan periode</label><select name="month" id="month" class="form-select">@foreach($dataBulan as $value=>$label)<option value="{{ $value }}" @selected((int)$value === $selectedMonth)>{{ $label }}</option>@endforeach</select></div>
            <div><label for="year">Tahun</label><select name="year" id="year" class="form-select">@foreach($years as $value)<option value="{{ $value }}" @selected($value === $selectedYear)>{{ $value }}</option>@endforeach</select></div>
            <button class="btn btn-success"><i class="fa fa-filter" aria-hidden="true"></i> Tampilkan</button>
        </form>
    </section>
    <div class="turnover-stats">
        <div class="turnover-stat is-highlight"><span>Total omset periode</span><strong>Rp {{ number_format($totals->sum(),0,',','.') }}</strong><small>Penjualan dikurangi pembatalan pada periode ini</small></div>
        <div class="turnover-stat"><span>Sales aktif</span><strong>{{ $karyawans->count() }} <small>pegawai</small></strong><small>Sales yang ditampilkan pada rekap</small></div>
        <div class="turnover-stat"><span>Rentang periode</span><strong>{{ count($rows) }} <small>hari kalender</small></strong><small>Jumlah hari dalam rentang, bukan jumlah hari kerja</small></div>
    </div>
    <section class="turnover-panel">
        <div class="turnover-panel-heading"><div><h2>Rincian omset harian</h2><p>Klik nominal untuk melihat invoice. Nilai negatif menunjukkan koreksi pembatalan.</p></div><span class="turnover-badge">{{ $periodStart->format('d/m/Y') }}–{{ $periodEnd->format('d/m/Y') }}</span></div>
        @if($karyawans->isEmpty())<p class="turnover-empty">Belum ada sales aktif untuk ditampilkan.</p>@else
        <div class="table-responsive"><table id="rekapTable" class="table table-hover turnover-table" style="width:100%">
            <thead class="table-success"><tr><th scope="col">Tanggal</th>@foreach($karyawans as $employee)<th scope="col" class="text-end">{{ $employee->nama }}</th>@endforeach</tr></thead>
            <tbody>@foreach($rows as $row)<tr><th scope="row" data-order="{{ $row['tanggal'] }}">{{ \Carbon\Carbon::parse($row['tanggal'])->locale('id')->translatedFormat('d M Y') }}</th>@foreach($karyawans as $employee)<td class="text-end {{ $row[$employee->id] < 0 ? 'turnover-negative' : '' }}" data-order="{{ $row[$employee->id] }}">@if($row[$employee->id] != 0)<a href="{{ route('statistik.omset-harian-sales.detail',['tanggal'=>$row['tanggal'],'karyawan_id'=>$employee->id,'month'=>$selectedMonth,'year'=>$selectedYear]) }}" aria-label="Detail omset {{ $employee->nama }} tanggal {{ $row['tanggal'] }}">{{ number_format($row[$employee->id],0,',','.') }}</a>@else<span class="text-muted">0</span>@endif</td>@endforeach</tr>@endforeach</tbody>
            <tfoot><tr><th scope="row">Total periode</th>@foreach($karyawans as $employee)<td class="text-end fw-bold">{{ number_format($totals[$employee->id],0,',','.') }}</td>@endforeach</tr></tfoot>
        </table></div>
        @endif
        <p class="turnover-footnote">Nominal dalam rupiah. Omset menggunakan nilai grand total invoice non-titipan, termasuk invoice yang belum lunas.</p>
    </section>
    @if($karyawans->isNotEmpty())<section class="turnover-panel"><div class="turnover-panel-heading"><div><h2>Tren omset sales</h2><p>Perbandingan omset harian selama periode yang dipilih.</p></div></div><div class="turnover-chart"><canvas id="omsetChart" role="img" aria-label="Grafik omset harian sales. Rincian angka tersedia pada tabel di atas."></canvas></div></section>@endif
</div>
@endsection
@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
<link href="{{ asset('sales-turnover.css').'?v='.filemtime(public_path('sales-turnover.css')) }}" rel="stylesheet">
@endpush
@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function () {
    if (!document.getElementById('rekapTable')) return;
    $('#rekapTable').DataTable({paging:false, ordering:false, searching:false, info:false, scrollCollapse:true, scrollY:'520px', scrollX:true, initComplete:function(){this.api().columns.adjust();}});
    const rows = @json($rows);
    const employees = @json($chartSales);
    const colors = ['#17694f','#3469a3','#ae6331','#7957a6','#a33c57','#36858a','#897326','#596b85'];
    const money = value => new Intl.NumberFormat('id-ID').format(value);
    new Chart(document.getElementById('omsetChart'), {
        type:'line',
        data:{labels:rows.map(row=>{const parts=row.tanggal.split('-');return parts[2]+'/'+parts[1];}),datasets:employees.map((employee,index)=>({label:employee.nama,data:rows.map(row=>Number(row[employee.id]||0)),borderColor:colors[index%colors.length],backgroundColor:'transparent',borderWidth:2,pointRadius:2,pointHoverRadius:5,tension:.2}))},
        options:{responsive:true,maintainAspectRatio:false,animation:!window.matchMedia('(prefers-reduced-motion: reduce)').matches,plugins:{legend:{position:'bottom',labels:{boxWidth:12,padding:18}},tooltip:{callbacks:{label:context=>context.dataset.label+': Rp '+money(context.parsed.y)}}},scales:{y:{beginAtZero:true,ticks:{callback:value=>'Rp '+money(value)}},x:{grid:{display:false}}}}
    });
});
</script>
@endpush
