<!DOCTYPE html>
<html>

<head>
    <title>Saran Order</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 10pt;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .header {
            margin-bottom: 20px;
            text-align: center;
        }

        .badge {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Laporan Saran Order</h2>
        <h2>{{Str::upper($perusahaan->nama)}}</h2>
        <p>Tanggal Cetak: {{ date('d-m-Y H:i') }} | Pengali Stok: {{ $multiplier }}x</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Merk</th>
                <th>PPN</th>
                <th>NON PPN</th>
                <th>Satuan</th>
                <th>Stok</th>
                <th>Avg Jual</th>
                <th>Saran ({{$multiplier}}x)</th>
                <th>Order Qty</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $key => $item)
            @php
            // Hitung manual di view karena ini object hasil get(), bukan datatables
            $saran = $item->avg_sales * $multiplier;
            $order = $saran - $item->stok_ready;
            @endphp
            <tr>
                <td class="text-center">{{ $key + 1 }}</td>
                <td>{{ $item->kode }}</td>
                <td>{{ $item->barang_nama->nama ?? '-' }}</td>
                <td>{{ $item->merk }}</td>
                <td class="text-center">
                    @if($item->jenis == 1)
                    <span class="badge">V</span>
                    @endif
                </td>
                <td class="text-center">
                    @if($item->jenis == 2)
                    <span class="badge">V</span>
                    @endif
                </td>
                <td class="text-center">{{ $item->satuan->nama ?? '-' }}</td>
                <td class="text-center">{{ number_format($item->stok_ready, 0, ',', '.') }}</td>
                <td class="text-center">{{ number_format($item->avg_sales, 1, ',', '.') }}</td>
                <td class="text-center">{{ number_format($saran, 0, ',', '.') }}</td>
                <td class="text-center"><strong>{{ number_format($order, 0, ',', '.') }}</strong></td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data saran order yang ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
