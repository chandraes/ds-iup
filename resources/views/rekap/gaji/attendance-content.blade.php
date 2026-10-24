@php
    $fuel = $detail['kind'] === 'bbm';
    $labels = ['present'=>'Hadir','half_day'=>'Masuk tanpa pulang','absent'=>'Tidak masuk','excused'=>'Cuti / izin','exempt'=>'Tidak wajib absensi','before_tracking'=>'Sebelum pencatatan','off'=>'Libur jadwal','holiday'=>'Hari libur','pending'=>'Belum terjadwal','invalid'=>'Tidak valid','upcoming'=>'Belum berlangsung','in_progress'=>'Masih berlangsung','pending_leave'=>'Menunggu persetujuan cuti/izin'];
@endphp
<p class="payroll-footnote mt-0">Periode {{ $detail['from'] }}–{{ $detail['to'] }} · Waktu {{ $detail['timezone'] }}</p>
<div class="payroll-commission-formula">
    <span>{{ $fuel ? 'BBM berdasarkan absen masuk, dikurangi keterlambatan setelah toleransi' : 'Potongan berdasarkan pendapatan tetap ÷ 25, maksimum 25% per periode' }}</span>
    <strong>Rp {{ number_format($detail['amount'],0,',','.') }}</strong>
</div>
<div class="payroll-commission-totals">
    <span>{{ $fuel ? 'Tarif BBM per hari' : 'Pendapatan per hari' }}<strong>Rp {{ number_format($detail['daily_rate'],0,',','.') }}</strong></span>
    @if($fuel)
        <span>BBM sebelum pengurangan<strong>Rp {{ number_format($detail['bbm_gross'],0,',','.') }}</strong></span><span>Pengurangan terlambat<strong>Rp {{ number_format($detail['bbm_reduction'],0,',','.') }}</strong></span>
    @else
        <span>Potongan sebelum batas<strong>Rp {{ number_format($detail['raw'],0,',','.') }}</strong></span><span>Batas maksimum<strong>Rp {{ number_format($detail['cap'],0,',','.') }}</strong></span>
    @endif
</div>
<div class="table-responsive"><table class="table table-bordered table-hover payroll-commission-table"><thead class="table-success"><tr><th scope="col">Tanggal</th><th scope="col">Jadwal</th><th scope="col">Status</th><th scope="col">Masuk</th><th scope="col">Telat setelah toleransi</th>@unless($fuel)<th scope="col">Pulang awal</th>@endunless<th scope="col">Menit efektif</th>@if($fuel)<th scope="col">BBM awal</th><th scope="col">Pengurangan</th><th scope="col">BBM diterima</th>@else<th scope="col">Potongan</th>@endif</tr></thead><tbody>
@foreach($detail['days'] as $day)<tr><th scope="row">{{ $day['date'] }}</th><td>{{ ($day['schedule_mode'] ?? 'weekly') === 'shift' ? ($day['schedule_name'] ?? 'Shift') : 'Mingguan' }}</td><td>{{ $labels[$day['status']] ?? $day['status'] }}</td><td>{{ $day['check_in_at'] ? \Carbon\Carbon::parse($day['check_in_at'])->timezone($detail['timezone'])->format('d/m H:i') : '—' }}</td><td>{{ $fuel ? $day['check_in_late_minutes'] : $day['late_minutes'] }} m</td>@unless($fuel)<td>{{ $day['early_minutes'] }} m</td>@endunless<td>{{ $day['effective_minutes'] }} m</td>@if($fuel)<td class="text-end">{{ number_format($day['bbm_gross'],0,',','.') }}</td><td class="text-end">{{ number_format($day['deduction'],0,',','.') }}</td><td class="text-end">{{ number_format($day['bbm_received'],0,',','.') }}</td>@else<td class="text-end">{{ number_format($day['deduction'],0,',','.') }}</td>@endif</tr>@endforeach
</tbody></table></div>
<p class="payroll-footnote">{{ $fuel ? 'BBM hanya untuk hari kerja dengan absen masuk. Tidak masuk, cuti/izin dan libur bernilai 0. Tidak absen pulang dan pulang cepat tidak mengurangi BBM. Pengurangan terlambat dibatasi tarif BBM harian, tanpa batas bulanan 25%.' : 'Tidak masuk dipotong satu hari, masuk tanpa pulang pada pola wajib pulang dipotong setengah hari. Telat dan pulang awal dihitung proporsional terhadap durasi jadwal dikurangi istirahat, tanpa potongan ganda.' }}</p>
<p class="payroll-footnote">{{ $stored ? 'Rincian dan tarif ini disimpan saat gaji diproses. Perubahan data pegawai setelahnya tidak mengubah rekap ini.' : 'Pratinjau menggunakan tarif pegawai saat ini untuk seluruh periode. Perbarui absensi setelah koreksi HR sebelum memproses gaji.' }} @if($detail['provisional']) Angka masih sementara; penggajian belum dapat dieksekusi.@endif</p>
