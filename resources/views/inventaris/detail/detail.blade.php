@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>DETAIL INVENTARIS</u></h1>
            <h1><u>{{$inventaris->kategori->nama}} - {{$inventaris->nama}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('inventaris.index')}}"><img src="{{asset('images/inventaris.svg')}}" alt="dokumen" width="30">
                            INVENTARIS</a></td>
                    <td><a href="{{url()->previous()}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30">
                        KEMBALI</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('inventaris.detail.aksi-modal')
<div class="container table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">Jumlah</th>
                    <th class="text-center align-middle">Harga Satuan</th>
                    <th class="text-center align-middle">Total Harga</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <div>
                <tbody>
                    @foreach ($data as $d)
                    <tr>
                        <td class="text-center align-middle">{{$d->tanggal}}</td>
                        <td class="text-start align-middle">{{$d->uraian}}</td>
                        <td class="text-center align-middle">{{$d->jumlah}}</td>
                        <td class="text-end align-middle">{{$d->nf_harga_satuan}}</td>
                        <td class="text-end align-middle">{{$d->nf_total}}</td>
                        <td class="text-center align-middle">
                            @if ($d->jenis == 1 && $d->pengurangan < $d->jumlah)
                            <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#aksiModal" onclick="aksiFun({{$d}})"><i class="fa fa-exclamation-triangle"></i> Aksi</button>
                            @endif
                        </td>
                    </tr>

                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th class="text-end align-middle" colspan="2">Grand Total : </th>
                        <th class="text-center align-middle">{{number_format($data->sum('jumlah'), '0', ',','.')}}</th>
                        <th class="text-end align-middle">{{number_format($data->sum('harga_satuan'), '0', ',','.')}}</th>
                        <th class="text-end align-middle">{{number_format($data->sum('total'), '0', ',','.')}}</th>
                        <th></th>
                    </tr>

                </tfoot>
            </div>
        </table>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>

    function aksiFun(data) {

        $('#jumlah_beli').val(data.jumlah);
        $('#harga_satuan_beli').val(data.nf_harga_satuan);
        $('#total_harga_beli').val(data.nf_total);
        $('#pengurangan').val(data.pengurangan);

        document.getElementById('aksiForm').action = '/inventaris/' + '{{ $kategori->id }}' + '/' + '{{ $inventaris->id }}' + '/' + data.id;
    }


</script>
@endpush
