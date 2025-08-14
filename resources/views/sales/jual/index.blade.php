@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>STOK & HARGA JUAL BARANG</u></h1>
        </div>
    </div>
    @include('swal')

    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    {{-- <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td> --}}
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
                        {{ $d->konsumen->kode_toko->kode }} - {{ $d->konsumen->nama }}
                        <span class="badge bg-secondary">{{ $d->pembayaran == 1 ? 'Cash' : ($d->pembayaran == 2 ? 'Tempo' : 'Titipan') }}</span>
                    </strong>
                </div>
                {{-- button lanjutkan and button delete --}}
                <div class="card-body">

                    <form action="{{ route('sales.jual.keranjang.delete', $d->id) }}" method="POST" class="d-inline">
                        @csrf
                        <a class="btn btn-primary btn-sm mb-2" href="{{ route('sales.jual.keranjang', $d->id) }}"><i class="fa fa-shopping-cart me-2"></i>Lanjutkan</a>
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

        <form action="{{ route('sales.jual.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="konsumen_id" class="form-label">Konsumen</label>
                    <select name="konsumen_id" id="konsumen_id" class="form-select select2">

                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="sistem_pembayaran" class="form-label">Sistem Pembayaran</label>
                    <select name="pembayaran" id="sistem_pembayaran" class="form-select">
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="tanggal" class="form-label">Kecamatan</label>
                    <input type="text" name="alamat" id="alamat" class="form-control" disabled>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                    <a href="{{ route('home') }}" class="btn btn-secondary ms-2">Kembali</a>
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
            minimumInputLength: 3,
            width: '100%',
            allowClear: true,
            theme: 'bootstrap-5', // tambahkan theme di sini
            ajax: {
                url: '{{ route("universal.search-konsumen") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.data.map(function(item) {
                            return {
                                id: item.id,
                                text: item.kode_toko.kode + ' ' + item.nama,
                                pembayaran: item.pembayaran,
                                kecamatan: item.kecamatan.nama_wilayah
                            };
                        })
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            var data = e.params.data;
            // Set alamat berdasarkan konsumen yang dipilih
            $('#alamat').val(data.kecamatan);

            // Load sistem pembayaran berdasarkan konsumen yang dipilih
            loadSistemPembayaran(data.pembayaran);
        });

        function loadSistemPembayaran(pembayaran) {
            var options = '';

            if (pembayaran == 2) {
                options += '<option value="">Pilih Pembayaran</option>';
                options += '<option value="1">Cash</option>';
                options += '<option value="2">Tempo</option>';
            } else if (pembayaran == 1) {
                options += '<option value="1">Cash</option>';
            }

            $('#sistem_pembayaran').html(options).select2({
                theme: 'bootstrap-5',
                placeholder: 'Pilih Sistem Pembayaran',
                allowClear: true
            });


        }
    });
</script>
@endpush
