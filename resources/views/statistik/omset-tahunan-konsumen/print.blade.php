<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Omset Tahunan {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Font kecil agar muat banyak */
            margin: 0;
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 { margin: 0; }
        .header p { margin: 5px 0; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #333;
            padding: 4px;
            text-align: right;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .text-left { text-align: left; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        /* Styling khusus saat diprint */
        @media print {
            @page {
                size: landscape; /* Otomatis Landscape */
                margin: 10mm;
            }
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ Cetak / Simpan PDF</button>
        <span style="font-size: 12px; color: red;">* Jika kolom terpotong, atur Scale di dialog print menjadi 70-80%</span>
    </div>

    <div class="header">
        <h2>LAPORAN OMSET TAHUNAN KONSUMEN</h2>
        <p>Tahun: {{ $tahun }} | Perusahaan: {{ $namaUnit }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="3%">Kode</th>
                <th width="2%">Kode Toko</th>
                <th width="15%">Nama Toko</th>
                <th>Jan</th> <th>Feb</th> <th>Mar</th> <th>Apr</th>
                <th>Mei</th> <th>Jun</th> <th>Jul</th> <th>Agu</th>
                <th>Sep</th> <th>Okt</th> <th>Nov</th> <th>Des</th>
                <th width="8%">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($laporan as $row)
            @php $grandTotal += $row->total_setahun; @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-left">{{ $row->full_kode }}</td>
                <td class="text-left">{{ $row->kode_toko->kode }}</td>
                <td class="text-left">{{ $row->nama }}</td>
                <td>{{ $row->bulan_1 ? number_format($row->bulan_1, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_2 ? number_format($row->bulan_2, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_3 ? number_format($row->bulan_3, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_4 ? number_format($row->bulan_4, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_5 ? number_format($row->bulan_5, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_6 ? number_format($row->bulan_6, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_7 ? number_format($row->bulan_7, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_8 ? number_format($row->bulan_8, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_9 ? number_format($row->bulan_9, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_10 ? number_format($row->bulan_10, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_11 ? number_format($row->bulan_11, 0, ',', '.') : '-' }}</td>
                <td>{{ $row->bulan_12 ? number_format($row->bulan_12, 0, ',', '.') : '-' }}</td>
                <td class="fw-bold">{{ number_format($row->total_setahun, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="16" class="text-center">GRAND TOTAL</td>
                <td>{{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
