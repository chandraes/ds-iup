@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>RENCANA STOK BAHAN JADI</u></h1>
        </div>
    </div>
    {{-- back button --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{route('billing')}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered table-sm" id="barangJadi">
                <thead class="table-primary">
                    <tr>
                        <th class="text-center align-middle">
                            NO
                        </th>
                        <th class="text-center align-middle">
                            KATEGORI<br>PRODUCT
                        </th>
                        <th class="text-center align-middle">
                            JENIS<br>PRODUCT
                        </th>
                        <th class="text-center align-middle">
                            KODE<br>PRODUKSI
                        </th>
                        <th class="text-center align-middle">
                            TANGGAL<br>PRODUKSI
                        </th>
                        <th class="text-center align-middle">
                            TANGGAL<br>EXPIRED
                        </th>
                        <th class="text-center align-middle">
                            RENCANA<br>KEMASAN
                        </th>
                        <th class="text-center align-middle">
                            RENCANA<br>PACKAGING
                        </th>
                        <th class="text-center align-middle">
                            JUMLAH<br>PRODUKSI
                        </th>
                        <th class="text-center align-middle">
                            REAL<br>KEMASAN
                        </th>
                        <th class="text-center align-middle">
                            REAL<br>PACKAGING
                        </th>
                        <th class="text-center align-middle">
                            ACT
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $group)
                    @php $i = 0; @endphp
                    @foreach($group as $d)
                    <tr>
                        @if($i++ == 0)
                        <td class="text-center align-middle" rowspan="{{ count($group) }}">{{$loop->iteration}}</td>
                        <td class="text-center align-middle" rowspan="{{ count($group) }}">
                            {{$d->product->kategori->nama}}</td>
                        <td class="text-center align-middle" rowspan="{{ count($group) }}">{{$d->product->nama}}</td>
                        @endif
                        <td class="text-center align-middle">{{$d->kode_produksi}}</td>
                        <td class="text-center align-middle">{{$d->id_tanggal_produksi}}</td>
                        <td class="text-center align-middle">{{$d->id_tanggal_expired}}</td>
                        <td class="text-center align-middle">{{$d->rencana_kemasan}}</td>
                        <td class="text-center align-middle">{{$d->rencana_packaging}}</td>
                        <td class="text-center align-middle">
                            @if ($d->produksi_detail && $d->produksi_detail->count() > 0)
                            {{$d->produksi_detail->count()}}
                            @else
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#modalId">
                                Tambah
                            </button>
                            <div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static"
                                data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm"
                                    role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalTitleId">
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{route('billing.stok-bahan-jadi.produksi-ke', $d)}}"
                                            method="post" id="productForm{{$d->id}}">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="jumlah_produksi" class="form-label">Masukan Jumlah
                                                        Produksi</label>
                                                    <input type="number" class="form-control" name="jumlah_produksi"
                                                        id="jumlah_produksi" aria-describedby="helpId" placeholder="" />

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
                            @endif
                        </td>
                        <td class="text-center align-middle">
                            {{$d->sum_kemasan ?? 0}}
                        </td>
                        <td class="text-center align-middle">
                            {{$d->real_packaging}}
                        </td>
                        <td class="text-center align-middle">
                            <a href="{{route('billing.stok-bahan-jadi.edit-produksi-ke', $d)}}"
                                class="btn btn-sm btn-warning my-2">Edit</a>
                            <button class="btn btn-sm btn-primary my-2" data-bs-toggle="modal"
                                data-bs-target="#pass{{$d->id}}">OK</button>
                            <div class="modal fade" id="pass{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
                                data-bs-keyboard="false" role="dialog" aria-labelledby="title{{$d->id}}"
                                aria-hidden="true">
                                <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="title{{$d->id}}">
                                                Masukan Password
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form action="{{route('billing.stok-bahan-jadi.rencana.lanjutkan', $d)}}" method="post" id="passwordForm{{$d->id}}">
                                            @csrf

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <input type="password" class="form-control" name="password"
                                                        id="password" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                Batalkan
                                            </button>
                                            <button type="submit" class="btn btn-primary">Lanjutkan</button>
                                        </div>
                                    </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('js')

<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>

</script>
@endpush
