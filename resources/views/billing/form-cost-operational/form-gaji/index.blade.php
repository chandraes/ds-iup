@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Gaji Staff</u></h1>
            <h1>{{$month}} {{date('Y')}}</h1>
        </div>
    </div>
    @php
        $total = 0;
        $grandTotalPotonganBpjsTk = 0;
        $grandTotalPotonganBpjsKesehatan = 0;
        $grandTotalPendapatanKotor = 0;
        $grandTotalPendapatanBersih = 0;
        $grandTotalKasbon = 0;
    @endphp
    @include('swal')
    <div style="font-size:12px">
        <table class="table table-bordered table-hover" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th rowspan="2" class="text-center align-middle">NIK</th>
                    <th rowspan="2" class="text-center align-middle">Nama</th>
                    <th rowspan="2" class="text-center align-middle">Jabatan</th>
                    <th rowspan="2" class="text-center align-middle">Gaji Pokok</th>
                    <th colspan="4" class="text-center align-middle">Tunjangan</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-TK (2%)</th>
                    <th rowspan="2" class="text-center align-middle">Potongan BPJS-Kesehatan (1%)</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Kotor</th>
                    <th rowspan="2" class="text-center align-middle">Total Pendapatan Bersih</th>
                    <th rowspan="2" class="text-center align-middle">Sisa Gaji Dibayar</th>
                </tr>
                <tr>
                    <th class="text-center align-middle">Jabatan</th>
                    <th class="text-center align-middle">Keluarga</th>
                    <th class="text-center align-middle">BPJS-TK (4,89%)</th>
                    <th class="text-center align-middle">BPJS-Kesehatan (4%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $i)
                @php
                     $bpjs_tk = $i->apa_bpjs_tk == 1 ? $i->gaji_pokok * 0.049 : 0;
                    $potongan_bpjs_tk = $i->apa_bpjs_tk == 1 ? $i->gaji_pokok * 0.02 : 0;
                    $bpjs_k = $i->apa_bpjs_kes == 1 ? $i->gaji_pokok * 0.04 : 0;
                    $potongan_bpjs_kesehatan = $i->apa_bpjs_kes == 1 ? $i->gaji_pokok * 0.01 : 0;

                    $grandTotalPotonganBpjsTk = $grandTotalPotonganBpjsTk + $potongan_bpjs_tk;
                    $grandTotalPotonganBpjsKesehatan = $grandTotalPotonganBpjsKesehatan + $potongan_bpjs_kesehatan;
                    $pendapatan_kotor = $i->gaji_pokok + $i->tunjangan_jabatan + $i->tunjangan_keluarga + $bpjs_tk + $bpjs_k;
                    $grandTotalPendapatanKotor = $grandTotalPendapatanKotor + $pendapatan_kotor;
                    $pendapatan_bersih = $i->gaji_pokok + $i->tunjangan_jabatan + $i->tunjangan_keluarga - $potongan_bpjs_tk - $potongan_bpjs_kesehatan;
                    $grandTotalPendapatanBersih = $grandTotalPendapatanBersih + $pendapatan_bersih;
                @endphp
                <tr>
                    <td class="text-center align-middle">{{$i->kode}}{{sprintf("%03d",$i->nomor)}}</td>
                    <td class="text-center align-middle">{{$i->nama}}</td>
                    <td class="text-center align-middle">{{$i->jabatan->nama}}</td>
                    <td class="text-center align-middle">{{number_format($i->gaji_pokok, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($i->tunjangan_jabatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($i->tunjangan_keluarga, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($bpjs_k, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($potongan_bpjs_tk, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($potongan_bpjs_kesehatan, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($pendapatan_kotor, 0, ',','.')}}</td>
                    <td class="text-center align-middle">{{number_format($pendapatan_bersih, 0, ',','.')}}</td>
                    <td class="text-center align-middle">
                        @php
                            $sisa_gaji_dibayar = $pendapatan_bersih;
                            $total = $total + $sisa_gaji_dibayar;
                            $grandTotalKasbon = $grandTotalKasbon;
                        @endphp
                        {{number_format($sisa_gaji_dibayar, 0, ',','.')}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="8" class="text-end align-middle">Grand Total : </th>
                    <th class="text-center align-middle">{{number_format($grandTotalPotonganBpjsTk, 0, ',','.')}}</th>
                    <th class="text-center align-middle">{{number_format($grandTotalPotonganBpjsKesehatan, 0, ',','.')}}</th>
                    <th class="text-center align-middle">{{number_format($grandTotalPendapatanKotor, 0, ',','.')}}</th>
                    <th class="text-center align-middle">{{number_format($grandTotalPendapatanBersih, 0, ',','.')}}</th>
                    <th class="text-center align-middle">{{number_format($total, 0, ',','.')}}</th>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="container-fluid mt-3 mb-3">
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
            <form action="{{route('billing.form-cost-operational.gaji.store')}}" method="post" id="lanjutForm">
                @csrf
                <input type="hidden" name="total" value="{{$total}}">
                <button class="btn btn-primary me-md-3 btn-lg" type="submit">Lanjutkan</button>
            </form>
            <a href="{{route('billing')}}" class="btn btn-secondary btn-lg">Batalkan</a>
            {{-- <a class="btn btn-success btn-lg" href="#">Export</a> --}}
          </div>
    </div>
</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
{{-- <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js" type="text/javascript"></script> --}}
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function(){

            $('#rekapTable').DataTable({
                "paging": false,
                "ordering": false,
                "scrollCollapse": true,
                "scrollY": "550px",
            });
        });
        // masukForm on submit, sweetalert confirm
        $('#lanjutForm').submit(function(e){
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
</script>
@endpush
