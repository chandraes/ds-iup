<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Omset Tahunan {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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

        @media print {
            @page {
                size: landscape;
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

    @php
        // Persiapan array untuk nama bulan dan variabel total
        $namaBulan = [1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Agu', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'];
        $grandTotalSetahun = 0;
        $totalPerBulan = array_fill(1, 12, 0); // Array berisi angka 0 untuk bulan 1 s/d 12
    @endphp

    <table>
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="3%">Kode</th>
                <th width="2%">Kode Toko</th>
                <th>Nama Toko</th>
                <th>Kab/Kota</th>
                <th>Kecamatan</th>
                <th>Sales Area</th>

                {{-- Tampilkan Header Bulan secara Dinamis --}}
                @for($i = 1; $i <= 12; $i++)
                    @if(empty($bulan) || in_array((string)$i, $bulan))
                        <th>{{ $namaBulan[$i] }}</th>
                    @endif
                @endfor

                <th width="8%">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalFilter = 0; // Ubah nama variabel agar lebih relevan
            @endphp

            @foreach($laporan as $row)
                @php
                    $rowTotal = 0; // Siapkan penampung total per baris yang difilter
                @endphp
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td class="text-left">{{ $row->full_kode }}</td>
                    <td class="text-left">{{ $row->kode_toko->kode ?? '-' }}</td>
                    <td class="text-left">{{ $row->nama }}</td>
                    <td class="text-left">{{ str_replace(['Kab. ', 'Kota '], '', $row->kabupaten_kota?->nama_wilayah) }}</td>
                    <td class="text-left">{{ str_replace(['Kec. '], '', $row->kecamatan?->nama_wilayah) }}</td>
                    <td class="text-left">{{ $row->karyawan?->nama }}</td>

                    {{-- Tampilkan Data Bulan secara Dinamis & Hitung Totalnya --}}
                    @for($i = 1; $i <= 12; $i++)
                        @if(empty($bulan) || in_array((string)$i, $bulan))
                            @php
                                $kolom = 'bulan_' . $i;
                                $nilaiBulan = $row->$kolom ?? 0;

                                $totalPerBulan[$i] += $nilaiBulan; // Tambahkan ke Grand Total per Bulan (bawah)
                                $rowTotal += $nilaiBulan;          // Tambahkan ke Total Baris ini (kanan)
                            @endphp
                            <td>{{ $nilaiBulan ? number_format($nilaiBulan, 0, ',', '.') : '-' }}</td>
                        @endif
                    @endfor

                    {{-- Tampilkan Total Baris yang sudah dinamis --}}
                    <td class="fw-bold">{{ number_format($rowTotal, 0, ',', '.') }}</td>
                </tr>

                @php
                    // Tambahkan total baris ini ke Grand Total Sudut Kanan Bawah
                    $grandTotalFilter += $rowTotal;
                @endphp
            @endforeach

            {{-- Baris Grand Total di Bawah --}}
            <tr style="background-color: #eee; font-weight: bold;">
                <td colspan="7" class="text-center">GRAND TOTAL</td>

                {{-- Tampilkan Grand Total per Bulan secara Dinamis --}}
                @for($i = 1; $i <= 12; $i++)
                    @if(empty($bulan) || in_array((string)$i, $bulan))
                        <td>{{ $totalPerBulan[$i] ? number_format($totalPerBulan[$i], 0, ',', '.') : '-' }}</td>
                    @endif
                @endfor

                {{-- Tampilkan Grand Total Keseluruhan (Sudut Kanan Bawah) --}}
                <td>{{ number_format($grandTotalFilter, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
