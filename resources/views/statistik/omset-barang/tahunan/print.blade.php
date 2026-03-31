<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Omset Tahunan Barang</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; margin: 0; padding: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: right; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        @media print {
            @page { size: landscape; margin: 10mm; }
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 10px; text-align: right;">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer;">Cetak Laporan</button>
    </div>

    <div class="header">
        <h2>LAPORAN OMSET TAHUNAN PER BARANG</h2>
        <p>Tahun: {{ $tahunAwal }} s/d {{ $tahunAkhir }}</p>
        <p>Perusahaan: {{ $namaUnit }} | Mode Tampil: {{ $modeTampil === 'qty' ? 'QTY (Jumlah Barang)' : 'Nominal Uang (Rp)' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th>Perusahaan</th>
                <th>Kelompok</th>
                <th>Kode</th>
                <th>Merk</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                @foreach($years as $y)
                    <th>{{ $y }}</th>
                @endforeach
                <th width="10%">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalFilter = 0;
                $totalPerTahun = [];
                foreach($years as $y) {
                    $totalPerTahun[$y] = 0;
                }
            @endphp

            @foreach($laporan as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $row->unit?->nama }}</td>
                    <td class="text-left">{{ $row->kategori?->nama }}</td>
                    <td class="text-center">{{ $row->kode }}</td>
                    <td class="text-left">{{ $row->merk }}</td>
                    <td class="text-left">{{ $row->barang_nama->nama ?? '-' }}</td>
                    <td class="text-center">{{ $row->satuan->nama ?? '-' }}</td>

                    @php
                        $rowTotal = 0;
                    @endphp

                    @for($i = 1; $i <= count($years); $i++)
                        @php
                            $colName = "tahun_" . $i;
                            $nilaiTahun = $row->$colName ?? 0;
                            $totalPerTahun[$years[$i-1]] += $nilaiTahun;
                            $rowTotal += $nilaiTahun;
                        @endphp
                        <td>{{ $nilaiTahun ? number_format($nilaiTahun, 0, ',', '.') : '-' }}</td>
                    @endfor

                    <td class="fw-bold">{{ number_format($rowTotal, 0, ',', '.') }}</td>
                </tr>

                @php
                    $grandTotalFilter += $rowTotal;
                @endphp
            @endforeach

            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="7" class="text-center">GRAND TOTAL</td>
                @foreach($years as $y)
                    <td>{{ $totalPerTahun[$y] ? number_format($totalPerTahun[$y], 0, ',', '.') : '-' }}</td>
                @endforeach
                <td>{{ number_format($grandTotalFilter, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
