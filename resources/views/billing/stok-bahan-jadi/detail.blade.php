@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>REKAP PRODUCT JADI</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td>
                        <a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30"> Billing</a>
                    </td>
                    <td>
                        <a href="{{route('billing.stok-bahan-jadi')}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30"> Kembali</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover" id="data-table">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Keterangan</th>
                    <th class="text-center align-middle">Jumlah</th>'
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                    <tr>
                        <td class="text-center align-middle"></td>
                        <td class="text-center align-middle">
                            {{$d->tanggal}}
                        </td>
                        <td class="text-start align-middle">
                            @if ($d->rencanaProduksi)
                            Produksi {{$d->rencanaProduksi->kode_produksi}}
                            @elseif ($d->invoiceJual)
                            Penjualan {{$d->invoiceJual->invoice}}
                            @endif
                        </td>
                        <td class="text-start align-middle">
                            @if ($d->rencanaProduksi)
                            <ul>
                                <li>Kemasan: {{$d->rencanaProduksi->produksi_detail->sum('total_kemasan')}}</li>
                                <li>Packaging: {{$d->rencanaProduksi->real_packaging}}</li>
                            </ul>
                            @elseif ($d->invoiceJual)
                            {{$d->invoiceJual->detail->where('product_jadi_id', $d->product_jadi_id)->first()->jumlah}}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<link rel="stylesheet" href="{{asset('assets/js/flatpickr/flatpickr.min.css')}}">
<script src="{{asset('assets/js/flatpickr/flatpickr.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    function detailInvoice(data)
    {
        document.getElementById('detailModalTitle').innerHTML = 'INVOICE : '+data.invoice;
    }
    $(document).ready(function() {
        var table = $('#data-table').DataTable({
            "paging": false,
            "searching": true,
            "scrollCollapse": true,
            "scrollY": "500px",

        });

        table.on( 'order.dt search.dt', function () {
            table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                cell.innerHTML = i+1;
            } );
        } ).draw();
    });
</script>
@endpush
