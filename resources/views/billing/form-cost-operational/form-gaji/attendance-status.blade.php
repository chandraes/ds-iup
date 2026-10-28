<section class="payroll-notice" aria-labelledby="payroll-attendance-title">
    <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
    <div class="flex-grow-1"><strong id="payroll-attendance-title">Data absensi & tunjangan BBM</strong>
        @if(!$attendanceEnabled)<p class="mb-0">Integrasi absensi nonaktif. Potongan absensi dan tunjangan BBM dihitung 0.</p>
        @else
            @php
                $syncLocked = $attendancePeriod?->remote_locked_at || $attendancePeriod?->lock_attempted_at || $attendancePeriod?->sealed_at;
                $syncFailed = !empty($attendancePeriod?->sync_last_error);
                $syncStale = $attendancePeriod?->fetched_at && \Carbon\Carbon::parse($attendancePeriod->fetched_at)->lt(now()->subMinutes(30));
                $syncLabel = $syncLocked ? 'Terkunci' : ($syncFailed ? 'Gagal diperbarui' : (!$attendanceSnapshot ? 'Belum tersedia' : ($syncStale ? 'Perlu diperbarui' : ($attendanceReady ? 'Terbaru' : 'Data sementara'))));
            @endphp
            <div class="d-flex flex-wrap align-items-center gap-2 my-2"><span class="badge {{ $syncLocked ? 'bg-secondary' : ($syncFailed ? 'bg-danger' : ($syncStale || !$attendanceReady ? 'bg-warning text-dark' : 'bg-success')) }}">{{ $syncLabel }}</span><span class="small text-muted">{{ config('attendance.payroll_auto_sync') ? 'Otomatis setiap 15 menit + manual' : 'Pembaruan manual; otomatis belum diaktifkan' }}</span></div>
            @if($syncFailed)<p class="text-danger mb-1">{{ $attendancePeriod->sync_last_error }} Snapshot terakhir tetap disimpan. @if(config('attendance.payroll_auto_sync') && $attendancePeriod?->next_sync_at && !$syncLocked) Percobaan berikutnya {{ \Carbon\Carbon::parse($attendancePeriod->next_sync_at)->format('d-m-Y H:i') }}. @endif</p>@endif
            @if($syncStale && !$syncLocked)<p class="text-warning-emphasis mb-1">Snapshot lebih dari 30 menit belum diperbarui. Periksa kembali sebelum memproses gaji.</p>@endif
            @if($attendancePeriod?->sync_attempted_at)<p class="small text-muted mb-1">Percobaan terakhir {{ \Carbon\Carbon::parse($attendancePeriod->sync_attempted_at)->format('d-m-Y H:i') }} · {{ $attendancePeriod->sync_source === 'automatic' ? 'Otomatis' : 'Manual' }}@if($attendancePeriod->sync_duration_ms !== null) · {{ number_format($attendancePeriod->sync_duration_ms / 1000, 2, ',', '.') }} detik @endif. Muat ulang halaman untuk melihat pembaruan background.</p>@endif
            <p class="mb-1">{{ $attendanceReady ? 'Data siap diperiksa untuk penggajian.' : 'Penggajian diblokir sampai data absensi lengkap dan masalah diselesaikan.' }}
                @if($attendancePeriod?->fetched_at) Terakhir ditarik {{ \Carbon\Carbon::parse($attendancePeriod->fetched_at)->locale('id')->translatedFormat('d M Y H:i') }}.@endif
            </p>
            @if($syncLocked)<p class="mb-0">Periode sedang/sudah dikunci. Jika proses sebelumnya terhenti, lanjutkan transaksi gaji yang sama; data absensi tidak dapat ditarik ulang.</p>@endif
            @if($attendanceSnapshot && !empty($attendanceSnapshot['issues']))<ul class="mb-2">@foreach($attendanceSnapshot['issues'] as $issue)<li>{{ $issue }}</li>@endforeach</ul>@endif
            @if(!$attendanceSnapshot)<p class="mb-2">Belum ada snapshot. Gunakan tombol perbarui untuk mengambil data dari aplikasi absensi.</p>@endif
            @unless($syncLocked)
                @if(in_array(auth()->user()->role, ['su','admin'], true))<form id="payroll-attendance-refresh" method="post" action="{{ route('billing.form-cost-operational.gaji.absensi.refresh') }}">@csrf<input type="hidden" name="periode" value="{{ $period->format('Y-m') }}"><button class="btn btn-outline-success mt-2" type="submit"><i class="fa fa-refresh" aria-hidden="true"></i> Perbarui data absensi</button></form>@else<p class="mb-0">Hubungi Admin untuk memperbarui data absensi.</p>@endif
            @endunless
        @endif
    </div>
</section>
