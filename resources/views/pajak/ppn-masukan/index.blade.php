@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PPN MASUKAN</u></h1>
            {{-- <h1>{{$stringBulanNow}} {{$tahun}}</h1> --}}
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/pajak.svg')}}" alt="dokumen" width="30">
                            PAJAK</a></td>
                </tr>
            </table>
        </div>
        {{-- <form action="{{route('rekap.invoice-belanja')}}" method="get" class="col-md-6">
            <div class="row mt-2">
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="bulan" id="bulan">
                        <option value="1" {{$bulan=='01' ? 'selected' : '' }}>Januari</option>
                        <option value="2" {{$bulan=='02' ? 'selected' : '' }}>Februari</option>
                        <option value="3" {{$bulan=='03' ? 'selected' : '' }}>Maret</option>
                        <option value="4" {{$bulan=='04' ? 'selected' : '' }}>April</option>
                        <option value="5" {{$bulan=='05' ? 'selected' : '' }}>Mei</option>
                        <option value="6" {{$bulan=='06' ? 'selected' : '' }}>Juni</option>
                        <option value="7" {{$bulan=='07' ? 'selected' : '' }}>Juli</option>
                        <option value="8" {{$bulan=='08' ? 'selected' : '' }}>Agustus</option>
                        <option value="9" {{$bulan=='09' ? 'selected' : '' }}>September</option>
                        <option value="10" {{$bulan=='10' ? 'selected' : '' }}>Oktober</option>
                        <option value="11" {{$bulan=='11' ? 'selected' : '' }}>November</option>
                        <option value="12" {{$bulan=='12' ? 'selected' : '' }}>Desember</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="tahun" id="tahun">
                        @foreach ($dataTahun as $d)
                        <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                </div>
            </div>
        </form> --}}
    </div>
</div>
<div class="container table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal Input</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Supplier</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">Tanggal Bayar</th>
                    <th class="text-center align-middle">Sebelum<br>Terbit<br>Faktur</th>
                    <th class="text-center align-middle">Setelah<br>Terbit<br>Faktur</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">
                      {{$d->invoiceBelanja->tanggal}}
                    </td>
                    <td class="text-center align-middle">
                        @if ($d->invoiceBelanja)
                        <a href="{{route('billing.invoice-supplier.detail', ['invoice' => $d])}}">
                            {{$d->invoiceBelanja->kode}}
                        </a>
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        {{$d->invoiceBelanja->supplier->nama}}
                      </td>
                    <td class="text-start align-middle">
                        {{$d->uraian}}
                    </td>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-end align-middle">
                        {{$d->nf_nominal}}
                    </td>
                    <td class="text-end align-middle">
                        0
                    </td>
                    <td class="text-end align-middle">

                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="5">Saldo PPn Masukan</th>
                    <th class="text-end align-middle">{{number_format($saldo, 0, ',','.')}}</th>
                    <th class="text-end align-middle">0</th>
                    <th></th>
                </tr>
            </tfoot>
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

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": true,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "400px",
            // default order column 1
            "order": [
                [1, 'asc']
            ],
            // "rowCallback": function(row, data, index) {
            //     // Update the row number
            //     $('td:eq(0)', row).html(index + 1);
            // }

        });

    });


</script>
@endpush
