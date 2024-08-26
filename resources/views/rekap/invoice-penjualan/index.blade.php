@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE PENJUALAN {{$ppn_kas == 1 ? "PPN" : "NON PPN" }}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>
                    <td>
                        <form action="{{route('rekap.invoice-penjualan.pdf')}}" method="get" target="_blank">
                            <input type="hidden" name="bulan" value="{{$bulan}}">
                            <input type="hidden" name="tahun" value="{{$tahun}}">
                            <input type="hidden" name="ppn_kas" value="{{$ppn_kas}}">
                            <button class="btn"><img src="{{asset('images/print.svg')}}" alt="dokumen"
                                width="30"> PDF</button>
                        </form>

                    </td>
                </tr>
            </table>
        </div>

    </div>
    @php
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
    @endphp
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('rekap.invoice-penjualan') }}" method="GET" class="form-inline">
                <input type="hidden" name="ppn_kas" value="{{$ppn_kas}}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group mb-2 mr-2">
                            <label for="bulan" class="sr-only">Bulan:</label>
                            <select name="bulan" id="bulan" class="form-control">
                                <option value="" disabled selected>Pilih Bulan</option>
                                @foreach($months as $key => $month)
                                    <option value="{{ $key }}" {{ $bulan == $key ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group mb-2 mr-2">
                            <label for="tahun" class="sr-only">Tahun:</label>
                            <select name="tahun" id="tahun" class="form-control">
                                <option value="" disabled selected>Pilih Tahun</option>
                                @foreach($dataTahun as $year)
                                    <option value="{{ $year->tahun }}" {{ $tahun == $year->tahun ? 'selected' : '' }}>{{ $year->tahun }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mb-2 mr-2">Filter</button>
                <a href="{{ route('rekap.invoice-penjualan', ['ppn_kas' => $ppn_kas]) }}" class="btn btn-secondary mb-2">Reset Filter</a>
            </form>
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
                    @if ($ppn_kas == 1)
                    <th class="text-center align-middle">PPn</th>
                    @endif

                    <th class="text-center align-middle">Penyesuaian</th>
                    <th class="text-center align-middle">Total<br>Belanja</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-center align-middle">{{$d->konsumen ? $d->konsumen->nama : $d->konsumen_temp->nama}}</td>
                    <td class="text-center align-middle">
                        <a href="{{route('rekap.invoice-penjualan.detail', $d)}}">
                            {{$d->kode}}
                        </a>
                    </td>
                    <td class="text-end align-middle">{{$d->dpp}}</td>
                    <td class="text-end align-middle">{{$d->nf_diskon}}</td>
                    @if ($ppn_kas == 1)
                    <td class="text-end align-middle">{{$d->nf_ppn}}</td>
                    @endif
                    <td class="text-end align-middle">{{$d->nf_add_fee}}</td>
                    <td class="text-end align-middle">{{$d->nf_grand_total}}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="3">Grand Toal</th>
                    <th class="text-end align-middle">{{number_format($data->sum('total'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('diskon'), 0, ',', '.')}}</th>
                    @if ($ppn_kas == 1)
                    <th class="text-end align-middle">{{number_format($data->sum('ppn'), 0, ',', '.')}}</th>
                    @endif
                    <th class="text-end align-middle">{{number_format($data->sum('add_fee'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('grand_total'), 0, ',', '.')}}</th>
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
