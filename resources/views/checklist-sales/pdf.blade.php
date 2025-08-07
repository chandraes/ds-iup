@extends('layouts.doc-nologo-1')
@section('content')
@if ($offset == 0)
<div class="container-fluid">
    <center>
        <h2>CHECKLIST SALES</h2>
    </center>
</div>
@endif
<div class="container-fluid table-responsive ml-3 text-pdf">
    <div class="row mt-3">
        <table class="table table-hover table-bordered table-pdf text-pdf" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle table-pdf text-pdf">NO</th>
                    <th class="text-center align-middle table-pdf text-pdf">KODE</th>
                    <th class="text-center align-middle table-pdf text-pdf">NAMA</th>
                    <th class="text-center align-middle table-pdf text-pdf">KECAMATAN</th>
                    <th class="text-center align-middle table-pdf text-pdf">SALES<br>AREA</th>
                    @foreach ($months as $item => $month)
                    <th class="text-center align-middle table-pdf text-pdf">{{$item}}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{ ($offset ?? 0) + $loop->iteration }}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">
                        {{$d->full_kode}}
                    </td>
                    <td class="text-start align-middle text-wrap table-pdf text-pdf">
                        {{$d->kode_toko->kode. ' ' .$d->nama}}
                    </td>
                    <td class="text-start align-middle table-pdf text-pdf">
                        {{$d->kecamatan ? str_replace('Kec. ','',$d->kecamatan->nama_wilayah) : ''}}
                    </td>
                    <td class="text-center align-middle table-pdf text-pdf">{{$d->karyawan ? $d->karyawan->nickname : ''}}</td>
                    @foreach ($months as $item => $month)
                    <td class="table-pdf text-pdf">&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
