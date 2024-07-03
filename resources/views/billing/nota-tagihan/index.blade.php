@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>NOTA TAGIHAN</u></h1>
        </div>
    </div>
    {{-- if has any error --}}
    @if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Whoops!</strong> Ada kesalahan dalam input data:
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{$error}}</li>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </ul>
            </div>
        </div>
    </div>
    @endif
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
                    <th class="text-center align-middle">No</th>
                    <th class="text-center align-middle">Customer</th>
                    <th class="text-center align-middle">Project</th>
                    <th class="text-center align-middle">Nilai DPP</th>
                    <th class="text-center align-middle">PPn</th>
                    <th class="text-center align-middle">PPh</th>
                    <th class="text-center align-middle">Total Tagihan</th>
                    <th class="text-center align-middle">Balance</th>
                    <th class="text-center align-middle">Sisa Tagihan</th>
                    <th class="text-center align-middle">PPn Masukan</th>
                    <th class="text-center align-middle">Total Kas Project</th>
                    <th class="text-center align-middle">Profit</th>
                    <th class="text-center align-middle">DP / Termin</th>
                    <th class="text-center align-middle">ACT</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle"></td>
                    <td class="text-center align-middle">{{$d->customer->singkatan}}</td>
                    <td class="text-start align-middle">{{$d->project->nama}}</td>

                    <td class="text-end align-middle">
                        {{$d->nf_nilai_tagihan}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_nilai_ppn}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_nilai_pph}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_total_tagihan}}
                    </td>
                    <td class="align-middle">
                        <div class="text-end">
                            <a href="#" data-bs-toggle="modal"
                                data-bs-target="#detailInvoice-{{$d->id}}">{{$d->nf_dibayar}}</a>
                        </div>

                        @include('billing.nota-tagihan.detail-modal')

                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_sisa_tagihan}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_ppn_masukan}}
                    </td>
                    <td class="text-end align-middle">
                        {{$d->nf_pengeluaran}}
                    </td>
                    <td class="text-end align-middle">

                        {{$d->nf_profit}}
                    </td>

                    <td class="text-start align-middle">
                        <!-- Modal trigger button -->
                        <div class="text-center">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#cicil-{{$d->id}}">
                                DP / Termin
                            </button>
                        </div>
                        <!-- Modal Body -->
                        <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                        <div class="modal fade" id="cicil-{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalTitleId">Jumlah Cicilan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{route('nota-tagihan.cicilan', ['invoice' => $d->id])}}"
                                        method="post" id="cicilForm-{{$d->id}}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Uraian</label>
                                                <input type="text" class="form-control" name="uraian" required>
                                            </div>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                                <input type="text" class="form-control @if ($errors->has('nominal'))
                                            is-invalid
                                        @endif" name="nominal" id="cicilanInput-{{$d->id}}" required
                                                    data-thousands=".">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </td>
                    <td class="text-center align-middle">
                        <!-- Modal trigger button -->
                        <button type="button" class="btn {{$d->ppn_masukan>0 ? 'btn-danger': 'btn-success'}}" data-bs-toggle="modal"
                            data-bs-target="#cutoff-{{$d->id}}">
                            Cutoff
                        </button>

                        <!-- Modal Body -->
                        <!-- if you want to close by clicking outside the modal, delete the last endpoint:data-bs-backdrop and data-bs-keyboard -->
                        <div class="modal fade" id="cutoff-{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                            data-bs-keyboard="false" role="dialog" aria-labelledby="cutoffInvoice-{{$d->id}}Label"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="cutoffInvoice-{{$d->id}}Label">
                                            Cutoff Invoice
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{route('nota-tagihan.cutoff', ['invoice'=> $d->id])}}" method="post"
                                        id="cutoffForm-{{$d->id}}">
                                        @csrf
                                        <div class="modal-body">
                                            <div class="col-md-12 mb-3">
                                                <label for="estimasi_pembayaran" class="form-label">Estimasi Pembayaran</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                                                    <input type="text" class="form-control" name="estimasi_pembayaran" id="estimasi_pembayaran-{{$d->id}}" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Tutup
                                            </button>
                                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                {{-- <button class="btn btn-primary">Test</button> --}}
                <script>
                    flatpickr("#estimasi_pembayaran-{{$d->id}}", {
                        dateFormat: "d-m-Y",
                    });

                    $('#cutoffForm-{{$d->id}}').submit(function(e){
                        e.preventDefault();
                        Swal.fire({
                            title: 'Apakah anda yakin?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, simpan!'
                            }).then((result) => {
                            if (result.isConfirmed) {
                                $('#spinner').show();
                                this.submit();
                            }
                        })
                    });

                    var cicilan{{$d->id}} = new Cleave('#cicilanInput-{{$d->id}}', {
                        numeral: true,
                        numeralThousandsGroupStyle: 'thousand',
                        numeralDecimalMark: ',',
                        delimiter: '.'
                    });

                    $('#cicilForm-{{$d->id}}').submit(function(e){
                        e.preventDefault();
                        Swal.fire({
                            title: 'Apakah anda yakin?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Ya, simpan!'
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
                    <th class="text-center align-middle" colspan="3">Grand Total</th>
                    <th class="text-end align-middle">{{number_format($data->sum('nilai_tagihan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('nilai_ppn'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('nilai_pph'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('total_tagihan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('dibayar'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('sisa_tagihan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('ppn_masukan'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('pengeluaran'), 0, ',', '.')}}</th>
                    <th class="text-end align-middle">{{number_format($data->sum('profit'), 0, ',', '.')}}</th>
                    <th colspan="2"></th>
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

        $('#editForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        $('#masukForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

        $('#lanjutkanForm').submit(function(e){
            var value = $('#total_tagih_display').val();
            var check = $('#total_tagih').val();

            if (check == 0 || check == '') {
                Swal.fire({
                    title: 'Tidak ada data yang dipilih!',
                    text: "Harap pilih tagihan terlebih dahulu!",
                    icon: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ok'
                    })
                return false;

            }

            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Total Tagihan Rp. "+value,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
