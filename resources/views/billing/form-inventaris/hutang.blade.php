@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>HUTANG INVENTARIS</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen"
                                width="30"> Billing</a></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover" id="data-table">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">Jatuh Tempo</th>
                    <th class="text-center align-middle">Jumlah</th>
                    <th class="text-center align-middle">Harga Satuan</th>
                    <th class="text-center align-middle">PPN</th>
                    <th class="text-center align-middle">Total</th>
                    <th class="text-center align-middle">DP</th>
                    <th class="text-center align-middle">Sisa</th>
                    <th class="text-center align-middle">Act</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                    <tr>

                        <td class="text-center align-middle">
                            {{$d->tanggal}}
                        </td>
                        <td class="text-start align-middle">{{$d->uraian}}</td>
                        <td class="text-center align-middle">
                            {{$d->id_tanggal_jatuh_tempo}}
                        </td>
                        <td class="text-center align-middle">
                            {{$d->nf_jumlah}}
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_harga_satuan}}
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_ppn}}
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_total}}
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_dp}}
                        </td>
                        <td class="text-end align-middle">
                            {{$d->nf_sisa_bayar}}
                        </td>
                        <td class="text-center align-middle">
                            <form action="{{route('billing.form-inventaris.hutang.pelunasan', $d)}}" method="post" id="pelunasanForm{{$d->id}}">
                                @csrf
                                <button type="submit" class="btn btn-primary"><i class="fa fa-credit-card"></i> Pelunasan</button>
                            </form>

                        </td>
                    </tr>
                    <script>
                        $('#pelunasanForm{{$d->id}}').submit(function(e){
                               e.preventDefault();
                               Swal.fire({
                                   title: 'Apakah data yakin untuk melakukan Pelunasan? Sisa Tagihan Sebesar Rp. {{$d->nf_sisa_bayar}}',
                                   icon: 'warning',
                                   showCancelButton: true,
                                   confirmButtonColor: '#3085d6',
                                   cancelButtonColor: '#6c757d',
                                   confirmButtonText: 'Ya, Lanjutkan!'
                                   }).then((result) => {
                                   if (result.isConfirmed) {
                                    $('#spinner').show();
                                       this.submit();
                                   }
                               })
                           });
                    </script>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="3">Grand Total : </th>
                    <th class="text-center align-middle">{{number_format($data->sum('jumlah'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('harga_satuan'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('ppn'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('total'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dp'),0,',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_bayar'),0,',','.')}}</th>
                    <th></th>
                </tr>
            </tfoot>
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

        // table.on( 'order.dt search.dt', function () {
        //     table.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
        //         cell.innerHTML = i+1;
        //     } );
        // } ).draw();
    });
</script>
@endpush
