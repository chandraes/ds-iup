@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>REKAP KAS PROJECT</u></h1>
            <h1>{{$stringBulanNow}} {{$tahun}}</h1>
        </div>
    </div>
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen"
                                width="30"> REKAP</a></td>
                    <td>
                        <a href="{{route('rekap.kas-project.print', ['project' => $project->id,'bulan' => $bulan, 'tahun' => $tahun])}}" target="_blank"><img src="{{asset('images/print.svg')}}" alt="dokumen"
                            width="30"> PRINT PDF</a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-5">
    <form action="{{route('rekap.kas-project')}}" method="get">
        <div class="row">
            <input type="hidden" name="project" value="{{$project->id}}">
            <div class="col-md-3 mb-3">
                <label for="bulan" class="form-label">Bulan</label>
                <select class="form-select" name="bulan" id="bulan">
                    <option value="1" {{$bulan=='01' ? 'selected' : '' }}>Januari</option>
                    <option value="2" {{$bulan=='02' ? 'selected' : '' }}>Februari</option>
                    <option value="3" {{$bulan=='03' ? 'selected' : '' }}>Maret</option>
                    <option value="4" {{$bulan=='04' ? 'selected' : '' }}>April</option>
                    <option value="5" {{$bulan=='05' ? 'selected' : '' }}>Mei</option>
                    <option value="6" {{$bulan=='06' ? 'selected' : '' }}>Juni</option>
                    <option value="7" {{$bulan=='07' ? 'selected' : '' }}>Juli</option>
                    <option value="8" {{$bulan=='08' ? 'selected' : '' }}>Agustus</option>
                    <option value="9" {{$bulan=='09' ? 'selected' : '' }}>September</option>
                    <option value="10" {{$bulan=='10' ? 'selected' : '' }}>Oktober</option>
                    <option value="11" {{$bulan=='11' ? 'selected' : '' }}>November</option>
                    <option value="12" {{$bulan=='12' ? 'selected' : '' }}>Desember</option>
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="tahun" class="form-label">Tahun</label>
                <select class="form-select" name="tahun" id="tahun">
                    @foreach ($dataTahun as $d)
                    <option value="{{$d->tahun}}" {{$d->tahun == $tahun ? 'selected' : ''}}>{{$d->tahun}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3">
                <label for="tahun" class="form-label">&nbsp;</label>
                <button type="submit" class="btn btn-primary form-control" id="btn-cari">Tampilkan</button>
            </div>
        </div>
    </form>
</div>
<div class="container-fluid table-responsive ml-3">
    <div class="row mx-5">
        <div class="col-md-6 d-flex justify-content-start">
            <table>
                <tr>
                    <th>Customer</th>
                    <th>:</th>
                    <th>{{$project->customer->nama}}</th>
                </tr>
                <tr>
                    <th>Nama Project</th>
                    <th style="width: 1rem">:</th>
                    <th>{{$project->nama}}</th>
                </tr>
                <tr>
                    <th>Nomor Kontrak</th>
                    <th>:</th>
                    <th>{{$project->nomor_kontrak}}</th>
                </tr>

            </table>
        </div>
           <div class="col-md-6 d-flex justify-content-end">
            <table>
                <tr>
                    <th>Nilai Kontrak</th>
                    <th>:</th>
                    <th>Rp {{$project->nf_total_tagihan}}</th>
                </tr>
                <tr>
                    <th>Tanggal PO</th>
                    <th>:</th>
                    <th>{{$project->id_tanggal_mulai}}</th>
                </tr>
                <tr>
                    <th>Tanggal Jatuh Tempo</th>
                    <th style="width: 20px">:</th>
                    <th>{{$project->id_jatuh_tempo}}</th>
                </tr>

            </table>
        </div>
    </div>
    <div style="display: flex; justify-content: flex-end;">

    </div>

    <div class="row mt-3">

        <table class="table table-hover table-bordered" id="rekapTable">
            <thead class=" table-success">
            <tr>
                <th class="text-center align-middle">Tanggal</th>
                <th class="text-center align-middle">Uraian</th>
                <th class="text-center align-middle">Masuk</th>
                <th class="text-center align-middle">Keluar</th>
                <th class="text-center align-middle">Sisa</th>
                <th class="text-center align-middle">Transfer Ke Rekening</th>
                <th class="text-center align-middle">Bank</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
            <tr class="table-warning">
                <td colspan="3" class="text-center align-middle">Sisa
                    {{$stringBulan}} {{$tahunSebelumnya}}</td>
                <td></td>
                <td class="text-end align-middle">Rp. {{$dataSebelumnya ? $dataSebelumnya->nf_sisa : ''}}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            </thead>
            <tbody>
                @foreach ($data as $d)
                <tr>
                    <td class="text-center align-middle">{{$d->tanggal}}</td>
                    <td class="text-start align-middle">
                        {{$d->uraian}}
                    </td>
                    <td class="text-end align-middle">{{$d->jenis === 1 ?
                       $d->nf_nominal : ''}}
                    </td>
                    <td class="text-end align-middle text-danger">{{$d->jenis === 0 ?
                        $d->nf_nominal : ''}}
                    </td>
                    <td class="text-end align-middle">{{$d->nf_sisa}}</td>
                    <td class="text-center align-middle">{{$d->nama_rek}}</td>
                    <td class="text-center align-middle">{{$d->bank}}</td>
                    <td class="text-center align-middle">
                        @if ($d->void == 0)
                        <form action="{{route('rekap.kas-project.void', ['kasProject'=> $d])}}" method="post"
                            id="void-{{$d->id}}">
                            @csrf
                            <button type="submit" class="btn btn-danger">Void</button>
                        </form>
                        @endif
                    </td>
                </tr>
                <script>

                    $('#void-{{$d->id}}').submit(function(e){
                        e.preventDefault();
                        Swal.fire({
                            title: 'Apakah anda yakin?',
                            text: "Uraian: Void {{$d->uraian}}",
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
                    <td class="text-center align-middle" colspan="2"><strong>GRAND TOTAL</strong></td>
                    <td class="text-end align-middle"><strong>{{number_format($data->where('jenis',
                            1)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    <td class="text-end align-middle text-danger"><strong>{{number_format($data->where('jenis',
                            0)->sum('nominal'), 0, ',', '.')}}</strong></td>
                    {{-- latest saldo --}}
                    <td class="text-end align-middle">
                        <strong>
                            {{$data->last() ? number_format($data->last()->sisa, 0, ',', '.') : ''}}
                        </strong>
                    </td>
                    <td></td>
                    <td></td>
                    <td></td>
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
<script src="{{asset('assets/plugins/date-picker/date-picker.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>

    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": false,
            "searching": false,
            "scrollCollapse": true,
            "scrollY": "550px",
            "fixedColumns": {
                "leftColumns": 4,
                "rightColumns": 2
            },

        });

    });

</script>
@endpush
