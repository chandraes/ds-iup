<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px; /* Ukuran font diperkecil agar muat 12 kolom bulan */
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .header p {
            margin: 0;
            font-size: 10px;
            color: #666;
        }
        .summary-box {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #999;
        }
        th, td {
            padding: 5px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #eaeaea;
            font-weight: bold;
        }
        .text-left {
            text-align: left;
        }
        /* Status Colors */
        .status-visited {
            background-color: #d4edda;
            color: #155724;
            font-weight: bold;
        }
        .status-not-visited {
            background-color: #f8d7da;
            color: #721c24;
            font-weight: bold;
        }
        .status-empty {
            background-color: #ffffff;
            color: #ccc;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>{{ $title }}</h2>
        <p>Dicetak pada: {{ $date_printed }}</p>
    </div>

    <div class="summary-box">
        <strong>Ringkasan Tahun {{ $tahun }}:</strong><br>
        Total Toko/Konsumen: {{ number_format($total_konsumen, 0, ',', '.') }} |
        Total Dikunjungi: {{ number_format($total_visited, 0, ',', '.') }} |
        Tidak Dikunjungi: {{ number_format($total_not_visited, 0, ',', '.') }} |
        Persentase Kunjungan Tahunan: {{ $persentase_tahun }}%
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 3%;">No</th>
                <th rowspan="2" style="width: 15%;">Nama Toko</th>
                <th rowspan="2" style="width: 12%;">Kecamatan</th>
                <th rowspan="2" style="width: 12%;">Sales Area</th>
                <th colspan="12">Bulan</th>
            </tr>
            <tr>
                <th>Jan</th><th>Feb</th><th>Mar</th><th>Apr</th><th>Mei</th><th>Jun</th>
                <th>Jul</th><th>Agu</th><th>Sep</th><th>Okt</th><th>Nov</th><th>Des</th>
            </tr>
        </thead>
        <tbody>
            @forelse($konsumens as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">
                        {{ $row->kode_toko ? $row->kode_toko->kode : '' }} {{ $row->nama }}
                    </td>
                    <td class="text-left">
                        {{ $row->kecamatan ? str_replace('Kec. ', '', $row->kecamatan->nama_wilayah) : '-' }}
                    </td>
                    <td class="text-left">
                        {{ $row->karyawan ? $row->karyawan->nickname : '-' }}
                    </td>

                    @for ($m = 1; $m <= 12; $m++)
                        @php
                            $checklist = $row->checklists->firstWhere('bulan', $m);
                            $status = $checklist ? $checklist->status : 'empty';
                        @endphp

                        @if ($status === 'visited')
                            <td class="status-visited">V</td>
                        @elseif ($status === 'not_visited')
                            <td class="status-not-visited">X</td>
                        @else
                            <td class="status-empty">-</td>
                        @endif
                    @endfor
                </tr>
            @empty
                <tr>
                    <td colspan="16">Data konsumen tidak ditemukan untuk filter ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="font-size: 10px; margin-top: 10px;">
        <strong>Keterangan Status:</strong><br>
        <span style="display:inline-block; width:15px; background-color:#d4edda; border:1px solid #999; text-align:center;">V</span> : Dikunjungi (Visited)<br>
        <span style="display:inline-block; width:15px; background-color:#f8d7da; border:1px solid #999; text-align:center;">X</span> : Tidak Dikunjungi (Not Visited)<br>
        <span style="display:inline-block; width:15px; background-color:#ffffff; border:1px solid #999; text-align:center;">-</span> : Belum ada data (Empty)
    </div>

</body>
</html>
