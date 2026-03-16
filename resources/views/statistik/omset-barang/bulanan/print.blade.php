<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Penjualan Barang {{ $tahun }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; padding: 10px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #333; padding: 4px; text-align: right; }
        th { background-color: #f2f2f2; text-align: center; font-weight: bold; }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        @media print {
            @page { size: landscape; margin: 10mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="header">
        <h2>LAPORAN PENJUALAN BARANG BULANAN ({{ strtoupper($modeTampil) }})</h2>
        <p>Tahun: {{ $tahun }} | Perusahaan: {{ $namaUnit }}</p>
    </div>

    @php
        $totalPerBulan = array_fill(1, 12, 0);
        $grandTotalFilter = 0;
    @endphp

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th>Perusahaan</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Satuan</th>
                @for($i = 1; $i <= 12; $i++)
                    @if(empty($bulan) || in_array((string)$i, $bulan))
                        <th>{{ ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$i-1] }}</th>
                    @endif
                @endfor
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($laporan as $row)
                @php $rowTotal = 0; @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-left">{{ $row->unit->nama ?? '-' }}</td>
                    <td class="text-left">{{ $row->kode }}</td>
                    <td class="text-left">{{ $row->barang_nama->nama ?? '-' }}</td>
                    <td class="text-center">{{ $row->satuan->nama ?? '-' }}</td>

                    @for($i = 1; $i <= 12; $i++)
                        @if(empty($bulan) || in_array((string)$i, $bulan))
                            @php
                                $kolom = 'bulan_'.$i;
                                $nilai = $row->$kolom ?? 0;
                                $totalPerBulan[$i] += $nilai;
                                $rowTotal += $nilai;
                            @endphp
                            <td>{{ $nilai ? number_format($nilai, 0, ',', '.') : '-' }}</td>
                        @endif
                    @endfor
                    <td style="font-weight:bold;">{{ number_format($rowTotal, 0, ',', '.') }}</td>
                </tr>
                @php $grandTotalFilter += $rowTotal; @endphp
            @endforeach
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="5" class="text-center">GRAND TOTAL</td>
                @for($i = 1; $i <= 12; $i++)
                    @if(empty($bulan) || in_array((string)$i, $bulan))
                        <td>{{ $totalPerBulan[$i] ? number_format($totalPerBulan[$i], 0, ',', '.') : '-' }}</td>
                    @endif
                @endfor
                <td>{{ number_format($grandTotalFilter, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
