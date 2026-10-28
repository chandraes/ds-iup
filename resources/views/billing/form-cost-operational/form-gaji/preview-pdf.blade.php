<!DOCTYPE html>
<html lang="id">
<head><meta charset="utf-8"><title>Preview Gaji IUP {{ $label }}</title>
<style>
@page { margin: 24px; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #24392f; }
h1 { font-size: 17px; margin: 0 0 5px; } p { margin: 5px 0; }
.notice { padding: 8px; border: 1px solid #b9cebf; background: #f3f7f4; margin: 12px 0; }
table { width: 100%; border-collapse: collapse; table-layout: fixed; }
th,td { border: 1px solid #c6d2ca; padding: 5px 3px; overflow-wrap: break-word; }
th { background: #ddefe4; font-size: 7px; } .money { text-align: right; white-space: nowrap; }
tfoot { font-weight: bold; background: #edf4ef; } .number { text-align: center; }
.watermark { position: fixed; top: 38%; left: 30%; color: #edf1ed; font-size: 100px; z-index: -1000; }
</style></head>
<body>
<div class="watermark">DRAFT</div>
<h1>PREVIEW GAJI PEGAWAI — IUP DISTRIBUTOR</h1>
<p><strong>{{ $label }}</strong> · Periode {{ $range }}</p>
<p>DRAFT — Belum diproses atau dibayarkan · Dibuat {{ now()->format('d-m-Y H:i') }}</p>
<div class="notice">{{ $status }} Unduhan ini tidak mengunci periode atau mencatat pembayaran gaji.</div>
<table>
<thead><tr><th style="width: 2%">No.</th><th style="width: 12%">Nama Pegawai</th><th style="width: 7%">Jabatan</th>@foreach($columns as $heading)<th>{{ $heading }}</th>@endforeach</tr></thead>
<tbody>@foreach($rows as $row)<tr><td class="number">{{ $loop->iteration }}</td><td>{{ $row['nama'] }}</td><td>{{ $row['jabatan'] }}</td>@foreach($columns as $key => $heading)<td class="money">{{ number_format($row[$key] ?? 0, 0, ',', '.') }}</td>@endforeach</tr>@endforeach</tbody>
<tfoot><tr><td colspan="3">TOTAL SELURUH PEGAWAI</td>@foreach($columns as $key => $heading)<td class="money">{{ number_format($rows->sum($key), 0, ',', '.') }}</td>@endforeach</tr></tfoot>
</table>
<p>Komisi dan BBM mengikuti perhitungan khusus IUP. Nilai dapat berubah sampai gaji diproses. Seluruh pegawai aktif disertakan, termasuk baris yang tersembunyi oleh pencarian pada form.</p>
</body></html>
