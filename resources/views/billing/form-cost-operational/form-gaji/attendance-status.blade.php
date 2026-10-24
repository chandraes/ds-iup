<section class="payroll-notice" aria-labelledby="payroll-attendance-title">
    <i class="fa fa-calendar-check-o" aria-hidden="true"></i>
    <div class="flex-grow-1"><strong id="payroll-attendance-title">Data absensi & tunjangan BBM</strong>
        @if(!$attendanceEnabled)<p class="mb-0">Integrasi absensi nonaktif. Potongan absensi dan tunjangan BBM dihitung 0.</p>
        @else
            <p class="mb-1">{{ $attendanceReady ? 'Data siap diperiksa untuk penggajian.' : 'Penggajian diblokir sampai data absensi lengkap dan masalah diselesaikan.' }}
                @if($attendancePeriod?->fetched_at) Terakhir ditarik {{ \Carbon\Carbon::parse($attendancePeriod->fetched_at)->locale('id')->translatedFormat('d M Y H:i') }}.@endif
            </p>
            @if($attendancePeriod?->remote_locked_at || $attendancePeriod?->lock_attempted_at)<p class="mb-0">Periode sedang/sudah dikunci. Jika proses sebelumnya terhenti, lanjutkan transaksi gaji yang sama; data absensi tidak dapat ditarik ulang.</p>@endif
            @if($attendanceSnapshot && !empty($attendanceSnapshot['issues']))<ul class="mb-2">@foreach($attendanceSnapshot['issues'] as $issue)<li>{{ $issue }}</li>@endforeach</ul>@endif
            @if(!$attendanceSnapshot)<p class="mb-2">Belum ada snapshot. Gunakan tombol perbarui untuk mengambil data dari aplikasi absensi.</p>@endif
            @unless($attendancePeriod?->remote_locked_at || $attendancePeriod?->lock_attempted_at)
                @if(in_array(auth()->user()->role, ['su','admin'], true))<form id="payroll-attendance-refresh" method="post" action="{{ route('billing.form-cost-operational.gaji.absensi.refresh') }}">@csrf<input type="hidden" name="periode" value="{{ $period->format('Y-m') }}"><button class="btn btn-outline-success mt-2" type="submit"><i class="fa fa-refresh" aria-hidden="true"></i> Perbarui data absensi</button></form>@else<p class="mb-0">Hubungi Admin untuk memperbarui data absensi.</p>@endif
            @endunless
        @endif
    </div>
</section>
