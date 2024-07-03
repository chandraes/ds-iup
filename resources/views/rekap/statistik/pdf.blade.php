@extends('layouts.doc-nologo-1')
@section('content')
<div class="container-fluid">
    <center>
        <h1>{{$customer->nama}}</h1>
    </center>
</div>
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row text-center">
        <h1>Statistik {{$nama_bulan}} {{$tahun}}</h1>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover text-pdf table-pdf" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf" style="width: 10%">Tanggal</th>
                    <th class="text-center align-middle table-pdf text-pdf">Berat (Kg)</th>
                    <th class="text-center align-middle table-pdf text-pdf">Belanja</th>
                    <th class="text-center align-middle table-pdf text-pdf">Tagihan</th>
                    <th class="text-center align-middle table-pdf text-pdf">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($statistics_monthly as $day => $data)
                    <tr>
                        <td class="text-center align-middle table-pdf text-pdf">{{ $day }}</td>
                        <td class="text-center align-middle table-pdf text-pdf">{{ number_format($data['total_berat'], 0, ',','.') }}</td>
                        <td class="text-center align-middle table-pdf text-pdf">{{ number_format($data['total_bayar'], 0, ',','.') }}</td>
                        <td class="text-center align-middle table-pdf text-pdf">{{ number_format($data['total_tagihan'], 0,',','.') }}</td>
                        <td class="text-center align-middle table-pdf text-pdf">{{ number_format($data['total_profit'], 0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf">Grand Total</th>
                    <th class="text-center align-middle table-pdf text-pdf">{{ number_format($grand_total_berat, 0, ',','.') }}</th>
                    <th class="text-center align-middle table-pdf text-pdf">{{ number_format($grand_total_bayar, 0, ',','.') }}</th>
                    <th class="text-center align-middle table-pdf text-pdf">{{ number_format($grand_total_tagihan, 0, ',','.') }}</th>
                    <th class="text-center align-middle table-pdf text-pdf">{{ number_format($grand_total_profit, 0, ',','.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="row mt-3 text-center">
        <h1>Statistik {{$tahun}}</h1>
        <table class="table table-hover table-bordered text-pdf table-pdf" id="rekapTahunan">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf" style="width: 10%">Bulan</th>
                    @for ($i = 1; $i <= 6; $i++)
                        <th class="text-center align-middle text-pdf table-pdf">{{$i}}</th> <!-- Month name -->
                    @endfor
                </tr>
            </thead>
            <tbody>
                <!-- Your table rows here -->
                @foreach (['Berat', 'Bayar', 'Tagihan', 'Profit'] as $statistic)
                    <tr>
                        <td class="text-center align-middle text-pdf table-pdf">{{ $statistic }}</td>
                        @for ($i = 1; $i <= 6; $i++)
                            <td class="text-center align-middle text-pdf table-pdf">{{ number_format($statistics_yearly[$i]['total_' . strtolower($statistic)], 0,',','.') }}</td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="table table-hover table-bordered text-pdf table-pdf mt-2" id="rekapTahunan">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle text-pdf table-pdf" style="width: 10%">Bulan</th>
                    @for ($i = 7; $i <= 12; $i++)
                        <th class="text-center align-middle text-pdf table-pdf">{{$i}}</th> <!-- Month name -->
                    @endfor
                    <th class="text-center align-middle text-pdf table-pdf" style="width: 13%">Total</th>
                </tr>
            </thead>
            <tbody>
                <!-- Your table rows here -->
                @foreach (['Berat', 'Bayar', 'Tagihan', 'Profit'] as $statistic)
                    <tr>
                        <td class="text-center align-middle text-pdf table-pdf">{{ $statistic }}</td>
                        @for ($i = 7; $i <= 12; $i++)
                            <td class="text-center align-middle text-pdf table-pdf">{{ number_format($statistics_yearly[$i]['total_' . strtolower($statistic)], 0,',','.') }}</td>
                        @endfor
                        <td class="text-center align-middle text-pdf table-pdf">{{ number_format(${'yearly_total_' . strtolower($statistic)}, 0,',','.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
