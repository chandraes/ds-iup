@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>REKAP GAJI KARYAWAN</u></h1>
            <h1>{{$bulan}} {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    {{-- <form action="{{route('print-rekap-gaji')}}" method="get">
        <input type="hidden" name="bulan" value="{{$bulan_angka}}">
        <input type="hidden" name="tahun" value="{{$tahun}}">
        <button type="submit" class="btn btn-success mb-3">Cetak Rekap gaji Karyawan</button>
    </form> --}}
    <div class="row">
        <div class="col-md-12">
            <a href="{{route('rekap')}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div style="font-size:12px">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">NIK</th>
                    <th rowspan="2" class="text-center align-middle">Nama</th>
                    <th rowspan="2" class="text-center align-middle">Jabatan</th>
                    <th rowspan="2" class="text-center align-middle">Gaji Pokok</th>
                    <th colspan="4" class="text-center align-middle">Tunjangan</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-TK (2%)</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-Kesehatan (1%)</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Kotor</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Bersih</th>
                    <th rowspan="2" class="text-center align-middle">Sisa Gaji Dibayar</th>
                    <th rowspan="2" class="text-center align-middle">Rekening</th>
                    <th rowspan="2" class="text-center align-middle">Nama Rekening</th>
                    <th rowspan="2" class="text-center align-middle">Bank</th>
                </tr>
                <tr>
                    <th class="text-center align-middle">Jabatan</th>
                    <th class="text-center align-middle">Keluarga</th>
                    <th class="text-center align-middle">BPJS-TK (4,89%)</th>
                    <th class="text-center align-middle">BPJS-Kesehatan (4%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data->details as $dir)
                <tr>
                    <td class="text-center align-middle">{{$dir->nik}}</td>
                    <td class="text-center align-middle">{{$dir->nama}}</td>
                    <td class="text-center align-middle">{{$dir->jabatan}}</td>
                    <td class="text-center align-middle">{{number_format($dir->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->bpjs_k, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->potongan_bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->potongan_bpjs_kesehatan, 0, ',','.')}}
                    </td>
                    <td class="text-center align-middle">{{number_format($dir->pendapatan_kotor, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->pendapatan_bersih, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($dir->sisa_gaji_dibayar, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{$dir->no_rek}}</td>
                    <td class="text-center align-middle">{{$dir->nama_rek}}</td>
                    <td class="text-center align-middle">{{$dir->bank}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script>
    $(document).ready(function(){
            $('#rekapTable').DataTable({
                "paging": false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        });
</script>
@endpush
