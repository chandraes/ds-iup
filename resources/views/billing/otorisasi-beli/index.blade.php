@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Header & Navigasi --}}
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1 class="fw-bold mb-0">
                OTORISASI PEMBELIAN <br> {{$user->name}}
            </h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>

                </tr>
            </table>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="row">
        @if (!empty($data))
        @foreach ($data as $d)
        <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-start border-primary border-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        {{ $d->barang_unit?->nama ?? 'Supplier Tidak Dikenal' }} ({{$d->details_count}} Items)
                    </h5>
                </div>
                <div class="card-body">
                    <p class="card-text mb-1">
                        <span class="badge bg-info text-dark me-1">{{ $d->sistem_pembayaran_text }}</span>
                        <span class="badge bg-secondary">{{ $d->kas_ppn_text }}</span>
                    </p>

                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <form action="{{ route('billing.form-beli.delete', $d->id) }}" method="POST"
                            class="d-flex gap-2 returDeleteForm" id="returDelete-{{ $d->id }}">
                            @csrf
                            <a class="btn btn-primary btn-sm" href="{{ route('billing.otorisasi-pembelian.keranjang', $d->id) }}">
                                <i class="fa fa-play me-1"></i> Lanjutkan
                            </a>

                            <button type="submit" class="btn btn-danger btn-sm delete-btn"
                                data-form-id="returDelete-{{ $d->id }}">
                                <i class="fa fa-trash-alt me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
        @else
        <div class="col-12">
            <div class="alert alert-info text-center" role="alert">
                <i class="fa fa-info-circle me-1"></i> Tidak ada transaksi pembelian yang belum selesai.
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
