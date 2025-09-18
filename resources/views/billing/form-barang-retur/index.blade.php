@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>FORM BARANG RETUR</u></h1>
        </div>
    </div>
    @include('swal')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="container mt-3 table-responsive ">
    <div class="row">
        @if (!empty($data))
        @foreach ($data as $d)
        <div class="col-md-3">
            <div class="card mb-3">
                <div class="card-header">
                    <strong>
                        {{ $d->barang_unit->nama }}
                        <span class="badge bg-secondary">{{ $d->tipe == 1 ? 'Dari Supplier' : 'Dari Konsumen' }}</span>
                    </strong>
                </div>
                {{-- button lanjutkan and button delete --}}
                <div class="card-body">

                    <form action="{{ route('billing.form-barang-retur.delete', $d->id) }}" method="POST" class="d-inline" id="returDelete">
                        @csrf
                        <a class="btn btn-primary btn-sm mb-2" href="{{ route('billing.form-barang-retur.detail', $d->id) }}"><i class="fa fa-rotate-left me-2"></i>Lanjutkan</a>
                        <span class="">|</span>
                        <button type="submit" class="btn btn-danger mb-2 btn-sm gap-2"><i class="fa fa-trash me-2"></i> Hapus</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
        @endif
    </div>
    <div class="row">

        <form action="{{ route('billing.form-barang-retur.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tipe" value="{{ $tipe }}">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="supplier_id" class="form-label">Supplier</label>
                    <select name="barang_unit_id" id="supplier_id" class="form-select select2">
                        <option value="">Pilih Supplier</option>
                        @foreach ($supplier as $s)
                        <option value="{{ $s->id }}">{{ $s->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($tipe == 2)
                  <div class="col-md-4 mb-3">
                    <label for="konsumen_id" class="form-label">Konsumen</label>
                    <select name="konsumen_id" id="konsumen_id" class="form-select select2">
                        <option value="">Pilih Konsumen</option>
                        @foreach ($konsumen as $k)
                        <option value="{{ $k->id }}">{{ $k->kode_toko->kode }} {{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>

                @endif
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    <a href="{{ route('billing') }}" class="btn btn-secondary ms-2">Kembali</a>
                </div>
            </div>

        </form>
    </div>
</div>
</div>

@endsection
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
@endpush
@push('js')
<script src="{{asset('assets/js/bootstrap-bundle.js')}}"></script>
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#konsumen_id').select2({
            placeholder: 'Pilih Konsumen',
            theme: 'bootstrap-5',
            allowClear: true
        });

        $('#supplier_id').select2({
            placeholder: 'Pilih Supplier',
            theme: 'bootstrap-5',
            allowClear: true
        });
    });

    confirmAndSubmit("#returDelete", "Apakah anda yakin?");

</script>
@endpush
