@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>HUTANG BELANJA</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing.form-transaksi')}}"><img src="{{asset('images/transaksi.svg')}}" alt="dokumen" width="30">
                            Form Transaksi</a></td>
                </tr>
            </table>
        </div>

    </div>
    <div class="row">
        <div class="col-md-6">
            <form action="{{ route('billing.form-transaksi.bahan-baku.hutang-belanja') }}" method="GET" class="form-inline">
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
                <a href="{{ route('billing.form-transaksi.bahan-baku.hutang-belanja') }}" class="btn btn-secondary mb-2">Reset Filter</a>
            </form>
        </div>
    </div>

</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Supplier</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">Nilai<br>DPP</th>
                    <th class="text-center align-middle">Diskon</th>
                    <th class="text-center align-middle">PPn</th>
                    <th class="text-center align-middle">Total<br>Belanja</th>
                    <th class="text-center align-middle">DP</th>
                    <th class="text-center align-middle">DP<br>PPN</th>
                    <th class="text-center align-middle">Sisa<br>PPN</th>
                    <th class="text-center align-middle">Sisa<br>Tagihan</th>
                    <th class="text-center align-middle">Jatuh<br>Tempo</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->supplier->nama}}</td>
                    <td class="text-center align-middle">
                        <a href="{{route('rekap.invoice-belanja.detail', ['invoice' => $d])}}">
                            {{$d->kode}}
                        </a>
                    </td>
                    <td class="text-start align-middle">{{$d->uraian}}</td>
                    <td class="text-end align-middle">
                        {{$d->dpp}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_diskon}}
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
                        {{$d->nf_dp_ppn}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_sisa_ppn}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_sisa}}
                    </td>
                    <td class="text-center align-middle">
                        {{$d->id_jatuh_tempo}}
                    </td>
                    <td class="text-center align-middle">
                        <form action="{{route('billing.form-transaksi.bahan-baku.hutang-belanja.bayar', ['invoice' => $d])}}" method="post" id="bayarForm{{ $d->id }}"
                            class="bayar-form" data-id="{{ $d->id }}" data-nominal="{{$d->nf_sisa}}">
                            @csrf
                                <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-credit-card"></i> Bayar</button>
                        </form>
                        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
                        <form action="{{route('billing.form-transaksi.bahan-baku.hutang-belanja.void', ['invoice' => $d])}}" method="post" id="voidForm{{ $d->id }}"
                            class="void-form m-3" data-id="{{ $d->id }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-exclamation-circle"></i> Void</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="text-end align-middle" colspan="4">Grand Toal</td>
                    <td class="text-end align-middle">{{number_format($data->sum('total')-$data->sum('ppn')+$data->sum('diskon'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle">{{number_format($data->sum('diskon'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle">{{number_format($data->sum('ppn'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle">{{number_format($data->sum('total'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle">{{number_format($data->sum('dp'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle">{{number_format($data->sum('dp_ppn'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle">{{number_format($data->sum('sisa_ppn'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle">{{number_format($data->sum('sisa'), 0, ',', '.')}}</td>
                    <td class="text-end align-middle"></td>
                    <td class="text-end align-middle"></td>
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


</script>
@endpush
