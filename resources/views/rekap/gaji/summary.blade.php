<div class="payroll-stats">
    <div class="payroll-stat"><span>Pegawai dalam rekap</span><strong>{{ $rows->count() }} <small>pegawai</small></strong><p>Seluruh pegawai pada periode ini</p></div>
    <div class="payroll-stat"><span>Total pendapatan bersih</span><strong>Rp {{ number_format($rows->sum('pendapatan_bersih'),0,',','.') }}</strong><p>Sebelum potongan kasbon</p></div>
    <div class="payroll-stat"><span>Total potongan kasbon</span><strong>Rp {{ number_format($rows->sum('potongan_kasbon'),0,',','.') }}</strong><p>Potong gaji dan angsuran cicilan</p></div>
    <div class="payroll-stat is-highlight"><span>Total gaji dibayar</span><strong>Rp {{ number_format($rows->sum('sisa_gaji_dibayar'),0,',','.') }}</strong><p>Setelah seluruh potongan</p></div>
</div>
