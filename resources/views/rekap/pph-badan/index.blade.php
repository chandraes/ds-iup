@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>PPH BADAN</u></h1>
        </div>
    </div>
    @include('swal')
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/back.svg')}}" alt="dokumen" width="30">
                            Back</a></td>
                </tr>
            </table>
        </div>
        <form action="{{route('rekap.pph-masa')}}" method="get" class="col-md-6">
            <div class="row mt-2">
                <div class="col-md-4 mb-3">
                    <select class="form-select" name="tahun" id="tahun">
                        @foreach ($dataTahun as $dt)
                        <option value="{{$dt->tahunArray}}" {{$dt->tahunArray == $tahun ? 'selected' :
                            ''}}>{{$dt->tahunArray}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover" id="data-table">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" style="width: 5%">No</th>
                    <th class="text-center align-middle">URAIAN</th>
                    <th class="text-center align-middle">NOMINAL</th>
                    <th class="text-center align-middle">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-start align-middle">1</td>
                    <td class="text-start align-middle"><strong>Laba bersih</strong></td>
                    <td class="text-start align-middle"></td>
                    <td class="text-end align-middle">
                        <strong>
                            {{number_format($data['laba_bersih'], 2, ',','.')}}
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle">A</td>
                    <td class="text-start align-middle">Omset Utama</td>
                    <td class="text-end align-middle">
                        {{number_format($data['omset'], 2, ',','.')}}
                    </td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td class="text-end align-middle">B</td>
                    <td class="text-start align-middle">Modal/HPP</td>
                    <td class="text-end align-middle">
                        {{number_format($data['modal'], 2, ',','.')}}
                    </td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td class="text-end align-middle">C</td>
                    <td class="text-start align-middle">COST OPERASIONAL</td>
                    <td class="text-end align-middle">
                        {{number_format($data['cost_operational'], 2, ',','.')}}
                    </td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-start align-middle">2</td>
                    <td class="text-start align-middle"><strong>Kelebihan PPH tahun sebelumnya</strong></td>
                    <td class="text-start align-middle">
                        <form action="{{route('rekap.pph-badan')}}" method="get">
                            <div class="row px-3">
                                <input type="text" name="kelebihan_pph" id="kelebihan_pph" class="form-control"
                                    value="{{$kelebihan}}">
                            </div>
                        </form>
                    </td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-start align-middle">3</td>
                    <td class="text-start align-middle"><strong>Nilai Pokok Penghitung PKP</strong></td>
                    <td class="text-start align-middle">

                    </td>
                    <td class="text-end align-middle">
                        <strong>
                            {{number_format($data['pokok_pkp'], 2, ',','.')}}
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-start align-middle">4</td>
                    <td class="text-start align-middle"><strong>Grand Total PPH Terhutang</strong></td>
                    <td class="text-start align-middle"></td>
                    <td class="text-end align-middle">
                        <strong>
                            {{number_format($data['gt_pph_terhutang'], 2, ',','.')}}
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td class="text-end align-middle">
                        <strong>
                            A
                        </strong>
                    </td>
                    <td class="text-start align-middle">
                        <strong>PPH Fasilitas</strong>
                    </td>
                    <td class="text-end align-middle"></td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td class="text-end align-middle">*</td>
                    <td class="text-start align-middle">PKP Fasilitas</td>
                    <td class="text-end align-middle">{{number_format($data['pkp_fasilitas'], 2, ',','.')}}</td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td class="text-end align-middle">*</td>
                    <td class="text-start align-middle">PPH Terhutang Fasilitas</td>
                    <td class="text-end align-middle">
                        <strong>
                            {{number_format($data['pph_terhutang_fasilitas'], 2, ',','.')}}
                        </strong>
                    </td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-end align-middle">
                        <strong>
                            B
                        </strong>
                    </td>
                    <td class="text-start align-middle">
                        <strong>PPH Non Fasilitas</strong>
                    </td>
                    <td class="text-end align-middle"></td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td class="text-end align-middle">*</td>
                    <td class="text-start align-middle">PKP Non Fasilitas</td>
                    <td class="text-end align-middle">
                        {{number_format($data['pkp_non_fasilitas'], 2, ',','.')}}
                    </td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td class="text-end align-middle">*</td>
                    <td class="text-start align-middle">PPH Terhutang Non Fasilitas</td>
                    <td class="text-end align-middle">
                        <strong>
                            {{number_format($data['pph_terhutang_non_fasilitas'], 2, ',','.')}}
                        </strong>
                    </td>
                    <td class="text-end align-middle"></td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-start align-middle">6</td>
                    <td class="text-start align-middle"><strong>Kredit PPH</strong></td>
                    <td class="text-start align-middle"></td>
                    <td class="text-end align-middle">
                        <strong>
                            {{number_format($data['kredit_pph'], 2, ',','.')}}
                        </strong>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td class="text-start align-middle">5</td>
                    <td class="text-start align-middle"><strong>PPH Kurang/lebih bayar</strong></td>
                    <td class="text-start align-middle"></td>
                    <td class="text-end align-middle">
                        <strong>
                            {{number_format($data['gt'], 2, ',','.')}}
                        </strong>
                    </td>
                </tr>
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
        var kelebihan_pph = new Cleave('#kelebihan_pph', {
            numeral: true,
            numeralThousandsGroupStyle: 'thousand',
            numeralDecimalMark: ',',
            delimiter: '.'
        });


    $(document).ready(function() {
        var table = $('#data-table').DataTable({
            "paging": false,
            "searching": false,
            "scrollCollapse": true,
            "info": false,
            "ordering": false,

        });

    });

</script>
@endpush
