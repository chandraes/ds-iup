@php
    $columns = ['gaji_pokok','tunjangan_jabatan','tunjangan_keluarga','bpjs_tk','bpjs_k','potongan_bpjs_tk','potongan_bpjs_kesehatan','komisi','tunjangan_bbm','pendapatan_kotor','pendapatan_bersih','potongan_kasbon','sisa_gaji_dibayar'];
@endphp
<section class="payroll-sheet">
    <div class="payroll-sheet-heading"><div><h2>Rincian gaji pegawai</h2><p>Cari pegawai berdasarkan nama, NIK, atau jabatan. Gulir tabel ke samping untuk melihat seluruh komponen.</p></div><span class="payroll-tag">Nominal dalam rupiah</span></div>
    <div class="table-responsive"><table id="rekapTable" class="table table-bordered table-hover payroll-table {{ $showAccounts ? 'payroll-with-accounts' : '' }}" style="width:100%">
        <thead class="table-success"><tr>
            <th rowspan="2" scope="col">NIK</th><th rowspan="2" scope="col">Nama pegawai</th><th rowspan="2" scope="col">Jabatan</th><th rowspan="2" scope="col">Gaji pokok</th>
            <th colspan="4" scope="colgroup" class="text-center">Tunjangan</th>
            <th rowspan="2" scope="col">Potongan BPJS-TK<small>2%</small></th><th rowspan="2" scope="col">Potongan BPJS-Kesehatan<small>1%</small></th>
            <th rowspan="2" scope="col">Komisi</th><th rowspan="2" scope="col">Tunjangan BBM</th><th rowspan="2" scope="col">Total Pendapatan Kotor</th><th rowspan="2" scope="col">Total Pendapatan Bersih</th><th rowspan="2" scope="col">Potongan Kasbon</th><th rowspan="2" scope="col" class="payroll-payable">Sisa Gaji Dibayar</th>
            @if($showAccounts)<th rowspan="2" scope="col">Rekening</th><th rowspan="2" scope="col">Nama rekening</th><th rowspan="2" scope="col">Bank</th>@endif
        </tr><tr><th scope="col">Jabatan</th><th scope="col">Keluarga</th><th scope="col">BPJS-TK<small>4,89%</small></th><th scope="col">BPJS-Kesehatan<small>4%</small></th></tr></thead>
        <tbody>@foreach($rows as $row)<tr><td>{{ $row['nik'] }}</td><td class="payroll-person">{{ $row['nama'] }}</td><td><span class="payroll-position">{{ $row['jabatan'] }}</span></td>
            @foreach($columns as $column)<td class="payroll-money {{ $column === 'sisa_gaji_dibayar' ? 'payroll-payable' : '' }}" data-order="{{ $row[$column] ?? 0 }}">@if($column === 'komisi' && !empty($row['commission_url']))<button type="button" class="payroll-commission-link" data-bs-toggle="modal" data-bs-target="#payroll-commission" data-url="{{ $row['commission_url'] }}" aria-label="Lihat rincian komisi {{ $row['nama'] }}">{{ number_format($row[$column] ?? 0,0,',','.') }}</button>@else{{ number_format($row[$column] ?? 0,0,',','.') }}@endif</td>@endforeach
            @if($showAccounts)<td>{{ $row['no_rek'] }}</td><td>{{ $row['nama_rek'] }}</td><td>{{ $row['bank'] }}</td>@endif
        </tr>@endforeach</tbody>
        <tfoot><tr><th colspan="3" scope="row">Total seluruh pegawai</th>@foreach($columns as $column)<td class="payroll-money {{ $column === 'sisa_gaji_dibayar' ? 'payroll-payable' : '' }}">{{ number_format($rows->sum($column),0,',','.') }}</td>@endforeach @if($showAccounts)<td colspan="3"></td>@endif</tr></tfoot>
    </table></div>
    <p class="payroll-footnote">Ringkasan dan total mencakup seluruh pegawai dalam periode ini, termasuk baris yang tidak terlihat saat pencarian.</p>
</section>

@include('rekap.gaji.commission-detail')
