@extends('layouts.app')
@section('content')
@include('billing.invoice-konsumen.cicil')
@include('billing.invoice-konsumen.cicil-non-new')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>INVOICE KONSUMEN<br> {{isset($titipan) && $titipan==1 ? 'TITIPAN' : 'TEMPO'}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('billing')}}"><img src="{{asset('images/billing.svg')}}" alt="dokumen"
                                width="30">
                            Billing</a></td>
                    <td><a target="_blank"
                            href="{{route('billing.invoice-konsumen.pdf', ['expired' => request()->has('expired') ? request('expired') : '', 'kas_ppn' => 1, 'titipan' => isset($titipan) && $titipan == 1 ? 1 : 0])}}"><img
                                src="{{asset('images/print.svg')}}" alt="dokumen" width="30">
                            Print</a></td>
                </tr>
            </table>
        </div>
        <div class="col-md-5">
            @include('wa-status')
        </div>
    </div>
    <div class="row">
        <form action="{{url()->current()}}" method="get">
            <div class="row">
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="expired" id="expired" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Data --</option>
                        <option value="yes" {{ request('expired')=='yes' ? 'selected' : '' }}>Expired</option>
                        <option value="no" {{ request()->has('expired') && request('expired') == 'no' ? 'selected' : ''
                            }}>Belum
                            Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="apa_ppn" id="apa_ppn" class="form-select" onchange="this.form.submit()">
                        <option value="">-- PPN & Non PPN --</option>
                        <option value="yes" {{ request('apa_ppn')=='yes' ? 'selected' : '' }}>PPN</option>
                        <option value="no" {{ request()->has('apa_ppn') && request('apa_ppn') == 'no' ? 'selected' : ''
                            }}>Non PPN</option>
                    </select>
                </div>
                <div class="col-md-2">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="karyawan_id" id="karyawan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Sales --</option>
                        @foreach ($sales as $s)
                        <option value="{{$s->id}}" {{ request('karyawan_id')==$s->id ? 'selected' : '' }}>{{$s->nama}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="kabupaten_id" id="kabupaten_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kab/Kota --</option>
                        @foreach ($kabupaten as $kab)
                        <option value="{{$kab->id}}" {{ request('kabupaten_id')==$kab->id ? 'selected' : '' }}>{{$kab->nama_wilayah}}</option>
                        @endforeach
                    </select>
                </div>
                 <div class="col-md-3">
                    {{-- tombol filter untuk expired yang akan mengirimkan expired = 1 atau 0 --}}
                    <select name="kecamatan_id" id="kecamatan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Semua Kecamatan --</option>
                        @foreach ($kecamatan as $k)
                        <option value="{{$k->id}}" {{ request('kecamatan_id')==$k->id ? 'selected' : '' }}>{{$k->nama_wilayah}}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </form>
        <div class="row mt-2">
        <div class="col-md-2">

                <a href="{{ url()->current() }}" class="btn btn-secondary mb-2"><i class="fa fa-repeat"></i> Reset Filter</a>
            </div>
        </div>
        </div>
    </div>

</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <table class="table table-hover table-bordered" id="rekapTable" style="font-size: 0.8rem;">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Sales</th>
                    <th class="text-center align-middle">Daerah</th>
                    <th class="text-center align-middle">Konsumen</th>
                    <th class="text-center align-middle">Nota</th>
                    <th class="text-center align-middle">Nilai</th>
                    <th class="text-center align-middle">Total <br>Belanja</th>
                    <th class="text-center align-middle">DP</th>
                    <th class="text-center align-middle">DP <br>PPN</th>
                    <th class="text-center align-middle">Cicilan</th>
                    <th class="text-center align-middle">Sisa <br>PPN</th>
                    <th class="text-center align-middle">Sisa <br>Tagihan</th>
                    <th class="text-center align-middle">Jatuh <br>Tempo</th>
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
                    <td class="text-center align-middle">{{$d->karyawan ? $d->karyawan->nama : '' }}</td>
                     <td class="text-start align-middle">
                            {{$d->konsumen->kabupaten_kota ? $d->konsumen->kabupaten_kota->nama_wilayah.', ' : ''}}
                            {{$d->konsumen->kecamatan ? $d->konsumen->kecamatan->nama_wilayah : ''}}</strong></li>
                    </td>
                    <td class="text-center align-middle">{{$d->konsumen->kode_toko ? $d->konsumen->kode_toko->kode.' ' :
                        '' }}{{$d->konsumen->nama}}</td>
                    <td class="text-center align-middle text-nowrap">
                        <a href="{{route('billing.invoice-konsumen.detail', $d)}}">
                            {{$d->kode}}
                        </a>

                    </td>
                    <td class="text-start align-middle text-nowrap">
                        <ul style="margin: 0; padding: 0; list-style: none;">
                            <li>DPP : <strong>{{$d->dpp}}</strong></li>
                            <li>Diskon : <strong>{{$d->nf_diskon}}</strong></li>
                            <li>PPN : <strong>{{$d->nf_ppn}}</strong></li>
                            <li>Penyesuaian : <strong>{{$d->nf_add_fee}}</strong></li>
                        </ul>

                    </td>
                    <td class="text-end align-middle">{{$d->nf_grand_total}}</td>
                    <td class="text-end align-middle">{{$d->nf_dp}}</td>
                    <td class="text-end align-middle">{{$d->nf_dp_ppn}}</td>
                    <td class="text-end align-middle">
                        @if ($d->invoice_jual_cicil && $d->invoice_jual_cicil->count() > 0)
                        <a href="#" data-bs-toggle="modal"
                            data-bs-target="#modalHistoriCicilan{{$d->id}}">{{number_format($d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn'),
                            0, ',', '.')}}</a>
                        @include('billing.invoice-konsumen.histori-cicil')
                        @php
                        $sumCicilan += $d->invoice_jual_cicil->sum('nominal')+$d->invoice_jual_cicil->sum('ppn');
                        @endphp
                        @else
                        0
                        @endif
                    </td>
                    <td class="text-end align-middle {{$d->ppn_dipungut ? '' : 'table-danger'}}">{{$d->nf_sisa_ppn}}
                    </td>
                    <td class="text-end align-middle">{{$d->nf_sisa_tagihan}}</td>
                    <td class="text-end align-middle">{{$d->jatuh_tempo}}</td>
                    <td class="text-end align-middle' text-nowrap">
                        <div class="row px-3">
                            @if (file_exists(public_path('storage/invoices/invoice-'.$d->id.'.pdf')))
                            <a href="{{asset('storage/invoices/invoice-'.$d->id.'.pdf')}}" target="_blank"
                                class="btn btn-primary btn-sm"><i class="fa fa-file me-1"></i> Invoice</a>
                            @else
                            <a href="{{route('billing.form-jual.invoice', ['invoice' => $d->id])}}" target="_blank"
                                class="btn btn-primary btn-sm"><i class="fa fa-file me-1"></i> Invoice</a>
                            @endif
                        </div>

                        <form action="{{route('billing.invoice-konsumen.bayar', ['invoice' => $d])}}" method="post"
                            id="bayarForm{{ $d->id }}" class="bayar-form" data-id="{{ $d->id }}"
                            data-nominal="{{$d->nf_sisa_tagihan}}">
                            @csrf
                            <div class="row p-3">
                                <button type="submit" class="btn btn-sm btn-success"><i
                                        class="fa fa-credit-card me-1"></i> Bayar</button>
                            </div>
                        </form>
                        <div class="row px-3">
                            @if ($d->kas_ppn == 1)
                                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#cicilanModal"
                                onclick="cicilan({{$d}})">Cicil</button>
                            @else
                             <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#cicilanNonModal"
                                onclick="cicilanNonPpn({{$d}})">Cicil</button>
                            @endif

                        </div>
                        @if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
                        <form action="{{route('billing.invoice-konsumen.void', ['invoice' => $d])}}" method="post"
                            id="voidForm{{ $d->id }}" class="void-form" data-id="{{ $d->id }}">
                            @csrf
                            <div class="row p-3">
                                <button type="submit" class="btn btn-sm btn-danger"><i
                                        class="fa fa-exclamation-circle me-1"></i> Void</button>
                            </div>
                        </form>
                        @endif
                        {{-- <a href="{{route('billing.invoice-konsumen.invoice-jpeg', ['invoice', $d->id])}}"
                            class="btn btn-sm btn-primary">Invoice</a> --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="5">Grand Total</th>
                    <th class="text-start text-nowrap align-middle">

                        <ul style="margin: 0; padding: 0; list-style: none;">
                            <li>DPP : <strong>{{number_format($data->sum('total'), 0, ',', '.')}}</strong></li>
                            <li>Diskon : <strong>{{number_format($data->sum('diskon'), 0, ',', '.')}}</strong></li>
                            <li>PPN : <strong>{{number_format($data->sum('ppn'), 0, ',', '.')}}</strong></li>
                            <li>Penyesuaian : <strong>{{number_format($data->sum('add_fee'), 0, ',', '.')}}</strong>
                            </li>
                        </ul>
                    </th>
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
            "scrollCollapse": true,
            "scrollY": "60vh", // Set scrollY to 50% of the viewport height
            "scrollX": true,
        });

        $('#supplier_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#karyawan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

        $('#kecamatan_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

           $('#kabupaten_id').select2({
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
        document.getElementById('edit_ppn_dipungut').value = data.ppn_dipungut;
        document.getElementById('edit_nominal').value = '';
        document.getElementById('edit_ppn').value = '';
        document.getElementById('edit_total').value = '';
        document.getElementById('cicilForm').action = '/billing/invoice-konsumen/cicil/' + data.id;

    }

    function cicilanNonPpn(data) {

        document.getElementById('edit_konsumen_nama').value = data.konsumen.nama;
        document.getElementById('edit_nota').value = data.kode;
        document.getElementById('edit_sisa_tagihan').value = data.nf_sisa_tagihan;
        document.getElementById('edit_apa_ppn').value = 0;
        document.getElementById('edit_nominal').value = '';
        document.getElementById('edit_total').value = '';
        document.getElementById('cicilForm').action = '/billing/invoice-konsumen/cicil/' + data.id;

    }



</script>
@endpush
