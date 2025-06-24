@extends('layouts.app')
@section('content')
@include('billing.invoice-konsumen.cicil')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>SALES ORDER<br>{{ request()->get('kas_ppn') == 1 ? 'PPN' : 'NON PPN' }}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>

                </tr>
            </table>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>


</div>
<div class="container-fluid table-responsive ml-3">

    <form method="GET" action="{{ url()->current() }}">
        <input type="hidden" name="kas_ppn" value="{{ request()->get('kas_ppn') }}">
        <div class="row text-end">
            <div class="col-md-3">
                <select class="form-select" name="karyawan_id" id="karyawan_id" onchange="this.form.submit()">
                    <option value="" selected>-- Semua Karyawan</option>
                    @foreach ($karyawan as $k)
                    <option value="{{$k->id}}" @if (request()->get('karyawan_id') == $k->id) selected @endif>{{$k->nama}}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </form>
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable" style="width: 100%">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Karyawan</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Pembayaran</th>
                    <th class="text-center align-middle">Nilai <br>DPP</th>
                    <th class="text-center align-middle">Diskon</th>
                    <th class="text-center align-middle">PPn</th>
                    <th class="text-center align-middle">Penyesuaian</th>
                    <th class="text-center align-middle">Total <br>Belanja</th>
                    <th class="text-center align-middle">DP</th>
                    <th class="text-center align-middle">DP <br>PPN</th>
                    <th class="text-center align-middle">Sisa <br>PPN</th>
                    <th class="text-center align-middle">Sisa <br>Tagihan</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @php
                $sumCicilan = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal_en}}</td>
                    <td class="text-center align-middle">{{$d->karyawan->nama}}</td>
                    <td class="text-center align-middle">{{$d->konsumen->kode_toko->kode}} {{$d->konsumen->nama}}</td>
                    <td class="text-center align-middle">{{$d->sistem_pembayaran_word}}</td>
                    <td class="text-end align-middle">{{$d->dpp}}</td>
                    <td class="text-end align-middle">{{$d->nf_diskon}}</td>
                    <td class="text-end align-middle">{{$d->nf_ppn}}</td>
                    <td class="text-end align-middle">{{$d->nf_add_fee}}</td>
                    <td class="text-end align-middle">{{$d->nf_grand_total}}</td>
                    <td class="text-end align-middle">{{$d->nf_dp}}</td>
                    <td class="text-end align-middle">{{$d->nf_dp_ppn}}</td>
                    <td class="text-end align-middle {{$d->ppn_dipungut ? '' : 'table-danger'}}">{{$d->nf_sisa_ppn}}
                    </td>
                    <td class="text-end align-middle">{{$d->nf_sisa_tagihan}}</td>
                    <td class="text-end align-middle text-nowrap">
                        <div class="row p-2">
                            <a href="{{route('billing.sales-order.detail', ['order' => $d->id])}}" target="_blank"
                                class="btn btn-primary btn-sm"><i class="fa fa-file me-1"></i> Detail</a>
                        </div>
                        <div class="row p-2">
                            <button type="button" onclick="lanjutkanOrder({{$d->id}})" class="btn btn-success btn-sm"><i
                                    class="fa fa-credit-card me-1"></i> Lanjutkan</button>
                        </div>
                        <div class="row p-2">
                            <button type="button" class="btn btn-danger btn-sm" onclick="voidOrder({{$d->id}})"><i
                                    class="fa fa-exclamation-circle me-1"></i> Void</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="4">Grand Toal</th>
                    <th class="text-end align-middle">{{number_format($data->sum('total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('diskon'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('add_fee'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('grand_total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dp'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dp_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_tagihan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle"></th>
                </tr>
            </tfoot>

        </table>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "60vh", // Set scrollY to 50% of the viewport height
            "scrollCollapse": true,
            "scrollX": true,

        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

    });

    function voidOrder(id) {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Saya  Yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('billing.sales-order.void', ':id')}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        if (data.status == 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message,
                            });
                        }
                    }
                });
            }
        })
    }

    function lanjutkanOrder(id)
    {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Saya  Yakin!'
        }).then((result) => {
            if (result.isConfirmed) {
                // redirect to route
                window.location.href = '{{route('billing.sales-order.lanjutkan', ['order' => ':id'])}}'.replace(':id', id);
            }
        })
    }

</script>
@endpush
