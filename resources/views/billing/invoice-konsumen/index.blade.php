@extends('layouts.app')
@section('content')
@include('billing.invoice-konsumen.cicil')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE KONSUMEN<br>PPN {{isset($titipan) && $titipan==1 ? 'TITIPAN' : 'TEMPO'}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            @include('wa-status')
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            {{-- <form action="{{ route('billing.invoice-supplier') }}" method="GET" class="form-inline">
                <div class="form-group mb-2">
                    <label for="supplier_id" class="sr-only">Supplier:</label>
                    <select name="supplier_id" id="supplier_id" class="form-control">
                        <option value="" disabled selected>Pilih Supplier</option>
                        @foreach($supplier as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Filter</button>
                <a href="{{ route('billing.invoice-supplier') }}" class="btn btn-secondary mb-2">Reset Filter</a>
            </form> --}}
        </div>
    </div>

</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Nilai<br>DPP</th>
                    <th class="text-center align-middle">Diskon</th>
                    <th class="text-center align-middle">PPn</th>
                    <th class="text-center align-middle">Penyesuaian</th>
                    <th class="text-center align-middle">Total<br>Belanja</th>
                    <th class="text-center align-middle">DP</th>
                    <th class="text-center align-middle">DP<br>PPN</th>
                    <th class="text-center align-middle">Cicilan</th>
                    <th class="text-center align-middle">Sisa<br>PPN</th>
                    <th class="text-center align-middle">Sisa<br>Tagihan</th>
                    <th class="text-center align-middle">Jatuh<br>Tempo</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @php
                $sumCicilan = 0;
                @endphp
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->konsumen->nama}}</td>
                    <td class="text-center align-middle">
                        <a href="{{route('billing.invoice-konsumen.detail', $d)}}">
                            {{$d->kode}}
                        </a>

                    </td>
                    <td class="text-end align-middle">{{$d->dpp}}</td>
                    <td class="text-end align-middle">{{$d->nf_diskon}}</td>
                    <td class="text-end align-middle">{{$d->nf_ppn}}</td>
                    <td class="text-end align-middle">{{$d->nf_add_fee}}</td>
                    <td class="text-end align-middle">{{$d->nf_grand_total}}</td>
                    <td class="text-end align-middle">{{$d->nf_dp}}</td>
                    <td class="text-end align-middle">{{$d->nf_dp_ppn}}</td>
                    <td class="text-end align-middle">
                        @if ($d->invoice_jual_cicil && $d->invoice_jual_cicil->count() > 0)
                        <a href="#" data-bs-toggle="modal"
                            data-bs-target="#modalHistoriCicilan{{$d->id}}">{{number_format($d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn'),
                            0, ',', '.')}}</a>
                        @include('billing.invoice-supplier.histori-cicil')
                        @php
                        $sumCicilan += $d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn');
                        @endphp
                        @else
                        0
                        @endif
                    </td>
                    <td class="text-end align-middle">{{$d->nf_sisa_ppn}}</td>
                    <td class="text-end align-middle">{{$d->nf_sisa_tagihan}}</td>
                    <td class="text-end align-middle">{{$d->id_jatuh_tempo}}</td>
                    <td class="text-end align-middle">

                        <form action="{{route('billing.invoice-konsumen.bayar', ['invoice' => $d])}}" method="post" id="bayarForm{{ $d->id }}"
                            class="bayar-form" data-id="{{ $d->id }}" data-nominal="{{$d->sisa_tagihan}}">
                            @csrf
                            <div class="row p-3">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-credit-card"></i> Bayar</button>
                            </div>
                        </form>
                        <div class="row px-3">
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#cicilanModal"
                             onclick="cicilan({{$d}})">Cicil</button>
                        </div>
                        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
                        <form action="{{route('billing.invoice-konsumen.void', ['invoice' => $d])}}" method="post" id="voidForm{{ $d->id }}"
                            class="void-form" data-id="{{ $d->id }}">
                            @csrf
                            <div class="row p-3">
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-exclamation-circle"></i> Void</button>
                            </div>
                        </form>
                        @endif
                        {{-- <a href="{{route('billing.invoice-konsumen.invoice-jpeg', ['invoice', $d->id])}}" class="btn btn-sm btn-primary">Invoice</a> --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="3">Grand Toal</th>
                    <th class="text-end align-middle">{{number_format($data->sum('total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('diskon'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('add_fee'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('grand_total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dp'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dp_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($sumCicilan, 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_tagihan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle" colspan="2"></th>
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
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('.bayar-form').submit(function(e){
            e.preventDefault();
            var formId = $(this).data('id');
            var nominal = $(this).data('nominal');
            Swal.fire({
                title: 'Apakah Anda Yakin? Sisa Tagihan Sebesar: Rp. ' + nominal,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#bayarForm${formId}`).unbind('submit').submit();
                    $('#spinner').show();
                }
            });
        });



        $('.void-form').submit(function(e){
            e.preventDefault();
            var formId = $(this).data('id'); // Store a reference to the form

            Swal.fire({
                title: 'Apakah anda Yakin Ingin Melakukan Void? Masukkan Password Konfirmasi',
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off'
                },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Submit',
                showLoaderOnConfirm: true,
                preConfirm: (password) => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: '{{route('pengaturan.password-konfirmasi-cek')}}',
                            type: 'POST',
                            data: JSON.stringify({ password: password }),
                            contentType: 'application/json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(data) {
                                if (data.status === 'success') {
                                    resolve();
                                } else {
                                    // swal show error message\
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: data.message
                                    });
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: textStatus
                                    });
                            }
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#voidForm${formId}`).unbind('submit').submit();
                    $('#spinner').show();

                }
            });
        });

    });

    function cicilan(data) {

        console.log(data);
        document.getElementById('edit_konsumen_nama').value = data.konsumen.nama;
        document.getElementById('edit_sisa_dpp').value = (data.sisa_tagihan - data.sisa_ppn).toLocaleString('id-ID');
        document.getElementById('edit_nota').value = data.kode;
        document.getElementById('edit_sisa_tagihan').value = data.nf_sisa_tagihan;
        document.getElementById('edit_sisa_ppn').value = data.nf_sisa_ppn;
        document.getElementById('edit_apa_ppn').value = 1;
        document.getElementById('cicilForm').action = '/billing/invoice-konsumen/cicil/' + data.id;

    }



</script>
@endpush
