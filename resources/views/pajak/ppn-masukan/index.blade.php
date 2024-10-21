@extends('layouts.app')
@section('content')
<div class="container-fluid">
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
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/pajak.svg')}}" alt="dokumen"
                                width="30">
                            PAJAK</a></td>
                </tr>
            </table>
        </div>

    </div>
</div>

@include('pajak.ppn-masukan.faktur-modal')
@include('pajak.ppn-masukan.show-faktur')

<div class="container-fluid table-responsive ml-3">
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
                        @if ($d->is_faktur == 0)
                        {{$d->nf_nominal}}
                        @else
                        0
                        @endif

                    </td>
                    <td class="text-end align-middle">
                        @if ($d->is_faktur == 1)
                        <a href="#" onclick="showFaktur({{$d->id}})" data-bs-toggle="modal" data-bs-target="#showModal">{{$d->nf_nominal}}</a>

                        @else
                        0
                        @endif
                    </td>
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-{{$d->is_faktur == 1 ? 'warning' : 'primary'}} btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalFaktur" onclick="faktur({{$d->id}})">
                            {{$d->is_faktur == 1 ? 'Ubah' : ''}} Faktur
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="5">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($total_blm_faktur, 0, ',','.')}}</th>
                    <th class="text-end align-middle">{{number_format($total_faktur, 0, ',','.')}}</th>
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
    function getDataById(id) {
        const data = @json($data);
        return data.find(x => x.id == id);
    }

    function showFaktur(id) {
        const d = getDataById(id);

        $('#no_faktur_show').val(d.is_faktur == 1 ? d.no_faktur : 'Faktur Belum Terisi');
    }

    function faktur(id) {
        const form = document.getElementById('fakturForm');
        form.action = `/pajak/ppn-masukan/store-faktur/${id}`;
        form.reset();

        const d = getDataById(id);

        $('#nota').val(d.invoice_belanja_id != null ? d.invoice_belanja.kode : 'Nota Belum Terisi');
        $('#nominal').val(d.nf_nominal);
        $('#no_faktur').val(d.is_faktur == 1 ? d.no_faktur : '');
    }

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
